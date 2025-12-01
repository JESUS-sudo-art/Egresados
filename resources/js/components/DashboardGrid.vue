<script setup lang="ts">
import ModuleCard from '@/components/ModuleCard.vue';
import { usePage } from '@inertiajs/vue3';
import { computed } from 'vue';

const page = usePage();

// Obtener roles del usuario
// Roles vienen como $page.props.auth.roles (HandleInertiaRequests)
const userRoles = computed(() => ((page.props as any)?.auth?.roles ?? []) as string[]);

// Verificar roles específicos
const isAdminGeneral = computed(() => userRoles.value?.includes('Administrador general'));
const isAdminUnidad = computed(() => userRoles.value?.includes('Administrador de unidad'));
const isAdminAcademico = computed(() => userRoles.value?.includes('Administrador academico'));
const isEgresado = computed(() => userRoles.value?.includes('Egresados'));
const isEstudiante = computed(() => userRoles.value?.includes('Estudiantes'));
const isComunidad = computed(() => userRoles.value?.includes('Comunidad universitaria'));

// Verificar si tiene algún rol administrativo
const isAdmin = computed(() => isAdminGeneral.value || isAdminUnidad.value || isAdminAcademico.value);

// Mostrar módulos de egresado/estudiante (NO mostrar para administradores)
const showEgresadoModules = computed(() => {
  // Si es administrador, no mostrar módulos de egresado
  if (isAdmin.value) return false;
  // Solo mostrar si es egresado o estudiante
  return isEgresado.value || isEstudiante.value;
});

// Mostrar módulos administrativos
const showAdminModules = computed(() => isAdmin.value);
</script>

<template>
  <div class="space-y-6">
    <!-- Mensaje de bienvenida según el rol -->
    <div v-if="isComunidad" class="bg-blue-50 dark:bg-blue-900/20 border border-blue-200 dark:border-blue-800 rounded-lg p-4">
      <p class="text-blue-800 dark:text-blue-200 text-sm">
        👥 Como miembro de la comunidad universitaria, puedes consultar información sobre el seguimiento de egresados.
      </p>
    </div>

    <div class="grid gap-6" :class="showAdminModules && showEgresadoModules ? 'md:grid-cols-3' : showEgresadoModules || showAdminModules ? 'md:grid-cols-1' : 'md:grid-cols-2'">
      <!-- Módulos de Egresado/Estudiante -->
      <div v-if="showEgresadoModules" class="flex flex-col gap-6">
        <div class="grid gap-4 md:grid-cols-2 lg:grid-cols-3">
          
          <!-- Encuesta pre-egreso para estudiantes y egresados (consulta) -->
          <ModuleCard 
            v-if="isEstudiante || isEgresado" 
            title="Encuesta preegreso" 
            subtitle="Llena antes de egresar" 
            color="teal" 
            to="/encuesta-preegreso" 
          />
          
          <!-- Encuestas solo para egresados -->
          <ModuleCard 
            v-if="isEgresado" 
            title="Encuesta de egreso" 
            subtitle="Después de 2 años" 
            color="teal" 
            to="/encuesta-egreso" 
          />
          
          <ModuleCard 
            v-if="isEgresado" 
            title="Encuesta laboral" 
            subtitle="Seguimiento periódico" 
            color="default" 
            to="/encuesta-laboral" 
          />
          
          <!-- Acuses de seguimiento para egresados -->
          <ModuleCard 
            v-if="isEgresado" 
            title="Acuses de seguimiento" 
            subtitle="Consulta tus encuestas" 
            color="default" 
            to="/acuses-seguimiento" 
          />
        </div>
      </div>

      <!-- Módulos Administrativos: se mantienen aquí excepto los que movimos al sidebar (Admin general, Gestor de permisos, Asignar roles, Admin académica, Admin unidad) -->
      <div v-if="showAdminModules" class="flex flex-col gap-4">
        <!-- Reportes e informes (según nivel) -->
        <ModuleCard 
          v-if="isAdminGeneral" 
          title="Reportes e informes" 
          subtitle="Todos los reportes" 
          color="default" 
          to="/reportes-informes" 
        />
        <ModuleCard 
          v-if="isAdminAcademico && !isAdminGeneral" 
          title="Reportes e informes" 
          subtitle="Reportes académicos" 
          color="default" 
          to="/reportes-informes" 
        />
        <ModuleCard 
          v-if="isAdminUnidad && !isAdminGeneral" 
          title="Reportes e informes" 
          subtitle="Reportes de mi unidad" 
          color="default" 
          to="/reportes-informes" 
        />
      </div>
    </div>

    <!-- Información para comunidad universitaria -->
    <div v-if="isComunidad && !showEgresadoModules" class="bg-white dark:bg-gray-800 rounded-lg shadow-md p-6">
      <h3 class="text-lg font-semibold text-gray-800 dark:text-gray-100 mb-4">
        Información de Seguimiento de Egresados
      </h3>
      <p class="text-gray-600 dark:text-gray-400 mb-4">
        Como miembro de la comunidad universitaria, puedes acceder a información pública sobre el seguimiento de egresados.
      </p>
      <div class="grid gap-4 md:grid-cols-2">
        <ModuleCard 
          title="Reportes públicos" 
          subtitle="Consulta estadísticas" 
          color="default" 
          to="/seguimiento-egresados" 
        />
      </div>
    </div>
  </div>
</template>
