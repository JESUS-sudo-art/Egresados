<script setup lang="ts">
import { ref, watch, computed, onMounted } from 'vue'
import AppLayout from '@/layouts/AppLayout.vue'
import { Head, router } from '@inertiajs/vue3'
import { Card, CardContent, CardHeader, CardTitle } from '@/components/ui/card'
import { Button } from '@/components/ui/button'
import { Input } from '@/components/ui/input'
import { Label } from '@/components/ui/label'

// Charts: usamos Chart.js por CDN para evitar instalación local
type AnyChart = any
const chartLibLoaded = ref(false)
function loadChartJs() {
  return new Promise<void>((resolve, reject) => {
    if ((window as any).Chart) { chartLibLoaded.value = true; return resolve() }
    const s = document.createElement('script')
    s.src = 'https://cdn.jsdelivr.net/npm/chart.js@4.4.4/dist/chart.umd.min.js'
    s.async = true
    s.onload = () => { chartLibLoaded.value = true; resolve() }
    s.onerror = () => reject(new Error('No se pudo cargar Chart.js'))
    document.head.appendChild(s)
  })
}

interface Carrera { id:number; nombre:string }
interface Generacion { id:number; nombre:string }
interface Stats {
  totalEncuestados: number
  tasaEmpleabilidad: number
  salarioPromedio: number
  sectorData: Record<string, number>
  tiempoData: Record<string, number>
  relacionData: Record<string, number>
  satisfaccionData: Record<string|number, number>
}

interface Props {
  carreras: Carrera[]
  generaciones: Generacion[]
  initial: Stats
  publico?: boolean
}
const props = defineProps<Props>()

// Filtros
const carreraId = ref<number|null>(null)
const generacionId = ref<number|null>(null)
const desde = ref<string>('')
const hasta = ref<string>('')

const stats = ref<Stats>(props.initial)
const cargando = ref(false)

async function cargar() {
  cargando.value = true
  const params = new URLSearchParams()
  if (carreraId.value) params.set('carrera_id', String(carreraId.value))
  if (generacionId.value) params.set('generacion_id', String(generacionId.value))
  if (desde.value) params.set('desde', desde.value)
  if (hasta.value) params.set('hasta', hasta.value)
  const url = `/reportes/datos?${params.toString()}`
  const res = await fetch(url, { headers:{ 'X-Requested-With':'XMLHttpRequest' } })
  const data = await res.json()
  stats.value = data
  cargando.value = false
  // actualizar gráficas con nuevos datos
  if (chartLibLoaded.value) renderCharts()
}

function exportar() {
  const params = new URLSearchParams()
  if (carreraId.value) params.set('carrera_id', String(carreraId.value))
  if (generacionId.value) params.set('generacion_id', String(generacionId.value))
  if (desde.value) params.set('desde', desde.value)
  if (hasta.value) params.set('hasta', hasta.value)
  window.location.href = `/reportes/exportar?${params.toString()}`
}

// Data for charts
const sectorLabels = ['Público', 'Privado', 'Social', 'Otro']
const sectorCanvas = ref<HTMLCanvasElement|null>(null)
let sectorChart: AnyChart | null = null

const tiempoLabels = ['Menos de 6 meses','6-12 meses','1-2 años','Más de 2 años','Trabajaba antes de egresar']
const tiempoCanvas = ref<HTMLCanvasElement|null>(null)
let tiempoChart: AnyChart | null = null

const relacionLabels = ['Si','No']
const relacionCanvas = ref<HTMLCanvasElement|null>(null)
let relacionChart: AnyChart | null = null

const satisfLabels = ['1','2','3','4','5']
const satisfCanvas = ref<HTMLCanvasElement|null>(null)
let satisfChart: AnyChart | null = null

function renderCharts(){
  const Chart = (window as any).Chart
  // Sector (Pie)
  if (sectorCanvas.value) {
    const data = sectorLabels.map(l => stats.value.sectorData[l] ?? 0)
    const cfg = {
      type: 'pie',
      data: { labels: sectorLabels, datasets: [{ data, backgroundColor: ['#60a5fa','#34d399','#fbbf24','#a78bfa'] }]},
      options: { responsive:true, maintainAspectRatio:false }
    }
    if (sectorChart) { sectorChart.data = cfg.data; sectorChart.update() } else { sectorChart = new Chart(sectorCanvas.value, cfg) }
  }
  // Tiempo (Bar)
  if (tiempoCanvas.value) {
    const data = tiempoLabels.map(l => stats.value.tiempoData[l] ?? 0)
    const cfg = {
      type: 'bar',
      data: { labels: tiempoLabels, datasets: [{ label:'Personas', data, backgroundColor:'#60a5fa' }]},
      options: { responsive:true, maintainAspectRatio:false }
    }
    if (tiempoChart) { tiempoChart.data = cfg.data; tiempoChart.update() } else { tiempoChart = new Chart(tiempoCanvas.value, cfg) }
  }
  // Relación (Bar)
  if (relacionCanvas.value) {
    const data = relacionLabels.map(l => stats.value.relacionData[l] ?? 0)
    const cfg = {
      type: 'bar',
      data: { labels: relacionLabels, datasets: [{ label:'Personas', data, backgroundColor:'#34d399' }]},
      options: { responsive:true, maintainAspectRatio:false }
    }
    if (relacionChart) { relacionChart.data = cfg.data; relacionChart.update() } else { relacionChart = new Chart(relacionCanvas.value, cfg) }
  }
  // Satisfacción (Bar)
  if (satisfCanvas.value) {
    const data = satisfLabels.map(l => stats.value.satisfaccionData[l] ?? 0)
    const cfg = {
      type: 'bar',
      data: { labels: satisfLabels, datasets: [{ label:'Respuestas', data, backgroundColor:'#a78bfa' }]},
      options: { responsive:true, maintainAspectRatio:false }
    }
    if (satisfChart) { satisfChart.data = cfg.data; satisfChart.update() } else { satisfChart = new Chart(satisfCanvas.value, cfg) }
  }
}

watch(stats, () => { if (chartLibLoaded.value) renderCharts() })

onMounted(async () => {
  try { await loadChartJs(); renderCharts() } catch (e) { console.error(e) }
})
</script>

<template>
  <Head title="Reportes e Informes" />
  <AppLayout :breadcrumbs="[{ title: 'Reportes e Informes', href: props.publico ? '/seguimiento-egresados' : '/reportes-informes' }]"><div class="container mx-auto px-4 py-8 max-w-7xl">
  <h1 class="text-3xl font-bold mb-6">Reportes e Informes</h1>

      <!-- Filtros globales -->
      <Card class="mb-6">
        <CardContent class="pt-6">
          <div class="grid grid-cols-1 md:grid-cols-4 gap-4">
            <div>
              <Label>Carrera</Label>
              <select v-model.number="carreraId" class="flex h-9 w-full rounded-md border border-input bg-transparent px-3 py-1 text-sm">
                <option :value="null">Todas</option>
                <option v-for="c in props.carreras" :key="c.id" :value="c.id">{{ c.nombre }}</option>
              </select>
            </div>
            <div>
              <Label>Generación</Label>
              <select v-model.number="generacionId" class="flex h-9 w-full rounded-md border border-input bg-transparent px-3 py-1 text-sm">
                <option :value="null">Todas</option>
                <option v-for="g in props.generaciones" :key="g.id" :value="g.id">{{ g.nombre }}</option>
              </select>
            </div>
            <div>
              <Label>Desde</Label>
              <Input type="date" v-model="desde" />
            </div>
            <div>
              <Label>Hasta</Label>
              <Input type="date" v-model="hasta" />
            </div>
          </div>
          <div class="mt-4 flex gap-3">
            <Button @click="cargar" :disabled="cargando">{{ cargando ? 'Cargando…' : 'Aplicar Filtros' }}</Button>
            <Button v-if="!props.publico" variant="outline" @click="exportar">Exportar datos a Excel</Button>
          </div>
        </CardContent>
      </Card>

      <!-- KPIs -->
      <div class="grid grid-cols-1 md:grid-cols-3 gap-4 mb-6">
        <Card>
          <CardHeader><CardTitle>Total de Encuestados</CardTitle></CardHeader>
          <CardContent class="text-3xl font-bold">{{ stats.totalEncuestados }}</CardContent>
        </Card>
        <Card>
          <CardHeader><CardTitle>Tasa de Empleabilidad</CardTitle></CardHeader>
          <CardContent class="text-3xl font-bold">{{ stats.tasaEmpleabilidad }}%</CardContent>
        </Card>
        <Card>
          <CardHeader><CardTitle>Salario Promedio</CardTitle></CardHeader>
          <CardContent class="text-3xl font-bold">${{ stats.salarioPromedio.toLocaleString() }}</CardContent>
        </Card>
      </div>

      <!-- Gráficas -->
      <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
        <Card>
          <CardHeader><CardTitle>Sector Laboral</CardTitle></CardHeader>
          <CardContent>
            <div style="min-height:280px">
              <canvas ref="sectorCanvas"></canvas>
            </div>
          </CardContent>
        </Card>

        <Card>
          <CardHeader><CardTitle>Tiempo para encontrar empleo</CardTitle></CardHeader>
          <CardContent>
            <div style="min-height:280px">
              <canvas ref="tiempoCanvas"></canvas>
            </div>
          </CardContent>
        </Card>

        <Card>
          <CardHeader><CardTitle>Relación Empleo-Carrera</CardTitle></CardHeader>
          <CardContent>
            <div style="min-height:280px">
              <canvas ref="relacionCanvas"></canvas>
            </div>
          </CardContent>
        </Card>

        <Card>
          <CardHeader><CardTitle>Satisfacción con la Formación</CardTitle></CardHeader>
          <CardContent>
            <div style="min-height:280px">
              <canvas ref="satisfCanvas"></canvas>
            </div>
          </CardContent>
        </Card>
      </div>
    </div></AppLayout>
</template>
