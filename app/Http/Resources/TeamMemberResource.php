<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class TeamMemberResource extends JsonResource
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
            'name' => $this->getTranslation('name', $locale),
            'title' => $this->getTranslation('title', $locale),
            'image' => $this->image ? url('/storage/' . $this->image) : null,
            'is_active' => $this->is_active,
            // For admin dashboard editing
            'translations' => $this->translations->groupBy('locale')->map(function ($items) {
                return $items->pluck('value', 'field');
            }),
        ];
    }
}
