<?php

require 'vendor/autoload.php';

$app = require_once 'bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

// Limpiar todas las respuestas
App\Models\Respuesta::truncate();

echo "✅ Todas las respuestas han sido eliminadas.\n";
echo "Ahora puedes probar el flujo completo de contestar encuesta.\n";
