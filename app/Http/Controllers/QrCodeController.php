<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Endroid\QrCode\QrCode;
use Endroid\QrCode\Writer\PngWriter;
use Inertia\Inertia;

class QrCodeController extends Controller
{
    /**
     * URL objetivo para el QR. Se puede sobrescribir con QR_TARGET_URL o app.qr_url.
     */
    private function getQrUrl(): string
    {
        return config('app.qr_url', env('QR_TARGET_URL', 'https://egresados.mesitest.com'));
    }

    /**
     * Muestra la vista administrativa del código QR
     */
    public function index()
    {
        $appUrl = $this->getQrUrl();
        
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
        $appUrl = $this->getQrUrl();
        
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
        $appUrl = $this->getQrUrl();
        
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
        $appUrl = $this->getQrUrl();
        
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
