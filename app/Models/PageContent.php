<?php

namespace App\Models;

use App\Traits\Translatable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PageContent extends Model
{
    use HasFactory, Translatable;

    public $translatable = ['content'];

    protected $fillable = [
        'page_key',
        'content',
    ];

    protected $casts = [
        'content' => 'array',
    ];

    public static function findByKey(string $pageKey): ?self
    {
        return static::where('page_key', $pageKey)->first();
    }

    public static function findByKeyOrFail(string $pageKey): self
    {
        return static::where('page_key', $pageKey)->firstOrFail();
    }
}
