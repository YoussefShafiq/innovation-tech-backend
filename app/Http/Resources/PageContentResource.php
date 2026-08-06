<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class PageContentResource extends JsonResource
{
    /**
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        $isAdmin = str_contains($request->path(), 'admin/pages');

        if ($isAdmin) {
            $arContent = $this->getTranslation('content', 'ar');
            if (is_string($arContent)) {
                $decoded = json_decode($arContent, true);
                $arContent = is_array($decoded) ? $decoded : $arContent;
            }

            return [
                'page_key' => $this->page_key,
                'content' => $this->content,
                'translations' => [
                    'ar' => [
                        'content' => is_array($arContent) ? $arContent : ($this->content ?? []),
                    ],
                ],
                'updated_at' => $this->updated_at,
            ];
        }

        $locale = $request->query('locale', 'en');
        $content = $this->getTranslation('content', $locale);
        if (is_string($content)) {
            $decoded = json_decode($content, true);
            $content = is_array($decoded) ? $decoded : [];
        }

        return [
            'page_key' => $this->page_key,
            'content' => $content,
        ];
    }
}
