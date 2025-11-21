<script setup lang="ts">
import AppLayout from '@/layouts/AppLayout.vue'
import { Head } from '@inertiajs/vue3'
import { Card, CardContent, CardDescription, CardHeader, CardTitle } from '@/components/ui/card'
import { Button } from '@/components/ui/button'

interface Encuesta {
  id: number
  tipo: string
  nombre: string
  fecha: string
  folio: string
}

interface Props {
  egresado?: any
  encuestas: Encuesta[]
}

const props = defineProps<Props>()

const descargarAcuse = (tipo: string, id: number) => {
  window.location.href = `/acuses-seguimiento/descargar/${tipo}/${id}`
}
</script>

<template>
  <Head title="Acuses de Seguimiento" />
  <AppLayout :breadcrumbs="[{ title: 'Acuses de Seguimiento', href: '/acuses-seguimiento' }]">
    <div class="container mx-auto px-4 py-8 max-w-5xl">
      <Card>
        <CardHeader>
          <CardTitle class="text-3xl">Historial de Encuestas Completadas</CardTitle>
          <CardDescription>
            Aquí puedes ver todas las encuestas que has completado y descargar los acuses correspondientes.
          </CardDescription>
        </CardHeader>
        <CardContent>
          <div v-if="encuestas && encuestas.length > 0" class="overflow-x-auto">
            <table class="w-full border-collapse">
              <thead>
                <tr class="border-b">
                  <th class="text-left py-3 px-4 font-semibold text-sm">Nombre de la Encuesta</th>
                  <th class="text-left py-3 px-4 font-semibold text-sm">Fecha de Respuesta</th>
                  <th class="text-right py-3 px-4 font-semibold text-sm">Acuse</th>
                </tr>
              </thead>
              <tbody>
                <tr 
                  v-for="encuesta in encuestas" 
                  :key="encuesta.tipo + '-' + encuesta.id"
                  class="border-b hover:bg-muted/50 transition-colors"
                >
                  <td class="py-3 px-4 font-medium">{{ encuesta.nombre }}</td>
                  <td class="py-3 px-4">{{ encuesta.fecha }}</td>
                  <td class="py-3 px-4 text-right">
                    <Button 
                      @click="descargarAcuse(encuesta.tipo, encuesta.id)"
                      variant="outline"
                      size="sm"
                    >
                      Descargar PDF
                    </Button>
                  </td>
                </tr>
              </tbody>
            </table>
          </div>
          
          <div v-else class="text-center py-12 text-muted-foreground">
            <p class="text-lg">No has completado ninguna encuesta aún.</p>
            <p class="text-sm mt-2">Las encuestas que completes aparecerán aquí.</p>
          </div>
        </CardContent>
      </Card>
    </div>
  </AppLayout>
</template>
