<script setup lang="ts">
import { Head } from '@inertiajs/vue3';
import AppLayout from '@/layouts/AppLayout.vue';
import UserRoleManager from '@/components/UserRoleManager.vue';

interface User {
  id: number;
  name: string;
  email: string;
  roles: string[];
}

interface Role {
  id: number;
  name: string;
}

interface Props {
  users: User[];
  roles: Role[];
}

defineProps<Props>();
</script>

<template>
  <Head title="Gestión de Usuarios y Roles" />

  <AppLayout>
    <div class="py-8 px-4 sm:px-6 lg:px-8">
      <UserRoleManager :users="users" :roles="roles" />
    </div>
  </AppLayout>
</template>
