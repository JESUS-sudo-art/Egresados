<script setup lang="ts">
import AppLayout from '@/layouts/AppLayout.vue';
import { Head, Link } from '@inertiajs/vue3';
import { Button } from '@/components/ui/button'

interface EncuestaResumen {
  id: number
  nombre: string
  descripcion?: string | null
}

interface Props {
  encuestas?: EncuestaResumen[]
}

const props = defineProps<Props>()
</script>

<template>
  <Head title="Escritorio (Agenda)" />
  <AppLayout :breadcrumbs="[{ title: 'Escritorio', href: '/escritorio' }]">
    <!-- Vista general del hub del egresado -->
    <div class="p-6 space-y-6">
      <h1 class="text-2xl font-semibold">Escritorio (Agenda)</h1>

      <!-- Acciones rápidas -->
      <div class="flex flex-wrap gap-3">
        <Link href="/encuesta-egreso">
          <Button>Ir a Encuesta de Egreso</Button>
        </Link>
        <Link href="/encuesta-laboral">
          <Button variant="outline">Ir a Encuesta Laboral</Button>
        </Link>
      </div>

      <!-- Ver preguntas contestadas -->
      <div class="space-y-3">
        <h2 class="text-xl font-medium">Mis encuestas contestadas</h2>
        <p class="text-sm text-muted-foreground">Da clic para ver las respuestas que has registrado.</p>
        <div v-if="props.encuestas && props.encuestas.length" class="grid gap-3 sm:grid-cols-2 lg:grid-cols-3">
          <div v-for="enc in props.encuestas" :key="enc.id" class="rounded-lg border p-4 space-y-2">
            <div class="font-semibold">{{ enc.nombre }}</div>
            <div v-if="enc.descripcion" class="text-xs text-muted-foreground">{{ enc.descripcion }}</div>
            <Link :href="`/encuesta/${enc.id}/mis-respuestas`">
              <Button size="sm" class="mt-2">Ver preguntas contestadas</Button>
            </Link>
          </div>
        </div>
        <div v-else class="rounded-lg border bg-muted/30 p-4 text-sm">
          Aún no hay encuestas con respuestas para mostrar.
        </div>
      </div>
    </div>
  </AppLayout>
</template>
