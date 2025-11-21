<script setup lang="ts">
import { ref, computed } from 'vue'
import AppLayout from '@/layouts/AppLayout.vue'
import { Head, useForm } from '@inertiajs/vue3'
import { Card, CardContent, CardDescription, CardHeader, CardTitle } from '@/components/ui/card'
import { Button } from '@/components/ui/button'
import { Input } from '@/components/ui/input'
import { Label } from '@/components/ui/label'

interface Unidad {
  id: number
  nombre: string
}

interface Usuario {
  id: number
  name: string
  email: string
  unidades: Unidad[]
}

interface Props {
  usuarios: Usuario[]
  unidades: Unidad[]
}

const props = defineProps<Props>()

const mostrarFormulario = ref(false)
const editandoUsuario = ref<Usuario | null>(null)

const form = useForm({
  name: '',
  email: '',
  password: '',
  unidades: [] as number[],
})

const tituloFormulario = computed(() => 
  editandoUsuario.value ? 'Editar Administrador' : 'Crear Administrador'
)

const abrirFormularioCrear = () => {
  editandoUsuario.value = null
  form.reset()
  form.clearErrors()
  mostrarFormulario.value = true
}

const abrirFormularioEditar = (usuario: Usuario) => {
  editandoUsuario.value = usuario
  form.name = usuario.name
  form.email = usuario.email
  form.password = ''
  form.unidades = usuario.unidades.map(u => u.id)
  mostrarFormulario.value = true
}

const cancelar = () => {
  mostrarFormulario.value = false
  form.reset()
  form.clearErrors()
  editandoUsuario.value = null
}

const guardarUsuario = () => {
  if (editandoUsuario.value) {
    form.put(`/admin-general/${editandoUsuario.value.id}`, {
      onSuccess: () => {
        cancelar()
      }
    })
  } else {
    form.post('/admin-general', {
      onSuccess: () => {
        cancelar()
      }
    })
  }
}

const eliminarUsuario = (id: number) => {
  if (confirm('¿Estás seguro de eliminar este usuario?')) {
    form.delete(`/admin-general/${id}`)
  }
}

const toggleUnidad = (unidadId: number) => {
  const index = form.unidades.indexOf(unidadId)
  if (index > -1) {
    form.unidades.splice(index, 1)
  } else {
    form.unidades.push(unidadId)
  }
}
</script>

<template>
  <Head title="Admin General" />
  <AppLayout :breadcrumbs="[{ title: 'Admin General', href: '/admin-general' }]">
    <div class="container mx-auto px-4 py-8 max-w-6xl space-y-6">
      
      <!-- Título -->
      <div class="flex justify-between items-center">
        <h1 class="text-3xl font-bold">Gestor de Usuarios (Administradores)</h1>
        <Button 
          @click="abrirFormularioCrear"
          v-if="!mostrarFormulario"
          class="bg-blue-600 hover:bg-blue-700"
        >
          Crear Nuevo Usuario
        </Button>
      </div>

      <!-- Formulario Crear/Editar -->
      <Card v-if="mostrarFormulario">
        <CardHeader>
          <CardTitle>{{ tituloFormulario }}</CardTitle>
          <CardDescription>
            {{ editandoUsuario ? 'Actualiza la información del administrador' : 'Completa los datos del nuevo administrador' }}
          </CardDescription>
        </CardHeader>
        <CardContent>
          <form @submit.prevent="guardarUsuario" class="space-y-4">
            
            <div>
              <Label for="name">Nombre *</Label>
              <Input 
                id="name" 
                v-model="form.name" 
                required 
                placeholder="Nombre completo"
              />
              <span v-if="form.errors.name" class="text-sm text-red-600">{{ form.errors.name }}</span>
            </div>

            <div>
              <Label for="email">Email *</Label>
              <Input 
                id="email" 
                v-model="form.email" 
                type="email" 
                required 
                placeholder="correo@ejemplo.com"
              />
              <span v-if="form.errors.email" class="text-sm text-red-600">{{ form.errors.email }}</span>
            </div>

            <div>
              <Label for="password">{{ editandoUsuario ? 'Contraseña (dejar vacío para mantener)' : 'Contraseña *' }}</Label>
              <Input 
                id="password" 
                v-model="form.password" 
                type="password" 
                :required="!editandoUsuario"
                placeholder="Mínimo 8 caracteres"
              />
              <span v-if="form.errors.password" class="text-sm text-red-600">{{ form.errors.password }}</span>
            </div>

            <div>
              <Label>Asignar a Unidades</Label>
              <div class="mt-2 space-y-2 max-h-48 overflow-y-auto border rounded-md p-3">
                <label 
                  v-for="unidad in unidades" 
                  :key="unidad.id"
                  class="flex items-center gap-2 cursor-pointer hover:bg-muted/50 p-2 rounded"
                >
                  <input 
                    type="checkbox" 
                    :value="unidad.id"
                    :checked="form.unidades.includes(unidad.id)"
                    @change="toggleUnidad(unidad.id)"
                    class="w-4 h-4"
                  />
                  <span>{{ unidad.nombre }}</span>
                </label>
              </div>
              <span v-if="form.errors.unidades" class="text-sm text-red-600">{{ form.errors.unidades }}</span>
            </div>

            <div class="flex gap-3 pt-4">
              <Button 
                type="submit" 
                :disabled="form.processing"
                class="bg-green-600 hover:bg-green-700"
              >
                {{ form.processing ? 'Guardando...' : 'Guardar Usuario' }}
              </Button>
              <Button 
                type="button" 
                @click="cancelar"
                variant="outline"
              >
                Cancelar
              </Button>
            </div>

          </form>
        </CardContent>
      </Card>

      <!-- Tabla de Usuarios -->
      <Card>
        <CardHeader>
          <CardTitle>Usuarios Administradores</CardTitle>
          <CardDescription>
            Lista de todos los usuarios administradores registrados en el sistema
          </CardDescription>
        </CardHeader>
        <CardContent>
          <div v-if="usuarios && usuarios.length > 0" class="overflow-x-auto">
            <table class="w-full border-collapse">
              <thead>
                <tr class="border-b">
                  <th class="text-left py-3 px-4 font-semibold text-sm">Nombre</th>
                  <th class="text-left py-3 px-4 font-semibold text-sm">Email</th>
                  <th class="text-left py-3 px-4 font-semibold text-sm">Unidades Asignadas</th>
                  <th class="text-right py-3 px-4 font-semibold text-sm">Acciones</th>
                </tr>
              </thead>
              <tbody>
                <tr 
                  v-for="usuario in usuarios" 
                  :key="usuario.id"
                  class="border-b hover:bg-muted/50 transition-colors"
                >
                  <td class="py-3 px-4 font-medium">{{ usuario.name }}</td>
                  <td class="py-3 px-4">{{ usuario.email }}</td>
                  <td class="py-3 px-4">
                    <div v-if="usuario.unidades.length > 0" class="flex flex-wrap gap-1">
                      <span 
                        v-for="unidad in usuario.unidades" 
                        :key="unidad.id"
                        class="inline-block bg-blue-100 text-blue-800 text-xs px-2 py-1 rounded"
                      >
                        {{ unidad.nombre }}
                      </span>
                    </div>
                    <span v-else class="text-muted-foreground text-sm">Sin unidades asignadas</span>
                  </td>
                  <td class="py-3 px-4 text-right">
                    <div class="flex gap-2 justify-end">
                      <Button 
                        @click="abrirFormularioEditar(usuario)"
                        variant="outline"
                        size="sm"
                      >
                        Editar
                      </Button>
                      <Button 
                        @click="eliminarUsuario(usuario.id)"
                        variant="destructive"
                        size="sm"
                      >
                        Eliminar
                      </Button>
                    </div>
                  </td>
                </tr>
              </tbody>
            </table>
          </div>
          
          <div v-else class="text-center py-12 text-muted-foreground">
            <p class="text-lg">No hay usuarios administradores registrados.</p>
            <p class="text-sm mt-2">Crea el primer usuario usando el botón "Crear Nuevo Usuario".</p>
          </div>
        </CardContent>
      </Card>

    </div>
  </AppLayout>
</template>
