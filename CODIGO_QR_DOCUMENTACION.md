# Generador de Código QR - Sistema de Egresados UABJO

## 📱 Funcionalidad Implementada

Sistema de generación de código QR general para acceso al Sistema de Egresados UABJO.

---

## 🎯 Características

### ✅ Código QR General
- Un único código QR para todo el sistema
- Redirige a la URL principal del sitio (configurada en `APP_URL`)
- Al escanear, los usuarios acceden a la página de login/registro

### ✅ Múltiples Formatos de Descarga

1. **Alta Resolución (800x800px)**
   - Ideal para credenciales físicas
   - Impresión en posters y documentos
   - Alta calidad para impresión profesional

2. **Optimizado para Compartir (600x600px)**
   - Tamaño perfecto para WhatsApp
   - Correos electrónicos
   - Redes sociales (Facebook, Instagram)

3. **Vista en Pantalla (400x400px)**
   - Visualización en la interfaz web
   - Tamaño optimizado para carga rápida

---

## 🚀 Acceso

### Para Administradores Generales:

1. Iniciar sesión como **Administrador General**
2. En el menú lateral, hacer clic en **"Código QR"**
3. Se mostrará la vista con el código QR

### Rutas Disponibles:

```
/admin/qr-code          → Vista administrativa (requiere login como Admin General)
/qr-code/generate       → Genera imagen PNG del QR (pública)
/qr-code/download       → Descarga QR alta resolución (pública)
/qr-code/share          → Descarga QR para compartir (pública)
```

---

## 📥 Opciones de Descarga

### 1. Descargar para Credenciales Físicas
- Botón: **"Descargar QR (Alta Resolución)"**
- Archivo: `qr-code-egresados-uabjo-YYYY-MM-DD.png`
- Resolución: 800x800px
- Uso: Impresión en credenciales, documentos oficiales, carteles

### 2. Descargar para Compartir
- Botón: **"Descargar QR para Compartir"**
- Archivo: `qr-compartir-egresados-YYYY-MM-DD.png`
- Resolución: 600x600px
- Uso: WhatsApp, correos, redes sociales

### 3. Imprimir Página
- Botón: **"Imprimir esta Página"**
- Imprime la página completa con el QR y la información

### 4. Copiar URL
- Botón: Icono de copiar junto a la URL
- Copia la URL del sistema al portapapeles

---

## 💡 Casos de Uso

### 1. Credenciales de Estudiantes/Egresados
```
1. Acceder a /admin/qr-code
2. Clic en "Descargar QR (Alta Resolución)"
3. Guardar archivo PNG
4. Importar en diseño de credencial
5. Imprimir credenciales
```

### 2. Envío por Correo Electrónico
```
1. Descargar QR para compartir
2. Adjuntar imagen en correo de bienvenida
3. Incluir instrucciones: "Escanea para acceder al sistema"
```

### 3. Compartir en WhatsApp
```
1. Descargar QR para compartir
2. Enviar imagen por WhatsApp a grupos de estudiantes
3. Mensaje: "Escanea este código para registrarte/acceder"
```

### 4. Publicación en Redes Sociales
```
1. Descargar QR para compartir
2. Publicar en Facebook/Instagram oficial de UABJO
3. Caption: "Accede fácilmente al Sistema de Egresados"
```

### 5. Carteles/Posters en Campus
```
1. Descargar QR alta resolución
2. Diseñar poster con herramienta de diseño
3. Imprimir en formato grande
4. Colocar en edificios/pasillos del campus
```

---

## 🔧 Configuración Técnica

### Librería Utilizada
- **endroid/qr-code** v6.0.9
- Generador de códigos QR para PHP/Laravel
- Alta calidad y personalizable

### Características Técnicas
- **Encoding:** UTF-8
- **Error Correction:** Alto (para credenciales) / Medio (para compartir)
- **Formato:** PNG
- **Márgenes:** Automáticos según tamaño

### URL del QR
El código QR redirige a la URL configurada en `.env`:
```env
APP_URL=http://localhost:8000
```

En producción, cambiar a:
```env
APP_URL=https://egresados.uabjo.mx
```

---

## 📂 Archivos Creados

```
app/Http/Controllers/QrCodeController.php       → Controlador
resources/js/Pages/admin/QrCode.vue             → Vista Vue
routes/web.php                                   → Rutas agregadas
resources/js/components/AppSidebar.vue          → Menú actualizado
```

---

## 🎨 Interfaz de Usuario

### Vista Principal
- ✅ Tarjeta con código QR visible
- ✅ URL del sistema mostrada
- ✅ Botón para copiar URL
- ✅ 3 botones de descarga/compartir
- ✅ Información de uso con iconos coloridos
- ✅ Nota explicativa sobre el funcionamiento

### Diseño Responsivo
- ✅ Adaptable a móviles y tablets
- ✅ Dark mode compatible
- ✅ Estilo consistente con Shadcn/UI

---

## ✨ Ventajas de esta Implementación

1. **Facilita el acceso:** Los usuarios pueden escanear y acceder instantáneamente
2. **Multiplataforma:** Funciona en cualquier dispositivo con cámara
3. **Profesional:** Códigos QR de alta calidad para imagen institucional
4. **Versátil:** Múltiples formatos para diferentes usos
5. **Sin costo adicional:** No requiere servicios externos de terceros
6. **Permanente:** El QR no caduca ni cambia

---

## 🔐 Permisos

- **Vista del QR:** Solo Administrador General
- **Descarga de imágenes:** Rutas públicas (no requieren autenticación)
  - Esto permite compartir los enlaces directos de descarga

---

## 📊 Métricas Sugeridas (Futuro)

Para próximas versiones, considerar:
- Contador de escaneos del QR
- Analítica de dispositivos que escanean
- Tasa de conversión (escaneo → registro)
- Ubicación de escaneos (si es relevante)

---

## 🛠️ Mantenimiento

### Cambiar URL del Sistema
1. Editar `.env`
2. Actualizar `APP_URL`
3. Limpiar caché: `php artisan config:clear`
4. El QR se regenerará automáticamente con la nueva URL

### Cambiar Diseño del QR
Editar [QrCodeController.php](app/Http/Controllers/QrCodeController.php):
- `size()` - Cambiar tamaño (px)
- `margin()` - Ajustar márgenes
- `errorCorrectionLevel()` - Ajustar nivel de corrección

---

## 📞 Soporte

Para dudas o modificaciones:
- Documentación de endroid/qr-code: https://github.com/endroid/qr-code
- Laravel Documentation: https://laravel.com/docs

---

**Fecha de implementación:** 16 de enero de 2026  
**Versión:** 1.0  
**Estado:** ✅ Funcional y listo para producción
