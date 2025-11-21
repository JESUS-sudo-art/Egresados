<script setup lang="ts">
import AppLayout from '@/layouts/AppLayout.vue';
import { dashboard } from '@/routes';
import { type BreadcrumbItem } from '@/types';
import { Head } from '@inertiajs/vue3';
import DashboardGrid from '@/components/DashboardGrid.vue';

const breadcrumbs: BreadcrumbItem[] = [
  { title: 'Dashboard', href: dashboard().url }
];
</script>

<template>
  <Head title="Dashboard" />
  <AppLayout :breadcrumbs="breadcrumbs">
    <div class="flex h-full flex-1 flex-col gap-4 overflow-x-auto rounded-xl p-4">
      <div class="relative rounded-xl border border-sidebar-border/70 p-4 dark:border-sidebar-border">
        <h2 class="mb-3 text-lg font-semibold">Interfaces rápidas</h2>
        <p class="mb-4 text-sm text-muted-foreground">
          Panel con accesos rápidos a los módulos principales (basado en el diagrama).
        </p>
        <DashboardGrid />
      </div>
    </div>
  </AppLayout>
</template>
