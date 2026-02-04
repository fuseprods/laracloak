<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

class User extends Authenticatable
{
    /** @use HasFactory<\Database\Factories\UserFactory> */
    use HasFactory, Notifiable;

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'name',
        'email',
        'password',
        'role',
        'theme',
        'locale',
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var list<string>
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
        ];
    }
    /**
     * The groups that the user belongs to.
     */
    public function groups()
    {
        return $this->belongsToMany(Group::class);
    }

    /**
     * Direct permissions for this user.
     */
    public function permissions()
    {
        return $this->morphMany(Permission::class, 'subject');
    }

    /**
     * Check if the user has a specific permission for a page.
     * Evaluates direct access, group-based access, and category-based access.
     */
    public function hasAccessToPage(Page $page, string $attribute = 'can_view'): bool
    {
        // Admins can see and do everything
        if ($this->role === 'admin') {
            return true;
        }

        // 1. Direct Page Permission
        if (
            $this->permissions()
                ->where('object_type', Page::class)
                ->where('object_id', $page->id)
                ->where($attribute, true)
                ->exists()
        ) {
            return true;
        }

        // 2. Direct Category Permission (User has access to Category the page belongs to)
        $categoryIds = $page->categories()->pluck('categories.id');
        if ($categoryIds->isNotEmpty()) {
            if (
                $this->permissions()
                    ->where('object_type', Category::class)
                    ->whereIn('object_id', $categoryIds)
                    ->where($attribute, true)
                    ->exists()
            ) {
                return true;
            }
        }

        // 3. Group Permissions
        $groupIds = $this->groups()->pluck('groups.id');
        if ($groupIds->isNotEmpty()) {
            // 3a. Group to Page access
            if (
                Permission::where('subject_type', Group::class)
                    ->whereIn('subject_id', $groupIds)
                    ->where('object_type', Page::class)
                    ->where('object_id', $page->id)
                    ->where($attribute, true)
                    ->exists()
            ) {
                return true;
            }

            // 3b. Group to Category access
            if ($categoryIds->isNotEmpty()) {
                if (
                    Permission::where('subject_type', Group::class)
                        ->whereIn('subject_id', $groupIds)
                        ->where('object_type', Category::class)
                        ->whereIn('object_id', $categoryIds)
                        ->where($attribute, true)
                        ->exists()
                ) {
                    return true;
                }
            }
        }

        return false;
    }
}
