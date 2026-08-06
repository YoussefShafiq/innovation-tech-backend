<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Traits\SecureIdTrait;

use App\Traits\Translatable;

class Setting extends Model
{
    use HasFactory, SecureIdTrait, Translatable;

    public $translatable = [
        'our_mission',
        'our_vision',
        'story_title',
        'story_subtitle',
        'story_description',
        'story_bullets',
        'address',
        'business_hours',
        'emergency_support'
    ];

    protected $fillable = [
        'our_mission',
        'our_vision',
        'years',
        'projects',
        'clients',
        'engineers',
        'story_title',
        'story_subtitle',
        'story_description',
        'story_bullets',
        'email',
        'phone',
        'address',
        'business_hours',
        'emergency_support',
        'theme_colors',
        'logo',
    ];

    protected $casts = [
        'story_bullets' => 'array',
        'business_hours' => 'array',
        'theme_colors' => 'array',
    ];

    // public function getImageAttribute($value)
    // {
    //     return $value ? env('APP_URL') . '/storage/' . $value : null;
    // }

}
