<?php

namespace App\Services;

use App\Models\User;
use Illuminate\Support\Facades\DB;

class NotificationService
{
    public function broadcast(string $title, string $message, array $roles = ['admin', 'decanat']): void
    {
        if (!DB::getSchemaBuilder()->hasTable('notifications')) {
            return;
        }

        User::whereIn('role', $roles)->get()->each(function (User $user) use ($title, $message) {
            $this->insert($user->id, $title, $message);
        });
    }

    public function notifyUser(User $user, string $title, string $message): void
    {
        if (!DB::getSchemaBuilder()->hasTable('notifications')) {
            return;
        }

        $this->insert($user->id, $title, $message);
    }

    public function notifyRole(string $role, string $title, string $message): void
    {
        if (!DB::getSchemaBuilder()->hasTable('notifications')) {
            return;
        }

        User::where('role', $role)->get()->each(function (User $user) use ($title, $message) {
            $this->insert($user->id, $title, $message);
        });
    }

    private function insert(int $userId, string $title, string $message): void
    {
        DB::table('notifications')->insert([
            'user_id' => $userId,
            'titre' => $title,
            'message' => $message,
            'created_at' => now(),
            'updated_at' => now(),
        ]);
    }
}
