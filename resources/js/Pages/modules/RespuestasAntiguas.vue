<script setup lang="ts">
import AppLayout from '@/layouts/AppLayout.vue';
import { Head, Link } from '@inertiajs/vue3';

interface Bitacora {
    id: number;
    encuesta: {
        id: number;
        nombre: string;
    };
    ciclo: {
        id: number;
        nombre: string;
    };
    fecha_inicio: string;
    fecha_fin: string;
    completada: boolean;
    total_respuestas: number;
    respuestas_numericas: number;
    respuestas_texto: number;
}

interface Egresado {
    id: number;
    nombre: string;
    apellidos: string;
}

const props = defineProps<{
    bitacoras: Bitacora[];
    egresado: Egresado;
}>();
</script>

<template>
    <Head title="Mis Respuestas Antiguas" />

    <AppLayout>
        <template #header>
            <h2 class="text-xl font-semibold leading-tight text-gray-800">
                Mis Encuestas Anteriores
            </h2>
        </template>

        <div class="py-12">
            <div class="mx-auto max-w-7xl sm:px-6 lg:px-8">
                <div class="overflow-hidden bg-white shadow-sm sm:rounded-lg">
                    <div class="p-6 text-gray-900">
                        <div class="mb-6">
                            <h3 class="text-lg font-medium text-gray-900">
                                Historial de Encuestas - {{ egresado.nombre }} {{ egresado.apellidos }}
                            </h3>
                            <p class="mt-1 text-sm text-gray-600">
                                Aquí puedes ver todas las encuestas que has contestado anteriormente.
                            </p>
                        </div>

                        <div v-if="bitacoras.length === 0" class="text-center py-12">
                            <svg class="mx-auto h-12 w-12 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                            </svg>
                            <h3 class="mt-2 text-sm font-medium text-gray-900">No hay encuestas anteriores</h3>
                            <p class="mt-1 text-sm text-gray-500">
                                Aún no has contestado ninguna encuesta del sistema anterior.
                            </p>
                        </div>

                        <div v-else class="space-y-4">
                            <div
                                v-for="bitacora in bitacoras"
                                :key="bitacora.id"
                                class="border border-gray-200 rounded-lg p-4 hover:border-blue-300 transition-colors"
                            >
                                <div class="flex items-start justify-between">
                                    <div class="flex-1">
                                        <h4 class="text-base font-semibold text-gray-900">
                                            {{ bitacora.encuesta.nombre }}
                                        </h4>
                                        <div class="mt-2 space-y-1">
                                            <p class="text-sm text-gray-600">
                                                <span class="font-medium">Ciclo:</span> {{ bitacora.ciclo.nombre }}
                                            </p>
                                            <p class="text-sm text-gray-600">
                                                <span class="font-medium">Fecha:</span> {{ bitacora.fecha_inicio }}
                                                <span v-if="bitacora.fecha_fin"> - {{ bitacora.fecha_fin }}</span>
                                            </p>
                                            <div class="flex items-center gap-4 text-sm text-gray-600">
                                                <span>
                                                    <span class="font-medium">Total:</span> {{ bitacora.total_respuestas }} respuestas
                                                </span>
                                                <span class="text-gray-400">|</span>
                                                <span>{{ bitacora.respuestas_numericas }} numéricas</span>
                                                <span class="text-gray-400">|</span>
                                                <span>{{ bitacora.respuestas_texto }} texto</span>
                                            </div>
                                        </div>
                                        <div class="mt-2">
                                            <span
                                                :class="[
                                                    'inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium',
                                                    bitacora.completada
                                                        ? 'bg-green-100 text-green-800'
                                                        : 'bg-yellow-100 text-yellow-800'
                                                ]"
                                            >
                                                {{ bitacora.completada ? 'Completada' : 'Incompleta' }}
                                            </span>
                                        </div>
                                    </div>
                                    <div class="ml-4">
                                        <Link
                                            :href="`/respuestas-antiguas/${bitacora.id}`"
                                            class="inline-flex items-center px-4 py-2 bg-blue-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-blue-700 focus:bg-blue-700 active:bg-blue-900 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:ring-offset-2 transition ease-in-out duration-150"
                                        >
                                            Ver Respuestas
                                        </Link>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </AppLayout>
</template>
