<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Storage;

class BackupDatabase extends Command
{
    protected $signature = 'db:backup 
                            {--database= : Nombre de la base de datos (por defecto la actual)}
                            {--path= : Ruta donde guardar el backup}';

    protected $description = 'Crea un backup de la base de datos MySQL';

    public function handle()
    {
        $driver = config('database.default');
        $connection = config("database.connections.{$driver}");
        
        $fecha = date('Y-m-d_His');
        $path = $this->option('path') ?: storage_path('backups');
        
        // Crear directorio si no existe
        if (!is_dir($path)) {
            mkdir($path, 0755, true);
        }
        
        // Manejar SQLite
        if ($driver === 'sqlite') {
            $dbPath = $connection['database'];
            $dbName = basename($dbPath);
            $filename = "backup_{$dbName}_{$fecha}.sqlite";
            $fullPath = "{$path}/{$filename}";
            
            $this->info("🔄 Creando backup de SQLite: {$dbName}");
            $this->info("📂 Guardando en: {$fullPath}");
            $this->newLine();
            
            // Copiar archivo SQLite
            if (file_exists($dbPath)) {
                copy($dbPath, $fullPath);
                $returnCode = 0;
            } else {
                $this->error("❌ Archivo de base de datos no encontrado: {$dbPath}");
                return Command::FAILURE;
            }
        } 
        // Manejar MySQL/MariaDB
        else {
            $database = $this->option('database') ?: $connection['database'];
            $host = $connection['host'];
            $username = $connection['username'];
            $password = $connection['password'];
            
            $filename = "backup_{$database}_{$fecha}.sql";
            $fullPath = "{$path}/{$filename}";
            
            $this->info("🔄 Creando backup de MySQL: {$database}");
            $this->info("📂 Guardando en: {$fullPath}");
            $this->newLine();
            
            // Construir comando mysqldump
            $command = sprintf(
                'mysqldump --user=%s --password=%s --host=%s %s > %s',
                escapeshellarg($username),
                escapeshellarg($password),
                escapeshellarg($host),
                escapeshellarg($database),
                escapeshellarg($fullPath)
            );
            
            // Ejecutar comando
            exec($command, $output, $returnCode);
        }
        
        // Procesar resultado
        $output = [];
        $returnCode = file_exists($fullPath) ? 0 : 1;
        
        if ($returnCode === 0) {
            $size = filesize($fullPath);
            $sizeFormatted = $this->formatBytes($size);
            
            $this->info("✅ Backup creado exitosamente");
            $this->info("📊 Tamaño: {$sizeFormatted}");
            $this->info("📁 Archivo: {$filename}");
            
            // Comprimir si es grande
            if ($size > 1048576) { // > 1MB
                $this->info("🗜️  Comprimiendo...");
                exec("gzip {$fullPath}", $output, $gzipCode);
                
                if ($gzipCode === 0) {
                    $gzSize = filesize("{$fullPath}.gz");
                    $this->info("✅ Comprimido: " . $this->formatBytes($gzSize));
                    $filename .= '.gz';
                }
            }
            
            $this->newLine();
            $this->comment("💡 Para restaurar:");
            
            if ($driver === 'sqlite') {
                $this->comment("   cp {$filename} " . config('database.connections.sqlite.database'));
            } else {
                $dbName = $this->option('database') ?: $connection['database'];
                $dbUser = $connection['username'];
                
                if (str_ends_with($filename, '.gz')) {
                    $this->comment("   gunzip < {$filename} | mysql -u {$dbUser} -p {$dbName}");
                } else {
                    $this->comment("   mysql -u {$dbUser} -p {$dbName} < {$filename}");
                }
            }
            
            return Command::SUCCESS;
        } else {
            $this->error("❌ Error al crear backup");
            return Command::FAILURE;
        }
    }
    
    private function formatBytes($bytes, $precision = 2)
    {
        $units = ['B', 'KB', 'MB', 'GB', 'TB'];
        
        for ($i = 0; $bytes > 1024 && $i < count($units) - 1; $i++) {
            $bytes /= 1024;
        }
        
        return round($bytes, $precision) . ' ' . $units[$i];
    }
}
