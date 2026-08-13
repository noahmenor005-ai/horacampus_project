<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Cache;

class Setting extends Model
{
    protected $fillable = ['cle', 'valeur'];

    public static function get(string $key, $default = null)
    {
        $all = static::map();

        return $all[$key] ?? $default;
    }

    public static function put(string $key, $value): void
    {
        static::updateOrCreate(['cle' => $key], ['valeur' => $value]);
        Cache::forget('horacampus.settings');
    }

    public static function map(): array
    {
        return Cache::remember('horacampus.settings', 60, function () {
            try {
                return static::query()->pluck('valeur', 'cle')->all();
            } catch (\Throwable $e) {
                return [];
            }
        });
    }
}
