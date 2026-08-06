<?php

namespace App\Models;

use App\Traits\SecureIdTrait;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

use App\Traits\Translatable;

class Service extends Model
{
    use HasFactory, SecureIdTrait, Translatable;

    public $translatable = ['title', 'description'];

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'title',
        'description',
        'slug',
        'icon',
        'tags',
        'is_active',
        'created_by',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'is_active' => 'boolean',
    ];

    /**
     * Get the user who created this service.
     */
    public function author()
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    /**
     * Scope a query to only include active services.
     */
    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    /**
     * Get the service by slug.
     */
    public function scopeBySlug($query, $slug)
    {
        return $query->where('slug', $slug);
    }
}
