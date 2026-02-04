<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;

class Credential extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'type', // basic, header, jwt
        'auth_key',
        'auth_value',
        'allowed_domains',
    ];

    protected function casts(): array
    {
        return [
            'auth_key' => 'encrypted',
            'auth_value' => 'encrypted',
            'allowed_domains' => 'array',
        ];
    }

    /**
     * Check if a given URL is allowed by the whitelist.
     * 
     * @param string $url
     * @return bool
     */
    public function isDomainAllowed(string $url): bool
    {
        // If no domains specified, default to DENY (strict security) or ALLOW (permissive)?
        // Requirement implies strict whitelist.
        if (empty($this->allowed_domains)) {
            return false;
        }

        $host = parse_url($url, PHP_URL_HOST);
        if (!$host)
            return false;

        foreach ($this->allowed_domains as $pattern) {
            if (Str::is($pattern, $host)) {
                return true;
            }
        }

        return false;
    }
}
