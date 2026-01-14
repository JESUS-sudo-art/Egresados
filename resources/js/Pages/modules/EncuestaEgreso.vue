<script setup lang="ts">
import AppLayout from '@/layouts/AppLayout.vue';
import { Head, Link } from '@inertiajs/vue3';
</script>

<template>
  <Head title="Encuesta de Egreso" />
  <AppLayout :breadcrumbs="[{ title: 'Encuesta de Egreso', href: '/encuesta-egreso' }]"><div class="p-4 space-y-4">
    <div class="flex items-center justify-between">
      <h1 class="text-xl font-semibold">Encuesta de Egreso</h1>
      <Link href="/acuses-seguimiento?from=encuesta-egreso" class="inline-flex items-center rounded-md bg-emerald-600 px-3 py-2 text-white hover:bg-emerald-700">
        Acuse de seguimiento
      </Link>
    </div>
    <!-- Contenido de la encuesta de egreso -->
  </div></AppLayout>
</template>
