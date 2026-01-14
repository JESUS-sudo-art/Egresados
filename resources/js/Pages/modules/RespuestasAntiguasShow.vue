<script setup lang="ts">
import AppLayout from '@/layouts/AppLayout.vue';
import { Head, Link } from '@inertiajs/vue3';
import { computed } from 'vue';

interface Respuesta {
    pregunta_id: number;
    pregunta_texto: string;
    tipo: string;
    dimension: string;
    dimension_orden: number;
    respuestas: {
        tipo: 'numerico' | 'texto';
        valor: string | number;
    }[];
}

interface Props {
    bitacora: {
        id: number;
        encuesta: {
            id: number;
            nombre: string;
        };
        ciclo: {
            nombre: string;
        };
        fecha_inicio: string;
        fecha_fin: string;
        completada: boolean;
    };
    respuestas: Respuesta[];
    egresado: {
        nombre: string;
        apellidos: string;
    };
}

const props = defineProps<Props>();

// Agrupar respuestas por dimensión
const respuestasPorDimension = computed(() => {
    const grupos = new Map<string, Respuesta[]>();
    
    props.respuestas.forEach(resp => {
        const dimension = resp.dimension;
        if (!grupos.has(dimension)) {
            grupos.set(dimension, []);
        }
        grupos.get(dimension)!.push(resp);
    });
    
    return Array.from(grupos.entries()).map(([dimension, preguntas]) => ({
        dimension,
        preguntas
    }));
});

console.log('✅ RespuestasAntiguasShow.vue cargado');
</script>

<template>
    <Head title="Detalle de Respuestas Antiguas" />

    <AppLayout>
        <template #header>
            <div class="flex items-center justify-between">
                <h2 class="text-xl font-semibold leading-tight text-gray-800">
                    Respuestas de Encuesta Anterior
                </h2>
                <Link
                    href="/respuestas-antiguas"
                    class="inline-flex items-center px-3 py-2 border border-gray-300 shadow-sm text-sm leading-4 font-medium rounded-md text-gray-700 bg-white hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500"
                >
                    ← Volver al listado
                </Link>
            </div>
        </template>

        <div class="py-12">
            <div class="mx-auto max-w-7xl sm:px-6 lg:px-8">
                <!-- Información de la encuesta -->
                <div class="mb-6 overflow-hidden bg-white shadow-sm sm:rounded-lg">
                    <div class="p-6">
                        <h3 class="text-lg font-semibold text-gray-900">
                            {{ bitacora.encuesta.nombre }}
                        </h3>
                        <div class="mt-4 grid grid-cols-1 gap-4 sm:grid-cols-3">
                            <div>
                                <p class="text-sm font-medium text-gray-500">Egresado</p>
                                <p class="mt-1 text-sm text-gray-900">{{ egresado.nombre }} {{ egresado.apellidos }}</p>
                            </div>
                            <div>
                                <p class="text-sm font-medium text-gray-500">Ciclo Escolar</p>
                                <p class="mt-1 text-sm text-gray-900">{{ bitacora.ciclo.nombre }}</p>
                            </div>
                            <div>
                                <p class="text-sm font-medium text-gray-500">Fecha de respuesta</p>
                                <p class="mt-1 text-sm text-gray-900">{{ bitacora.fecha_inicio }}</p>
                            </div>
                        </div>
                        <div class="mt-4">
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
                </div>

                <!-- Respuestas agrupadas por dimensión -->
                <div class="space-y-6">
                    <div
                        v-for="(grupo, index) in respuestasPorDimension"
                        :key="index"
                        class="overflow-hidden bg-white shadow-sm sm:rounded-lg"
                    >
                        <div class="border-b border-gray-200 bg-gray-50 px-6 py-3">
                            <h4 class="text-base font-semibold text-gray-900">
                                {{ grupo.dimension }}
                            </h4>
                        </div>
                        <div class="p-6">
                            <div class="space-y-6">
                                <div
                                    v-for="pregunta in grupo.preguntas"
                                    :key="pregunta.pregunta_id"
                                    class="border-b border-gray-100 pb-4 last:border-b-0 last:pb-0"
                                >
                                    <div class="flex items-start">
                                        <div class="flex-shrink-0">
                                            <span class="inline-flex items-center justify-center h-8 w-8 rounded-full bg-blue-100 text-blue-800 text-sm font-medium">
                                                {{ pregunta.pregunta_id }}
                                            </span>
                                        </div>
                                        <div class="ml-4 flex-1">
                                            <p class="text-sm font-medium text-gray-900">
                                                {{ pregunta.pregunta_texto }}
                                            </p>
                                            <p class="mt-1 text-xs text-gray-500">
                                                Tipo: {{ pregunta.tipo }}
                                            </p>
                                            <div class="mt-3 space-y-2">
                                                <div
                                                    v-for="(resp, respIndex) in pregunta.respuestas"
                                                    :key="respIndex"
                                                    class="rounded-md bg-gray-50 px-3 py-2"
                                                >
                                                    <div class="flex items-center">
                                                        <span
                                                            :class="[
                                                                'inline-flex items-center px-2 py-0.5 rounded text-xs font-medium mr-2',
                                                                resp.tipo === 'numerico'
                                                                    ? 'bg-purple-100 text-purple-800'
                                                                    : 'bg-blue-100 text-blue-800'
                                                            ]"
                                                        >
                                                            {{ resp.tipo === 'numerico' ? '123' : 'ABC' }}
                                                        </span>
                                                        <span class="text-sm text-gray-900">
                                                            {{ resp.valor }}
                                                        </span>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Mensaje si no hay respuestas -->
                <div v-if="respuestas.length === 0" class="bg-white shadow-sm sm:rounded-lg p-12 text-center">
                    <svg class="mx-auto h-12 w-12 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                    </svg>
                    <h3 class="mt-2 text-sm font-medium text-gray-900">No hay respuestas registradas</h3>
                    <p class="mt-1 text-sm text-gray-500">
                        Esta bitácora no tiene respuestas asociadas.
                    </p>
                </div>
            </div>
        </div>
    </AppLayout>
</template>
