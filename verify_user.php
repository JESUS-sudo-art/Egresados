<?php
require __DIR__.'/vendor/autoload.php';
$app = require_once __DIR__.'/bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();
$user = App\Models\User::where('email','jesus25020304@gmail.com')->first();
if(!$user){echo "Usuario no encontrado\n";exit(1);} 
$user->email_verified_at = now();
$user->save();
echo "Verificado: ".$user->email." en ".$user->email_verified_at."\n";