<script setup lang="ts">
import { ref } from 'vue'
import AppLayout from '@/layouts/AppLayout.vue'
import { Head, useForm } from '@inertiajs/vue3'
import { Card, CardContent, CardDescription, CardHeader, CardTitle } from '@/components/ui/card'
import { Button } from '@/components/ui/button'
import { Input } from '@/components/ui/input'
import { Label } from '@/components/ui/label'

interface Unidad {
  id: number
  nombre: string
  clave: string | null
  domicilio: string | null
  web: string | null
  email: string | null
  estatus: string
}

interface Carrera {
  id: number
  nombre: string
  nivel: string | null
  tipo_programa: string | null
  estatus: string
}

interface Generacion {
  id: number
  nombre: string
  estatus: string
}

interface Props {
  unidades: Unidad[]
  carreras: Carrera[]
  generaciones: Generacion[]
  nivelesEstudio: NivelEstudio[]
  ciclosEscolares: CicloEscolar[]
}

const props = defineProps<Props>()

// Control de pestañas
const tabActiva = ref('unidades')

// Estados de formularios
const mostrarFormUnidad = ref(false)
const mostrarFormCarrera = ref(false)
const mostrarFormGeneracion = ref(false)
const mostrarAsignarCarreras = ref(false)
const mostrarFormNivel = ref(false)
const mostrarFormCiclo = ref(false)

const editandoUnidad = ref<Unidad | null>(null)
const editandoCarrera = ref<Carrera | null>(null)
const editandoGeneracion = ref<Generacion | null>(null)
const unidadParaAsignar = ref<Unidad | null>(null)
const editandoNivel = ref<NivelEstudio | null>(null)
const editandoCiclo = ref<CicloEscolar | null>(null)

// Formularios
const formUnidad = useForm({
  nombre: '',
  clave: '',
  domicilio: '',
  web: '',
  email: '',
  estatus: 'A',
})

const formCarrera = useForm({
  nombre: '',
  nivel: '',
  tipo_programa: '',
  estatus: 'A',
})

const formGeneracion = useForm({
  nombre: '',
  estatus: 'A',
})

const formNivel = useForm({
  nombre: '',
  estatus: 'A',
})

const formCiclo = useForm({
  nombre: '',
  fecha_inicio: '',
  fecha_fin: '',
  estatus: 'A',
})

const formAsignarCarreras = useForm({
  carreras_ids: [] as number[],
})

// ===== UNIDADES =====
const abrirFormUnidadCrear = () => {
  editandoUnidad.value = null
  formUnidad.reset()
  formUnidad.clearErrors()
  mostrarFormUnidad.value = true
}

const abrirFormUnidadEditar = (unidad: Unidad) => {
  editandoUnidad.value = unidad
  formUnidad.nombre = unidad.nombre
  formUnidad.clave = unidad.clave || ''
  formUnidad.domicilio = unidad.domicilio || ''
  formUnidad.web = unidad.web || ''
  formUnidad.email = unidad.email || ''
  formUnidad.estatus = unidad.estatus
  mostrarFormUnidad.value = true
}

const cancelarUnidad = () => {
  mostrarFormUnidad.value = false
  formUnidad.reset()
  formUnidad.clearErrors()
  editandoUnidad.value = null
}

const guardarUnidad = () => {
  if (editandoUnidad.value) {
    formUnidad.put(`/admin-academica/unidades/${editandoUnidad.value.id}`, {
      onSuccess: () => cancelarUnidad()
    })
  } else {
    formUnidad.post('/admin-academica/unidades', {
      onSuccess: () => cancelarUnidad()
    })
  }
}

const eliminarUnidad = (id: number) => {
  if (confirm('¿Estás seguro de eliminar esta unidad?')) {
    formUnidad.delete(`/admin-academica/unidades/${id}`)
  }
}

// ===== CARRERAS =====
const abrirFormCarreraCrear = () => {
  editandoCarrera.value = null
  formCarrera.reset()
  formCarrera.clearErrors()
  mostrarFormCarrera.value = true
}

const abrirFormCarreraEditar = (carrera: Carrera) => {
  editandoCarrera.value = carrera
  formCarrera.nombre = carrera.nombre
  formCarrera.nivel = carrera.nivel || ''
  formCarrera.tipo_programa = carrera.tipo_programa || ''
  formCarrera.estatus = carrera.estatus
  mostrarFormCarrera.value = true
}

const cancelarCarrera = () => {
  mostrarFormCarrera.value = false
  formCarrera.reset()
  formCarrera.clearErrors()
  editandoCarrera.value = null
}

const guardarCarrera = () => {
  if (editandoCarrera.value) {
    formCarrera.put(`/admin-academica/carreras/${editandoCarrera.value.id}`, {
      onSuccess: () => cancelarCarrera()
    })
  } else {
    formCarrera.post('/admin-academica/carreras', {
      onSuccess: () => cancelarCarrera()
    })
  }
}

const eliminarCarrera = (id: number) => {
  if (confirm('¿Estás seguro de eliminar esta carrera?')) {
    formCarrera.delete(`/admin-academica/carreras/${id}`)
  }
}

// ===== GENERACIONES =====
const abrirFormGeneracionCrear = () => {
  editandoGeneracion.value = null
  formGeneracion.reset()
  formGeneracion.clearErrors()
  mostrarFormGeneracion.value = true
}

const abrirFormGeneracionEditar = (generacion: Generacion) => {
  editandoGeneracion.value = generacion
  formGeneracion.nombre = generacion.nombre
  formGeneracion.estatus = generacion.estatus
  mostrarFormGeneracion.value = true
}

const cancelarGeneracion = () => {
  mostrarFormGeneracion.value = false
  formGeneracion.reset()
  formGeneracion.clearErrors()
  editandoGeneracion.value = null
}

const guardarGeneracion = () => {
  if (editandoGeneracion.value) {
    formGeneracion.put(`/admin-academica/generaciones/${editandoGeneracion.value.id}`, {
      onSuccess: () => cancelarGeneracion()
    })
  } else {
    formGeneracion.post('/admin-academica/generaciones', {
      onSuccess: () => cancelarGeneracion()
    })
  }
}

const eliminarGeneracion = (id: number) => {
  if (confirm('¿Estás seguro de eliminar esta generación?')) {
    formGeneracion.delete(`/admin-academica/generaciones/${id}`)
  }
}

// ===== ASIGNAR CARRERAS =====
const abrirAsignarCarreras = (unidad: Unidad) => {
  unidadParaAsignar.value = unidad
  // Cargar carreras ya asignadas a esta unidad
  formAsignarCarreras.carreras_ids = []
  mostrarAsignarCarreras.value = true
}

const toggleCarrera = (carreraId: number) => {
  const index = formAsignarCarreras.carreras_ids.indexOf(carreraId)
  if (index > -1) {
    formAsignarCarreras.carreras_ids.splice(index, 1)
  } else {
    formAsignarCarreras.carreras_ids.push(carreraId)
  }
}

const guardarAsignacionCarreras = () => {
  if (!unidadParaAsignar.value) return
  
  formAsignarCarreras.post(`/admin-academica/unidades/${unidadParaAsignar.value.id}/asignar-carreras`, {
    onSuccess: () => {
      mostrarAsignarCarreras.value = false
      formAsignarCarreras.reset()
      unidadParaAsignar.value = null
    }
  })
}

const cancelarAsignacion = () => {
  mostrarAsignarCarreras.value = false
  formAsignarCarreras.reset()
  unidadParaAsignar.value = null
}

const getEstatusLabel = (estatus: string) => estatus === 'A' ? 'Activo' : 'Inactivo'
const getEstatusClass = (estatus: string) => estatus === 'A' 
  ? 'bg-green-100 text-green-800' 
  : 'bg-red-100 text-red-800'

// ===== NIVELES DE ESTUDIO =====
interface NivelEstudio { id: number; nombre: string; estatus: string }

const abrirFormNivelCrear = () => {
  editandoNivel.value = null
  formNivel.reset(); formNivel.clearErrors(); mostrarFormNivel.value = true
}
const abrirFormNivelEditar = (nivel: NivelEstudio) => {
  editandoNivel.value = nivel
  formNivel.nombre = nivel.nombre
  formNivel.estatus = nivel.estatus
  mostrarFormNivel.value = true
}
const cancelarNivel = () => { mostrarFormNivel.value = false; formNivel.reset(); formNivel.clearErrors(); editandoNivel.value = null }
const guardarNivel = () => {
  if (editandoNivel.value) {
    formNivel.put(`/admin-academica/niveles/${editandoNivel.value.id}`, { onSuccess: () => cancelarNivel() })
  } else {
    formNivel.post('/admin-academica/niveles', { onSuccess: () => cancelarNivel() })
  }
}
const eliminarNivel = (id: number) => { if (confirm('¿Eliminar nivel de estudio?')) formNivel.delete(`/admin-academica/niveles/${id}`) }

// ===== CICLOS ESCOLARES =====
interface CicloEscolar { id: number; nombre: string; fecha_inicio: string | null; fecha_fin: string | null; estatus: string }

const abrirFormCicloCrear = () => { editandoCiclo.value = null; formCiclo.reset(); formCiclo.clearErrors(); mostrarFormCiclo.value = true }
const abrirFormCicloEditar = (ciclo: CicloEscolar) => {
  editandoCiclo.value = ciclo
  formCiclo.nombre = ciclo.nombre
  formCiclo.fecha_inicio = ciclo.fecha_inicio || ''
  formCiclo.fecha_fin = ciclo.fecha_fin || ''
  formCiclo.estatus = ciclo.estatus
  mostrarFormCiclo.value = true
}
const cancelarCiclo = () => { mostrarFormCiclo.value = false; formCiclo.reset(); formCiclo.clearErrors(); editandoCiclo.value = null }
const guardarCiclo = () => {
  if (editandoCiclo.value) {
    formCiclo.put(`/admin-academica/ciclos/${editandoCiclo.value.id}`, { onSuccess: () => cancelarCiclo() })
  } else {
    formCiclo.post('/admin-academica/ciclos', { onSuccess: () => cancelarCiclo() })
  }
}
const eliminarCiclo = (id: number) => { if (confirm('¿Eliminar ciclo escolar?')) formCiclo.delete(`/admin-academica/ciclos/${id}`) }


</script>

<template>
  <Head title="Admin Académica" />
  <AppLayout :breadcrumbs="[{ title: 'Admin Académica', href: '/admin-academica' }]">
    <div class="container mx-auto px-4 py-8 max-w-7xl">
      
      <div class="mb-6 flex items-center justify-between gap-4">
        <h1 class="text-3xl font-bold">Gestión Académica</h1>
        <Button variant="outline" @click="() => { window.location.href = '/admin-unidad/backup' }">Respaldar base de datos</Button>
      </div>

      <!-- Pestañas -->
      <div class="flex border-b mb-6">
        <button
          @click="tabActiva = 'unidades'"
          :class="['px-6 py-3 font-medium border-b-2 transition-colors', tabActiva === 'unidades' ? 'border-blue-600 text-blue-600' : 'border-transparent text-gray-600 hover:text-blue-600']"
        >
          Gestor de Unidades
        </button>
        <button
          @click="tabActiva = 'carreras'"
          :class="['px-6 py-3 font-medium border-b-2 transition-colors', tabActiva === 'carreras' ? 'border-green-600 text-green-600' : 'border-transparent text-gray-600 hover:text-green-600']"
        >
          Gestor de Carreras
        </button>
        <button
          @click="tabActiva = 'generaciones'"
          :class="['px-6 py-3 font-medium border-b-2 transition-colors', tabActiva === 'generaciones' ? 'border-purple-600 text-purple-600' : 'border-transparent text-gray-600 hover:text-purple-600']"
        >
          Gestor de Generaciones
        </button>
        <button
          @click="tabActiva = 'niveles'"
          :class="['px-6 py-3 font-medium border-b-2 transition-colors', tabActiva === 'niveles' ? 'border-indigo-600 text-indigo-600' : 'border-transparent text-gray-600 hover:text-indigo-600']"
        >
          Niveles de Estudio
        </button>
        <button
          @click="tabActiva = 'ciclos'"
          :class="['px-6 py-3 font-medium border-b-2 transition-colors', tabActiva === 'ciclos' ? 'border-amber-600 text-amber-600' : 'border-transparent text-gray-600 hover:text-amber-600']"
        >
          Ciclos Escolares
        </button>
      </div>

      <!-- PESTAÑA 1: UNIDADES -->
      <div v-if="tabActiva === 'unidades'" class="space-y-6">
        <div class="flex justify-between items-center">
          <h2 class="text-2xl font-bold">Unidades</h2>
          <Button @click="abrirFormUnidadCrear" v-if="!mostrarFormUnidad" class="bg-blue-600 hover:bg-blue-700">
            Crear Nueva Unidad
          </Button>
        </div>

        <!-- Formulario Unidad -->
        <Card v-if="mostrarFormUnidad">
          <CardHeader>
            <CardTitle>{{ editandoUnidad ? 'Editar Unidad' : 'Nueva Unidad' }}</CardTitle>
          </CardHeader>
          <CardContent>
            <form @submit.prevent="guardarUnidad" class="space-y-4">
              <div>
                <Label for="unidad_nombre">Nombre *</Label>
                <Input id="unidad_nombre" v-model="formUnidad.nombre" required />
              </div>
              <div>
                <Label for="unidad_clave">Clave</Label>
                <Input id="unidad_clave" v-model="formUnidad.clave" />
              </div>
              <div>
                <Label for="unidad_domicilio">Domicilio</Label>
                <textarea id="unidad_domicilio" v-model="formUnidad.domicilio" rows="2" class="flex w-full rounded-md border border-input bg-transparent px-3 py-2 text-sm"></textarea>
              </div>
              <div class="grid grid-cols-2 gap-4">
                <div>
                  <Label for="unidad_web">Sitio Web</Label>
                  <Input id="unidad_web" v-model="formUnidad.web" type="url" />
                </div>
                <div>
                  <Label for="unidad_email">Email</Label>
                  <Input id="unidad_email" v-model="formUnidad.email" type="email" />
                </div>
              </div>
              <div>
                <Label for="unidad_estatus">Estatus *</Label>
                <select id="unidad_estatus" v-model="formUnidad.estatus" required class="flex h-9 w-full rounded-md border border-input bg-transparent px-3 py-1 text-sm">
                  <option value="A">Activo</option>
                  <option value="I">Inactivo</option>
                </select>
              </div>
              <div class="flex gap-3">
                <Button type="submit" :disabled="formUnidad.processing" class="bg-green-600 hover:bg-green-700">
                  {{ formUnidad.processing ? 'Guardando...' : 'Guardar' }}
                </Button>
                <Button type="button" @click="cancelarUnidad" variant="outline">Cancelar</Button>
              </div>
            </form>
          </CardContent>
        </Card>

        <!-- Modal Asignar Carreras -->
        <Card v-if="mostrarAsignarCarreras">
          <CardHeader>
            <CardTitle>Asignar Carreras a {{ unidadParaAsignar?.nombre }}</CardTitle>
            <CardDescription>Selecciona las carreras que pertenecen a esta unidad</CardDescription>
          </CardHeader>
          <CardContent>
            <form @submit.prevent="guardarAsignacionCarreras" class="space-y-4">
              <div class="max-h-96 overflow-y-auto border rounded-md p-4 space-y-2">
                <label 
                  v-for="carrera in carreras" 
                  :key="carrera.id"
                  class="flex items-center gap-3 p-3 rounded hover:bg-muted/50 cursor-pointer transition-colors"
                >
                  <input 
                    type="checkbox" 
                    :value="carrera.id"
                    :checked="formAsignarCarreras.carreras_ids.includes(carrera.id)"
                    @change="toggleCarrera(carrera.id)"
                    class="w-4 h-4 rounded border-gray-300"
                  />
                  <div class="flex-1">
                    <div class="font-medium">{{ carrera.nombre }}</div>
                    <div class="text-sm text-muted-foreground">{{ carrera.nivel || 'Sin nivel' }}</div>
                  </div>
                  <span :class="['text-xs px-2 py-1 rounded', getEstatusClass(carrera.estatus)]">
                    {{ getEstatusLabel(carrera.estatus) }}
                  </span>
                </label>
              </div>
              <div class="flex gap-3 pt-4">
                <Button type="submit" :disabled="formAsignarCarreras.processing" class="bg-purple-600 hover:bg-purple-700">
                  {{ formAsignarCarreras.processing ? 'Guardando...' : 'Guardar Asignación' }}
                </Button>
                <Button type="button" @click="cancelarAsignacion" variant="outline">Cancelar</Button>
              </div>
            </form>
          </CardContent>
        </Card>

        <!-- Tabla Unidades -->
        <Card>
          <CardContent class="pt-6">
            <table class="w-full">
              <thead>
                <tr class="border-b">
                  <th class="text-left py-3 px-4">Nombre</th>
                  <th class="text-left py-3 px-4">Clave</th>
                  <th class="text-left py-3 px-4">Email</th>
                  <th class="text-center py-3 px-4">Estatus</th>
                  <th class="text-right py-3 px-4">Acciones</th>
                </tr>
              </thead>
              <tbody>
                <tr v-for="unidad in unidades" :key="unidad.id" class="border-b hover:bg-muted/50">
                  <td class="py-3 px-4">{{ unidad.nombre }}</td>
                  <td class="py-3 px-4">{{ unidad.clave || '-' }}</td>
                  <td class="py-3 px-4">{{ unidad.email || '-' }}</td>
                  <td class="py-3 px-4 text-center">
                    <span :class="['text-xs px-2 py-1 rounded', getEstatusClass(unidad.estatus)]">
                      {{ getEstatusLabel(unidad.estatus) }}
                    </span>
                  </td>
                  <td class="py-3 px-4 text-right">
                    <div class="flex gap-2 justify-end">
                      <Button @click="abrirAsignarCarreras(unidad)" variant="outline" size="sm" class="bg-purple-50 hover:bg-purple-100">Asignar Carreras</Button>
                      <Button @click="abrirFormUnidadEditar(unidad)" variant="outline" size="sm">Editar</Button>
                      <Button @click="eliminarUnidad(unidad.id)" variant="destructive" size="sm">Eliminar</Button>
                    </div>
                  </td>
                </tr>
              </tbody>
            </table>
          </CardContent>
        </Card>
      </div>

      <!-- PESTAÑA 4: NIVELES DE ESTUDIO -->
      <div v-if="tabActiva === 'niveles'" class="space-y-6">
        <div class="flex justify-between items-center">
          <h2 class="text-2xl font-bold">Niveles de Estudio</h2>
          <Button @click="abrirFormNivelCrear" v-if="!mostrarFormNivel" class="bg-indigo-600 hover:bg-indigo-700">
            Crear Nuevo Nivel
          </Button>
        </div>
        <Card v-if="mostrarFormNivel">
          <CardHeader>
            <CardTitle>{{ editandoNivel ? 'Editar Nivel' : 'Nuevo Nivel' }}</CardTitle>
          </CardHeader>
          <CardContent>
            <form @submit.prevent="guardarNivel" class="space-y-4">
              <div>
                <Label for="nivel_nombre">Nombre *</Label>
                <Input id="nivel_nombre" v-model="formNivel.nombre" required />
              </div>
              <div>
                <Label for="nivel_estatus">Estatus *</Label>
                <select id="nivel_estatus" v-model="formNivel.estatus" required class="flex h-9 w-full rounded-md border border-input bg-transparent px-3 py-1 text-sm">
                  <option value="A">Activo</option>
                  <option value="I">Inactivo</option>
                </select>
              </div>
              <div class="flex gap-3">
                <Button type="submit" :disabled="formNivel.processing" class="bg-green-600 hover:bg-green-700">
                  {{ formNivel.processing ? 'Guardando...' : 'Guardar' }}
                </Button>
                <Button type="button" @click="cancelarNivel" variant="outline">Cancelar</Button>
              </div>
            </form>
          </CardContent>
        </Card>
        <Card>
          <CardContent class="pt-6">
            <table class="w-full">
              <thead>
                <tr class="border-b">
                  <th class="text-left py-3 px-4">Nombre</th>
                  <th class="text-center py-3 px-4">Estatus</th>
                  <th class="text-right py-3 px-4">Acciones</th>
                </tr>
              </thead>
              <tbody>
                <tr v-for="nivel in nivelesEstudio" :key="nivel.id" class="border-b hover:bg-muted/50">
                  <td class="py-3 px-4">{{ nivel.nombre }}</td>
                  <td class="py-3 px-4 text-center">
                    <span :class="['text-xs px-2 py-1 rounded', getEstatusClass(nivel.estatus)]">{{ getEstatusLabel(nivel.estatus) }}</span>
                  </td>
                  <td class="py-3 px-4 text-right">
                    <div class="flex gap-2 justify-end">
                      <Button @click="abrirFormNivelEditar(nivel)" variant="outline" size="sm">Editar</Button>
                      <Button @click="eliminarNivel(nivel.id)" variant="destructive" size="sm">Eliminar</Button>
                    </div>
                  </td>
                </tr>
              </tbody>
            </table>
          </CardContent>
        </Card>
      </div>

      <!-- PESTAÑA 5: CICLOS ESCOLARES -->
      <div v-if="tabActiva === 'ciclos'" class="space-y-6">
        <div class="flex justify-between items-center">
          <h2 class="text-2xl font-bold">Ciclos Escolares</h2>
          <Button @click="abrirFormCicloCrear" v-if="!mostrarFormCiclo" class="bg-amber-600 hover:bg-amber-700">
            Crear Nuevo Ciclo
          </Button>
        </div>
        <Card v-if="mostrarFormCiclo">
          <CardHeader>
            <CardTitle>{{ editandoCiclo ? 'Editar Ciclo' : 'Nuevo Ciclo' }}</CardTitle>
          </CardHeader>
          <CardContent>
            <form @submit.prevent="guardarCiclo" class="space-y-4">
              <div>
                <Label for="ciclo_nombre">Nombre *</Label>
                <Input id="ciclo_nombre" v-model="formCiclo.nombre" required placeholder="Ej: 2024-2025" />
              </div>
              <div class="grid grid-cols-2 gap-4">
                <div>
                  <Label for="ciclo_inicio">Fecha Inicio</Label>
                  <Input id="ciclo_inicio" type="date" v-model="formCiclo.fecha_inicio" />
                </div>
                <div>
                  <Label for="ciclo_fin">Fecha Fin</Label>
                  <Input id="ciclo_fin" type="date" v-model="formCiclo.fecha_fin" />
                </div>
              </div>
              <div>
                <Label for="ciclo_estatus">Estatus *</Label>
                <select id="ciclo_estatus" v-model="formCiclo.estatus" required class="flex h-9 w-full rounded-md border border-input bg-transparent px-3 py-1 text-sm">
                  <option value="A">Activo</option>
                  <option value="I">Inactivo</option>
                </select>
              </div>
              <div class="flex gap-3">
                <Button type="submit" :disabled="formCiclo.processing" class="bg-green-600 hover:bg-green-700">
                  {{ formCiclo.processing ? 'Guardando...' : 'Guardar' }}
                </Button>
                <Button type="button" @click="cancelarCiclo" variant="outline">Cancelar</Button>
              </div>
            </form>
          </CardContent>
        </Card>
        <Card>
          <CardContent class="pt-6">
            <table class="w-full">
              <thead>
                <tr class="border-b">
                  <th class="text-left py-3 px-4">Nombre</th>
                  <th class="text-left py-3 px-4">Inicio</th>
                  <th class="text-left py-3 px-4">Fin</th>
                  <th class="text-center py-3 px-4">Estatus</th>
                  <th class="text-right py-3 px-4">Acciones</th>
                </tr>
              </thead>
              <tbody>
                <tr v-for="ciclo in ciclosEscolares" :key="ciclo.id" class="border-b hover:bg-muted/50">
                  <td class="py-3 px-4">{{ ciclo.nombre }}</td>
                  <td class="py-3 px-4">{{ ciclo.fecha_inicio || '-' }}</td>
                  <td class="py-3 px-4">{{ ciclo.fecha_fin || '-' }}</td>
                  <td class="py-3 px-4 text-center">
                    <span :class="['text-xs px-2 py-1 rounded', getEstatusClass(ciclo.estatus)]">{{ getEstatusLabel(ciclo.estatus) }}</span>
                  </td>
                  <td class="py-3 px-4 text-right">
                    <div class="flex gap-2 justify-end">
                      <Button @click="abrirFormCicloEditar(ciclo)" variant="outline" size="sm">Editar</Button>
                      <Button @click="eliminarCiclo(ciclo.id)" variant="destructive" size="sm">Eliminar</Button>
                    </div>
                  </td>
                </tr>
              </tbody>
            </table>
          </CardContent>
        </Card>
      </div>

      <!-- PESTAÑA 2: CARRERAS -->
      <div v-if="tabActiva === 'carreras'" class="space-y-6">
        <div class="flex justify-between items-center">
          <h2 class="text-2xl font-bold">Carreras</h2>
          <Button @click="abrirFormCarreraCrear" v-if="!mostrarFormCarrera" class="bg-green-600 hover:bg-green-700">
            Crear Nueva Carrera
          </Button>
        </div>

        <!-- Formulario Carrera -->
        <Card v-if="mostrarFormCarrera">
          <CardHeader>
            <CardTitle>{{ editandoCarrera ? 'Editar Carrera' : 'Nueva Carrera' }}</CardTitle>
          </CardHeader>
          <CardContent>
            <form @submit.prevent="guardarCarrera" class="space-y-4">
              <div>
                <Label for="carrera_nombre">Nombre *</Label>
                <Input id="carrera_nombre" v-model="formCarrera.nombre" required />
              </div>
              <div>
                <Label for="carrera_nivel">Nivel</Label>
                <Input id="carrera_nivel" v-model="formCarrera.nivel" placeholder="Ej: Licenciatura, Maestría" />
              </div>
              <div>
                <Label for="carrera_tipo">Tipo de Programa</Label>
                <Input id="carrera_tipo" v-model="formCarrera.tipo_programa" />
              </div>
              <div>
                <Label for="carrera_estatus">Estatus *</Label>
                <select id="carrera_estatus" v-model="formCarrera.estatus" required class="flex h-9 w-full rounded-md border border-input bg-transparent px-3 py-1 text-sm">
                  <option value="A">Activo</option>
                  <option value="I">Inactivo</option>
                </select>
              </div>
              <div class="flex gap-3">
                <Button type="submit" :disabled="formCarrera.processing" class="bg-green-600 hover:bg-green-700">
                  {{ formCarrera.processing ? 'Guardando...' : 'Guardar' }}
                </Button>
                <Button type="button" @click="cancelarCarrera" variant="outline">Cancelar</Button>
              </div>
            </form>
          </CardContent>
        </Card>

        <!-- Tabla Carreras -->
        <Card>
          <CardContent class="pt-6">
            <table class="w-full">
              <thead>
                <tr class="border-b">
                  <th class="text-left py-3 px-4">Nombre</th>
                  <th class="text-left py-3 px-4">Nivel</th>
                  <th class="text-center py-3 px-4">Estatus</th>
                  <th class="text-right py-3 px-4">Acciones</th>
                </tr>
              </thead>
              <tbody>
                <tr v-for="carrera in carreras" :key="carrera.id" class="border-b hover:bg-muted/50">
                  <td class="py-3 px-4">{{ carrera.nombre }}</td>
                  <td class="py-3 px-4">{{ carrera.nivel || '-' }}</td>
                  <td class="py-3 px-4 text-center">
                    <span :class="['text-xs px-2 py-1 rounded', getEstatusClass(carrera.estatus)]">
                      {{ getEstatusLabel(carrera.estatus) }}
                    </span>
                  </td>
                  <td class="py-3 px-4 text-right">
                    <div class="flex gap-2 justify-end">
                      <Button @click="abrirFormCarreraEditar(carrera)" variant="outline" size="sm">Editar</Button>
                      <Button @click="eliminarCarrera(carrera.id)" variant="destructive" size="sm">Eliminar</Button>
                    </div>
                  </td>
                </tr>
              </tbody>
            </table>
          </CardContent>
        </Card>
      </div>

      <!-- PESTAÑA 3: GENERACIONES -->
      <div v-if="tabActiva === 'generaciones'" class="space-y-6">
        <div class="flex justify-between items-center">
          <h2 class="text-2xl font-bold">Generaciones</h2>
          <Button @click="abrirFormGeneracionCrear" v-if="!mostrarFormGeneracion" class="bg-purple-600 hover:bg-purple-700">
            Crear Nueva Generación
          </Button>
        </div>

        <!-- Formulario Generación -->
        <Card v-if="mostrarFormGeneracion">
          <CardHeader>
            <CardTitle>{{ editandoGeneracion ? 'Editar Generación' : 'Nueva Generación' }}</CardTitle>
          </CardHeader>
          <CardContent>
            <form @submit.prevent="guardarGeneracion" class="space-y-4">
              <div>
                <Label for="generacion_nombre">Nombre *</Label>
                <Input id="generacion_nombre" v-model="formGeneracion.nombre" required placeholder="Ej: 2021-2025" />
              </div>
              <div>
                <Label for="generacion_estatus">Estatus *</Label>
                <select id="generacion_estatus" v-model="formGeneracion.estatus" required class="flex h-9 w-full rounded-md border border-input bg-transparent px-3 py-1 text-sm">
                  <option value="A">Activo</option>
                  <option value="I">Inactivo</option>
                </select>
              </div>
              <div class="flex gap-3">
                <Button type="submit" :disabled="formGeneracion.processing" class="bg-green-600 hover:bg-green-700">
                  {{ formGeneracion.processing ? 'Guardando...' : 'Guardar' }}
                </Button>
                <Button type="button" @click="cancelarGeneracion" variant="outline">Cancelar</Button>
              </div>
            </form>
          </CardContent>
        </Card>

        <!-- Tabla Generaciones -->
        <Card>
          <CardContent class="pt-6">
            <table class="w-full">
              <thead>
                <tr class="border-b">
                  <th class="text-left py-3 px-4">Nombre</th>
                  <th class="text-center py-3 px-4">Estatus</th>
                  <th class="text-right py-3 px-4">Acciones</th>
                </tr>
              </thead>
              <tbody>
                <tr v-for="generacion in generaciones" :key="generacion.id" class="border-b hover:bg-muted/50">
                  <td class="py-3 px-4">{{ generacion.nombre }}</td>
                  <td class="py-3 px-4 text-center">
                    <span :class="['text-xs px-2 py-1 rounded', getEstatusClass(generacion.estatus)]">
                      {{ getEstatusLabel(generacion.estatus) }}
                    </span>
                  </td>
                  <td class="py-3 px-4 text-right">
                    <div class="flex gap-2 justify-end">
                      <Button @click="abrirFormGeneracionEditar(generacion)" variant="outline" size="sm">Editar</Button>
                      <Button @click="eliminarGeneracion(generacion.id)" variant="destructive" size="sm">Eliminar</Button>
                    </div>
                  </td>
                </tr>
              </tbody>
            </table>
          </CardContent>
        </Card>
      </div>

    </div>
  </AppLayout>
</template>
