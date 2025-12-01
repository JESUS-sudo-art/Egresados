<script setup lang="ts">
import { computed, ref } from 'vue'
import AppLayout from '@/layouts/AppLayout.vue'
import { Head, router } from '@inertiajs/vue3'
import { Card, CardContent, CardDescription, CardHeader, CardTitle } from '@/components/ui/card'
import { Input } from '@/components/ui/input'
import { Button } from '@/components/ui/button'
import { Badge } from '@/components/ui/badge'

interface Encuesta {
  id: number
  nombre: string
}

interface Egresado {
  id: number
  matricula: string | null
  nombre: string
  apellidos: string
  email: string | null
  estatus: string
  carreras: string[]
  encuestas_contestadas: Encuesta[]
  num_encuestas: number
}

interface Filters {
  search: string
  estatus: string
}

interface Props {
  egresados: Egresado[]
  filters: Filters
}

const props = defineProps<Props>()

// Debug: verificar qué datos llegan
console.log('Egresados recibidos:', props.egresados)
if (props.egresados.length > 0) {
  console.log('Primer egresado:', props.egresados[0])
}

const search = ref(props.filters.search || '')

const filtrados = computed(() => {
  if (!search.value) return props.egresados
  const term = search.value.toLowerCase()
  return props.egresados.filter(e =>
    [e.nombre, e.apellidos, e.matricula, e.email].filter(Boolean).some(v => v!.toLowerCase().includes(term))
  )
})

const submitSearch = () => {
  router.get('/catalogo-egresados', { search: search.value }, { preserveScroll: true, preserveState: true })
}
</script>

<template>
  <Head title="Catálogo de Egresados" />
  <AppLayout :breadcrumbs="[{ title: 'Catálogo Egresados', href: '/catalogo-egresados' }]">
    <div class="container mx-auto px-4 py-8 max-w-7xl space-y-6">
      <div class="flex justify-between items-center">
        <h1 class="text-3xl font-bold">Catálogo de Egresados</h1>
      </div>

      <Card>
        <CardHeader>
          <CardTitle>Filtros</CardTitle>
          <CardDescription>Busca por nombre, apellidos, matrícula o correo</CardDescription>
        </CardHeader>
        <CardContent>
          <form @submit.prevent="submitSearch" class="flex flex-wrap gap-4 items-end">
            <div class="flex-1 min-w-[240px]">
              <label class="text-sm font-medium mb-1 block">Buscar</label>
              <Input v-model="search" placeholder="Ej. Juan Pérez / 2020 / correo@..." />
            </div>
            <Button type="submit">Aplicar</Button>
          </form>
        </CardContent>
      </Card>

      <Card>
        <CardHeader>
          <CardTitle>Listado</CardTitle>
          <CardDescription>Total: {{ filtrados.length }}</CardDescription>
        </CardHeader>
        <CardContent>
          <div v-if="filtrados.length" class="overflow-x-auto">
            <table class="w-full border-collapse">
              <thead>
                <tr class="border-b bg-muted/50">
                  <th class="text-left py-2 px-3 text-sm font-semibold">Matrícula</th>
                  <th class="text-left py-2 px-3 text-sm font-semibold">Nombre</th>
                  <th class="text-left py-2 px-3 text-sm font-semibold">Carreras</th>
                  <th class="text-left py-2 px-3 text-sm font-semibold">Email</th>
                  <th class="text-left py-2 px-3 text-sm font-semibold">Encuestas</th>
                  <th class="text-left py-2 px-3 text-sm font-semibold">Estatus</th>
                  <th class="text-left py-2 px-3 text-sm font-semibold">Acciones</th>
                </tr>
              </thead>
              <tbody>
                <tr v-for="e in filtrados" :key="e.id" class="border-b hover:bg-muted/50">
                  <td class="py-2 px-3 text-sm">{{ e.matricula || '—' }}</td>
                  <td class="py-2 px-3 text-sm font-medium">{{ e.nombre }} {{ e.apellidos }}</td>
                  <td class="py-2 px-3 text-sm">
                    <div class="flex flex-wrap gap-1">
                      <Badge v-for="c in e.carreras" :key="c" variant="secondary" class="text-xs">{{ c }}</Badge>
                      <span v-if="!e.carreras.length" class="text-muted-foreground">Sin carreras</span>
                    </div>
                  </td>
                  <td class="py-2 px-3 text-sm">{{ e.email || '—' }}</td>
                  <td class="py-2 px-3 text-sm">
                    <div class="flex flex-wrap gap-1">
                      <Badge v-for="enc in e.encuestas_contestadas" :key="enc.id" variant="default" class="text-xs">{{ enc.nombre }}</Badge>
                      <span v-if="!e.encuestas_contestadas.length" class="text-muted-foreground">Sin encuestas</span>
                    </div>
                  </td>
                  <td class="py-2 px-3 text-sm">
                    <Badge :variant="e.estatus === 'ACTIVO' ? 'default' : 'outline'" class="text-xs">{{ e.estatus }}</Badge>
                  </td>
                  <td class="py-2 px-3 text-sm">
                    <Button 
                      size="sm" 
                      variant="outline"
                      @click="router.visit(`/egresados/${e.id}`)"
                    >
                      Ver perfil
                    </Button>
                  </td>
                </tr>
              </tbody>
            </table>
          </div>
          <div v-else class="text-center py-12 text-muted-foreground">No se encontraron egresados.</div>
        </CardContent>
      </Card>
    </div>
  </AppLayout>
</template>
