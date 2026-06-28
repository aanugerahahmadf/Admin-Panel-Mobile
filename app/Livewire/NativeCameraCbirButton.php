<?php

namespace App\Livewire;

use App\Models\Package;
use App\Models\Product;
use App\Providers\NativeServiceProvider;
use App\Services\CBIRService;
use Filament\Notifications\Notification;
use Livewire\Attributes\On;
use Livewire\Component;
use Livewire\Features\SupportFileUploads\TemporaryUploadedFile;
use Livewire\WithFileUploads;
use Native\Mobile\Events\Camera\PhotoCancelled;
use Native\Mobile\Events\Camera\PermissionDenied;
use Native\Mobile\Events\Camera\PhotoTaken;
use Native\Mobile\Events\Camera\VideoCancelled;
use Native\Mobile\Events\Camera\VideoRecorded;
use Native\Mobile\Facades\Camera;
use Native\Mobile\Facades\File as NativeFile;
use Illuminate\Support\Facades\Storage;
use Symfony\Component\HttpFoundation\File\File;

class NativeCameraCbirButton extends Component
{
    use WithFileUploads;

    public bool $isLoading = false;

    public ?TemporaryUploadedFile $cameraUpload = null;

    public array $recentUploads = [];

    public ?string $statusMessage = null;

    private const IMAGE_EXTENSIONS = ['jpg', 'jpeg', 'png', 'webp', 'heic'];

    private const VIDEO_EXTENSIONS = ['mp4', 'mov', 'avi', 'mkv'];

    private const CAMERA_ACCEPT = 'image/jpeg,image/png,image/webp,image/heic,image/heif,video/mp4,video/quicktime,video/x-msvideo,video/x-matroska,.jpg,.jpeg,.png,.webp,.heic,.mp4,.mov,.avi,.mkv';

    public function openCamera(string $mode = 'photo-back'): void
    {
        if (! NativeServiceProvider::isNativeMobile()) {
            $this->dispatch('native-camera-open-input', mode: $mode, componentId: $this->getId());

            return;
        }

        $this->isLoading = true;
        $this->statusMessage = __('Membuka kamera...');

        match ($mode) {
            'video' => Camera::recordVideo(['maxDuration' => 120])->id('cbir-camera-video')->start(),
            'gallery' => Camera::pickImages('all', false)->id('cbir-camera-gallery')->start(),
            default => Camera::getPhoto(['camera' => $mode === 'photo-front' ? 'front' : 'rear'])
                ->id('cbir-camera-photo')
                ->start(),
        };
    }

    public function updatedCameraUpload(): void
    {
        if (! $this->cameraUpload) {
            return;
        }

        $this->isLoading = true;
        $this->storeAndProcessUploadedFile($this->cameraUpload, 'cbir-camera');
        $this->cameraUpload = null;
        $this->isLoading = false;
    }

    #[On('native:'.PhotoTaken::class)]
    public function onPhotoTaken(string $path, string $mimeType = 'image/jpeg'): void
    {
        $this->isLoading = false;

        $this->storeAndProcessNativeFile($path, $mimeType, 'cbir-camera');
    }

    #[On('native:'.VideoRecorded::class)]
    public function onVideoRecorded(string $path, string $mimeType = 'video/mp4', ?string $id = null): void
    {
        $this->isLoading = false;

        $this->storeAndProcessNativeFile($path, $mimeType, 'cbir-camera');
    }

    #[On('native:'.\Native\Mobile\Events\Gallery\MediaSelected::class)]
    public function onMediaSelected(bool $success, array $files = [], int $count = 0, ?string $error = null, bool $cancelled = false): void
    {
        $this->isLoading = false;

        if ($cancelled || ! $success || empty($files)) {
            return;
        }

        $file = $files[0];
        $path = is_string($file) ? $file : ($file['path'] ?? null);
        $mimeType = is_array($file) ? ($file['mimeType'] ?? $file['mime_type'] ?? null) : null;

        if ($path) {
            $this->storeAndProcessNativeFile($path, $mimeType, 'cbir-camera');
        }
    }

    #[On('native:'.PhotoCancelled::class)]
    #[On('native:'.VideoCancelled::class)]
    public function onCaptureCancelled(?string $id = null): void
    {
        $this->isLoading = false;
        $this->statusMessage = null;
    }

    #[On('native:'.PermissionDenied::class)]
    public function onPermissionDenied(string $action, ?string $id = null): void
    {
        $this->isLoading = false;

        Notification::make()
            ->title(__('Izin Kamera Diperlukan'))
            ->body(__('Harap aktifkan izin kamera di Pengaturan > Izin Aplikasi.'))
            ->warning()
            ->send();
    }

    private function storeAndProcessUploadedFile(TemporaryUploadedFile $file, string $directory): void
    {
        $extension = strtolower($file->getClientOriginalExtension() ?: $file->extension() ?: '');

        if (! $this->isAllowedExtension($extension)) {
            $this->notifyUnsupportedFile();

            return;
        }

        $path = $file->store($directory, 'public');
        $absolutePath = Storage::disk('public')->path($path);
        $this->rememberUpload($path, $file->getMimeType());

        if ($this->isImageExtension($extension)) {
            $this->runCbirSearch($absolutePath);
        } else {
            $this->statusMessage = __('Video berhasil diunggah ke Storage.');
        }
    }

    private function storeAndProcessNativeFile(string $path, ?string $mimeType, string $directory): void
    {
        if (! file_exists($path)) {
            $this->statusMessage = __('File tidak ditemukan.');

            return;
        }

        $extension = strtolower(pathinfo($path, PATHINFO_EXTENSION));

        if (! $this->isAllowedExtension($extension)) {
            $this->notifyUnsupportedFile();

            return;
        }

        $storedPath = $directory.'/'.uniqid('native-', true).'.'.$extension;
        $destination = Storage::disk('public')->path($storedPath);

        if (! is_dir(dirname($destination))) {
            mkdir(dirname($destination), 0755, true);
        }

        $copied = NativeServiceProvider::isNativeMobile()
            ? NativeFile::copy($path, $destination)
            : copy($path, $destination);

        if (! $copied) {
            $this->statusMessage = __('Gagal menyalin file ke Storage.');

            return;
        }

        $this->rememberUpload($storedPath, $mimeType);

        if ($this->isImageExtension($extension)) {
            $this->runCbirSearch($destination);
        } else {
            $this->statusMessage = __('Video berhasil diunggah ke Storage.');
        }
    }

    private function runCbirSearch(string $absolutePath): void
    {
        if (! file_exists($absolutePath)) {
            return;
        }

        $this->statusMessage = __('Mencari dekorasi...');
        $cbirService = app(CBIRService::class);
        $response = $cbirService->searchByImage(new File($absolutePath), 20);

        if (isset($response['error']) || ! ($response['success'] ?? false)) {
            $this->statusMessage = $response['message'] ?? __('File berhasil diunggah, tetapi pencarian gagal.');

            return;
        }

        $results = $response['results'] ?? [];

        if (! empty($results)) {
            $mixedResults = $this->buildCbirMixedResults($results);

            if (! empty($mixedResults)) {
                session()->put('cbir_mixed_results', $mixedResults);
                session()->put('cbir_package_results_ids', collect($mixedResults)->where('type', 'package')->pluck('data.id')->all());
                session()->put('cbir_search_time', $response['query_time_seconds'] ?? 0);
                session()->put('cbir_context', 'package');
                $this->statusMessage = __('Upload selesai. Hasil CBIR siap.');

                return;
            }
        }

        $this->statusMessage = __('Upload selesai. Tidak ada hasil CBIR yang cocok.');
    }

    private function rememberUpload(string $path, ?string $mimeType): void
    {
        array_unshift($this->recentUploads, [
            'name' => basename($path),
            'url' => Storage::disk('public')->url($path),
            'mime' => $mimeType ?: Storage::disk('public')->mimeType($path),
            'path' => $path,
        ]);

        $this->recentUploads = array_slice($this->recentUploads, 0, 3);
    }

    private function isAllowedExtension(string $extension): bool
    {
        return $this->isImageExtension($extension) || in_array($extension, self::VIDEO_EXTENSIONS, true);
    }

    private function isImageExtension(string $extension): bool
    {
        return in_array($extension, self::IMAGE_EXTENSIONS, true);
    }

    private function notifyUnsupportedFile(): void
    {
        $this->statusMessage = __('Format file tidak didukung.');

        Notification::make()
            ->title(__('Format Tidak Didukung'))
            ->body(__('Gunakan JPG, JPEG, PNG, WEBP, HEIC, MP4, MOV, AVI, atau MKV.'))
            ->warning()
            ->send();
    }

    private function buildCbirMixedResults(array $results): array
    {
        $mixed = [];
        $seen = [];

        foreach ($results as $r) {
            $type = $r['type'] ?? 'package';
            $id = $r['owner_id'] ?? $r['id'] ?? null;

            if (! $id) {
                continue;
            }

            if (($r['similarity'] ?? 0) <= 0) {
                continue;
            }

            $key = "{$type}_{$id}";

            if (isset($seen[$key])) {
                continue;
            }

            $model = $type === 'package'
                ? Package::with(['category'])->find($id)
                : Product::with(['category'])->find($id);

            if (! $model) {
                continue;
            }

            $mixed[] = [
                'type' => $type,
                'similarity' => $r['similarity'] ?? (($r['score'] ?? 0) * 100),
                'data' => array_merge($model->toArray(), [
                    'image_url' => $model->image_url,
                    'category' => $model->category?->toArray(),
                    'rating' => number_format($model->reviews()->avg('rating') ?: 0, 1),
                    'stock' => $model->stock ?? 0,
                ]),
            ];

            $seen[$key] = true;
        }

        usort($mixed, fn ($a, $b) => ($b['similarity'] ?? 0) <=> ($a['similarity'] ?? 0));

        return $mixed;
    }

    public function render()
    {
        return view('livewire.native-camera-cbir-button', [
            'cameraAccept' => self::CAMERA_ACCEPT,
            'isNative' => NativeServiceProvider::isNativeMobile(),
        ]);
    }
}
