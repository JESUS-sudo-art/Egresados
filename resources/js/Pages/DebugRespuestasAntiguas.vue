<script setup lang="ts">
import AppLayout from '@/layouts/AppLayout.vue';
import { Head } from '@inertiajs/vue3';
import { AlertCircle, CheckCircle, Users } from 'lucide-vue-next';

interface User {
    id: number;
    email: string;
    nombre: string;
    apellidos: string;
    bitacoras: number;
}

interface Props {
    currentUser: {
        id: number;
        email: string;
        name: string;
        roles: string[];
    } | null;
    currentEgresado: {
        id: number;
        nombre: string;
        apellidos: string;
        bitacoras_count: number;
    } | null;
    usersWithBitacoras: User[];
}

defineProps<Props>();
</script>

<template>
    <Head title="Debug - Respuestas Antiguas" />

    <AppLayout>
        <template #header>
            <h2 class="text-xl font-semibold leading-tight text-gray-800">
                Debug - Verificar Acceso a Respuestas Antiguas
            </h2>
        </template>

        <div class="py-12">
            <div class="mx-auto max-w-7xl sm:px-6 lg:px-8 space-y-6">
                <!-- Current User Info Card -->
                <div class="overflow-hidden bg-white shadow-sm sm:rounded-lg">
                    <div class="p-6 text-gray-900">
                        <div class="mb-6">
                            <h3 class="text-lg font-medium text-gray-900">
                                Tu Información Actual
                            </h3>
                            <p class="mt-1 text-sm text-gray-600">
                                Verifica si tienes acceso a tus respuestas antiguas
                            </p>
                        </div>

                        <div v-if="currentUser" class="space-y-6">
                            <!-- User Details Grid -->
                            <div class="grid grid-cols-1 gap-4 sm:grid-cols-2 lg:grid-cols-4">
                                <div class="rounded-lg border border-gray-200 p-4">
                                    <p class="text-xs font-semibold uppercase tracking-wider text-gray-600">
                                        Email
                                    </p>
                                    <p class="mt-2 text-sm font-medium text-gray-900">
                                        {{ currentUser.email }}
                                    </p>
                                </div>
                                <div class="rounded-lg border border-gray-200 p-4">
                                    <p class="text-xs font-semibold uppercase tracking-wider text-gray-600">
                                        Nombre
                                    </p>
                                    <p class="mt-2 text-sm font-medium text-gray-900">
                                        {{ currentUser.name }}
                                    </p>
                                </div>
                                <div class="rounded-lg border border-gray-200 p-4">
                                    <p class="text-xs font-semibold uppercase tracking-wider text-gray-600">
                                        ID Usuario
                                    </p>
                                    <p class="mt-2 text-sm font-medium text-gray-900">
                                        #{{ currentUser.id }}
                                    </p>
                                </div>
                                <div class="rounded-lg border border-gray-200 p-4">
                                    <p class="text-xs font-semibold uppercase tracking-wider text-gray-600">
                                        Roles
                                    </p>
                                    <div class="mt-2 flex flex-wrap gap-1">
                                        <span
                                            v-for="role in currentUser.roles"
                                            :key="role"
                                            class="inline-flex items-center px-2 py-1 rounded text-xs font-medium bg-blue-100 text-blue-800"
                                        >
                                            {{ role }}
                                        </span>
                                    </div>
                                </div>
                            </div>

                            <!-- Egresado Status Section -->
                            <div class="border-t border-gray-200 pt-6">
                                <div v-if="currentEgresado">
                                    <div class="rounded-lg border border-green-200 bg-green-50 p-4">
                                        <div class="flex items-start gap-3">
                                            <CheckCircle class="h-5 w-5 flex-shrink-0 text-green-600 mt-0.5" />
                                            <div class="flex-1">
                                                <p class="font-semibold text-green-900">
                                                    ✓ Egresado Encontrado
                                                </p>
                                                <p class="mt-1 text-sm text-green-800">
                                                    {{ currentEgresado.nombre }} {{ currentEgresado.apellidos }}
                                                </p>
                                                <div class="mt-3 flex items-center gap-4">
                                                    <span class="text-sm font-semibold text-green-900">
                                                        Respuestas Antiguas: <span class="text-lg">{{ currentEgresado.bitacoras_count }}</span>
                                                    </span>
                                                    <a
                                                        v-if="currentEgresado.bitacoras_count > 0"
                                                        href="/respuestas-antiguas"
                                                        class="inline-flex items-center px-4 py-2 rounded-md bg-green-600 text-white text-sm font-semibold hover:bg-green-700 transition"
                                                    >
                                                        Ver Respuestas →
                                                    </a>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                                <div v-else>
                                    <div class="rounded-lg border border-yellow-200 bg-yellow-50 p-4">
                                        <div class="flex items-start gap-3">
                                            <AlertCircle class="h-5 w-5 flex-shrink-0 text-yellow-600 mt-0.5" />
                                            <div class="flex-1">
                                                <p class="font-semibold text-yellow-900">
                                                    ⚠ No hay Egresado Asociado
                                                </p>
                                                <p class="mt-1 text-sm text-yellow-800">
                                                    No se encontró un egresado con el email <strong>{{ currentUser.email }}</strong>
                                                </p>
                                                <p class="mt-2 text-xs text-yellow-700">
                                                    Tu usuario probablemente no existía en el sistema anterior. Prueba con uno de los emails que aparecen abajo.
                                                </p>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Available Users Table -->
                <div class="overflow-hidden bg-white shadow-sm sm:rounded-lg">
                    <div class="p-6 text-gray-900">
                        <div class="mb-6 flex items-center gap-2">
                            <Users class="h-5 w-5 text-gray-600" />
                            <div>
                                <h3 class="text-lg font-medium text-gray-900">
                                    Egresados con Respuestas Antiguas
                                </h3>
                                <p class="mt-1 text-sm text-gray-600">
                                    {{ usersWithBitacoras.length }} usuarios disponibles para prueba
                                </p>
                            </div>
                        </div>

                        <div class="space-y-2">
                            <div
                                v-for="user in usersWithBitacoras"
                                :key="user.id"
                                class="flex items-center justify-between rounded-lg border border-gray-200 p-4 hover:border-blue-300 hover:bg-blue-50 transition-colors"
                            >
                                <div class="flex-1">
                                    <p class="font-medium text-gray-900">
                                        {{ user.nombre }} {{ user.apellidos }}
                                    </p>
                                    <p class="mt-1 text-sm text-gray-600 font-mono">
                                        {{ user.email }}
                                    </p>
                                </div>
                                <div class="flex items-center gap-4">
                                    <span class="inline-flex items-center px-3 py-1 rounded-full text-sm font-semibold bg-blue-100 text-blue-800">
                                        {{ user.bitacoras }} respuestas
                                    </span>
                                </div>
                            </div>

                            <div v-if="usersWithBitacoras.length === 0" class="text-center py-8">
                                <p class="text-gray-500">No hay egresados con respuestas antiguas</p>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Info Box -->
                <div class="rounded-lg border border-blue-200 bg-blue-50 p-6">
                    <p class="font-semibold text-blue-900 mb-3">
                        💡 ¿Cómo usar esta página?
                    </p>
                    <ul class="space-y-2 text-sm text-blue-800">
                        <li>• Si tienes respuestas antiguas, verás el número en tu card de arriba</li>
                        <li>• Si no apareces en la lista de abajo, tu usuario no estaba en el sistema anterior</li>
                        <li>• Puedes copiar cualquier email de abajo para loguearte y probar la funcionalidad</li>
                    </ul>
                </div>
            </div>
        </div>
    </AppLayout>
</template>

