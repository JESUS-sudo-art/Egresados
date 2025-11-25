<script setup lang="ts">
import { computed } from 'vue'
import AppLayout from '@/layouts/AppLayout.vue'
import { Head, Link } from '@inertiajs/vue3'
import { Card, CardContent, CardDescription, CardHeader, CardTitle } from '@/components/ui/card'
import { Button } from '@/components/ui/button'
import { Badge } from '@/components/ui/badge'
import { ArrowLeft, CheckCircle2 } from 'lucide-vue-next'

interface Opcion {
  id: number
  texto: string
  valor: number | null
}

interface Respuesta {
  opcion_id: number | null
  opcion_texto: string | null
  respuesta_texto: string | null
  respuesta_entero: number | null
}

interface Pregunta {
  id: number
  texto: string
  tipo: string
  orden: number | null
  opciones: Opcion[]
  respuestas: Respuesta[]
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

const formatearRespuesta = (pregunta: Pregunta) => {
  if (!pregunta.respuestas || pregunta.respuestas.length === 0) {
    return 'Sin respuesta'
  }

  // Para preguntas de texto abierto
  if (pregunta.tipo === 'Abierta') {
    return pregunta.respuestas[0].respuesta_texto || 'Sin respuesta'
  }

  // Para preguntas numéricas
  if (pregunta.tipo === 'Numérica') {
    return pregunta.respuestas[0].respuesta_entero?.toString() || 'Sin respuesta'
  }

  // Para preguntas de fecha
  if (pregunta.tipo === 'Fecha') {
    return pregunta.respuestas[0].respuesta_texto || 'Sin respuesta'
  }

  // Para preguntas Sí/No (guardadas como texto)
  if (pregunta.tipo === 'Sí/No') {
    const respuesta = pregunta.respuestas[0].respuesta_texto
    if (respuesta === 'si') return 'Sí'
    if (respuesta === 'no') return 'No'
    return pregunta.respuestas[0].opcion_texto || 'Sin respuesta'
  }

  // Para preguntas con opciones (radio, likert)
  if (pregunta.tipo === 'Opción Múltiple' || pregunta.tipo === 'Escala Likert') {
    return pregunta.respuestas[0].opcion_texto || 'Sin respuesta'
  }

  // Para casillas de verificación (múltiples respuestas)
  if (pregunta.tipo === 'Casillas de Verificación') {
    const opciones = pregunta.respuestas
      .map(r => r.opcion_texto)
      .filter(Boolean)
      .join(', ')
    return opciones || 'Sin respuesta'
  }

  return 'Sin respuesta'
}
</script>

<template>
  <Head title="Mis Respuestas" />
  <AppLayout>
    <div class="flex h-full flex-1 flex-col gap-6 overflow-x-auto rounded-xl p-6">
      <!-- Encabezado -->
      <div class="flex items-center justify-between">
        <div class="flex items-center gap-4">
          <Link href="/dashboard">
            <Button variant="outline" size="icon">
              <ArrowLeft class="h-4 w-4" />
            </Button>
          </Link>
          <div>
            <h1 class="text-3xl font-bold">{{ encuesta.nombre }}</h1>
            <p v-if="encuesta.descripcion" class="text-muted-foreground mt-1">
              {{ encuesta.descripcion }}
            </p>
          </div>
        </div>
        <Badge class="bg-green-100 text-green-800 dark:bg-green-900 dark:text-green-100 flex items-center gap-2 px-4 py-2">
          <CheckCircle2 class="h-4 w-4" />
          Completada
        </Badge>
      </div>

      <!-- Preguntas y Respuestas -->
      <div class="space-y-6">
        <div v-for="(dimension, dIndex) in encuesta.dimensiones" :key="`dim-${dimension.id}-${dIndex}`" class="space-y-4">
          <h2 class="text-2xl font-semibold mt-6" v-if="dimension.nombre">
            {{ dimension.nombre }}
            <span v-if="dimension.descripcion" class="block text-sm text-muted-foreground font-normal">{{ dimension.descripcion }}</span>
          </h2>
          <Card v-for="(pregunta, index) in dimension.preguntas" :key="pregunta.id">
          <CardHeader>
            <div class="flex items-start gap-3">
              <div class="flex-shrink-0 flex items-center justify-center w-8 h-8 rounded-full bg-primary/10 text-primary font-semibold">
                {{ index + 1 }}
              </div>
              <div class="flex-1">
                <CardTitle class="text-lg">{{ pregunta.texto }}</CardTitle>
                <CardDescription class="mt-1">
                  <Badge variant="outline" class="text-xs">{{ pregunta.tipo }}</Badge>
                </CardDescription>
              </div>
            </div>
          </CardHeader>
          <CardContent>
            <div class="pl-11">
              <!-- Respuesta de Texto -->
              <div v-if="pregunta.tipo === 'Abierta'" class="bg-muted/50 rounded-lg p-4">
                <p class="text-sm whitespace-pre-wrap">{{ formatearRespuesta(pregunta) }}</p>
              </div>

              <!-- Respuesta con Opciones -->
              <div v-else-if="pregunta.tipo === 'Opción Múltiple' || pregunta.tipo === 'Sí/No' || pregunta.tipo === 'Escala Likert'">
                <div class="bg-primary/5 border-l-4 border-primary rounded-r-lg p-4">
                  <p class="font-medium">{{ formatearRespuesta(pregunta) }}</p>
                </div>
              </div>

              <!-- Casillas de Verificación (múltiples) -->
              <div v-else-if="pregunta.tipo === 'Casillas de Verificación'">
                <div class="space-y-2">
                  <div 
                    v-for="respuesta in pregunta.respuestas" 
                    :key="respuesta.opcion_id"
                    class="flex items-center gap-2 bg-primary/5 rounded-lg p-3"
                  >
                    <CheckCircle2 class="h-4 w-4 text-primary flex-shrink-0" />
                    <span class="font-medium">{{ respuesta.opcion_texto }}</span>
                  </div>
                  <p v-if="pregunta.respuestas.length === 0" class="text-sm text-muted-foreground italic">
                    Sin respuestas seleccionadas
                  </p>
                </div>
              </div>

              <!-- Respuesta Numérica -->
              <div v-else-if="pregunta.tipo === 'Numérica'">
                <div class="bg-blue-50 dark:bg-blue-950 border border-blue-200 dark:border-blue-800 rounded-lg p-4">
                  <p class="text-2xl font-bold text-blue-600 dark:text-blue-400">
                    {{ formatearRespuesta(pregunta) }}
                  </p>
                </div>
              </div>

              <!-- Respuesta de Fecha -->
              <div v-else-if="pregunta.tipo === 'Fecha'">
                <div class="bg-purple-50 dark:bg-purple-950 border border-purple-200 dark:border-purple-800 rounded-lg p-4">
                  <p class="text-lg font-semibold text-purple-600 dark:text-purple-400">
                    📅 {{ formatearRespuesta(pregunta) }}
                  </p>
                </div>
              </div>

              <!-- Otros tipos -->
              <div v-else>
                <p class="text-sm text-muted-foreground">{{ formatearRespuesta(pregunta) }}</p>
              </div>
            </div>
          </CardContent>
          </Card>
        </div>
      </div>

      <!-- Botón para volver -->
      <div class="flex justify-center pt-4">
        <Link href="/dashboard">
          <Button size="lg">
            Volver al Panel
          </Button>
        </Link>
      </div>
    </div>
  </AppLayout>
</template>
