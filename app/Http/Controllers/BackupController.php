<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Spatie\DbDumper\Databases\MySql as MySqlDumper;

class BackupController extends Controller
{
    /**
     * Genera y descarga un respaldo de la base de datos en formato SQL.
     * Requiere rol Administrador de unidad, Administrador académico o Administrador general.
     */
    public function download(Request $request)
    {
        $user = Auth::user();
        if (!$user || !$user->hasAnyRole(['Administrador de unidad', 'Administrador academico', 'Administrador general'])) {
            abort(403);
        }

        // Usamos mysqldump vía shell para simplicidad. Requiere que el binario esté disponible.
        $connection = config('database.default');
        $cfg = config("database.connections.$connection");

        if (!$cfg || $cfg['driver'] !== 'mysql') {
            abort(500, 'Solo soportado para MySQL/MariaDB en este respaldo rápido.');
        }

        $host = $cfg['host'] ?? '127.0.0.1';
        $port = $cfg['port'] ?? 3306;
        $database = $cfg['database'] ?? '';
        $username = $cfg['username'] ?? '';
        $password = $cfg['password'] ?? '';
        $charset = $cfg['charset'] ?? 'utf8mb4';

        $file = 'backup_'.date('Ymd_His').'.sql';
        $path = storage_path('app/'.$file);

        try {
            MySqlDumper::create()
                ->setDbName($database)
                ->setUserName($username)
                ->setPassword($password)
                ->setHost($host)
                ->setPort($port)
                ->setDefaultCharacterSet($charset)
                ->includeTriggers()
                ->includeEvents()
                ->dumpToFile($path);
        } catch (\Throwable $e) {
            abort(500, 'No se pudo generar el respaldo: '.$e->getMessage());
        }

        if (!file_exists($path) || filesize($path) === 0) {
            abort(500, 'El respaldo no se generó. Verifique que mysqldump esté instalado y credenciales válidas.');
        }

        return response()->download($path)->deleteFileAfterSend(true);
    }
}
