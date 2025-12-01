<?php

require __DIR__.'/vendor/autoload.php';

$app = require_once __DIR__.'/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Http\Kernel::class);
$kernel->bootstrap();

$router = app('router');
$routes = $router->getRoutes();

echo "Total de rutas: " . count($routes) . "\n\n";
echo "Buscando rutas que contengan 'roles':\n";
echo str_repeat('-', 80) . "\n";

foreach ($routes as $route) {
    if (stripos($route->uri(), 'roles') !== false) {
        $methods = implode('|', $route->methods());
        $name = $route->getName() ?? '(sin nombre)';
        $action = $route->getActionName();
        
        echo sprintf("%-10s %-30s %-25s %s\n", $methods, $route->uri(), $name, $action);
    }
}
