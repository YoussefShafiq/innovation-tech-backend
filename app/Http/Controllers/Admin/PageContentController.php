<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Resources\PageContentResource;
use App\Models\PageContent;
use App\Traits\ResponseTrait;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Validator;

class PageContentController extends Controller
{
    use ResponseTrait;

    public function show(string $pageKey)
    {
        $this->authorize('view_pages');

        $page = PageContent::findByKey($pageKey);
        if (!$page) {
            return $this->error('Page content not found', 404);
        }

        return $this->success(new PageContentResource($page), 'Page content retrieved successfully');
    }

    public function update(Request $request, string $pageKey)
    {
        $this->authorize('edit_pages');

        $page = PageContent::findByKey($pageKey);
        if (!$page) {
            return $this->error('Page content not found', 404);
        }

        $validator = Validator::make($request->all(), [
            'content' => 'required|array',
            'translations' => 'nullable|array',
            'translations.ar' => 'nullable|array',
            'translations.ar.content' => 'nullable|array',
        ]);

        if ($validator->fails()) {
            return $this->error($validator->errors()->first(), 422);
        }

        try {
            $page->update([
                'content' => $request->input('content'),
            ]);

            if ($request->has('translations.ar.content')) {
                $arContent = $request->input('translations.ar.content');
                $page->setTranslation(
                    'content',
                    'ar',
                    is_array($arContent) ? json_encode($arContent, JSON_UNESCAPED_UNICODE) : $arContent
                );
            }

            return $this->success(
                new PageContentResource($page->fresh()),
                'Page content updated successfully'
            );
        } catch (\Exception $e) {
            Log::error('Page content update failed', [
                'page_key' => $pageKey,
                'error' => $e->getMessage(),
            ]);
            return $this->error('Operation failed', 500);
        }
    }
}
