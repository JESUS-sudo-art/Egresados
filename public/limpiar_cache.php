<?php
/**
 * Limpiar cache de Laravel
 * ELIMINAR después de usar
 */
require __DIR__.'/../vendor/autoload.php';
$app = require_once __DIR__.'/../bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

try {
    Artisan::call('config:clear');
    echo "✅ Config cache limpiado<br>";
    
    Artisan::call('cache:clear');
    echo "✅ Application cache limpiado<br>";
    
    Artisan::call('view:clear');
    echo "✅ View cache limpiado<br>";
    
    Artisan::call('route:clear');
    echo "✅ Route cache limpiado<br>";
    
    echo "<br><strong style='color: red;'>IMPORTANTE: Elimina este archivo (limpiar_cache.php) del servidor AHORA.</strong>";
    
} catch (\Exception $e) {
    echo "❌ Error: " . $e->getMessage();
}
?>
