<script setup lang="ts">
import { ref } from 'vue';
import { router } from '@inertiajs/vue3';

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

const props = defineProps<Props>();

const selectedUser = ref<User | null>(null);
const selectedRoles = ref<string[]>([]);
const loading = ref(false);
const successMessage = ref('');
const errorMessage = ref('');

const openAssignRoles = (user: User) => {
  selectedUser.value = user;
  selectedRoles.value = [...user.roles];
};

const closeModal = () => {
  selectedUser.value = null;
  selectedRoles.value = [];
};

const toggleRole = (roleName: string) => {
  const index = selectedRoles.value.indexOf(roleName);
  if (index > -1) {
    selectedRoles.value.splice(index, 1);
  } else {
    selectedRoles.value.push(roleName);
  }
};

const saveRoles = () => {
  if (!selectedUser.value) return;

  loading.value = true;
  errorMessage.value = '';
  successMessage.value = '';

  router.post(
    `/usuarios/${selectedUser.value.id}/asignar-roles`,
    {
      roles: selectedRoles.value,
    },
    {
      preserveScroll: true,
      onSuccess: () => {
        successMessage.value = `Roles actualizados para ${selectedUser.value?.name}`;
        closeModal();
        setTimeout(() => {
          successMessage.value = '';
        }, 3000);
      },
      onError: (errors) => {
        errorMessage.value = 'Error al actualizar los roles. Intenta nuevamente.';
        console.error(errors);
      },
      onFinish: () => {
        loading.value = false;
      },
    }
  );
};

const getRoleBadgeColor = (roleName: string): string => {
  const colors: Record<string, string> = {
    'Administrador general': 'bg-red-100 text-red-800 dark:bg-red-900 dark:text-red-300',
    'Administrador de unidad': 'bg-blue-100 text-blue-800 dark:bg-blue-900 dark:text-blue-300',
    'Administrador academico': 'bg-purple-100 text-purple-800 dark:bg-purple-900 dark:text-purple-300',
    'Egresados': 'bg-green-100 text-green-800 dark:bg-green-900 dark:text-green-300',
    'Estudiantes': 'bg-yellow-100 text-yellow-800 dark:bg-yellow-900 dark:text-yellow-300',
    'Comunidad universitaria': 'bg-gray-100 text-gray-800 dark:bg-gray-700 dark:text-gray-300',
  };
  return colors[roleName] || 'bg-gray-100 text-gray-800';
};
</script>

<template>
  <div class="user-role-manager">
    <div class="header mb-6">
      <h2 class="text-2xl font-bold text-gray-800 dark:text-gray-100">
        Gestión de Roles por Usuario
      </h2>
      <p class="text-sm text-gray-600 dark:text-gray-400 mt-2">
        Asigna roles a los usuarios del sistema
      </p>
    </div>

    <!-- Mensajes de éxito/error -->
    <div v-if="successMessage" class="mb-4 p-4 bg-green-100 border border-green-400 text-green-700 rounded">
      {{ successMessage }}
    </div>
    <div v-if="errorMessage" class="mb-4 p-4 bg-red-100 border border-red-400 text-red-700 rounded">
      {{ errorMessage }}
    </div>

    <!-- Tabla de usuarios -->
    <div class="bg-white dark:bg-gray-800 rounded-lg shadow-md overflow-hidden">
      <table class="min-w-full divide-y divide-gray-200 dark:divide-gray-700">
        <thead class="bg-gray-50 dark:bg-gray-700">
          <tr>
            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">
              Usuario
            </th>
            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">
              Email
            </th>
            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">
              Roles Asignados
            </th>
            <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">
              Acciones
            </th>
          </tr>
        </thead>
        <tbody class="bg-white dark:bg-gray-800 divide-y divide-gray-200 dark:divide-gray-700">
          <tr v-for="user in users" :key="user.id" class="hover:bg-gray-50 dark:hover:bg-gray-700">
            <td class="px-6 py-4 whitespace-nowrap">
              <div class="text-sm font-medium text-gray-900 dark:text-gray-100">
                {{ user.name }}
              </div>
            </td>
            <td class="px-6 py-4 whitespace-nowrap">
              <div class="text-sm text-gray-500 dark:text-gray-400">
                {{ user.email }}
              </div>
            </td>
            <td class="px-6 py-4">
              <div class="flex flex-wrap gap-2">
                <span
                  v-for="role in user.roles"
                  :key="role"
                  :class="getRoleBadgeColor(role)"
                  class="px-2 py-1 text-xs font-semibold rounded-full"
                >
                  {{ role }}
                </span>
                <span v-if="user.roles.length === 0" class="text-sm text-gray-400 italic">
                  Sin roles asignados
                </span>
              </div>
            </td>
            <td class="px-6 py-4 whitespace-nowrap text-right text-sm font-medium">
              <button
                @click="openAssignRoles(user)"
                class="text-blue-600 hover:text-blue-900 dark:text-blue-400 dark:hover:text-blue-300"
              >
                Gestionar roles
              </button>
            </td>
          </tr>
        </tbody>
      </table>
    </div>

    <!-- Modal para asignar roles -->
    <div
      v-if="selectedUser"
      class="fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center z-50 p-4"
      @click.self="closeModal"
    >
      <div class="bg-white dark:bg-gray-800 rounded-lg shadow-xl max-w-md w-full p-6">
        <div class="flex justify-between items-center mb-4">
          <h3 class="text-xl font-bold text-gray-900 dark:text-gray-100">
            Asignar Roles
          </h3>
          <button
            @click="closeModal"
            class="text-gray-400 hover:text-gray-600 dark:hover:text-gray-300"
          >
            <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
            </svg>
          </button>
        </div>

        <div class="mb-4">
          <p class="text-sm text-gray-600 dark:text-gray-400">
            Usuario: <span class="font-semibold">{{ selectedUser.name }}</span>
          </p>
          <p class="text-sm text-gray-600 dark:text-gray-400">
            Email: <span class="font-semibold">{{ selectedUser.email }}</span>
          </p>
        </div>

        <div class="mb-6">
          <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-3">
            Selecciona los roles:
          </label>
          <div class="space-y-2">
            <label
              v-for="role in roles"
              :key="role.id"
              class="flex items-center p-3 border border-gray-200 dark:border-gray-600 rounded-lg hover:bg-gray-50 dark:hover:bg-gray-700 cursor-pointer transition"
            >
              <input
                type="checkbox"
                :checked="selectedRoles.includes(role.name)"
                @change="toggleRole(role.name)"
                class="w-4 h-4 text-blue-600 bg-gray-100 border-gray-300 rounded focus:ring-blue-500 dark:focus:ring-blue-600 dark:ring-offset-gray-800 focus:ring-2 dark:bg-gray-700 dark:border-gray-600"
              />
              <span class="ml-3 text-sm text-gray-700 dark:text-gray-300">
                {{ role.name }}
              </span>
            </label>
          </div>
        </div>

        <div class="flex justify-end gap-3">
          <button
            @click="closeModal"
            class="px-4 py-2 bg-gray-200 hover:bg-gray-300 text-gray-800 rounded-lg transition"
            type="button"
          >
            Cancelar
          </button>
          <button
            @click="saveRoles"
            :disabled="loading"
            class="px-4 py-2 bg-blue-600 hover:bg-blue-700 text-white rounded-lg transition disabled:opacity-50 disabled:cursor-not-allowed"
            type="button"
          >
            <span v-if="!loading">Guardar cambios</span>
            <span v-else>Guardando...</span>
          </button>
        </div>
      </div>
    </div>
  </div>
</template>

<style scoped>
.user-role-manager {
  max-width: 1200px;
  margin: 0 auto;
}
</style>
