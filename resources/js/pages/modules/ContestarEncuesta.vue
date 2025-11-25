<script setup lang="ts">
import { ref } from 'vue'
import AppLayout from '@/layouts/AppLayout.vue'
import { Head, useForm } from '@inertiajs/vue3'
import { Card, CardContent, CardDescription, CardHeader, CardTitle } from '@/components/ui/card'
import { Button } from '@/components/ui/button'
import { Input } from '@/components/ui/input'
import { Label } from '@/components/ui/label'
import { Textarea } from '@/components/ui/textarea'
import { Checkbox } from '@/components/ui/checkbox'
import { RadioGroup, RadioGroupItem } from '@/components/ui/radio-group'
import { FileText, Send } from 'lucide-vue-next'

interface Opcion {
  id: number
  texto: string
  valor: number | null
}

interface Pregunta {
  id: number
  texto: string
  tipo: string
  orden: number | null
  opciones: Opcion[]
}

interface Dimension {
  id: number | null
  nombre: string
  descripcion: string | null
  orden: number | null
  preguntas: Pregunta[]
}

interface Encuesta {
  id: number
  nombre: string
  descripcion: string | null
  dimensiones: Dimension[]
}

interface Props {
  encuesta: Encuesta
}

const props = defineProps<Props>()

// Crear estructura de respuestas
const respuestas = ref<Record<number, any>>({})

// Inicializar respuestas vacías
props.encuesta.dimensiones.forEach(dimension => {
  dimension.preguntas.forEach(pregunta => {
    if (pregunta.tipo === 'Casillas de Verificación') {
      respuestas.value[pregunta.id] = []
    } else {
      respuestas.value[pregunta.id] = null
    }
  })
})

const form = useForm({
  respuestas: [] as any[]
})

const handleCheckboxChange = (preguntaId: number, opcionId: number, checked: boolean) => {
  console.log(`Checkbox changed - Pregunta: ${preguntaId}, Opcion: ${opcionId}, Checked: ${checked}`)
  
  if (!Array.isArray(respuestas.value[preguntaId])) {
    respuestas.value[preguntaId] = []
  }
  
  if (checked) {
    respuestas.value[preguntaId].push(opcionId)
    console.log(`Agregado. Array actual:`, respuestas.value[preguntaId])
  } else {
    respuestas.value[preguntaId] = respuestas.value[preguntaId].filter((id: number) => id !== opcionId)
    console.log(`Removido. Array actual:`, respuestas.value[preguntaId])
  }
}

const enviarEncuesta = () => {
  // Debug: Ver el estado actual de todas las respuestas
  console.log('Estado completo de respuestas antes de enviar:', JSON.parse(JSON.stringify(respuestas.value)))
  
  // Convertir respuestas a formato para el backend
  const respuestasArray = Object.entries(respuestas.value).map(([preguntaId, respuesta]) => {
    const pregunta = props.encuesta.preguntas.find(p => p.id === parseInt(preguntaId))
    
    console.log(`Procesando pregunta ${preguntaId} (${pregunta?.tipo}):`, respuesta)
    
    // Para preguntas de tipo checkbox (múltiples opciones)
    if (pregunta?.tipo === 'Casillas de Verificación') {
      return {
        pregunta_id: parseInt(preguntaId),
        opciones_seleccionadas: Array.isArray(respuesta) ? respuesta : [],
        respuesta: null
      }
    }
    // Para preguntas con opciones (radio, likert)
    else if (pregunta?.tipo === 'Opción Múltiple' || pregunta?.tipo === 'Escala Likert') {
      return {
        pregunta_id: parseInt(preguntaId),
        opciones_seleccionadas: respuesta ? [parseInt(respuesta)] : [],
        respuesta: null
      }
    }
    // Para preguntas de Sí/No - guardamos como texto
    else if (pregunta?.tipo === 'Sí/No') {
      return {
        pregunta_id: parseInt(preguntaId),
        respuesta: respuesta,
        opciones_seleccionadas: null
      }
    }
    // Para preguntas de texto abierto o numéricas
    else {
      return {
        pregunta_id: parseInt(preguntaId),
        respuesta: respuesta,
        opciones_seleccionadas: null
      }
    }
  })

  console.log('Enviando respuestas:', respuestasArray)
  form.respuestas = respuestasArray
  form.post(`/encuesta/${props.encuesta.id}/responder`)
}
</script>

<template>
  <Head :title="`Contestar: ${encuesta.nombre}`" />
  <AppLayout :breadcrumbs="[
    { title: 'Panel', href: '/dashboard' },
    { title: encuesta.nombre, href: '#' }
  ]">
    <div class="container mx-auto px-4 py-8 max-w-4xl">
      <div class="mb-8">
        <div class="flex items-center gap-3 mb-4">
          <FileText class="h-8 w-8 text-primary" />
          <h1 class="text-3xl font-bold">{{ encuesta.nombre }}</h1>
        </div>
        <p v-if="encuesta.descripcion" class="text-muted-foreground">{{ encuesta.descripcion }}</p>
      </div>

      <form @submit.prevent="enviarEncuesta" class="space-y-6">
        <div v-for="(dimension, dIndex) in encuesta.dimensiones" :key="`dim-${dimension.id}-${dIndex}`" class="space-y-4">
          <h2 class="text-2xl font-semibold mt-8" v-if="dimension.nombre">
            {{ dimension.nombre }}
            <span v-if="dimension.descripcion" class="block text-sm text-muted-foreground font-normal">{{ dimension.descripcion }}</span>
          </h2>
          <Card v-for="(pregunta, index) in dimension.preguntas" :key="pregunta.id">
          <CardHeader>
            <CardTitle class="text-lg">
              <span class="text-muted-foreground mr-2">{{ index + 1 }}.</span>
              {{ pregunta.texto }}
            </CardTitle>
            <CardDescription>
              <span class="text-xs px-2 py-1 rounded-full bg-blue-100 text-blue-800 dark:bg-blue-900 dark:text-blue-100">
                {{ pregunta.tipo }}
              </span>
            </CardDescription>
          </CardHeader>
          <CardContent>
            <!-- Pregunta Abierta -->
            <div v-if="pregunta.tipo === 'Abierta'">
              <textarea
                v-model="respuestas[pregunta.id]" 
                placeholder="Escribe tu respuesta aquí..."
                rows="4"
                class="w-full min-h-[100px] rounded-md border border-input bg-background px-3 py-2 text-sm ring-offset-background placeholder:text-muted-foreground focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-ring focus-visible:ring-offset-2 disabled:cursor-not-allowed disabled:opacity-50"
              ></textarea>
              <p class="text-xs text-muted-foreground mt-1">Valor actual: {{ respuestas[pregunta.id] || 'vacío' }}</p>
            </div>

            <!-- Opción Múltiple (Radio) -->
            <div v-else-if="pregunta.tipo === 'Opción Múltiple'">
              <RadioGroup v-model="respuestas[pregunta.id]">
                <div v-for="opcion in pregunta.opciones" :key="opcion.id" class="flex items-center space-x-2 mb-3">
                  <RadioGroupItem :value="opcion.id.toString()" :id="`p${pregunta.id}-o${opcion.id}`" />
                  <Label :for="`p${pregunta.id}-o${opcion.id}`" class="cursor-pointer">{{ opcion.texto }}</Label>
                </div>
              </RadioGroup>
            </div>

            <!-- Casillas de Verificación (Múltiples opciones) -->
            <div v-else-if="pregunta.tipo === 'Casillas de Verificación'">
              <div v-if="!pregunta.opciones || pregunta.opciones.length === 0" class="text-sm text-muted-foreground">
                Esta pregunta no tiene opciones configuradas.
              </div>
              <div v-for="opcion in pregunta.opciones" :key="opcion.id" class="flex items-center space-x-3 mb-3">
                <input 
                  type="checkbox"
                  :id="`p${pregunta.id}-o${opcion.id}`"
                  :value="opcion.id"
                  @change="(e: any) => handleCheckboxChange(pregunta.id, opcion.id, e.target.checked)"
                  class="w-4 h-4 text-primary bg-gray-100 border-gray-300 rounded focus:ring-primary focus:ring-2"
                />
                <Label :for="`p${pregunta.id}-o${opcion.id}`" class="cursor-pointer">
                  {{ opcion.texto || '(Sin texto)' }}
                </Label>
              </div>
              <p class="text-xs text-muted-foreground mt-2">Seleccionadas: {{ respuestas[pregunta.id]?.length || 0 }}</p>
            </div>

            <!-- Escala Likert -->
            <div v-else-if="pregunta.tipo === 'Escala Likert'">
              <RadioGroup v-model="respuestas[pregunta.id]">
                <div class="grid grid-cols-5 gap-3">
                  <div v-for="opcion in pregunta.opciones" :key="opcion.id" class="flex flex-col items-center space-y-2">
                    <RadioGroupItem :value="opcion.id.toString()" :id="`p${pregunta.id}-o${opcion.id}`" />
                    <Label :for="`p${pregunta.id}-o${opcion.id}`" class="text-center text-sm cursor-pointer">
                      {{ opcion.texto }}
                    </Label>
                  </div>
                </div>
              </RadioGroup>
            </div>

            <!-- Sí/No -->
            <div v-else-if="pregunta.tipo === 'Sí/No'">
              <RadioGroup v-model="respuestas[pregunta.id]">
                <div class="flex items-center space-x-6">
                  <div class="flex items-center space-x-2">
                    <RadioGroupItem value="si" :id="`p${pregunta.id}-si`" />
                    <Label :for="`p${pregunta.id}-si`" class="cursor-pointer">Sí</Label>
                  </div>
                  <div class="flex items-center space-x-2">
                    <RadioGroupItem value="no" :id="`p${pregunta.id}-no`" />
                    <Label :for="`p${pregunta.id}-no`" class="cursor-pointer">No</Label>
                  </div>
                </div>
              </RadioGroup>
            </div>

            <!-- Numérica -->
            <div v-else-if="pregunta.tipo === 'Numérica'">
              <Input 
                type="number" 
                v-model.number="respuestas[pregunta.id]" 
                placeholder="Ingresa un número"
                class="w-full"
              />
            </div>

            <!-- Fecha -->
            <div v-else-if="pregunta.tipo === 'Fecha'">
              <Input 
                type="date" 
                v-model="respuestas[pregunta.id]" 
                class="w-full"
              />
            </div>
          </CardContent>
          </Card>
        </div>

        <div class="flex justify-end gap-4 pt-4">
          <Button type="button" variant="outline" @click="() => $inertia.visit('/dashboard')">
            Cancelar
          </Button>
          <Button type="submit" :disabled="form.processing" class="gap-2">
            <Send class="h-4 w-4" />
            {{ form.processing ? 'Enviando...' : 'Enviar Encuesta' }}
          </Button>
        </div>
      </form>
    </div>
  </AppLayout>
</template>
