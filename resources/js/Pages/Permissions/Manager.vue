<script setup lang="ts">
import { Head } from '@inertiajs/vue3';
import AppLayout from '@/layouts/AppLayout.vue';
import PermissionManager from '@/components/PermissionManager.vue';

interface Permission {
  id: number;
  name: string;
}

interface Role {
  id: number;
  name: string;
  permissions: Permission[];
}

interface Props {
  roles: Role[];
  permissions: Permission[];
}

defineProps<Props>();
</script>

<template>
  <Head title="Gestión de Permisos" />

  <AppLayout>
    <div class="py-8 px-4 sm:px-6 lg:px-8">
      <PermissionManager :roles="roles" :permissions="permissions" />
    </div>
  </AppLayout>
</template>
