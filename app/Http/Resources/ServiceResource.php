<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class ServiceResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        $locale = $request->query('locale', 'en');

        return [
            'id' => $this->encoded_id,
            'title' => $this->getTranslation('title', $locale),
            'description' => $this->getTranslation('description', $locale),
            'slug' => $this->slug,
            'icon' => $this->icon,
            'tags' => $this->tags ? explode(',', $this->tags) : [],
            'is_active' => $this->is_active,
            'author' => [
                'id' => $this->author?->encoded_id,
                'name' => $this->author?->name,
                'email' => $this->author?->email,
            ],
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,
            // For admin dashboard editing
            'translations' => $this->translations->groupBy('locale')->map(function ($items) {
                return $items->pluck('value', 'field');
            }),
        ];
    }
}
