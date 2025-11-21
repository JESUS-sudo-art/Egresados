<script setup lang="ts">
import { ref, computed } from 'vue'
import AppLayout from '@/layouts/AppLayout.vue'
import { Head, Link, useForm } from '@inertiajs/vue3'
import { Button } from '@/components/ui/button'
import { Input } from '@/components/ui/input'
import { Label } from '@/components/ui/label'
import { Card, CardContent, CardDescription, CardHeader, CardTitle } from '@/components/ui/card'

interface Props {
  egresado?: any
  estadosCiviles: any[]
  cedulaExistente?: any
  soloLectura?: boolean
  isEgresado?: boolean
}

const props = defineProps<Props>()

// Opciones para promedio
const promediosOpciones = [
  { value: '9.5', label: '9.5 - 10.0' },
  { value: '9.0', label: '9.0 - 9.4' },
  { value: '8.5', label: '8.5 - 8.9' },
  { value: '8.0', label: '8.0 - 8.4' },
  { value: '7.5', label: '7.5 - 7.9' },
  { value: '7.0', label: '7.0 - 7.4' },
]

// Opciones para distritos (hardcoded - ajustar según necesidad)
const distritosOpciones = [
  'Tuxtepec',
  'Oaxaca de Juárez',
  'Juchitán',
  'Salina Cruz',
  'Puerto Escondido',
  'Otro',
]

// Cargar datos existentes de la cédula si existe
const parseObservaciones = (observaciones: string) => {
  const data: any = {}
  if (!observaciones) return data
  
  const partes = observaciones.split(' | ')
  partes.forEach(parte => {
    if (parte.includes('Idioma:')) data.idioma = parte.replace('Idioma: ', '')
    if (parte.includes('Lengua indígena:')) data.lengua = parte.replace('Lengua indígena: ', '')
    if (parte.includes('Grupo étnico:')) data.grupoEtnico = parte.replace('Grupo étnico: ', '')
    if (parte.includes('Beca:')) data.beca = parte.split('Beca: ')[1]?.split(' (')[0]
    if (parte.includes('Vigencia:')) data.vigencia = parte.match(/Vigencia: ([^)]+)/)?.[1]
    if (parte.includes('Semestres:')) {
      const semestres = parte.match(/Semestres: (.+)/)?.[1]
      if (semestres) data.semestres = semestres.split(', ').map(s => parseInt(s))
    }
    if (parte.includes('Año de ingreso:')) data.anioIngreso = parseInt(parte.replace('Año de ingreso: ', ''))
    if (parte.includes('Alumno foráneo:')) data.foraneo = parte.includes('Sí')
    if (parte.includes('Distrito de origen:')) data.distrito = parte.replace('Distrito de origen: ', '')
    if (parte.includes('Edad:')) data.edad = parte.includes('No especificada') ? null : parseInt(parte.replace('Edad: ', ''))
    if (parte.includes('Sexo:')) data.sexo = parte.replace('Sexo: ', '')
  })
  return data
}

const datosExistentes = props.cedulaExistente ? parseObservaciones(props.cedulaExistente.observaciones) : {}

// Formulario
const form = useForm({
  apellido_paterno: props.egresado?.apellidos?.split(' ')[0] || '',
  apellido_materno: props.egresado?.apellidos?.split(' ')[1] || '',
  nombres: props.egresado?.nombre || '',
  sexo: datosExistentes.sexo || 'Hombre',
  curp: props.egresado?.curp || '',
  edad: datosExistentes.edad || null as number | null,
  telefono: props.cedulaExistente?.telefono_contacto || '',
  email: props.egresado?.email || '',
  habla_segundo_idioma: props.egresado?.habla_segundo_idioma || false,
  cual_idioma: datosExistentes.idioma || '',
  habla_lengua_indigena: props.egresado?.habla_lengua_indigena || false,
  cual_lengua_indigena: datosExistentes.lengua || '',
  pertenece_grupo_etnico: props.egresado?.pertenece_grupo_etnico || false,
  cual_grupo_etnico: datosExistentes.grupoEtnico || '',
  estado_civil_id: props.egresado?.estado_civil_id || null as number | null,
  tiene_hijos: props.egresado?.tiene_hijos || false,
  es_alumno_foraneo: datosExistentes.foraneo || false,
  domicilio_origen: props.egresado?.domicilio || '',
  distrito_origen: datosExistentes.distrito || '',
  domicilio_actual: props.egresado?.domicilio_actual || '',
  anio_ingreso: datosExistentes.anioIngreso || null as number | null,
  promedio: props.cedulaExistente?.promedio?.toString() || '' as string,
  beneficiado_beca: !!datosExistentes.beca,
  nombre_beca: datosExistentes.beca || '',
  vigencia_beca: datosExistentes.vigencia || '',
  semestres_beca: datosExistentes.semestres || [] as number[],
  facebook: props.egresado?.facebook_url || '',
})

// Mostrar u ocultar campos condicionales
const mostrarCualIdioma = computed(() => form.habla_segundo_idioma)
const mostrarCualLengua = computed(() => form.habla_lengua_indigena)
const mostrarCualGrupoEtnico = computed(() => form.pertenece_grupo_etnico)
const mostrarDatosBeca = computed(() => form.beneficiado_beca)

// Toggle semestre
const toggleSemestre = (semestre: number) => {
  const index = form.semestres_beca.indexOf(semestre)
  if (index > -1) {
    form.semestres_beca.splice(index, 1)
  } else {
    form.semestres_beca.push(semestre)
  }
}

// Enviar formulario
const submitForm = () => {
  form.post('/encuesta-preegreso/store', {
    onSuccess: () => {
      alert('Cédula de Pre-Egreso enviada correctamente')
    },
    onError: (errors) => {
      console.error('Errores:', errors)
    }
  })
}

const tipo = 'preegreso'
</script>

<template>
  <Head title="Encuesta Preegreso" />
  <AppLayout :breadcrumbs="[{ title: 'Encuesta Preegreso', href: '/encuesta-preegreso' }]">
    <div class="container mx-auto px-4 py-8 max-w-4xl">
      <Card>
        <CardHeader>
          <CardTitle class="text-3xl">Cédula de Pre-Egreso</CardTitle>
          <CardDescription>
            <template v-if="props.soloLectura">
              <div class="bg-blue-100 dark:bg-blue-900 border border-blue-300 dark:border-blue-700 text-blue-800 dark:text-blue-100 px-4 py-3 rounded relative mt-2" role="alert">
                <strong class="font-bold">Vista de solo lectura:</strong>
                <span class="block sm:inline"> Esta encuesta fue contestada cuando eras estudiante. Como egresado, solo puedes visualizar tus respuestas previas.</span>
              </div>
            </template>
            <template v-else>
              Por favor, completa todos los datos solicitados. Esta información actualizará tu perfil automáticamente.
            </template>
          </CardDescription>
        </CardHeader>
        <CardContent>
          <form @submit.prevent="submitForm" class="space-y-6">
            <!-- Sección: Datos Personales -->
            <div class="border-b pb-4">
              <h3 class="text-xl font-semibold mb-4">Datos Personales</h3>
              <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                <div>
                  <Label for="apellido_paterno">Apellido Paterno *</Label>
                  <Input id="apellido_paterno" v-model="form.apellido_paterno" :disabled="props.soloLectura" required />
                  <span v-if="form.errors.apellido_paterno" class="text-sm text-red-600">{{ form.errors.apellido_paterno }}</span>
                </div>
                <div>
                  <Label for="apellido_materno">Apellido Materno *</Label>
                  <Input id="apellido_materno" v-model="form.apellido_materno" :disabled="props.soloLectura" required />
                  <span v-if="form.errors.apellido_materno" class="text-sm text-red-600">{{ form.errors.apellido_materno }}</span>
                </div>
                <div>
                  <Label for="nombres">Nombre(s) *</Label>
                  <Input id="nombres" v-model="form.nombres" :disabled="props.soloLectura" required />
                  <span v-if="form.errors.nombres" class="text-sm text-red-600">{{ form.errors.nombres }}</span>
                </div>
              </div>

              <div class="grid grid-cols-1 md:grid-cols-3 gap-4 mt-4">
                <div>
                  <Label for="sexo">Sexo *</Label>
                  <select id="sexo" v-model="form.sexo" :disabled="props.soloLectura" required class="flex h-9 w-full rounded-md border border-input bg-transparent px-3 py-1 text-sm shadow-sm transition-colors placeholder:text-muted-foreground focus-visible:outline-none focus-visible:ring-1 focus-visible:ring-ring disabled:opacity-50 disabled:cursor-not-allowed">
                    <option value="Hombre">Hombre</option>
                    <option value="Mujer">Mujer</option>
                    <option value="Otro">Otro</option>
                  </select>
                </div>
                <div>
                  <Label for="curp">CURP</Label>
                  <Input id="curp" v-model="form.curp" :disabled="props.soloLectura" maxlength="18" />
                  <span v-if="form.errors.curp" class="text-sm text-red-600">{{ form.errors.curp }}</span>
                </div>
                <div>
                  <Label for="edad">Edad</Label>
                  <Input id="edad" v-model="form.edad" :disabled="props.soloLectura" type="number" min="15" max="100" />
                  <span v-if="form.errors.edad" class="text-sm text-red-600">{{ form.errors.edad }}</span>
                </div>
              </div>

              <div class="grid grid-cols-1 md:grid-cols-2 gap-4 mt-4">
                <div>
                  <Label for="telefono">Teléfono</Label>
                  <Input id="telefono" v-model="form.telefono" :disabled="props.soloLectura" type="tel" />
                  <span v-if="form.errors.telefono" class="text-sm text-red-600">{{ form.errors.telefono }}</span>
                </div>
                <div>
                  <Label for="email">Correo Electrónico *</Label>
                  <Input id="email" v-model="form.email" :disabled="props.soloLectura" type="email" required />
                  <span v-if="form.errors.email" class="text-sm text-red-600">{{ form.errors.email }}</span>
                </div>
              </div>
            </div>

            <!-- Sección: Idiomas y Lenguas -->
            <div class="border-b pb-4">
              <h3 class="text-xl font-semibold mb-4">Idiomas y Lenguas</h3>
              
              <div class="space-y-3">
                <div>
                  <Label>¿Hablas un segundo idioma? *</Label>
                  <div class="flex gap-4 mt-1">
                    <label class="flex items-center gap-2">
                      <input type="radio" :value="true" v-model="form.habla_segundo_idioma" :disabled="props.soloLectura" required class="w-4 h-4 disabled:opacity-50 disabled:cursor-not-allowed" />
                      Sí
                    </label>
                    <label class="flex items-center gap-2">
                      <input type="radio" :value="false" v-model="form.habla_segundo_idioma" :disabled="props.soloLectura" required class="w-4 h-4 disabled:opacity-50 disabled:cursor-not-allowed" />
                      No
                    </label>
                  </div>
                </div>
                <div v-if="mostrarCualIdioma">
                  <Label for="cual_idioma">¿Cuál idioma?</Label>
                  <Input id="cual_idioma" v-model="form.cual_idioma" :disabled="props.soloLectura" placeholder="Ej: Inglés, Francés..." />
                </div>

                <div>
                  <Label>¿Hablas alguna lengua indígena? *</Label>
                  <div class="flex gap-4 mt-1">
                    <label class="flex items-center gap-2">
                      <input type="radio" :value="true" v-model="form.habla_lengua_indigena" :disabled="props.soloLectura" required class="w-4 h-4 disabled:opacity-50 disabled:cursor-not-allowed" />
                      Sí
                    </label>
                    <label class="flex items-center gap-2">
                      <input type="radio" :value="false" v-model="form.habla_lengua_indigena" :disabled="props.soloLectura" required class="w-4 h-4 disabled:opacity-50 disabled:cursor-not-allowed" />
                      No
                    </label>
                  </div>
                </div>
                <div v-if="mostrarCualLengua">
                  <Label for="cual_lengua_indigena">¿Cuál lengua?</Label>
                  <Input id="cual_lengua_indigena" v-model="form.cual_lengua_indigena" :disabled="props.soloLectura" placeholder="Ej: Zapoteco, Mixteco..." />
                </div>

                <div>
                  <Label>¿Perteneces a algún grupo étnico? *</Label>
                  <div class="flex gap-4 mt-1">
                    <label class="flex items-center gap-2">
                      <input type="radio" :value="true" v-model="form.pertenece_grupo_etnico" :disabled="props.soloLectura" required class="w-4 h-4 disabled:opacity-50 disabled:cursor-not-allowed" />
                      Sí
                    </label>
                    <label class="flex items-center gap-2">
                      <input type="radio" :value="false" v-model="form.pertenece_grupo_etnico" :disabled="props.soloLectura" required class="w-4 h-4 disabled:opacity-50 disabled:cursor-not-allowed" />
                      No
                    </label>
                  </div>
                </div>
                <div v-if="mostrarCualGrupoEtnico">
                  <Label for="cual_grupo_etnico">¿Cuál grupo étnico?</Label>
                  <Input id="cual_grupo_etnico" v-model="form.cual_grupo_etnico" :disabled="props.soloLectura" placeholder="Especifica el grupo étnico" />
                </div>
              </div>
            </div>

            <!-- Sección: Datos Familiares y Domicilio -->
            <div class="border-b pb-4">
              <h3 class="text-xl font-semibold mb-4">Datos Familiares y Domicilio</h3>
              
              <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                <div>
                  <Label for="estado_civil_id">Estado Civil *</Label>
                  <select id="estado_civil_id" v-model="form.estado_civil_id" :disabled="props.soloLectura" required class="flex h-9 w-full rounded-md border border-input bg-transparent px-3 py-1 text-sm shadow-sm transition-colors placeholder:text-muted-foreground focus-visible:outline-none focus-visible:ring-1 focus-visible:ring-ring disabled:opacity-50 disabled:cursor-not-allowed">
                    <option value="">Selecciona...</option>
                    <option v-for="ec in estadosCiviles" :key="ec.id" :value="ec.id">{{ ec.nombre }}</option>
                  </select>
                  <span v-if="form.errors.estado_civil_id" class="text-sm text-red-600">{{ form.errors.estado_civil_id }}</span>
                </div>
                
                <div>
                  <Label>¿Tienes hijos? *</Label>
                  <div class="flex gap-4 mt-1">
                    <label class="flex items-center gap-2">
                      <input type="radio" :value="true" v-model="form.tiene_hijos" :disabled="props.soloLectura" required class="w-4 h-4 disabled:opacity-50 disabled:cursor-not-allowed" />
                      Sí
                    </label>
                    <label class="flex items-center gap-2">
                      <input type="radio" :value="false" v-model="form.tiene_hijos" :disabled="props.soloLectura" required class="w-4 h-4 disabled:opacity-50 disabled:cursor-not-allowed" />
                      No
                    </label>
                  </div>
                </div>
              </div>

              <div class="mt-4">
                <Label>¿Eres alumno foráneo? *</Label>
                <div class="flex gap-4 mt-1">
                  <label class="flex items-center gap-2">
                    <input type="radio" :value="true" v-model="form.es_alumno_foraneo" :disabled="props.soloLectura" required class="w-4 h-4 disabled:opacity-50 disabled:cursor-not-allowed" />
                    Sí
                  </label>
                  <label class="flex items-center gap-2">
                    <input type="radio" :value="false" v-model="form.es_alumno_foraneo" :disabled="props.soloLectura" required class="w-4 h-4 disabled:opacity-50 disabled:cursor-not-allowed" />
                    No
                  </label>
                </div>
              </div>

              <div class="mt-4">
                <Label for="domicilio_origen">Domicilio de Origen *</Label>
                <textarea id="domicilio_origen" v-model="form.domicilio_origen" :disabled="props.soloLectura" required rows="2" class="flex w-full rounded-md border border-input bg-transparent px-3 py-2 text-sm shadow-sm transition-colors placeholder:text-muted-foreground focus-visible:outline-none focus-visible:ring-1 focus-visible:ring-ring disabled:opacity-50 disabled:cursor-not-allowed"></textarea>
                <span v-if="form.errors.domicilio_origen" class="text-sm text-red-600">{{ form.errors.domicilio_origen }}</span>
              </div>

              <div class="grid grid-cols-1 md:grid-cols-2 gap-4 mt-4">
                <div>
                  <Label for="distrito_origen">Distrito de Origen</Label>
                  <select id="distrito_origen" v-model="form.distrito_origen" :disabled="props.soloLectura" class="flex h-9 w-full rounded-md border border-input bg-transparent px-3 py-1 text-sm shadow-sm transition-colors placeholder:text-muted-foreground focus-visible:outline-none focus-visible:ring-1 focus-visible:ring-ring disabled:opacity-50 disabled:cursor-not-allowed">
                    <option value="">Selecciona...</option>
                    <option v-for="distrito in distritosOpciones" :key="distrito" :value="distrito">{{ distrito }}</option>
                  </select>
                </div>
              </div>

              <div class="mt-4">
                <Label for="domicilio_actual">Domicilio Actual *</Label>
                <textarea id="domicilio_actual" v-model="form.domicilio_actual" :disabled="props.soloLectura" required rows="2" class="flex w-full rounded-md border border-input bg-transparent px-3 py-2 text-sm shadow-sm transition-colors placeholder:text-muted-foreground focus-visible:outline-none focus-visible:ring-1 focus-visible:ring-ring disabled:opacity-50 disabled:cursor-not-allowed"></textarea>
                <span v-if="form.errors.domicilio_actual" class="text-sm text-red-600">{{ form.errors.domicilio_actual }}</span>
              </div>
            </div>

            <!-- Sección: Datos Académicos -->
            <div class="border-b pb-4">
              <h3 class="text-xl font-semibold mb-4">Datos Académicos</h3>
              
              <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                <div>
                  <Label for="anio_ingreso">Año de Ingreso *</Label>
                  <Input id="anio_ingreso" v-model="form.anio_ingreso" :disabled="props.soloLectura" type="number" min="1990" max="2030" required />
                  <span v-if="form.errors.anio_ingreso" class="text-sm text-red-600">{{ form.errors.anio_ingreso }}</span>
                </div>
                
                <div>
                  <Label for="promedio">Promedio *</Label>
                  <select id="promedio" v-model="form.promedio" :disabled="props.soloLectura" required class="flex h-9 w-full rounded-md border border-input bg-transparent px-3 py-1 text-sm shadow-sm transition-colors placeholder:text-muted-foreground focus-visible:outline-none focus-visible:ring-1 focus-visible:ring-ring disabled:opacity-50 disabled:cursor-not-allowed">
                    <option value="">Selecciona...</option>
                    <option v-for="prom in promediosOpciones" :key="prom.value" :value="prom.value">{{ prom.label }}</option>
                  </select>
                  <span v-if="form.errors.promedio" class="text-sm text-red-600">{{ form.errors.promedio }}</span>
                </div>
              </div>

              <div class="mt-4">
                <Label>¿Te beneficiaste de alguna beca? *</Label>
                <div class="flex gap-4 mt-1">
                  <label class="flex items-center gap-2">
                    <input type="radio" :value="true" v-model="form.beneficiado_beca" :disabled="props.soloLectura" required class="w-4 h-4 disabled:opacity-50 disabled:cursor-not-allowed" />
                    Sí
                  </label>
                  <label class="flex items-center gap-2">
                    <input type="radio" :value="false" v-model="form.beneficiado_beca" :disabled="props.soloLectura" required class="w-4 h-4 disabled:opacity-50 disabled:cursor-not-allowed" />
                    No
                  </label>
                </div>
              </div>

              <div v-if="mostrarDatosBeca" class="mt-4 space-y-4 p-4 bg-gray-50 dark:bg-gray-800 rounded-lg">
                <div>
                  <Label for="nombre_beca">Nombre de la Beca</Label>
                  <Input id="nombre_beca" v-model="form.nombre_beca" :disabled="props.soloLectura" placeholder="Ej: Benito Juárez, CONACyT..." />
                </div>
                <div>
                  <Label for="vigencia_beca">Vigencia de la Beca</Label>
                  <Input id="vigencia_beca" v-model="form.vigencia_beca" :disabled="props.soloLectura" placeholder="Ej: 2019-2023, 4 años..." />
                </div>
                <div>
                  <Label>Semestres en los que tuviste beca</Label>
                  <div class="grid grid-cols-5 gap-2 mt-2">
                    <label v-for="sem in 10" :key="sem" class="flex items-center gap-2">
                      <input 
                        type="checkbox" 
                        :checked="form.semestres_beca.includes(sem)" 
                        @change="toggleSemestre(sem)"
                        :disabled="props.soloLectura"
                        class="w-4 h-4 disabled:opacity-50 disabled:cursor-not-allowed" 
                      />
                      {{ sem }}°
                    </label>
                  </div>
                </div>
              </div>
            </div>

            <!-- Sección: Redes Sociales -->
            <div class="border-b pb-4">
              <h3 class="text-xl font-semibold mb-4">Redes Sociales</h3>
              <div>
                <Label for="facebook">Facebook (perfil o URL)</Label>
                <Input id="facebook" v-model="form.facebook" :disabled="props.soloLectura" placeholder="https://facebook.com/tu-perfil" />
                <span v-if="form.errors.facebook" class="text-sm text-red-600">{{ form.errors.facebook }}</span>
              </div>
            </div>

            <!-- Botones -->
            <div class="flex justify-between items-center pt-4">
              <Link 
                :href="`/acuses-seguimiento?from=${tipo}`"
                class="text-blue-600 hover:text-blue-700 underline"
              >
                Ver Acuses de Seguimiento
              </Link>
              
              <Button 
                v-if="!props.soloLectura"
                type="submit" 
                :disabled="form.processing"
                class="bg-green-600 hover:bg-green-700 text-white px-8"
              >
                {{ form.processing ? 'Enviando...' : 'Enviar Cédula de Pre-Egreso' }}
              </Button>
            </div>
          </form>
        </CardContent>
      </Card>
    </div>
  </AppLayout>
</template>
