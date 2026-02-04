<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Category extends Model
{
    protected $fillable = ['name', 'description'];

    public function pages()
    {
        return $this->belongsToMany(Page::class);
    }

    public function permissions()
    {
        return $this->morphMany(Permission::class, 'object');
    }
}
