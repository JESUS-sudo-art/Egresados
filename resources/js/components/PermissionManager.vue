<script setup lang="ts">
import { ref, onMounted } from 'vue';
import { router } from '@inertiajs/vue3';

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

const props = defineProps<Props>();

const rolePermissions = ref<Record<number, string[]>>({});
const loading = ref(false);
const successMessage = ref('');
const errorMessage = ref('');

onMounted(() => {
  // Inicializar los permisos de cada rol
  props.roles.forEach(role => {
    rolePermissions.value[role.id] = role.permissions.map(p => p.name);
  });
});

const hasPermission = (roleId: number, permissionName: string): boolean => {
  return rolePermissions.value[roleId]?.includes(permissionName) || false;
};

const togglePermission = (roleId: number, permissionName: string) => {
  if (!rolePermissions.value[roleId]) {
    rolePermissions.value[roleId] = [];
  }

  const index = rolePermissions.value[roleId].indexOf(permissionName);
  if (index > -1) {
    rolePermissions.value[roleId].splice(index, 1);
  } else {
    rolePermissions.value[roleId].push(permissionName);
  }
};

const saveRolePermissions = async (role: Role) => {
  loading.value = true;
  errorMessage.value = '';
  successMessage.value = '';

  try {
    router.post(
      `/permisos/roles/${role.id}`,
      {
        permissions: rolePermissions.value[role.id] || [],
      },
      {
        preserveScroll: true,
        onSuccess: () => {
          successMessage.value = `Permisos del rol "${role.name}" actualizados correctamente.`;
          setTimeout(() => {
            successMessage.value = '';
          }, 3000);
        },
        onError: (errors) => {
          errorMessage.value = 'Error al actualizar los permisos. Intenta nuevamente.';
          console.error(errors);
        },
        onFinish: () => {
          loading.value = false;
        },
      }
    );
  } catch (error) {
    errorMessage.value = 'Error al guardar los permisos.';
    console.error(error);
    loading.value = false;
  }
};

const selectAllPermissions = (roleId: number) => {
  rolePermissions.value[roleId] = props.permissions.map(p => p.name);
};

const deselectAllPermissions = (roleId: number) => {
  rolePermissions.value[roleId] = [];
};

const getPermissionLabel = (permissionName: string): string => {
  const labels: Record<string, string> = {
    ver: 'Ver listados',
    ver_uno: 'Ver detalle',
    crear: 'Crear',
    actualizar: 'Actualizar',
    eliminar: 'Eliminar',
    restaurar: 'Restaurar',
    forzar_eliminacion: 'Eliminar permanentemente',
  };
  return labels[permissionName] || permissionName;
};
</script>

<template>
  <div class="permission-manager">
    <div class="header mb-6">
      <h2 class="text-2xl font-bold text-gray-800 dark:text-gray-100">
        Gestor de Permisos
      </h2>
      <p class="text-sm text-gray-600 dark:text-gray-400 mt-2">
        Administra los permisos para cada rol del sistema
      </p>
    </div>

    <!-- Mensajes de éxito/error -->
    <div v-if="successMessage" class="mb-4 p-4 bg-green-100 border border-green-400 text-green-700 rounded">
      {{ successMessage }}
    </div>
    <div v-if="errorMessage" class="mb-4 p-4 bg-red-100 border border-red-400 text-red-700 rounded">
      {{ errorMessage }}
    </div>

    <!-- Tabla de permisos -->
    <div class="roles-grid space-y-6">
      <div
        v-for="role in roles"
        :key="role.id"
        class="role-card bg-white dark:bg-gray-800 rounded-lg shadow-md p-6 border border-gray-200 dark:border-gray-700"
      >
        <div class="role-header flex justify-between items-center mb-4">
          <h3 class="text-xl font-semibold text-gray-800 dark:text-gray-100">
            {{ role.name }}
          </h3>
          <div class="action-buttons flex gap-2">
            <button
              @click="selectAllPermissions(role.id)"
              class="text-sm px-3 py-1 bg-blue-100 hover:bg-blue-200 text-blue-700 rounded transition"
              type="button"
            >
              Seleccionar todos
            </button>
            <button
              @click="deselectAllPermissions(role.id)"
              class="text-sm px-3 py-1 bg-gray-100 hover:bg-gray-200 text-gray-700 rounded transition"
              type="button"
            >
              Deseleccionar todos
            </button>
          </div>
        </div>

        <div class="permissions-grid grid grid-cols-2 md:grid-cols-3 lg:grid-cols-4 gap-4 mb-4">
          <label
            v-for="permission in permissions"
            :key="permission.id"
            class="permission-item flex items-center space-x-2 cursor-pointer hover:bg-gray-50 dark:hover:bg-gray-700 p-2 rounded transition"
          >
            <input
              type="checkbox"
              :checked="hasPermission(role.id, permission.name)"
              @change="togglePermission(role.id, permission.name)"
              class="w-4 h-4 text-blue-600 bg-gray-100 border-gray-300 rounded focus:ring-blue-500 dark:focus:ring-blue-600 dark:ring-offset-gray-800 focus:ring-2 dark:bg-gray-700 dark:border-gray-600"
            />
            <span class="text-sm text-gray-700 dark:text-gray-300">
              {{ getPermissionLabel(permission.name) }}
            </span>
          </label>
        </div>

        <div class="role-actions flex justify-end">
          <button
            @click="saveRolePermissions(role)"
            :disabled="loading"
            class="px-6 py-2 bg-blue-600 hover:bg-blue-700 text-white font-medium rounded-lg transition disabled:opacity-50 disabled:cursor-not-allowed"
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
.permission-manager {
  max-width: 1400px;
  margin: 0 auto;
}

.role-card {
  transition: transform 0.2s;
}

.role-card:hover {
  transform: translateY(-2px);
}

.permission-item:hover input[type="checkbox"] {
  border-color: #3b82f6;
}
</style>
