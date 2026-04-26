<?php

namespace App\Livewire\Messages;

use App\Models\Message;
use Illuminate\Contracts\View\Factory;
use Illuminate\Contracts\View\View;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Foundation\Application;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Auth;
use Livewire\Attributes\On;
use Livewire\Component;

/**
 * @mixin Component
 */
class Search extends Component
{
    public $search = '';

    public Collection $messages;

    public string $panelId = 'admin';

    public function mount(): void
    {
        $this->panelId = filament()->getCurrentPanel()?->getId() ?? 'admin';
        $this->messages = collect();
    }

    #[On('close-modal')]
    public function clearSearch(): void
    {
        $this->search = '';
        $this->updatedSearch();
    }

    public function updatedSearch(): void
    {
        $search = trim($this->search);
        $this->messages = collect();
        if (! empty($search)) {
            /** @var Builder $query */
            $query = Message::query();

            $this->messages = $query->with(['inbox'])
                ->whereHas('inbox', function (Builder $q): void {
                    $q->whereJsonContains('user_ids', Auth::id(), 'and', false);
                })
                ->where('message', 'like', "%$search%")
                ->limit(5)
                ->latest()
                ->get(['*']);
        }
    }

    public function render(): Application|Factory|View|\Illuminate\View\View
    {
        return view('livewire.messages.search', [
            'messages' => $this->messages,
        ]);
    }
}
