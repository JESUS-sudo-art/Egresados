<?php
require __DIR__.'/vendor/autoload.php';
$app = require_once __DIR__.'/bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();
$user = App\Models\User::where('email','jesus25020304@gmail.com')->first();
if(!$user){echo "no user\n";exit;}
echo 'verified_at: '.($user->email_verified_at ?? 'NULL')."\n";