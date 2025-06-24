<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Firmware extends Model
{
    use HasFactory;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'version',
        'filename',
        'description',
        'size',
        'is_active',
        // uploaded_at will be handled by timestamps if we use created_at for it
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'is_active' => 'boolean',
        'size' => 'integer',
    ];

    // If using created_at as uploaded_at, no need for a separate uploaded_at in fillable or casts
    // if you want a distinct 'uploaded_at' that can be different from 'created_at':
    // public $timestamps = true; // ensure timestamps are enabled
    // const UPDATED_AT = null; // if you only want created_at and a custom uploaded_at
    // protected $dates = ['uploaded_at']; // if it's a custom date column
}
