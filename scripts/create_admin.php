<?php

require __DIR__ . '/../vendor/autoload.php';
$app = require_once __DIR__ . '/../bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use App\Models\User;
use Illuminate\Support\Facades\Hash;

User::updateOrCreate(
    ['email' => 'noahmenor005@gmail.com'],
    [
        'name' => 'NOAH MENOR',
        'password' => Hash::make('#noah005'),
        'role' => 'admin',
        'status' => User::STATUS_ACCEPTED,
    ]
);

echo "Admin user created or updated successfully.\n";
