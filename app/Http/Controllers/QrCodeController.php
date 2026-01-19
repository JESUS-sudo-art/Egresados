<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Endroid\QrCode\QrCode;
use Endroid\QrCode\Writer\PngWriter;
use Inertia\Inertia;

class QrCodeController extends Controller
{
    /**
     * Muestra la vista administrativa del código QR
     */
    public function index()
    {
        $appUrl = config('app.url');
        
        return Inertia::render('admin/QrCode', [
            'appUrl' => $appUrl,
            'qrImageUrl' => url('/qr-code/generate') // URL absoluta
        ]);
    }

    /**
     * Genera el código QR como imagen PNG
     */
    public function generate()
    {
        $appUrl = config('app.url');
        
        // Crear el código QR
        $qrCode = new QrCode($appUrl);
        $writer = new PngWriter();
        $result = $writer->write($qrCode);

        return response($result->getString())
            ->header('Content-Type', $result->getMimeType());
    }

    /**
     * Descarga el código QR como archivo PNG
     */
    public function download()
    {
        $appUrl = config('app.url');
        
        // Crear el código QR
        $qrCode = new QrCode($appUrl);
        $writer = new PngWriter();
        $result = $writer->write($qrCode);

        $filename = 'qr-code-egresados-uabjo-' . date('Y-m-d') . '.png';

        return response($result->getString())
            ->header('Content-Type', $result->getMimeType())
            ->header('Content-Disposition', 'attachment; filename="' . $filename . '"');
    }

    /**
     * Genera QR para compartir (tamaño mediano optimizado para web)
     */
    public function share()
    {
        $appUrl = config('app.url');
        
        // Código QR optimizado para compartir
        $qrCode = new QrCode($appUrl);
        $writer = new PngWriter();
        $result = $writer->write($qrCode);

        $filename = 'qr-compartir-egresados-' . date('Y-m-d') . '.png';

        return response($result->getString())
            ->header('Content-Type', $result->getMimeType())
            ->header('Content-Disposition', 'attachment; filename="' . $filename . '"');
    }
}
