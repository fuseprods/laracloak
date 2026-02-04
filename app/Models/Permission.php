<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Permission extends Model
{
    protected $fillable = [
        'subject_id',
        'subject_type',
        'object_id',
        'object_type',
        'can_view',
        'can_edit',
    ];

    protected $casts = [
        'can_view' => 'boolean',
        'can_edit' => 'boolean',
    ];

    public function subject()
    {
        return $this->morphTo();
    }

    public function object()
    {
        return $this->morphTo();
    }
}
