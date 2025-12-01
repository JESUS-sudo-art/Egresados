<script setup lang="ts">
import AppLayout from '@/layouts/AppLayout.vue';
import { Head, useForm, router, usePage } from '@inertiajs/vue3';
import { ref, computed } from 'vue';
import { Button } from '@/components/ui/button';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import {
  Card,
  CardContent,
  CardDescription,
  CardHeader,
  CardTitle,
} from '@/components/ui/card';

interface Egresado {
  id: number;
  matricula: string | null;
  nombre: string;
  apellidos: string;
  curp: string | null;
  email: string;
  domicilio: string | null;
  fecha_nacimiento: string | null;
  estado_origen: string | null;
  genero_id: number | null;
  estado_civil_id: number | null;
  estatus_id: number | null;
  genero?: { id: number; nombre: string };
  estadoCivil?: { id: number; nombre: string };
  estatus?: { id: number; nombre: string };
  carreras?: Array<{
    carrera?: { nombre: string };
    generacion?: { nombre: string };
  }>;
}

interface Catalogo {
  id: number;
  nombre: string;
}

interface Empleo {
  id: number;
  empresa: string;
  puesto: string | null;
  sector: string | null;
  fecha_inicio: string | null;
  fecha_fin: string | null;
  actualmente_activo: boolean;
}

const props = defineProps<{
  egresado: Egresado | null;
  generos: Catalogo[];
  estadosCiviles: Catalogo[];
  estatuses: Catalogo[];
  empleos: Empleo[];
}>();

const page = usePage();

// Verificar el rol del usuario
const userRoles = computed(() => ((page.props as any)?.auth?.roles ?? []) as string[]);
const isEstudiante = computed(() => userRoles.value?.includes('Estudiantes'));
const isEgresado = computed(() => userRoles.value?.includes('Egresados'));

// Solo mostrar pestaña laboral para egresados, no para estudiantes
const showLaboralTab = computed(() => isEgresado.value && !isEstudiante.value);

const activeTab = ref('personales');

// Formulario de Datos Personales
const formPersonales = useForm({
  id: props.egresado?.id || 0,
  matricula: props.egresado?.matricula || '',
  nombre: props.egresado?.nombre || '',
  apellidos: props.egresado?.apellidos || '',
  curp: props.egresado?.curp || '',
  email: props.egresado?.email || '',
  domicilio: props.egresado?.domicilio || '',
  fecha_nacimiento: props.egresado?.fecha_nacimiento || '',
  estado_origen: props.egresado?.estado_origen || '',
  genero_id: props.egresado?.genero_id || null,
  estado_civil_id: props.egresado?.estado_civil_id || null,
  estatus_id: props.egresado?.estatus_id || null,
});

const submitPersonales = () => {
  formPersonales.post('/perfil/datos-personales', {
    preserveScroll: true,
  });
};

// Formulario de Empleos
const formEmpleo = useForm({
  egresado_id: props.egresado?.id || 0,
  empresa: '',
  puesto: '',
  sector: '',
  fecha_inicio: '',
  fecha_fin: '',
  actualmente_activo: false,
});

const editingEmpleoId = ref<number | null>(null);

const submitEmpleo = () => {
  if (editingEmpleoId.value) {
    formEmpleo.put(`/perfil/empleos/${editingEmpleoId.value}`, {
      preserveScroll: true,
      onSuccess: () => {
        resetFormEmpleo();
      },
    });
  } else {
    formEmpleo.post('/perfil/empleos', {
      preserveScroll: true,
      onSuccess: () => {
        resetFormEmpleo();
      },
    });
  }
};

const editEmpleo = (empleo: Empleo) => {
  editingEmpleoId.value = empleo.id;
  formEmpleo.empresa = empleo.empresa;
  formEmpleo.puesto = empleo.puesto || '';
  formEmpleo.sector = empleo.sector || '';
  formEmpleo.fecha_inicio = empleo.fecha_inicio || '';
  formEmpleo.fecha_fin = empleo.fecha_fin || '';
  formEmpleo.actualmente_activo = empleo.actualmente_activo;
};

const deleteEmpleo = (id: number) => {
  if (confirm('¿Estás seguro de eliminar este empleo?')) {
    router.delete(`/perfil/empleos/${id}`, {
      preserveScroll: true,
    });
  }
};

const resetFormEmpleo = () => {
  editingEmpleoId.value = null;
  formEmpleo.reset();
  formEmpleo.egresado_id = props.egresado?.id || 0;
};

const carreraInfo = computed(() => {
  if (!props.egresado?.carreras || props.egresado.carreras.length === 0) {
    return null;
  }
  return props.egresado.carreras[0];
});
</script>

<template>
  <Head title="Perfil y Datos" />
  <AppLayout :breadcrumbs="[{ title: 'Perfil y Datos', href: '/perfil-datos' }]">
    <div class="flex h-full flex-1 flex-col gap-4 p-4">
      <div class="rounded-xl border border-sidebar-border/70 dark:border-sidebar-border">
        <!-- Pestañas -->
        <div class="flex border-b border-sidebar-border/70 dark:border-sidebar-border">
          <button
            @click="activeTab = 'personales'"
            :class="[
              'px-6 py-3 font-medium transition-colors',
              activeTab === 'personales'
                ? 'border-b-2 border-blue-600 text-blue-600'
                : 'text-gray-600 hover:text-gray-900 dark:text-gray-400 dark:hover:text-gray-200'
            ]"
          >
            Datos Personales
          </button>
          <button
            @click="activeTab = 'academicos'"
            :class="[
              'px-6 py-3 font-medium transition-colors',
              activeTab === 'academicos'
                ? 'border-b-2 border-blue-600 text-blue-600'
                : 'text-gray-600 hover:text-gray-900 dark:text-gray-400 dark:hover:text-gray-200'
            ]"
          >
            Datos Académicos
          </button>
          <button
            v-if="showLaboralTab"
            @click="activeTab = 'laboral'"
            :class="[
              'px-6 py-3 font-medium transition-colors',
              activeTab === 'laboral'
                ? 'border-b-2 border-blue-600 text-blue-600'
                : 'text-gray-600 hover:text-gray-900 dark:text-gray-400 dark:hover:text-gray-200'
            ]"
          >
            Situación Laboral
          </button>
        </div>

        <!-- Contenido de Pestañas -->
        <div class="p-6">
          <!-- Pestaña 1: Datos Personales -->
          <div v-if="activeTab === 'personales'">
            <form @submit.prevent="submitPersonales" class="space-y-6">
              <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                <div>
                  <Label for="matricula">Matrícula</Label>
                  <Input id="matricula" v-model="formPersonales.matricula" />
                </div>
                <div>
                  <Label for="curp">CURP</Label>
                  <Input 
                    id="curp" 
                    v-model="formPersonales.curp" 
                    maxlength="18"
                    placeholder="Opcional"
                  />
                </div>
                <div>
                  <Label for="nombre">Nombre(s)</Label>
                  <Input id="nombre" v-model="formPersonales.nombre" required />
                </div>
                <div>
                  <Label for="apellidos">Apellidos</Label>
                  <Input id="apellidos" v-model="formPersonales.apellidos" required />
                </div>
                <div>
                  <Label for="email">Email</Label>
                  <Input id="email" type="email" v-model="formPersonales.email" required />
                </div>
                <div>
                  <Label for="genero">Género</Label>
                  <select
                    id="genero"
                    v-model="formPersonales.genero_id"
                    class="flex h-10 w-full rounded-md border border-input bg-background px-3 py-2 text-base ring-offset-background focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-ring focus-visible:ring-offset-2 disabled:cursor-not-allowed disabled:opacity-50"
                  >
                    <option :value="null">Seleccionar género</option>
                    <option v-for="g in generos" :key="g.id" :value="g.id">
                      {{ g.nombre }}
                    </option>
                  </select>
                </div>
                <div>
                  <Label for="estado_civil">Estado Civil</Label>
                  <select
                    id="estado_civil"
                    v-model="formPersonales.estado_civil_id"
                    class="flex h-10 w-full rounded-md border border-input bg-background px-3 py-2 text-base ring-offset-background focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-ring focus-visible:ring-offset-2 disabled:cursor-not-allowed disabled:opacity-50"
                  >
                    <option :value="null">Seleccionar estado civil</option>
                    <option v-for="ec in estadosCiviles" :key="ec.id" :value="ec.id">
                      {{ ec.nombre }}
                    </option>
                  </select>
                </div>
                <div>
                  <Label for="estatus">Estatus</Label>
                  <select
                    id="estatus"
                    v-model="formPersonales.estatus_id"
                    disabled
                    class="flex h-10 w-full rounded-md border border-input bg-muted px-3 py-2 text-base ring-offset-background disabled:cursor-not-allowed disabled:opacity-50"
                  >
                    <option :value="null">Seleccionar estatus</option>
                    <option v-for="e in estatuses" :key="e.id" :value="e.id">
                      {{ e.nombre }}
                    </option>
                  </select>
                  <p class="text-xs text-muted-foreground mt-1">Este campo se establece automáticamente según tu tipo de usuario</p>
                </div>
                <div class="md:col-span-2">
                  <Label for="domicilio">Domicilio</Label>
                  <Input 
                    id="domicilio" 
                    v-model="formPersonales.domicilio" 
                    placeholder="Opcional"
                  />
                </div>
                <div>
                  <Label for="fecha_nacimiento">Fecha de Nacimiento</Label>
                  <Input 
                    id="fecha_nacimiento" 
                    type="date"
                    v-model="formPersonales.fecha_nacimiento" 
                  />
                </div>
                <div>
                  <Label for="estado_origen">Estado de Origen</Label>
                  <Input 
                    id="estado_origen" 
                    v-model="formPersonales.estado_origen" 
                    placeholder="Ej. Oaxaca"
                  />
                </div>
              </div>
              <div class="flex justify-end">
                <Button type="submit" :disabled="formPersonales.processing">
                  {{ formPersonales.processing ? 'Guardando...' : 'Guardar Datos Personales' }}
                </Button>
              </div>
            </form>
          </div>

          <!-- Pestaña 2: Datos Académicos -->
          <div v-if="activeTab === 'academicos'">
            <Card>
              <CardHeader>
                <CardTitle>Información Académica</CardTitle>
                <CardDescription>Datos registrados al crear tu cuenta</CardDescription>
              </CardHeader>
              <CardContent>
                <div class="space-y-4">
                  <div>
                    <Label class="text-muted-foreground">Unidad</Label>
                    <p class="text-lg font-medium">{{ egresado?.unidad?.nombre || 'No disponible' }}</p>
                  </div>
                  <div>
                    <Label class="text-muted-foreground">Carrera</Label>
                    <p class="text-lg font-medium">{{ egresado?.carrera?.nombre || 'No disponible' }}</p>
                  </div>
                  <div v-if="egresado?.anio_egreso">
                    <Label class="text-muted-foreground">Año de Egreso</Label>
                    <p class="text-lg font-medium">{{ egresado.anio_egreso }}</p>
                  </div>
                  <div v-if="carreraInfo">
                    <Label class="text-muted-foreground">Generación</Label>
                    <p class="text-lg font-medium">{{ carreraInfo.generacion?.nombre || 'No disponible' }}</p>
                  </div>
                </div>
              </CardContent>
            </Card>
          </div>

          <!-- Pestaña 3: Situación Laboral -->
          <div v-if="showLaboralTab && activeTab === 'laboral'" class="space-y-6">
            <!-- Formulario de Empleo -->
            <Card>
              <CardHeader>
                <CardTitle>{{ editingEmpleoId ? 'Editar Empleo' : 'Añadir Nuevo Empleo' }}</CardTitle>
              </CardHeader>
              <CardContent>
                <form @submit.prevent="submitEmpleo" class="space-y-4">
                  <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <div>
                      <Label for="empresa">Empresa *</Label>
                      <Input id="empresa" v-model="formEmpleo.empresa" required />
                    </div>
                    <div>
                      <Label for="puesto">Puesto</Label>
                      <Input id="puesto" v-model="formEmpleo.puesto" />
                    </div>
                    <div>
                      <Label for="sector">Sector</Label>
                      <Input id="sector" v-model="formEmpleo.sector" />
                    </div>
                    <div>
                      <Label for="fecha_inicio">Fecha de Inicio</Label>
                      <Input id="fecha_inicio" type="date" v-model="formEmpleo.fecha_inicio" />
                    </div>
                    <div>
                      <Label for="fecha_fin">Fecha de Fin</Label>
                      <Input id="fecha_fin" type="date" v-model="formEmpleo.fecha_fin" />
                    </div>
                    <div class="flex items-center space-x-2">
                      <input
                        type="checkbox"
                        id="actualmente_activo"
                        v-model="formEmpleo.actualmente_activo"
                        class="h-4 w-4 rounded border-gray-300"
                      />
                      <Label for="actualmente_activo" class="!mb-0">Actualmente activo</Label>
                    </div>
                  </div>
                  <div class="flex gap-2 justify-end">
                    <Button v-if="editingEmpleoId" type="button" variant="outline" @click="resetFormEmpleo">
                      Cancelar
                    </Button>
                    <Button type="submit" :disabled="formEmpleo.processing">
                      {{ formEmpleo.processing ? 'Guardando...' : (editingEmpleoId ? 'Actualizar' : 'Añadir') }}
                    </Button>
                  </div>
                </form>
              </CardContent>
            </Card>

            <!-- Lista de Empleos -->
            <Card>
              <CardHeader>
                <CardTitle>Historial de Empleos</CardTitle>
              </CardHeader>
              <CardContent>
                <div v-if="empleos.length === 0" class="text-center py-8 text-muted-foreground">
                  No hay empleos registrados.
                </div>
                <div v-else class="space-y-4">
                  <div
                    v-for="empleo in empleos"
                    :key="empleo.id"
                    class="border rounded-lg p-4 flex justify-between items-start hover:bg-accent/50 transition-colors"
                  >
                    <div class="space-y-1">
                      <h4 class="font-semibold">{{ empleo.empresa }}</h4>
                      <p v-if="empleo.puesto" class="text-sm text-muted-foreground">{{ empleo.puesto }}</p>
                      <p v-if="empleo.sector" class="text-xs text-muted-foreground">{{ empleo.sector }}</p>
                      <p class="text-xs text-muted-foreground">
                        {{ empleo.fecha_inicio || 'Sin fecha' }} - 
                        {{ empleo.actualmente_activo ? 'Presente' : (empleo.fecha_fin || 'Sin fecha') }}
                      </p>
                    </div>
                    <div class="flex gap-2">
                      <Button size="sm" variant="outline" @click="editEmpleo(empleo)">
                        Editar
                      </Button>
                      <Button size="sm" variant="destructive" @click="deleteEmpleo(empleo.id)">
                        Eliminar
                      </Button>
                    </div>
                  </div>
                </div>
              </CardContent>
            </Card>
          </div>
        </div>
      </div>
    </div>
  </AppLayout>
</template>

