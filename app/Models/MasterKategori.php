<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class MasterKategori extends Model
{
    protected $fillable = [
        'nama',
        'urutan',
        'is_active',
    ];

    protected $casts = [
        'urutan'    => 'integer',
        'is_active' => 'boolean',
    ];

    /**
     * Get ordered active category names, with sensible defaults when empty.
     */
    public static function getActiveNames(): array
    {
        $names = static::where('is_active', true)
            ->orderBy('urutan')
            ->orderBy('nama')
            ->pluck('nama')
            ->all();

        return !empty($names) ? $names : [
            'PELATIHAN FUNGSIONAL',
            'PELATIHAN MANAJERIAL',
            'PELATIHAN TEKNIS',
        ];
    }
}
