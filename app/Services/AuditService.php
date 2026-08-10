<?php

namespace App\Services;

use App\Models\User;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;

class AuditService
{
    public function record(string $action, ?Model $subject = null, ?User $user = null, array $payload = []): void
    {
        if (!DB::getSchemaBuilder()->hasTable('historiques')) {
            return;
        }

        DB::table('historiques')->insert([
            'user_id' => optional($user)->id,
            'action' => $action,
            'model_type' => $subject ? get_class($subject) : null,
            'model_id' => optional($subject)->getKey(),
            'payload' => json_encode($payload),
            'ip_address' => request()->ip(),
            'created_at' => now(),
            'updated_at' => now(),
        ]);
    }
}
