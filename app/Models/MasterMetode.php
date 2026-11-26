<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Cache;

class MasterMetode extends Model
{
    protected $fillable = [
        'nama',
        'kode_warna',
        'warna_teks',
        'urutan',
        'is_active',
    ];

    protected $casts = [
        'urutan'    => 'integer',
        'is_active' => 'boolean',
    ];

    protected static function booted(): void
    {
        static::saved(fn() => Cache::forget('master_metodes_active'));
        static::deleted(fn() => Cache::forget('master_metodes_active'));
    }

    /**
     * Fetch cached active methods ordered for legend and dropdowns.
     */
    public static function getActiveCached()
    {
        try {
            return Cache::remember('master_metodes_active', 300, function () {
                return static::where('is_active', true)
                    ->orderBy('urutan')
                    ->orderBy('nama')
                    ->get();
            });
        } catch (\Throwable $e) {
            return collect();
        }
    }
}
