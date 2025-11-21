<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Acuse de Seguimiento</title>
    <style>
        @page {
            margin: 1.5cm;
        }
        
        body {
            font-family: Arial, sans-serif;
            color: #000;
            line-height: 1.4;
        }
        
        .header {
            margin-bottom: 30px;
            position: relative;
            min-height: 100px;
        }
        
        .header-content {
            text-align: center;
            margin-left: 100px;
        }
        
        .logo {
            position: absolute;
            left: 0;
            top: 0;
            width: 90px;
            height: auto;
        }
        
        .logo img {
            width: 100%;
            height: auto;
        }
        
        .universidad {
            font-size: 16px;
            font-weight: bold;
            margin: 5px 0;
            text-transform: uppercase;
        }
        
        .secretaria {
            font-size: 14px;
            font-weight: bold;
            margin: 5px 0;
            text-transform: uppercase;
        }
        
        .seguimiento-title {
            font-size: 18px;
            font-weight: bold;
            margin-top: 20px;
            margin-bottom: 40px;
            text-align: center;
            text-transform: uppercase;
            letter-spacing: 1px;
        }
        
        .content {
            margin: 40px 0 60px 0;
            font-size: 14px;
        }
        
        .field-row {
            margin: 18px 0;
            line-height: 1.6;
        }
        
        .field-label {
            font-weight: bold;
            display: inline;
        }
        
        .field-value {
            display: inline;
            margin-left: 5px;
        }
        
        .estatus-container {
            text-align: center;
            margin-top: 80px;
        }
        
        .estatus-label {
            font-weight: bold;
            font-size: 14px;
            display: block;
            margin-bottom: 5px;
        }
        
        .estatus-value {
            font-size: 16px;
            font-weight: bold;
            text-transform: uppercase;
        }
    </style>
</head>
<body>
    <div class="header">
        <div class="logo">
            <img src="{{ public_path('images/logo-uabjo.jpg') }}" alt="UABJO Logo">
        </div>
        <div class="header-content">
            <div class="universidad">Universidad Autónoma "Benito Juárez" de Oaxaca</div>
            <div class="secretaria">Secretaría Académica</div>
        </div>
    </div>
    
    <div class="seguimiento-title">SEGUIMIENTO DE EGRESADOS</div>
    
    <div class="content">
        <div class="field-row">
            <span class="field-label">Matrícula:</span>
            <span class="field-value">{{ $matricula }}</span>
        </div>
        
        <div class="field-row">
            <span class="field-label">Nombre:</span>
            <span class="field-value">{{ $nombre }}</span>
        </div>
        
        <div class="field-row">
            <span class="field-label">Escuela / Facultad:</span>
            <span class="field-value">{{ $facultad }}</span>
        </div>
        
        <div class="field-row">
            <span class="field-label">Licenciatura:</span>
            <span class="field-value">{{ $licenciatura }}</span>
        </div>
        
        <div class="field-row">
            <span class="field-label">Año de egreso:</span>
            <span class="field-value">{{ $anioEgreso }}</span>
        </div>
        
        <div class="field-row">
            <span class="field-label">Encuesta:</span>
            <span class="field-value">{{ $nombreEncuesta }}</span>
        </div>
        
        <div class="estatus-container">
            <span class="estatus-label">Estatus:</span>
            <span class="estatus-value">CONTESTADA</span>
        </div>
    </div>
</body>
</html>
