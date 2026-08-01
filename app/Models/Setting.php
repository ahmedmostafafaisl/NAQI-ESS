<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Cache;

class Setting extends Model
{
    protected $fillable = ['key', 'value', 'type', 'description', 'is_public'];

    protected function casts(): array
    {
        return [
            'is_public' => 'boolean',
        ];
    }

    protected static function booted(): void
    {
        static::saved(fn(self $setting) => Cache::forget("setting:{$setting->key}"));
        static::deleted(fn(self $setting) => Cache::forget("setting:{$setting->key}"));
    }

    /** Cast the stored string value according to this setting's `type`. */
    public function getCastValueAttribute(): mixed
    {
        return match ($this->type) {
            'integer' => (int) $this->value,
            'boolean' => filter_var($this->value, FILTER_VALIDATE_BOOLEAN),
            'json' => json_decode((string) $this->value, true),
            default => $this->value,
        };
    }

    /** Read a setting's value by key, cached, with an optional default if it doesn't exist. */
    public static function get(string $key, mixed $default = null): mixed
    {
        return Cache::rememberForever("setting:{$key}", function () use ($key, $default) {
            $setting = static::where('key', $key)->first();

            return $setting ? $setting->cast_value : $default;
        });
    }

    /** Create or update a setting by key. Automatically infers `type` from the value unless given. */
    public static function set(string $key, mixed $value, ?string $type = null, ?string $description = null, bool $isPublic = false): self
    {
        $type ??= match (true) {
            is_bool($value) => 'boolean',
            is_int($value) => 'integer',
            is_array($value) => 'json',
            default => 'string',
        };

        $storedValue = match (true) {
            $type === 'json' => json_encode($value),
            is_bool($value) => $value ? '1' : '0',
            default => (string) $value,
        };

        $attributes = ['value' => $storedValue, 'type' => $type, 'is_public' => $isPublic];

        if ($description !== null) {
            $attributes['description'] = $description;
        }

        return static::updateOrCreate(['key' => $key], $attributes);
    }
}
