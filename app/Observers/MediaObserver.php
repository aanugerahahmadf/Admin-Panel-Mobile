<?php

namespace App\Observers;

use App\Models\WeddingOrganizer;
use App\Services\CBIRService;
use Spatie\MediaLibrary\MediaCollections\Models\Media;

class MediaObserver
{
    protected $cbirService;

    public function __construct(CBIRService $cbirService)
    {
        $this->cbirService = $cbirService;
    }

    public function created(Media $media)
    {
        $targetCollections = ['gallery', 'product_image', 'package_image', 'category_image'];
        
        if (in_array($media->collection_name, $targetCollections)) {
            // Index the image for CBIR
            $this->cbirService->indexMedia($media);
        }
    }

    public function deleted(Media $media)
    {
        // Remove from CBIR index when media is deleted
        $this->cbirService->removeFromIndex($media->id);
    }
}
