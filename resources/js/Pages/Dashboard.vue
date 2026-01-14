<script setup lang="ts">
import AppLayout from '@/layouts/AppLayout.vue';
import { dashboard } from '@/routes';
import { type BreadcrumbItem } from '@/types';
import { Head, Link } from '@inertiajs/vue3';
import DashboardGrid from '@/components/DashboardGrid.vue';
import { Card, CardContent, CardDescription, CardHeader, CardTitle } from '@/components/ui/card';
import { Badge } from '@/components/ui/badge';
import { Button } from '@/components/ui/button';
import { Calendar, FileText } from 'lucide-vue-next';

interface EncuestaAsignada {
  id: number;
  encuesta_id: number;
  nombre: string;
  descripcion: string | null;
  fecha_inicio: string | null;
  fecha_fin: string | null;
  tipo_asignacion: string;
  ya_respondida: boolean;
}

interface Props {
  encuestasAsignadas?: EncuestaAsignada[];
}

const props = withDefaults(defineProps<Props>(), {
  encuestasAsignadas: () => []
});

const breadcrumbs: BreadcrumbItem[] = [
  { title: 'Panel', href: dashboard().url }
];

const tipoAsignacionLabel = (tipo: string) => {
  switch(tipo) {
    case 'todos': return 'Para Todos';
    case 'unidad': return 'Por Unidad';
    case 'generacion': return 'Por Generación';
    case 'carrera_generacion': return 'Específica';
    default: return tipo;
  }
};

const tipoAsignacionColor = (tipo: string) => {
  switch(tipo) {
    case 'todos': return 'bg-purple-100 text-purple-800 dark:bg-purple-900 dark:text-purple-100';
    case 'unidad': return 'bg-blue-100 text-blue-800 dark:bg-blue-900 dark:text-blue-100';
    case 'generacion': return 'bg-green-100 text-green-800 dark:bg-green-900 dark:text-green-100';
    case 'carrera_generacion': return 'bg-orange-100 text-orange-800 dark:bg-orange-900 dark:text-orange-100';
    default: return 'bg-gray-100 text-gray-800 dark:bg-gray-900 dark:text-gray-100';
  }
};


</script>

<template>
  <Head title="Panel" />
  <AppLayout :breadcrumbs="breadcrumbs">
    <div class="flex h-full flex-1 flex-col gap-4 overflow-x-auto rounded-xl p-4">
      <!-- Encuestas Asignadas -->
      <div v-if="props.encuestasAsignadas && props.encuestasAsignadas.length > 0" class="relative rounded-xl border border-sidebar-border/70 p-4 dark:border-sidebar-border mb-4">
        <h2 class="text-2xl font-bold mb-4 flex items-center gap-2">
          <FileText class="h-6 w-6" />
          Encuestas Disponibles
        </h2>
        <div class="grid gap-4 md:grid-cols-2 lg:grid-cols-3">
          <Card v-for="encuesta in props.encuestasAsignadas" :key="encuesta.id" class="hover:shadow-lg transition-shadow">
            <CardHeader>
              <div class="flex justify-between items-start mb-2">
                <CardTitle class="text-lg">{{ encuesta.nombre }}</CardTitle>
                <Badge :class="tipoAsignacionColor(encuesta.tipo_asignacion)" class="text-xs">
                  {{ tipoAsignacionLabel(encuesta.tipo_asignacion) }}
                </Badge>
              </div>
              <CardDescription v-if="encuesta.descripcion">
                {{ encuesta.descripcion }}
              </CardDescription>
            </CardHeader>
            <CardContent>
              <div class="space-y-3">
                <div v-if="encuesta.fecha_inicio || encuesta.fecha_fin" class="flex items-center gap-2 text-sm text-muted-foreground">
                  <Calendar class="h-4 w-4" />
                  <span>
                    {{ encuesta.fecha_inicio || 'Sin fecha' }} - {{ encuesta.fecha_fin || 'Sin fecha' }}
                  </span>
                </div>
                <Link v-if="!encuesta.ya_respondida" :href="`/encuesta/${encuesta.encuesta_id}`">
                  <Button class="w-full">
                    Contestar Encuesta
                  </Button>
                </Link>
                <Link v-else :href="`/encuesta/${encuesta.encuesta_id}/mis-respuestas`">
                  <Button class="w-full" variant="outline">
                    Ver Mis Respuestas
                  </Button>
                </Link>
              </div>
            </CardContent>
          </Card>
        </div>
      </div>

      <!-- Módulos del Dashboard -->
      <div class="relative rounded-xl border border-sidebar-border/70 p-4 dark:border-sidebar-border">
        <DashboardGrid />
      </div>
    </div>
  </AppLayout>
</template>
