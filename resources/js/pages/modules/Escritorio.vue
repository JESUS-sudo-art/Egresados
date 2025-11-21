<script setup lang="ts">
import AppLayout from '@/layouts/AppLayout.vue';
import { Head } from '@inertiajs/vue3';
</script>

<template>
  <Head title="Escritorio (Agenda)" />
  <AppLayout :breadcrumbs="[{ title: 'Escritorio', href: '/escritorio' }]">
    <!-- Vista general del hub del egresado -->
    <div class="p-4">Escritorio (Agenda)</div>
  </AppLayout>
</template>
