<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Auth;

class AuditLog extends Model
{
    protected $fillable = [
        'user_id',
        'user_name',
        'action',
        'model_type',
        'model_id',
        'description',
        'changes',
    ];

    protected $casts = [
        'changes' => 'array',
    ];

    /**
     * Record an administrative or system action safely.
     */
    public static function record(
        string $action,
        string $description,
        ?string $modelType = null,
        ?int $modelId = null,
        ?array $changes = null
    ): void {
        try {
            $user = Auth::user();
            static::create([
                'user_id'     => $user?->id,
                'user_name'   => $user?->name ?? 'System',
                'action'      => $action,
                'model_type'  => $modelType,
                'model_id'    => $modelId,
                'description' => mb_substr($description, 0, 500),
                'changes'     => $changes,
            ]);
        } catch (\Throwable $e) {
            // Do not break user flow if audit logging fails
        }
    }
}
