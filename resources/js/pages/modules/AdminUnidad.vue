<script setup lang="ts">
import { ref, computed, onMounted } from 'vue'
import AppLayout from '@/layouts/AppLayout.vue'
import { Head, useForm, router } from '@inertiajs/vue3'
import { Card, CardContent, CardHeader, CardTitle } from '@/components/ui/card'
import { Button } from '@/components/ui/button'
import { Input } from '@/components/ui/input'
import { Label } from '@/components/ui/label'

interface Encuesta { id:number; nombre:string; descripcion?:string|null; fecha_inicio?:string|null; fecha_fin?:string|null; estatus:string }
interface Carrera { id:number; nombre:string }
interface Generacion { id:number; nombre:string }
interface Unidad { id:number; nombre:string }
interface TipoPregunta { id:number; descripcion:string }
interface Opcion { id:number; texto:string; valor:number|null; orden:number|null }
interface Tipo { id:number, descripcion:string }
interface Pregunta { id:number; texto:string; orden:number|null; tipo?:Tipo; opciones:Opcion[] }

interface Props {
  encuestas: Encuesta[]
  carreras: Carrera[]
  generaciones: Generacion[]
  unidades: Unidad[]
  asignaciones: Array<{ id:number; encuesta:Encuesta; carrera:Carrera|null; generacion:Generacion|null; unidad:Unidad|null; tipo_asignacion:string|null }>
  tiposPregunta: TipoPregunta[]
}
const props = defineProps<Props>()

const tab = ref<'encuestas' | 'preguntas' | 'asignaciones'>('encuestas')
const encuestaSeleccionadaId = ref<number | null>(null)

// Gestor de Encuestas
const mostrandoFormEncuesta = ref(false)
const editandoEncuestaId = ref<number | null>(null)
const formEncuesta = useForm({ nombre: '', descripcion: '' })

const abrirNuevaEncuesta = () => {
  editandoEncuestaId.value = null
  formEncuesta.reset(); formEncuesta.clearErrors(); mostrandoFormEncuesta.value = true
}
const guardarEncuesta = () => {
  if (editandoEncuestaId.value) {
    formEncuesta.put(`/admin-unidad/encuestas/${editandoEncuestaId.value}`, { onSuccess: () => cancelarEncuesta() })
  } else {
    formEncuesta.post('/admin-unidad/encuestas', { onSuccess: () => cancelarEncuesta() })
  }
}
const cancelarEncuesta = () => { mostrandoFormEncuesta.value = false; formEncuesta.reset(); formEncuesta.clearErrors(); editandoEncuestaId.value = null }
const eliminarEncuesta = (id:number) => { if (confirm('¿Eliminar encuesta?')) router.delete(`/admin-unidad/encuestas/${id}`) }
const editarPreguntas = (id:number) => { encuestaSeleccionadaId.value = id; tab.value = 'preguntas'; cargarPreguntas() }

// Creador de Preguntas
const preguntas = ref<Pregunta[]>([])
const cargandoPreguntas = ref(false)
const tipos = ['Abierta', 'Opción Múltiple', 'Casillas de Verificación', 'Escala Likert', 'Sí/No', 'Numérica', 'Fecha']
const formPregunta = useForm({ texto:'', tipo:'Abierta', orden:0 })

function cargarPreguntas(){
  if (!encuestaSeleccionadaId.value) return
  console.log('Cargando preguntas para encuesta:', encuestaSeleccionadaId.value)
  cargandoPreguntas.value = true
  
  // Usar XMLHttpRequest para evitar interceptación de Inertia
  const xhr = new XMLHttpRequest()
  xhr.open('GET', `/admin-unidad/encuestas/${encuestaSeleccionadaId.value}/preguntas`)
  xhr.setRequestHeader('X-Requested-With', 'XMLHttpRequest')
  xhr.setRequestHeader('Accept', 'application/json')
  xhr.onload = () => {
    console.log('Preguntas cargadas, status:', xhr.status)
    if (xhr.status === 200) {
      const data = JSON.parse(xhr.responseText)
      console.log('Preguntas recibidas:', data.length)
      
      // Log detallado de opciones
      data.forEach((pregunta: any) => {
        if (pregunta.opciones && pregunta.opciones.length > 0) {
          console.log(`📋 Pregunta "${pregunta.texto}" tiene ${pregunta.opciones.length} opciones:`)
          pregunta.opciones.forEach((opcion: any) => {
            console.log(`  ${opcion.texto ? '✅' : '❌'} ID: ${opcion.id} | Texto: "${opcion.texto}" | Valor: ${opcion.valor}`)
          })
        }
      })
      
      preguntas.value = data
    }
    cargandoPreguntas.value = false
  }
  xhr.onerror = () => { 
    console.error('Error cargando preguntas')
    cargandoPreguntas.value = false 
  }
  xhr.send()
}
function agregarPregunta(){
  if (!encuestaSeleccionadaId.value) return
  formPregunta.post(`/admin-unidad/encuestas/${encuestaSeleccionadaId.value}/preguntas`, { onSuccess: () => { formPregunta.reset('texto','orden'); cargarPreguntas() } })
}
function actualizarPregunta(p:Pregunta){
  router.put(`/admin-unidad/preguntas/${p.id}`, { texto:p.texto, tipo:p.tipo?.descripcion ?? 'Abierta', orden:p.orden ?? 0 }, { onSuccess: cargarPreguntas })
}
function borrarPregunta(id:number){ if (confirm('¿Eliminar pregunta?')) router.delete(`/admin-unidad/preguntas/${id}`, { onSuccess: cargarPreguntas }) }

function agregarOpcion(p:Pregunta){
  console.log('Agregando opción a pregunta:', p.id)
  
  // Inicializar el array de opciones si no existe
  if (!p.opciones) {
    p.opciones = []
  }
  
  // Crear una nueva opción localmente SIN enviar al servidor todavía
  const nuevaOpcionLocal: Opcion = {
    id: -Date.now(), // ID temporal NEGATIVO para identificarla
    texto: '',
    valor: null,
    orden: p.opciones.length + 1
  }
  
  p.opciones.push(nuevaOpcionLocal)
  console.log('✓ Nueva opción agregada localmente. Escribe el texto y haz clic en "Guardar y Publicar"')
}

const opcionTimers = new Map<number, number>()

function actualizarOpcion(o:Opcion, instantaneo = false){
  console.log('Actualizando opción:', o.id, 'texto:', o.texto)
  
  // Si no es instantáneo, usar debounce
  if (!instantaneo) {
    if (opcionTimers.has(o.id)) {
      clearTimeout(opcionTimers.get(o.id)!)
    }
    
    const timer = setTimeout(() => {
      guardarOpcion(o)
      opcionTimers.delete(o.id)
    }, 800)
    
    opcionTimers.set(o.id, timer)
    return
  }
  
  guardarOpcion(o)
}

let tokenExpirado = false

function guardarOpcion(o:Opcion) {
  // Si ya sabemos que el token expiró, no intentar guardar
  if (tokenExpirado) {
    console.warn('⚠️ Token expirado, no se puede guardar. Recarga la página.')
    return
  }
  
  const token = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content')
  if (!token) {
    console.error('✗ No se encontró el token CSRF.')
    return
  }
  
  const xhr = new XMLHttpRequest()
  xhr.open('PUT', `/admin-unidad/opciones/${o.id}`)
  xhr.setRequestHeader('Content-Type', 'application/json')
  xhr.setRequestHeader('X-Requested-With', 'XMLHttpRequest')
  xhr.setRequestHeader('Accept', 'application/json')
  xhr.setRequestHeader('X-CSRF-TOKEN', token)
  xhr.onload = () => { 
    if (xhr.status === 200) {
      console.log('✓ Opción guardada:', o.id, 'texto:', o.texto)
    } else if (xhr.status === 419) {
      tokenExpirado = true
      console.error('✗ Token CSRF expirado. Recargando página...')
      setTimeout(() => {
        window.location.reload()
      }, 1000)
    } else {
      console.error('✗ Error guardando opción:', o.id, 'status:', xhr.status)
    }
  }
  xhr.onerror = () => console.error('✗ Error de red guardando opción:', o.id)
  xhr.send(JSON.stringify({ texto:o.texto, valor:o.valor, orden:o.orden }))
}

function borrarOpcion(preguntaId: number, id: number){ 
  if (!confirm('¿Eliminar opción?')) return
  
  // Si el ID es negativo, es una opción temporal que no está en el servidor
  if (id < 0) {
    console.log('Eliminando opción temporal (no guardada):', id)
    const pregunta = preguntas.value.find(p => p.id === preguntaId)
    if (pregunta && pregunta.opciones) {
      const index = pregunta.opciones.findIndex(o => o.id === id)
      if (index !== -1) {
        pregunta.opciones.splice(index, 1)
        console.log('✓ Opción temporal eliminada')
      }
    }
    return
  }
  
  // Si el ID es positivo, eliminar del servidor
  router.delete(`/admin-unidad/opciones/${id}`, {
    preserveState: true,
    preserveScroll: true,
    onSuccess: () => {
      console.log('✓ Opción eliminada del servidor')
      cargarPreguntas()
    },
    onError: (error) => {
      console.error('✗ Error eliminando opción:', error)
      alert('Error al eliminar la opción.')
    }
  })
}

const guardandoCambios = ref(false)
function guardarYPublicar() {
  if (guardandoCambios.value) return
  guardandoCambios.value = true
  
  console.log('Guardando todas las opciones...')
  let pendientes = 0
  let completadas = 0
  const errores: string[] = []
  
  // Validar que todas las opciones tengan texto
  let opcionesVacias = 0
  preguntas.value.forEach(pregunta => {
    if (pregunta.opciones && pregunta.opciones.length > 0) {
      pregunta.opciones.forEach(opcion => {
        if (!opcion.texto || opcion.texto.trim() === '') {
          opcionesVacias++
        }
      })
    }
  })
  
  if (opcionesVacias > 0) {
    alert(`⚠️ Hay ${opcionesVacias} opción(es) sin texto.\n\nPor favor escribe un texto válido en todas las opciones antes de guardar.`)
    guardandoCambios.value = false
    return
  }
  
  // Procesar cada pregunta
  preguntas.value.forEach(pregunta => {
    if (pregunta.opciones && pregunta.opciones.length > 0) {
      pregunta.opciones.forEach(opcion => {
        pendientes++
        
        // Si el ID es negativo, es una opción nueva (crear)
        if (opcion.id < 0) {
          console.log('Creando nueva opción:', opcion.texto)
          const formOpcion = useForm({ 
            texto: opcion.texto, 
            valor: opcion.valor, 
            orden: opcion.orden 
          })
          
          formOpcion.post(`/admin-unidad/preguntas/${pregunta.id}/opciones`, {
            preserveState: true,
            preserveScroll: true,
            onSuccess: () => {
              completadas++
              console.log('✓ Nueva opción creada:', opcion.texto)
              verificarCompletado()
            },
            onError: (error) => {
              completadas++
              errores.push(`Error creando opción: ${opcion.texto}`)
              console.error('✗ Error creando opción:', error)
              verificarCompletado()
            }
          })
        } else {
          // Actualizar opción existente
          console.log('Actualizando opción:', opcion.id, 'texto:', opcion.texto)
          router.put(
            `/admin-unidad/opciones/${opcion.id}`,
            { texto: opcion.texto, valor: opcion.valor, orden: opcion.orden },
            {
              preserveState: true,
              preserveScroll: true,
              onSuccess: () => {
                completadas++
                console.log('✓ Opción actualizada:', opcion.id)
                verificarCompletado()
              },
              onError: (error) => {
                completadas++
                errores.push(`Error actualizando opción ${opcion.id}`)
                console.error('✗ Error actualizando:', error)
                verificarCompletado()
              }
            }
          )
        }
      })
    }
  })
  
  function verificarCompletado() {
    if (completadas === pendientes) {
      setTimeout(() => {
        cargarPreguntas()
        guardandoCambios.value = false
        if (errores.length > 0) {
          alert(`Guardado con ${errores.length} errores:\n${errores.join('\n')}`)
        } else {
          alert(`¡Cambios guardados correctamente! (${pendientes} opciones procesadas)\n\nLos estudiantes verán las opciones en sus encuestas.`)
        }
      }, 500)
    }
  }
  
  if (pendientes === 0) {
    alert('No hay opciones para guardar')
    guardandoCambios.value = false
  }
}

// Asignaciones
const formAsignacion = useForm({ 
  encuesta_id:null as number|null, 
  tipo_asignacion:'carrera_generacion' as string,
  unidad_id:null as number|null,
  carrera_id:null as number|null, 
  generacion_id:null as number|null, 
  fecha_inicio:'', 
  fecha_fin:'' 
})
const asignar = () => { formAsignacion.post('/admin-unidad/asignaciones', { onSuccess: () => formAsignacion.reset() }) }
const eliminarAsignacion = (id:number) => { if (confirm('¿Eliminar asignación?')) router.delete(`/admin-unidad/asignaciones/${id}`) }

const tipoAsignacionLabel = (tipo:string|null) => {
  if (!tipo) return 'Carrera + Generación'
  switch(tipo) {
    case 'todos': return 'Todos'
    case 'unidad': return 'Por Unidad'
    case 'generacion': return 'Por Generación'
    case 'carrera_generacion': return 'Carrera + Generación'
    default: return tipo
  }
}

onMounted(()=>{ /* no-op */ })
</script>

<template>
  <Head title="Admin Unidad (Encuestas)" />
  <AppLayout :breadcrumbs="[{ title: 'Admin Unidad', href: '/admin-unidad' }]"><div class="container mx-auto px-4 py-8 max-w-7xl">
      <div class="mb-6 flex items-center justify-between gap-4">
        <h1 class="text-3xl font-bold">Admin Unidad · Encuestas</h1>
        <Button variant="outline" @click="() => { window.location.href = '/admin-unidad/backup' }">Respaldar base de datos</Button>
      </div>

      <div class="flex border-b mb-6">
        <button @click="tab='encuestas'" :class="['px-6 py-3 font-medium border-b-2', tab==='encuestas' ? 'border-blue-600 text-blue-600' : 'border-transparent text-gray-600']">Gestor de Encuestas</button>
        <button @click="tab='preguntas'" :class="['px-6 py-3 font-medium border-b-2', tab==='preguntas' ? 'border-green-600 text-green-600' : 'border-transparent text-gray-600']">Creador de Preguntas</button>
        <button @click="tab='asignaciones'" :class="['px-6 py-3 font-medium border-b-2', tab==='asignaciones' ? 'border-purple-600 text-purple-600' : 'border-transparent text-gray-600']">Asignar Encuestas</button>
      </div>

      <!-- TAB 1: Encuestas -->
      <div v-if="tab==='encuestas'" class="space-y-6">
        <div class="flex justify-between items-center">
          <h2 class="text-2xl font-bold">Gestor de Encuestas</h2>
          <Button v-if="!mostrandoFormEncuesta" @click="abrirNuevaEncuesta" class="bg-blue-600 hover:bg-blue-700">Crear Encuesta</Button>
        </div>
        <Card v-if="mostrandoFormEncuesta">
          <CardHeader><CardTitle>Nueva Encuesta</CardTitle></CardHeader>
          <CardContent>
            <form @submit.prevent="guardarEncuesta" class="space-y-4">
              <div>
                <Label for="enc_nombre">Nombre *</Label>
                <Input id="enc_nombre" v-model="formEncuesta.nombre" required />
              </div>
              <div>
                <Label for="enc_desc">Descripción</Label>
                <textarea id="enc_desc" v-model="formEncuesta.descripcion" rows="3" class="flex w-full rounded-md border border-input bg-transparent px-3 py-2 text-sm"></textarea>
              </div>
              <div class="flex gap-3">
                <Button type="submit" :disabled="formEncuesta.processing" class="bg-green-600 hover:bg-green-700">{{ formEncuesta.processing ? 'Guardando…' : 'Guardar' }}</Button>
                <Button type="button" variant="outline" @click="cancelarEncuesta">Cancelar</Button>
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
                  <th class="text-left py-3 px-4">Descripción</th>
                  <th class="text-left py-3 px-4">Vigencia</th>
                  <th class="text-right py-3 px-4">Acciones</th>
                </tr>
              </thead>
              <tbody>
                <tr v-for="e in props.encuestas" :key="e.id" class="border-b hover:bg-muted/50">
                  <td class="py-3 px-4">{{ e.nombre }}</td>
                  <td class="py-3 px-4">{{ e.descripcion || '-' }}</td>
                  <td class="py-3 px-4">{{ e.fecha_inicio || '-' }} — {{ e.fecha_fin || '-' }}</td>
                  <td class="py-3 px-4 text-right">
                    <div class="flex gap-2 justify-end">
                      <Button size="sm" variant="outline" @click="editarPreguntas(e.id)">Editar Preguntas</Button>
                      <Button size="sm" variant="destructive" @click="eliminarEncuesta(e.id)">Eliminar</Button>
                    </div>
                  </td>
                </tr>
              </tbody>
            </table>
          </CardContent>
        </Card>
      </div>

      <!-- TAB 2: Preguntas -->
      <div v-if="tab==='preguntas'" class="space-y-6">
        <div class="flex items-end gap-4">
          <div class="flex-1">
            <Label>Encuesta</Label>
            <select v-model.number="encuestaSeleccionadaId" @change="cargarPreguntas" class="flex h-9 w-full rounded-md border border-input bg-transparent px-3 py-1 text-sm">
              <option :value="null">Seleccione…</option>
              <option v-for="e in props.encuestas" :key="e.id" :value="e.id">{{ e.nombre }}</option>
            </select>
          </div>
        </div>

        <Card>
          <CardHeader class="flex flex-row items-center justify-between">
            <CardTitle>Preguntas</CardTitle>
            <div class="flex gap-2">
              <Input v-model="formPregunta.texto" placeholder="Texto de la pregunta" class="w-80" />
              <select v-model="formPregunta.tipo" class="flex h-9 rounded-md border border-input bg-transparent px-3 py-1 text-sm">
                <option v-for="t in tipos" :key="t" :value="t">{{ t }}</option>
              </select>
              <Input v-model.number="formPregunta.orden" type="number" min="0" class="w-24" />
              <Button :disabled="!encuestaSeleccionadaId" @click="agregarPregunta">+ Añadir Pregunta</Button>
            </div>
          </CardHeader>
          <CardContent>
            <div v-if="!encuestaSeleccionadaId" class="text-muted-foreground">Seleccione una encuesta para gestionar preguntas.</div>
            <div v-else>
              <div v-if="cargandoPreguntas">Cargando…</div>
              <div v-else class="space-y-4">
                <div v-for="p in preguntas" :key="p.id" class="border rounded-md p-4 space-y-3">
                  <div class="grid grid-cols-12 gap-3 items-center">
                    <div class="col-span-6">
                      <Label>Texto</Label>
                      <Input v-model="p.texto" @change="() => actualizarPregunta(p)" />
                    </div>
                    <div class="col-span-3">
                      <Label>Tipo</Label>
                      <select :value="p.tipo?.descripcion || 'Abierta'" @change="(e: Event) => { p.tipo = { id: 0, descripcion: (e.target as HTMLSelectElement).value }; actualizarPregunta(p) }" class="flex h-9 w-full rounded-md border border-input bg-transparent px-3 py-1 text-sm">
                        <option v-for="t in tipos" :key="t" :value="t">{{ t }}</option>
                      </select>
                    </div>
                    <div class="col-span-2">
                      <Label>Orden</Label>
                      <Input type="number" v-model.number="p.orden" @change="() => actualizarPregunta(p)" />
                    </div>
                    <div class="col-span-1 text-right">
                      <Button size="sm" variant="destructive" @click="borrarPregunta(p.id)">Eliminar</Button>
                    </div>
                  </div>

                  <!-- Opciones cuando el tipo requiere opciones -->
                  <div v-if="['Opción Múltiple', 'Casillas de Verificación', 'Escala Likert'].includes(p.tipo?.descripcion || '')" class="mt-2 border-t pt-3">
                    <div class="flex justify-between items-center mb-2">
                      <div class="font-medium">Opciones</div>
                      <Button size="sm" @click="agregarOpcion(p)">+ Añadir Opción</Button>
                    </div>
                    <div v-if="!p.opciones || p.opciones.length===0" class="text-sm text-muted-foreground">Sin opciones.</div>
                    <div v-else class="space-y-2">
                      <div v-for="o in p.opciones" :key="o.id" class="grid grid-cols-12 gap-2 items-center">
                        <Input 
                          class="col-span-8" 
                          v-model="o.texto" 
                          placeholder="Escribe el texto de la opción aquí"
                        />
                        <Input class="col-span-2" type="number" v-model.number="o.valor" placeholder="Valor" />
                        <div class="col-span-1 text-right">
                          <Button size="sm" variant="destructive" @click="borrarOpcion(p.id, o.id)">X</Button>
                        </div>
                      </div>
                    </div>
                  </div>
                </div>
              </div>
              
              <!-- Botón de Guardar Todo -->
              <div v-if="preguntas.length > 0" class="mt-6 flex justify-end gap-3 border-t pt-4">
                <Button @click="cargarPreguntas" variant="outline">
                  Recargar Preguntas
                </Button>
                <Button @click="guardarYPublicar" class="bg-green-600 hover:bg-green-700">
                  Guardar y Publicar Cambios
                </Button>
              </div>
            </div>
          </CardContent>
        </Card>
      </div>

      <!-- TAB 3: Asignaciones -->
      <div v-if="tab==='asignaciones'" class="space-y-6">
        <h2 class="text-2xl font-bold">Asignar Encuestas</h2>
        <Card>
          <CardContent class="space-y-4 pt-6">
            <form @submit.prevent="asignar" class="grid grid-cols-1 md:grid-cols-2 gap-4">
              <div>
                <Label>Encuesta *</Label>
                <select v-model.number="formAsignacion.encuesta_id" class="flex h-9 w-full rounded-md border border-input bg-transparent px-3 py-1 text-sm" required>
                  <option :value="null">Seleccione…</option>
                  <option v-for="e in props.encuestas" :key="e.id" :value="e.id">{{ e.nombre }}</option>
                </select>
              </div>
              <div>
                <Label>Tipo de Asignación *</Label>
                <select v-model="formAsignacion.tipo_asignacion" class="flex h-9 w-full rounded-md border border-input bg-transparent px-3 py-1 text-sm" required>
                  <option value="todos">Todos (todos los egresados/estudiantes)</option>
                  <option value="unidad">Por Unidad</option>
                  <option value="generacion">Por Generación (todas las carreras)</option>
                  <option value="carrera_generacion">Carrera + Generación</option>
                </select>
              </div>
              
              <!-- Campo Unidad (solo si tipo es 'unidad') -->
              <div v-if="formAsignacion.tipo_asignacion === 'unidad'">
                <Label>Unidad *</Label>
                <select v-model.number="formAsignacion.unidad_id" class="flex h-9 w-full rounded-md border border-input bg-transparent px-3 py-1 text-sm" required>
                  <option :value="null">Seleccione…</option>
                  <option v-for="u in props.unidades" :key="u.id" :value="u.id">{{ u.nombre }}</option>
                </select>
              </div>

              <!-- Campo Carrera (solo si tipo es 'carrera_generacion') -->
              <div v-if="formAsignacion.tipo_asignacion === 'carrera_generacion'">
                <Label>Carrera *</Label>
                <select v-model.number="formAsignacion.carrera_id" class="flex h-9 w-full rounded-md border border-input bg-transparent px-3 py-1 text-sm" required>
                  <option :value="null">Seleccione…</option>
                  <option v-for="c in props.carreras" :key="c.id" :value="c.id">{{ c.nombre }}</option>
                </select>
              </div>

              <!-- Campo Generación (si tipo es 'generacion' o 'carrera_generacion') -->
              <div v-if="formAsignacion.tipo_asignacion === 'generacion' || formAsignacion.tipo_asignacion === 'carrera_generacion'">
                <Label>Generación *</Label>
                <select v-model.number="formAsignacion.generacion_id" class="flex h-9 w-full rounded-md border border-input bg-transparent px-3 py-1 text-sm" required>
                  <option :value="null">Seleccione…</option>
                  <option v-for="g in props.generaciones" :key="g.id" :value="g.id">{{ g.nombre }}</option>
                </select>
              </div>

              <div>
                <Label>Fecha de Inicio</Label>
                <Input type="date" v-model="formAsignacion.fecha_inicio" />
              </div>
              <div>
                <Label>Fecha de Fin</Label>
                <Input type="date" v-model="formAsignacion.fecha_fin" />
              </div>
              <div class="md:col-span-2">
                <Button type="submit" :disabled="formAsignacion.processing">{{ formAsignacion.processing ? 'Asignando…' : 'Asignar Encuesta' }}</Button>
              </div>
            </form>
          </CardContent>
        </Card>

        <Card>
          <CardContent class="pt-6">
            <table class="w-full">
              <thead>
                <tr class="border-b">
                  <th class="text-left py-3 px-4">Encuesta</th>
                  <th class="text-left py-3 px-4">Tipo</th>
                  <th class="text-left py-3 px-4">Alcance</th>
                  <th class="text-left py-3 px-4">Vigencia</th>
                  <th class="text-right py-3 px-4">Acciones</th>
                </tr>
              </thead>
              <tbody>
                <tr v-for="a in props.asignaciones" :key="a.id" class="border-b hover:bg-muted/50">
                  <td class="py-3 px-4">{{ a.encuesta?.nombre }}</td>
                  <td class="py-3 px-4">
                    <span class="px-2 py-1 text-xs rounded-full bg-blue-100 dark:bg-blue-900 text-blue-800 dark:text-blue-100">
                      {{ tipoAsignacionLabel(a.tipo_asignacion) }}
                    </span>
                  </td>
                  <td class="py-3 px-4">
                    <template v-if="a.tipo_asignacion === 'todos'">
                      <span class="text-muted-foreground">Todos los usuarios</span>
                    </template>
                    <template v-else-if="a.tipo_asignacion === 'unidad'">
                      <span class="font-medium">{{ a.unidad?.nombre || '-' }}</span>
                    </template>
                    <template v-else-if="a.tipo_asignacion === 'generacion'">
                      <span class="font-medium">{{ a.generacion?.nombre || '-' }}</span>
                    </template>
                    <template v-else>
                      <span class="font-medium">{{ a.carrera?.nombre || '-' }}</span> · 
                      <span class="text-muted-foreground">{{ a.generacion?.nombre || '-' }}</span>
                    </template>
                  </td>
                  <td class="py-3 px-4 text-sm text-muted-foreground">{{ a.encuesta?.fecha_inicio || '-' }} — {{ a.encuesta?.fecha_fin || '-' }}</td>
                  <td class="py-3 px-4 text-right">
                    <Button size="sm" variant="destructive" @click="eliminarAsignacion(a.id)">Eliminar</Button>
                  </td>
                </tr>
              </tbody>
            </table>
          </CardContent>
        </Card>
      </div>
    </div></AppLayout>
  </template>

