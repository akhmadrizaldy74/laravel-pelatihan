<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Cache;

class AppSetting extends Model
{
    protected $primaryKey = 'key';
    public $incrementing = false;
    protected $keyType = 'string';

    protected $fillable = ['key', 'value'];

    public static function getBool(string $key, bool $default = true): bool
    {
        try {
            $val = Cache::remember("app_setting_{$key}", 300, function () use ($key) {
                return static::where('key', $key)->value('value');
            });

            if ($val === null) {
                return $default;
            }

            return in_array(strtolower((string)$val), ['1', 'true', 'yes', 'on'], true);
        } catch (\Throwable $e) {
            return $default;
        }
    }

    public static function setBool(string $key, bool $value): void
    {
        static::updateOrCreate(
            ['key' => $key],
            ['value' => $value ? '1' : '0']
        );
        Cache::forget("app_setting_{$key}");
    }
}
