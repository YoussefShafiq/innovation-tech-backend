<?php

namespace App\Models;

use App\Traits\SecureIdTrait;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

use App\Traits\Translatable;

class TeamMember extends Model
{
    use HasFactory, SecureIdTrait, Translatable;

    public $translatable = ['name', 'title'];

    protected $fillable = [
        'name',
        'title',
        'image',
        'is_active',
    ];

    protected $casts = [
        'is_active' => 'boolean',
    ];

    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }
}
