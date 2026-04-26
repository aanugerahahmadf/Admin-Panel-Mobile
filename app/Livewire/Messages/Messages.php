<?php

namespace App\Livewire\Messages;

use App\Enums\Messages\MediaCollectionType;
use App\Filament\Admin\Pages\MessagesPage;
use App\Jobs\SendBotReply;
use App\Livewire\Traits\CanMarkAsRead;
use App\Livewire\Traits\CanValidateFiles;
use App\Livewire\Traits\HasPollInterval;
use App\Models\Message;
use emmanpbarrameda\FilamentTakePictureField\Forms\Components\TakePicture;
use Filament\Facades\Filament;
use Filament\Forms;
use Filament\Forms\Concerns\InteractsWithForms;
use Filament\Forms\Contracts\HasForms;
use Filament\Forms\Form;
use Filament\Notifications\Notification;
use Illuminate\Contracts\Pagination\Paginator;
use Illuminate\Contracts\View\Factory;
use Illuminate\Contracts\View\View;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Foundation\Application;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Livewire\Attributes\Computed;
use Livewire\Component;
use Livewire\WithPagination;
use Spatie\MediaLibrary\MediaCollections\Models\Media;
use TangoDevIt\FilamentEmojiPicker\EmojiPickerAction;

/**
 * @mixin Component
 */
class Messages extends Component implements HasForms
{
    use CanMarkAsRead, CanValidateFiles, HasPollInterval, InteractsWithForms, WithPagination;

    public $selectedConversation;

    public $currentPage = 1;

    public Collection $conversationMessages;

    public ?array $data = [];

    public bool $showUpload = false;

    public bool $showEmojiPicker = false;

    public bool $showCamera = false;

    public string $panelId = 'admin';

    public function mount(): void
    {
        $this->panelId = filament()->getCurrentPanel()?->getId() ?? 'admin';
        $this->setPollInterval();
        $this->form->fill();
        if ($this->selectedConversation) {
            $this->conversationMessages = collect();
            $this->loadMessages();
            $this->markAsRead();
        }
    }

    public function pollMessages(): void
    {
        if (! $this->selectedConversation) {
            return;
        }

        $latestId = $this->conversationMessages->pluck('id')->first();

        /** @var Builder $query */
        $query = $this->selectedConversation->messages();

        $polledMessages = $query->where('id', '>', $latestId ?? 0)->latest()->get(['*']);
        if ($polledMessages->isNotEmpty()) {
            $this->conversationMessages = collect([
                ...$polledMessages,
                ...$this->conversationMessages,
            ]);

            // Mark new incoming messages as read
            $this->markAsRead();
        }

        // Optional: Check if the status of the sender's last unread message has changed to 'read'
        // Since we are polling, we need to refresh the messages to see the 'Dilihat' status
        // if the other person has read it in the meantime.
        $unreadOutgoingExists = $this->conversationMessages
            ->where('user_id', auth()->id())
            ->filter(fn ($msg) => empty($msg->read_by) || count(array_filter($msg->read_by, fn ($id) => $id !== auth()->id())) === 0)
            ->isNotEmpty();

        if ($unreadOutgoingExists) {
            // Re-fetch the message objects to get updated read_by status
            $this->conversationMessages = $this->conversationMessages->map(function ($msg) {
                // Only re-fetch if it was previously unread by others
                $wasUnread = empty($msg->read_by) || count(array_filter($msg->read_by, fn ($id) => $id !== auth()->id())) === 0;
                if ($wasUnread && $msg->user_id === auth()->id()) {
                    return Message::find($msg->id);
                }

                return $msg;
            });
        }
    }

    public function loadMessages(): void
    {
        $this->conversationMessages->push(...$this->paginator->items());
        $this->currentPage += 1;
    }

    public function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\SpatieMediaLibraryFileUpload::make('attachments')
                    ->hiddenLabel()
                    ->collection(MediaCollectionType::FILAMENT_MESSAGES->value)
                    ->multiple()
                    ->panelLayout('grid')
                    ->visible(fn () => $this->showUpload)
                    ->maxFiles(config('messages.attachments.max_files'))
                    ->minFiles(config('messages.attachments.min_files'))
                    ->maxSize(config('messages.attachments.max_file_size'))
                    ->minSize(config('messages.attachments.min_file_size'))
                    ->live(),
                Forms\Components\Split::make([
                    Forms\Components\Actions::make([
                        Forms\Components\Actions\Action::make('show_hide_upload')
                            ->hiddenLabel()
                            ->icon('heroicon-o-paper-clip')
                            ->color('gray')
                            ->tooltip(__('Attach Files'))
                            ->action(fn () => $this->showUpload = ! $this->showUpload),
                        Forms\Components\Actions\Action::make('toggle_camera')
                            ->hiddenLabel()
                            ->icon('heroicon-o-camera')
                            ->color('gray')
                            ->tooltip(__('Open Camera'))
                            ->action(fn () => $this->showCamera = ! $this->showCamera),
                    ])->grow(false),
                    Forms\Components\TextInput::make('message')
                        ->live()
                        ->hiddenLabel()
                        ->placeholder(__('Write a message...'))
                        ->suffixAction(EmojiPickerAction::make('emoji-message')),
                ])->verticallyAlignEnd(),
                TakePicture::make('camera_image')
                    ->hiddenLabel()
                    ->visible(fn () => $this->showCamera)
                    ->disk('public') // Bisa disesuaikan dengan disk aplikasi Anda
                    ->directory('messages-camera') // Folder penyimpanan sementara gambar
                    ->visibility('public')
                    ->showCameraSelector(true)
                    ->aspect('16:9')
                    ->imageQuality(80),
            ])->statePath('data');
    }

    public function sendMessage(): void
    {
        $data = $this->form->getState();
        $rawData = $this->form->getRawState();

        try {
            DB::transaction(function () use ($data, $rawData): void {
                $this->showUpload = false;

                $newMessage = $this->selectedConversation->messages()->create([
                    'message' => $data['message'] ?? null,
                    'user_id' => Auth::id(),
                    'read_by' => [Auth::id()],
                    'read_at' => [now()],
                    'notified' => [Auth::id()],
                ]);

                // Dispatch bot reply if user is not admin
                if (! auth()->user()->hasRole('super_admin')) {
                    SendBotReply::dispatch($newMessage->id)->delay(now()->addSeconds(5));
                }

                $this->conversationMessages->prepend($newMessage);
                collect($rawData['attachments'] ?? [])->each(function ($attachment) use ($newMessage): void {
                    $newMessage->addMedia($attachment)->usingFileName(Str::slug(config('messages.slug'), '_').'_'.Str::random(20).'.'.$attachment->extension())->toMediaCollection(MediaCollectionType::FILAMENT_MESSAGES->value);
                });

                if (! empty($data['camera_image'])) {
                    // Ambil config 'disk' aplikasi/gambar - jika pakai default public
                    $newMessage->addMediaFromDisk($data['camera_image'], 'public')
                        ->toMediaCollection(MediaCollectionType::FILAMENT_MESSAGES->value);
                }

                $this->showCamera = false;
                $this->form->fill();

                $this->selectedConversation->updated_at = now();

                $this->selectedConversation->save();

                $this->dispatch('refresh-inbox');
            });
        } catch (\Exception $exception) {
            Notification::make()
                ->title(__('Something went wrong'))
                ->body($exception->getMessage())
                ->danger()
                ->persistent()
                ->send();
        }
    }

    #[Computed()]
    public function paginator(): Paginator
    {
        /** @var Builder $query */
        $query = $this->selectedConversation->messages();

        return $query->latest()->paginate(10, ['*'], 'page', $this->currentPage);
    }

    public function downloadAttachment(int $mediaId)
    {
        $media = Media::findOrFail($mediaId);

        return response()->download($media->getPath(), $media->file_name);
    }

    public function validateMessage(): bool
    {
        $rawData = $this->form->getRawState();

        $hasAttachments = ! empty($rawData['attachments']);
        $hasCameraImage = ! empty($rawData['camera_image']);
        $hasMessage = ! empty($rawData['message']);

        // Return true (disabled) only if ALL are empty
        return ! ($hasAttachments || $hasCameraImage || $hasMessage);
    }

    public function deleteConversation()
    {
        if ($this->selectedConversation && in_array(Auth::id(), $this->selectedConversation->user_ids)) {
            $this->selectedConversation->delete();

            Notification::make()
                ->title(__('Conversation deleted'))
                ->success()
                ->send();

            $isAdmin = Filament::getCurrentPanel()?->getId() === 'admin';
            $redirectUrl = $isAdmin
                ? MessagesPage::getUrl()
                : \App\Filament\User\Pages\MessagesPage::getUrl();

            return $this->redirect($redirectUrl);
        }
    }

    public function render(): Application|Factory|View|\Illuminate\View\View
    {
        return view('livewire.messages.messages');
    }
}
