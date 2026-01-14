<script setup lang="ts">
import { ref, computed } from 'vue'
import AppLayout from '@/layouts/AppLayout.vue'
import { Head } from '@inertiajs/vue3'
import { Button } from '@/components/ui/button'
import { Card, CardHeader, CardTitle, CardContent, CardDescription } from '@/components/ui/card'
import { Input } from '@/components/ui/input'
import { Badge } from '@/components/ui/badge'
import { Copy, Terminal } from 'lucide-vue-next'

interface ScriptItem {
  name: string
  description: string
  exists: boolean
  size: number
  content: string | null
  examples: string[]
}

const props = defineProps<{ scripts: ScriptItem[] }>()
const filtro = ref('')

const filtrados = computed(() => {
  if (!filtro.value) return props.scripts
  return props.scripts.filter(s => s.name.toLowerCase().includes(filtro.value.toLowerCase()))
})

const copiar = (txt: string) => {
  navigator.clipboard.writeText(txt)
}
</script>

<template>
  <Head title="Scripts Utilitarios" />
  <AppLayout :breadcrumbs="[{ title: 'Panel', href: '/dashboard' }, { title: 'Scripts', href: '/scripts' }]">
    <div class="max-w-6xl mx-auto px-6 py-8 space-y-6">
      <div class="flex items-center justify-between flex-wrap gap-4">
        <div>
          <h1 class="text-3xl font-bold">Scripts Utilitarios</h1>
          <p class="text-muted-foreground mt-1">Herramientas administrativas rápidas (solo lectura desde la interfaz).</p>
        </div>
        <div class="flex gap-2 items-center">
          <Input v-model="filtro" placeholder="Filtrar por nombre" class="w-64" />
        </div>
      </div>

      <div class="grid md:grid-cols-2 gap-6">
        <Card v-for="script in filtrados" :key="script.name" class="flex flex-col">
          <CardHeader>
            <div class="flex items-start justify-between gap-4">
              <div>
                <CardTitle class="text-lg flex items-center gap-2">
                  <Terminal class="h-5 w-5 text-primary" />
                  {{ script.name }}
                </CardTitle>
                <CardDescription class="mt-1">{{ script.description }}</CardDescription>
              </div>
              <Badge :variant="script.exists ? 'default' : 'destructive'">
                {{ script.exists ? 'OK' : 'Falta' }}
              </Badge>
            </div>
          </CardHeader>
          <CardContent class="space-y-4">
            <div>
              <p class="text-xs text-muted-foreground mb-1 font-medium">Ejemplos:</p>
              <div class="space-y-2">
                <div v-for="ex in script.examples" :key="ex" class="flex items-center gap-2">
                  <code class="text-xs bg-muted px-2 py-1 rounded-md flex-1 overflow-x-auto">{{ ex }}</code>
                  <Button variant="outline" size="sm" @click="copiar(ex)">
                    <Copy class="h-4 w-4" />
                  </Button>
                </div>
              </div>
            </div>
            <details class="border rounded-md bg-muted/40">
              <summary class="cursor-pointer px-3 py-2 text-sm font-medium">Ver contenido ({{ script.size }} bytes)</summary>
              <pre class="p-3 text-xs overflow-x-auto max-h-80 leading-relaxed">{{ script.content }}</pre>
            </details>
          </CardContent>
        </Card>
      </div>
    </div>
  </AppLayout>
</template>
