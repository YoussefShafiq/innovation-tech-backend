<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class SettingsResource extends JsonResource
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
            'our_mission' => $this->getTranslation('our_mission', $locale),
            'our_vision' => $this->getTranslation('our_vision', $locale),
            'years' => $this->years,
            'projects' => $this->projects,
            'clients' => $this->clients,
            'engineers' => $this->engineers,
            'story' => [
                'title' => $this->getTranslation('story_title', $locale),
                'subtitle' => $this->getTranslation('story_subtitle', $locale),
                'description' => $this->getTranslation('story_description', $locale),
                'bullets' => $this->getTranslation('story_bullets', $locale),
            ],
            'email' => $this->email,
            'phone' => $this->phone,
            'address' => $this->getTranslation('address', $locale),
            'business_hours' => $this->getTranslation('business_hours', $locale),
            'emergency_support' => $this->getTranslation('emergency_support', $locale),
            // For admin dashboard editing
            'translations' => $this->translations->groupBy('locale')->map(function ($items) {
                return $items->pluck('value', 'field');
            }),
        ];
    }
}
