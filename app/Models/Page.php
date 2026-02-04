<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Page extends Model
{
    protected $fillable = [
        'slug',
        'destination_url',
        'upstream_method',
        'config',
        'response_filters',
        'refresh_rate',
        'success_message',
        'redirect_url',
        'type',
        'is_published',
        'credential_id',
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'destination_url' => 'encrypted',
            'config' => 'array',
            'response_filters' => 'array',
            'is_published' => 'boolean',
        ];
    }

    public function credential()
    {
        return $this->belongsTo(Credential::class);
    }

    /**
     * The categories that the page belongs to.
     */
    public function categories()
    {
        return $this->belongsToMany(Category::class);
    }

    /**
     * Direct permissions for this page.
     */
    public function permissions()
    {
        return $this->morphMany(Permission::class, 'object');
    }
}
