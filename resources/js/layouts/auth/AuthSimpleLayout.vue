<script setup lang="ts">
import AppLogoIcon from '@/components/AppLogoIcon.vue';
import { Users } from 'lucide-vue-next';

defineProps<{
    title?: string;
    description?: string;
}>();
</script>

<template>
    <div class="min-h-screen flex items-center justify-center bg-slate-100 px-4 py-10">
        <div
            class="grid w-full max-w-5xl overflow-hidden rounded-2xl bg-white shadow-2xl ring-1 ring-slate-200 md:grid-cols-2"
        >
            <!-- Lado izquierdo: contenido y formulario -->
            <div class="p-10 md:p-12">
                <div class="flex flex-col gap-4">
                    <div class="flex items-center gap-3">
                        <div class="flex h-11 w-11 items-center justify-center rounded-xl bg-slate-900 text-white">
                            <AppLogoIcon class="size-7" />
                        </div>
                        <div class="leading-tight">
                            <p class="text-sm font-semibold text-slate-800">Sistema de Egresados</p>
                            <p class="text-xs text-slate-500">UABJO</p>
                        </div>
                    </div>

                    <div class="space-y-2">
                        <h1 class="text-2xl font-semibold text-slate-900">{{ title }}</h1>
                        <p class="text-sm text-slate-600">{{ description }}</p>
                    </div>
                </div>

                <div class="mt-8">
                    <slot />
                </div>
            </div>

            <!-- Lado derecho: panel visual -->
            <div class="flex flex-col items-center justify-center gap-4 bg-indigo-50 p-10 text-center">
                <div class="flex h-16 w-16 items-center justify-center rounded-full bg-white shadow-sm text-indigo-600">
                    <Users class="h-10 w-10" />
                </div>
                <div class="space-y-1">
                    <p class="text-lg font-semibold text-slate-900">Sistema de Egresados</p>
                    <p class="text-sm text-slate-600">Seguimiento y gestión profesional</p>
                </div>
            </div>
        </div>
    </div>
</template>
