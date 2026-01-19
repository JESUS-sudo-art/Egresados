<script setup lang="ts">
import { computed } from 'vue';
import { Head } from '@inertiajs/vue3';
import AppLayout from '@/layouts/AppLayout.vue';
import { Card, CardContent, CardDescription, CardHeader, CardTitle } from '@/components/ui/card';
import { Button } from '@/components/ui/button';
import { Download, Share2, Printer, Copy, Check } from 'lucide-vue-next';
import { ref } from 'vue';

interface Props {
    appUrl: string;
    qrImageUrl: string;
}

const props = defineProps<Props>();

const copied = ref(false);
const imageError = ref(false);

const copyUrl = () => {
    navigator.clipboard.writeText(props.appUrl);
    copied.value = true;
    setTimeout(() => {
        copied.value = false;
    }, 2000);
};

const printQr = () => {
    window.print();
};

const downloadQr = () => {
    window.location.href = '/qr-code/download';
};

const shareQr = () => {
    window.location.href = '/qr-code/share';
};

const handleImageError = () => {
    imageError.value = true;
    console.error('Error al cargar la imagen QR desde:', props.qrImageUrl);
};
</script>

<template>
    <AppLayout>
        <Head title="Código QR - Sistema Egresados" />

        <div class="max-w-5xl mx-auto p-6 space-y-6">
            <!-- Encabezado -->
            <div>
                <h1 class="text-3xl font-bold text-gray-900 dark:text-white">
                    Código QR del Sistema
                </h1>
                <p class="mt-2 text-gray-600 dark:text-gray-400">
                    Código QR para acceder al Sistema de Egresados UABJO
                </p>
            </div>

            <!-- Contenido Principal -->
            <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
                <!-- Tarjeta del QR -->
                <Card>
                    <CardHeader>
                        <CardTitle>Código QR</CardTitle>
                        <CardDescription>
                            Escanea este código para acceder al sistema
                        </CardDescription>
                    </CardHeader>
                    <CardContent class="flex flex-col items-center justify-center p-8 no-print">
                        <!-- Imagen del QR -->
                        <div class="bg-white p-6 rounded-lg shadow-lg">
                            <img 
                                v-if="!imageError"
                                :src="qrImageUrl" 
                                alt="Código QR del Sistema" 
                                class="w-64 h-64"
                                @error="handleImageError"
                            />
                            <div v-else class="w-64 h-64 flex items-center justify-center bg-gray-100 rounded-lg">
                                <div class="text-center p-4">
                                    <p class="text-sm text-red-600 mb-2">Error al cargar la imagen</p>
                                    <p class="text-xs text-gray-600">{{ qrImageUrl }}</p>
                                    <Button 
                                        @click="imageError = false" 
                                        variant="outline" 
                                        size="sm" 
                                        class="mt-2"
                                    >
                                        Reintentar
                                    </Button>
                                </div>
                            </div>
                        </div>
                        
                        <!-- URL -->
                        <div class="mt-6 w-full">
                            <p class="text-sm text-gray-600 dark:text-gray-400 text-center mb-2">
                                URL del sistema:
                            </p>
                            <div class="flex items-center gap-2">
                                <input 
                                    type="text" 
                                    :value="appUrl" 
                                    readonly 
                                    class="flex-1 px-3 py-2 text-sm border rounded-md bg-gray-50 dark:bg-gray-800 dark:border-gray-700"
                                />
                                <Button 
                                    size="icon" 
                                    variant="outline"
                                    @click="copyUrl"
                                >
                                    <Check v-if="copied" class="h-4 w-4 text-green-600" />
                                    <Copy v-else class="h-4 w-4" />
                                </Button>
                            </div>
                        </div>
                    </CardContent>
                </Card>

                <!-- Tarjeta de Acciones -->
                <Card>
                    <CardHeader>
                        <CardTitle>Acciones</CardTitle>
                        <CardDescription>
                            Descarga o comparte el código QR
                        </CardDescription>
                    </CardHeader>
                    <CardContent class="space-y-4">
                        <!-- Descargar para Impresión -->
                        <div class="space-y-2">
                            <h3 class="font-semibold text-sm">Para Credenciales Físicas</h3>
                            <Button 
                                @click="downloadQr" 
                                class="w-full"
                                variant="default"
                            >
                                <Download class="mr-2 h-4 w-4" />
                                Descargar QR (Alta Resolución)
                            </Button>
                            <p class="text-xs text-gray-500 dark:text-gray-400">
                                800x800px - Ideal para imprimir en credenciales, posters o documentos
                            </p>
                        </div>

                        <div class="border-t dark:border-gray-700 pt-4 space-y-2">
                            <h3 class="font-semibold text-sm">Para Compartir</h3>
                            <Button 
                                @click="shareQr" 
                                class="w-full"
                                variant="outline"
                            >
                                <Share2 class="mr-2 h-4 w-4" />
                                Descargar QR para Compartir
                            </Button>
                            <p class="text-xs text-gray-500 dark:text-gray-400">
                                600x600px - Optimizado para WhatsApp, correo y redes sociales
                            </p>
                        </div>

                        <div class="border-t dark:border-gray-700 pt-4">
                            <Button 
                                @click="printQr" 
                                class="w-full"
                                variant="outline"
                            >
                                <Printer class="mr-2 h-4 w-4" />
                                Imprimir esta Página
                            </Button>
                        </div>
                    </CardContent>
                </Card>
            </div>

            <!-- Información Adicional -->
            <Card>
                <CardHeader>
                    <CardTitle>Información de Uso</CardTitle>
                </CardHeader>
                <CardContent>
                    <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                        <div class="space-y-2">
                            <div class="flex items-center gap-2">
                                <div class="w-2 h-2 bg-green-500 rounded-full"></div>
                                <h4 class="font-semibold text-sm">Credenciales Físicas</h4>
                            </div>
                            <p class="text-sm text-gray-600 dark:text-gray-400">
                                Imprime el QR de alta resolución en credenciales de estudiantes y egresados
                            </p>
                        </div>

                        <div class="space-y-2">
                            <div class="flex items-center gap-2">
                                <div class="w-2 h-2 bg-blue-500 rounded-full"></div>
                                <h4 class="font-semibold text-sm">Correo Electrónico</h4>
                            </div>
                            <p class="text-sm text-gray-600 dark:text-gray-400">
                                Incluye el QR en correos de bienvenida o notificaciones importantes
                            </p>
                        </div>

                        <div class="space-y-2">
                            <div class="flex items-center gap-2">
                                <div class="w-2 h-2 bg-purple-500 rounded-full"></div>
                                <h4 class="font-semibold text-sm">Redes Sociales</h4>
                            </div>
                            <p class="text-sm text-gray-600 dark:text-gray-400">
                                Comparte en WhatsApp, Facebook o Instagram para facilitar el acceso
                            </p>
                        </div>
                    </div>

                    <div class="mt-6 p-4 bg-amber-50 dark:bg-amber-900/20 rounded-lg border border-amber-200 dark:border-amber-800">
                        <p class="text-sm text-amber-800 dark:text-amber-200">
                            <strong>Nota:</strong> Este código QR redirige a la página principal del sistema ({{ appUrl }}). 
                            Los usuarios deberán iniciar sesión o registrarse después de escanear el código.
                        </p>
                    </div>
                </CardContent>
            </Card>
        </div>
    </AppLayout>
</template>

<style scoped>
@media print {
    .no-print {
        display: none !important;
    }
    
    @page {
        margin: 2cm;
    }
    
    img {
        max-width: 100%;
        page-break-inside: avoid;
    }
}
</style>
