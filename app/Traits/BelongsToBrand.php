<?php

namespace App\Traits;

use App\Models\WeddingOrganizer;
use Illuminate\Database\Eloquent\Builder;

trait BelongsToBrand
{
    /**
     * Boot the trait to enforce single-brand logic.
     */
    public static function bootBelongsToBrand(): void
    {
        // Automatically assign the brand ID when creating a new record
        static::creating(function ($model) {
            if (empty($model->wedding_organizer_id)) {
                $brandId = WeddingOrganizer::getBrand()?->id;
                if ($brandId) {
                    $model->wedding_organizer_id = $brandId;
                }
            }
        });

        // Global scope to filter all queries to the primary brand
        static::addGlobalScope('brand', function (Builder $builder) {
            $brandId = WeddingOrganizer::getBrand()?->id;
            if ($brandId) {
                $builder->where($builder->getModel()->getTable().'.wedding_organizer_id', $brandId);
            }
        });
    }

    /**
     * Get the wedding organizer associated with the model.
     */
    public function weddingOrganizer()
    {
        return $this->belongsTo(WeddingOrganizer::class, 'wedding_organizer_id');
    }
}
