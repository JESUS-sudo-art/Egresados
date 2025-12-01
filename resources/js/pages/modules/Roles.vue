<script setup lang="ts">
import AppLayout from '@/layouts/AppLayout.vue';
import { Head, useForm, usePage } from '@inertiajs/vue3';
import { ref, computed } from 'vue';

interface Role {
  id: number;
  name: string;
  guard_name: string;
  created_at: string;
}

const page = usePage();
const roles = computed<Role[]>(() => (page.props as any).roles ?? []);

const form = useForm({
  name: '' as string,
});

const submitting = ref(false);

function submit() {
  submitting.value = true;
  form.post('/roles', {
    preserveScroll: true,
    onFinish: () => { submitting.value = false; },
    onSuccess: () => { form.reset('name'); },
  });
}
</script>

<template>
  <AppLayout>
    <Head title="Roles" />

    <div class="p-6 space-y-6">
      <div>
        <h1 class="text-2xl font-semibold">Gestión de Roles</h1>
        <p class="text-sm text-gray-500">Crear nuevos roles (solo nombre). No se asignan permisos aquí.</p>
      </div>

      <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
        <!-- Crear rol -->
        <div class="rounded-lg border p-4 bg-white">
          <h2 class="text-lg font-medium mb-3">Crear rol</h2>
          <form @submit.prevent="submit" class="space-y-3">
            <div>
              <label for="name" class="block text-sm font-medium">Nombre del rol</label>
              <input
                id="name"
                v-model="form.name"
                type="text"
                class="mt-1 w-full rounded border px-3 py-2"
                placeholder="Ej. Editor de contenidos"
                :disabled="submitting"
              />
              <div v-if="form.errors.name" class="text-sm text-red-600 mt-1">{{ form.errors.name }}</div>
            </div>
            <div class="flex items-center gap-3">
              <button type="submit" class="rounded bg-blue-600 text-white px-4 py-2" :disabled="submitting">
                {{ submitting ? 'Creando…' : 'Crear' }}
              </button>
              <span v-if="(page.props as any).flash?.success" class="text-green-700 text-sm">
                {{ (page.props as any).flash.success }}
              </span>
            </div>
          </form>
        </div>

        <!-- Listado de roles -->
        <div class="rounded-lg border p-4 bg-white">
          <h2 class="text-lg font-medium mb-3">Roles existentes</h2>
          <div v-if="!roles.length" class="text-sm text-gray-500">No hay roles registrados.</div>
          <table v-else class="w-full text-sm">
            <thead>
              <tr class="text-left border-b">
                <th class="py-2 pr-2">Nombre</th>
                <th class="py-2 pr-2">Guard</th>
                <th class="py-2 pr-2">Creado</th>
              </tr>
            </thead>
            <tbody>
              <tr v-for="r in roles" :key="r.id" class="border-b last:border-b-0">
                <td class="py-2 pr-2">{{ r.name }}</td>
                <td class="py-2 pr-2">{{ r.guard_name }}</td>
                <td class="py-2 pr-2">{{ new Date(r.created_at).toLocaleString() }}</td>
              </tr>
            </tbody>
          </table>
        </div>
      </div>
    </div>
  </AppLayout>
</template>
