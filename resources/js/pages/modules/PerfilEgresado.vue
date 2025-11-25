<script setup lang="ts">
import { ref } from 'vue'
import AppLayout from '@/layouts/AppLayout.vue'
import { Head, router, useForm } from '@inertiajs/vue3'
import { Card, CardContent, CardDescription, CardHeader, CardTitle } from '@/components/ui/card'
import { Input } from '@/components/ui/input'
import { Button } from '@/components/ui/button'
import { Badge } from '@/components/ui/badge'
import { Label } from '@/components/ui/label'
import { Separator } from '@/components/ui/separator'
import { TrashIcon } from 'lucide-vue-next'

interface Carrera {
  id: number
  carrera_id: number
  carrera_nombre: string
  generacion_id: number
  generacion_nombre: string
  fecha_ingreso: string | null
  fecha_egreso: string | null
  tipo_egreso: string | null
}

interface Empleo {
  id: number
  puesto: string
  empresa: string
  fecha_inicio: string
  fecha_fin: string | null
  sueldo: number | null
  ciudad: string | null
}

interface Egresado {
  id: number
  matricula: string | null
  nombre: string
  apellidos: string
  curp: string | null
  email: string
  fecha_nacimiento: string | null
  lugar_nacimiento: string | null
  domicilio: string | null
  domicilio_actual: string | null
  genero_id: number | null
  genero: string | null
  estado_civil_id: number | null
  estado_civil: string | null
  estatus_id: number
  estatus: string
  tiene_hijos: boolean
  habla_lengua_indigena: boolean
  habla_segundo_idioma: boolean
  pertenece_grupo_etnico: boolean
  facebook_url: string | null
  tipo_estudiante: string | null
  validado_sice: string | null
  carreras: Carrera[]
  empleos: Empleo[]
  tiene_usuario: boolean
  roles_usuario: string[]
}

interface Catalogos {
  carreras: { id: number; nombre: string }[]
  generaciones: { id: number; nombre: string }[]
  estadosCiviles: { id: number; nombre: string }[]
  estatuses: { id: number; nombre: string }[]
  generos: { id: number; nombre: string }[]
}

interface Props {
  egresado: Egresado
  catalogos: Catalogos
}

const props = defineProps<Props>()

// Tab activa
const activeTab = ref<'datos' | 'carreras' | 'empleos' | 'usuario'>('datos')

// Formulario de datos personales
const formDatos = useForm({
  matricula: props.egresado.matricula,
  nombre: props.egresado.nombre,
  apellidos: props.egresado.apellidos,
  curp: props.egresado.curp,
  email: props.egresado.email,
  fecha_nacimiento: props.egresado.fecha_nacimiento,
  lugar_nacimiento: props.egresado.lugar_nacimiento,
  domicilio: props.egresado.domicilio,
  domicilio_actual: props.egresado.domicilio_actual,
  genero_id: props.egresado.genero_id,
  estado_civil_id: props.egresado.estado_civil_id,
  estatus_id: props.egresado.estatus_id,
  tiene_hijos: props.egresado.tiene_hijos,
  habla_lengua_indigena: props.egresado.habla_lengua_indigena,
  habla_segundo_idioma: props.egresado.habla_segundo_idioma,
  pertenece_grupo_etnico: props.egresado.pertenece_grupo_etnico,
  facebook_url: props.egresado.facebook_url,
  tipo_estudiante: props.egresado.tipo_estudiante,
})

const submitDatos = () => {
  formDatos.transform((data) => ({
    ...data,
    tiene_hijos: !!data.tiene_hijos,
    habla_lengua_indigena: !!data.habla_lengua_indigena,
    habla_segundo_idioma: !!data.habla_segundo_idioma,
    pertenece_grupo_etnico: !!data.pertenece_grupo_etnico,
  })).put(`/egresados/${props.egresado.id}`, {
    preserveScroll: true,
    onSuccess: () => {
      // Mostrar mensaje de éxito
    },
  })
}

// Formulario de contraseña
const showPasswordForm = ref(false)
const formPassword = useForm({
  password: '',
  password_confirmation: '',
})

const submitPassword = () => {
  formPassword.put(`/egresados/${props.egresado.id}/password`, {
    preserveScroll: true,
    onSuccess: () => {
      formPassword.reset()
      showPasswordForm.value = false
    },
  })
}

// Formulario agregar carrera
const showCarreraForm = ref(false)
const formCarrera = useForm({
  carrera_id: null as number | null,
  generacion_id: null as number | null,
  fecha_ingreso: '',
  fecha_egreso: '',
})

const submitCarrera = () => {
  formCarrera.post(`/egresados/${props.egresado.id}/carreras`, {
    preserveScroll: true,
    onSuccess: () => {
      formCarrera.reset()
      showCarreraForm.value = false
    },
  })
}

const deleteCarrera = (carreraId: number) => {
  if (confirm('¿Eliminar esta carrera del egresado?')) {
    router.delete(`/egresados/${props.egresado.id}/carreras/${carreraId}`, {
      preserveScroll: true,
    })
  }
}
</script>

<template>
  <Head :title="`Perfil: ${egresado.nombre} ${egresado.apellidos}`" />
  <AppLayout
    :breadcrumbs="[
      { title: 'Catálogo Egresados', href: '/catalogo-egresados' },
      { title: `${egresado.nombre} ${egresado.apellidos}`, href: `/egresados/${egresado.id}` },
    ]"
  >
    <div class="container mx-auto px-4 py-8 max-w-7xl space-y-6">
      <div class="flex justify-between items-center">
        <div>
          <h1 class="text-3xl font-bold">{{ egresado.nombre }} {{ egresado.apellidos }}</h1>
          <p class="text-muted-foreground">
            <span v-if="egresado.matricula">Matrícula: {{ egresado.matricula }} | </span>
            <span>{{ egresado.email }}</span>
          </p>
        </div>
        <Badge :variant="egresado.estatus === 'ACTIVO' ? 'default' : 'secondary'">
          {{ egresado.estatus }}
        </Badge>
      </div>

      <!-- Tabs manuales -->
      <div class="border-b">
        <nav class="flex gap-4">
          <button
            @click="activeTab = 'datos'"
            :class="[
              'px-4 py-2 border-b-2 font-medium transition-colors',
              activeTab === 'datos'
                ? 'border-primary text-primary'
                : 'border-transparent text-muted-foreground hover:text-foreground'
            ]"
          >
            Datos Personales
          </button>
          <button
            @click="activeTab = 'carreras'"
            :class="[
              'px-4 py-2 border-b-2 font-medium transition-colors',
              activeTab === 'carreras'
                ? 'border-primary text-primary'
                : 'border-transparent text-muted-foreground hover:text-foreground'
            ]"
          >
            Carreras ({{ egresado.carreras.length }})
          </button>
          <button
            @click="activeTab = 'empleos'"
            :class="[
              'px-4 py-2 border-b-2 font-medium transition-colors',
              activeTab === 'empleos'
                ? 'border-primary text-primary'
                : 'border-transparent text-muted-foreground hover:text-foreground'
            ]"
          >
            Empleos ({{ egresado.empleos.length }})
          </button>
          <button
            @click="activeTab = 'usuario'"
            :class="[
              'px-4 py-2 border-b-2 font-medium transition-colors',
              activeTab === 'usuario'
                ? 'border-primary text-primary'
                : 'border-transparent text-muted-foreground hover:text-foreground'
            ]"
          >
            Usuario
          </button>
        </nav>
      </div>

      <!-- Tab Datos Personales -->
      <div v-show="activeTab === 'datos'" class="space-y-6">
        <Card>
          <CardHeader>
            <CardTitle>Información Personal</CardTitle>
            <CardDescription>Datos del egresado</CardDescription>
          </CardHeader>
          <CardContent>
            <form @submit.prevent="submitDatos" class="space-y-4">
              <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                <div>
                  <Label for="matricula">Matrícula</Label>
                  <Input id="matricula" v-model="formDatos.matricula" />
                </div>

                <div>
                  <Label for="curp">CURP</Label>
                  <Input id="curp" v-model="formDatos.curp" maxlength="18" />
                </div>

                <div>
                  <Label for="nombre">Nombre(s) *</Label>
                  <Input id="nombre" v-model="formDatos.nombre" required />
                </div>

                <div>
                  <Label for="apellidos">Apellidos *</Label>
                  <Input id="apellidos" v-model="formDatos.apellidos" required />
                </div>

                <div>
                  <Label for="email">Email *</Label>
                  <Input id="email" type="email" v-model="formDatos.email" required />
                </div>

                <div>
                  <Label for="fecha_nacimiento">Fecha de Nacimiento</Label>
                  <Input id="fecha_nacimiento" type="date" v-model="formDatos.fecha_nacimiento" />
                </div>

                <div>
                  <Label for="genero_id">Género</Label>
                  <select
                    id="genero_id"
                    v-model="formDatos.genero_id"
                    class="flex h-10 w-full rounded-md border border-input bg-background px-3 py-2 text-sm"
                  >
                    <option :value="null">Seleccionar...</option>
                    <option v-for="g in catalogos.generos" :key="g.id" :value="g.id">
                      {{ g.nombre }}
                    </option>
                  </select>
                </div>

                <div>
                  <Label for="estado_civil_id">Estado Civil</Label>
                  <select
                    id="estado_civil_id"
                    v-model="formDatos.estado_civil_id"
                    class="flex h-10 w-full rounded-md border border-input bg-background px-3 py-2 text-sm"
                  >
                    <option :value="null">Seleccionar...</option>
                    <option v-for="ec in catalogos.estadosCiviles" :key="ec.id" :value="ec.id">
                      {{ ec.nombre }}
                    </option>
                  </select>
                </div>

                <div>
                  <Label for="estatus_id">Estatus *</Label>
                  <select
                    id="estatus_id"
                    v-model="formDatos.estatus_id"
                    class="flex h-10 w-full rounded-md border border-input bg-background px-3 py-2 text-sm"
                    required
                  >
                    <option v-for="est in catalogos.estatuses" :key="est.id" :value="est.id">
                      {{ est.nombre }}
                    </option>
                  </select>
                </div>

                <div>
                  <Label for="lugar_nacimiento">Lugar de Nacimiento</Label>
                  <Input id="lugar_nacimiento" v-model="formDatos.lugar_nacimiento" />
                </div>

                <div class="md:col-span-2">
                  <Label for="domicilio">Domicilio de Origen</Label>
                  <Input id="domicilio" v-model="formDatos.domicilio" />
                </div>

                <div class="md:col-span-2">
                  <Label for="domicilio_actual">Domicilio Actual</Label>
                  <Input id="domicilio_actual" v-model="formDatos.domicilio_actual" />
                </div>

                <div>
                  <Label for="facebook_url">Facebook URL</Label>
                  <Input id="facebook_url" v-model="formDatos.facebook_url" />
                </div>
              </div>

              <Separator />

              <div class="space-y-3">
                <h3 class="text-sm font-semibold">Características</h3>
                <div class="flex flex-col gap-2">
                  <label class="flex items-center gap-2 cursor-pointer">
                    <input type="checkbox" v-model="formDatos.tiene_hijos" class="rounded" />
                    <span class="text-sm">Tiene hijos</span>
                  </label>
                  <label class="flex items-center gap-2 cursor-pointer">
                    <input type="checkbox" v-model="formDatos.habla_lengua_indigena" class="rounded" />
                    <span class="text-sm">Habla lengua indígena</span>
                  </label>
                  <label class="flex items-center gap-2 cursor-pointer">
                    <input type="checkbox" v-model="formDatos.habla_segundo_idioma" class="rounded" />
                    <span class="text-sm">Habla segundo idioma</span>
                  </label>
                  <label class="flex items-center gap-2 cursor-pointer">
                    <input type="checkbox" v-model="formDatos.pertenece_grupo_etnico" class="rounded" />
                    <span class="text-sm">Pertenece a grupo étnico</span>
                  </label>
                </div>
              </div>

              <div class="flex justify-end">
                <Button type="submit" :disabled="formDatos.processing">
                  {{ formDatos.processing ? 'Guardando...' : 'Guardar Cambios' }}
                </Button>
              </div>
            </form>
          </CardContent>
        </Card>
      </div>

      <!-- Tab Carreras -->
      <div v-show="activeTab === 'carreras'" class="space-y-6">
        <Card>
          <CardHeader>
            <div class="flex justify-between items-center">
              <div>
                <CardTitle>Carreras del Egresado</CardTitle>
                <CardDescription>Programas académicos cursados</CardDescription>
              </div>
              <Button @click="showCarreraForm = !showCarreraForm" variant="outline">
                {{ showCarreraForm ? 'Cancelar' : 'Agregar Carrera' }}
              </Button>
            </div>
          </CardHeader>
          <CardContent class="space-y-4">
            <form v-if="showCarreraForm" @submit.prevent="submitCarrera" class="p-4 border rounded-lg space-y-4">
              <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                <div>
                  <Label>Carrera *</Label>
                  <select
                    v-model="formCarrera.carrera_id"
                    class="flex h-10 w-full rounded-md border border-input bg-background px-3 py-2 text-sm"
                    required
                  >
                    <option :value="null">Seleccionar carrera...</option>
                    <option v-for="c in catalogos.carreras" :key="c.id" :value="c.id">
                      {{ c.nombre }}
                    </option>
                  </select>
                </div>

                <div>
                  <Label>Generación *</Label>
                  <select
                    v-model="formCarrera.generacion_id"
                    class="flex h-10 w-full rounded-md border border-input bg-background px-3 py-2 text-sm"
                    required
                  >
                    <option :value="null">Seleccionar generación...</option>
                    <option v-for="g in catalogos.generaciones" :key="g.id" :value="g.id">
                      {{ g.nombre }}
                    </option>
                  </select>
                </div>

                <div>
                  <Label>Fecha Ingreso</Label>
                  <Input type="date" v-model="formCarrera.fecha_ingreso" />
                </div>

                <div>
                  <Label>Fecha Egreso</Label>
                  <Input type="date" v-model="formCarrera.fecha_egreso" />
                </div>
              </div>

              <div class="flex justify-end">
                <Button type="submit" :disabled="formCarrera.processing">
                  {{ formCarrera.processing ? 'Guardando...' : 'Agregar' }}
                </Button>
              </div>
            </form>

            <div v-if="egresado.carreras.length" class="space-y-2">
              <div
                v-for="carrera in egresado.carreras"
                :key="carrera.id"
                class="flex items-center justify-between p-4 border rounded-lg"
              >
                <div>
                  <p class="font-medium">{{ carrera.carrera_nombre }}</p>
                  <p class="text-sm text-muted-foreground">Generación: {{ carrera.generacion_nombre }}</p>
                  <p v-if="carrera.fecha_ingreso || carrera.fecha_egreso" class="text-xs text-muted-foreground mt-1">
                    {{ carrera.fecha_ingreso || '?' }} — {{ carrera.fecha_egreso || '?' }}
                  </p>
                </div>
                <Button
                  size="sm"
                  variant="destructive"
                  @click="deleteCarrera(carrera.id)"
                >
                  <TrashIcon class="h-4 w-4" />
                </Button>
              </div>
            </div>
            <div v-else class="text-center py-8 text-muted-foreground">
              No tiene carreras asignadas
            </div>
          </CardContent>
        </Card>
      </div>

      <!-- Tab Empleos -->
      <div v-show="activeTab === 'empleos'" class="space-y-6">
        <Card>
          <CardHeader>
            <CardTitle>Historial Laboral</CardTitle>
            <CardDescription>Empleos registrados por el egresado</CardDescription>
          </CardHeader>
          <CardContent>
            <div v-if="egresado.empleos.length" class="space-y-3">
              <div
                v-for="empleo in egresado.empleos"
                :key="empleo.id"
                class="p-4 border rounded-lg"
              >
                <p class="font-semibold">{{ empleo.puesto }}</p>
                <p class="text-sm">{{ empleo.empresa }}</p>
                <p class="text-xs text-muted-foreground mt-1">
                  {{ empleo.fecha_inicio }} — {{ empleo.fecha_fin || 'Actual' }}
                  <span v-if="empleo.ciudad"> | {{ empleo.ciudad }}</span>
                  <span v-if="empleo.sueldo"> | ${{ empleo.sueldo.toLocaleString() }}</span>
                </p>
              </div>
            </div>
            <div v-else class="text-center py-8 text-muted-foreground">
              No tiene empleos registrados
            </div>
          </CardContent>
        </Card>
      </div>

      <!-- Tab Usuario -->
      <div v-show="activeTab === 'usuario'" class="space-y-6">
        <Card>
          <CardHeader>
            <CardTitle>Acceso al Sistema</CardTitle>
            <CardDescription>Información de usuario y contraseña</CardDescription>
          </CardHeader>
          <CardContent class="space-y-4">
            <div class="flex items-center gap-2">
              <span class="text-sm font-medium">Estado:</span>
              <Badge :variant="egresado.tiene_usuario ? 'default' : 'secondary'">
                {{ egresado.tiene_usuario ? 'Usuario Activo' : 'Sin Usuario' }}
              </Badge>
            </div>

            <div v-if="egresado.tiene_usuario">
              <div class="space-y-2">
                <p class="text-sm">
                  <span class="font-medium">Email:</span> {{ egresado.email }}
                </p>
                <p class="text-sm">
                  <span class="font-medium">Roles:</span>
                  <Badge v-for="rol in egresado.roles_usuario" :key="rol" variant="outline" class="ml-1">
                    {{ rol }}
                  </Badge>
                  <span v-if="!egresado.roles_usuario.length" class="text-muted-foreground">Sin roles</span>
                </p>
              </div>

              <Separator class="my-4" />

              <div>
                <Button
                  v-if="!showPasswordForm"
                  @click="showPasswordForm = true"
                  variant="outline"
                >
                  Cambiar Contraseña
                </Button>

                <form v-else @submit.prevent="submitPassword" class="space-y-4 max-w-md">
                  <div>
                    <Label for="password">Nueva Contraseña</Label>
                    <Input
                      id="password"
                      type="password"
                      v-model="formPassword.password"
                      required
                      minlength="8"
                    />
                  </div>

                  <div>
                    <Label for="password_confirmation">Confirmar Contraseña</Label>
                    <Input
                      id="password_confirmation"
                      type="password"
                      v-model="formPassword.password_confirmation"
                      required
                      minlength="8"
                    />
                  </div>

                  <div class="flex gap-2">
                    <Button type="submit" :disabled="formPassword.processing">
                      {{ formPassword.processing ? 'Actualizando...' : 'Actualizar' }}
                    </Button>
                    <Button
                      type="button"
                      variant="outline"
                      @click="showPasswordForm = false; formPassword.reset()"
                    >
                      Cancelar
                    </Button>
                  </div>
                </form>
              </div>
            </div>

            <div v-else class="text-muted-foreground">
              Este egresado no tiene un usuario registrado en el sistema.
            </div>
          </CardContent>
        </Card>
      </div>
    </div>
  </AppLayout>
</template>
