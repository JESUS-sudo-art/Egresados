<script setup lang="ts">
import { computed } from 'vue'
import AppLayout from '@/layouts/AppLayout.vue'
import { Head, Link, useForm } from '@inertiajs/vue3'
import { Button } from '@/components/ui/button'
import { Input } from '@/components/ui/input'
import { Label } from '@/components/ui/label'
import { Card, CardContent, CardDescription, CardHeader, CardTitle } from '@/components/ui/card'

interface Props {
  egresado?: any
  estadosCiviles: any[]
  encuestaExistente?: any
}

const props = defineProps<Props>()

// Formulario
const form = useForm({
  // Sección I: Datos Generales
  nombre_completo: ((props.egresado?.nombre ?? '') + ' ' + (props.egresado?.apellidos ?? '')).trim(),
  genero: 'Hombre',
  edad: null as number | null,
  curp: props.egresado?.curp || '',
  telefono: '',
  email: props.egresado?.email || '',
  estado_civil_id: props.egresado?.estado_civil_id || null as number | null,
  residencia_actual: props.egresado?.domicilio_actual || '',
  pertenece_grupo_etnico: false,
  cual_grupo_etnico: '',
  habla_lengua_originaria: false,
  cual_lengua_originaria: '',
  comunidad_diversa: '',
  tiene_hijos: false,
  num_hijos: null as number | null,
  dependientes_economicos: null as number | null,
  
  // Sección II: Trayectoria Académica
  programa_academico: props.egresado?.carreras?.[0]?.carrera?.nombre || '',
  fecha_ingreso: '',
  fecha_egreso: '',
  realizo_practicas: false,
  descripcion_practicas: '',
  tiene_titulo: false,
  fecha_titulacion: '',
  estudios_posgrado: false,
  nivel_posgrado: '',
  institucion_posgrado: '',
  area_posgrado: '',
  status_posgrado: '',
  participo_movilidad: false,
  tipo_movilidad: '',
  pais_movilidad: '',
  duracion_movilidad: '',
  
  // Sección III: Inserción Laboral
  trabaja_actualmente: false,
  motivo_no_trabaja: '',
  tiempo_primer_empleo: '',
  rango_salario: '',
  relacion_carrera: null as boolean | null,
  tipo_contrato: '',
  jornada_laboral: '',
  medio_obtencion_empleo: '',
  cambios_empleo: null as number | null,
  satisfaccion_laboral: '',
  
  // Sección IV: Datos del Empleador
  nombre_empresa: '',
  sector_empresa: '',
  giro_empresa: '',
  ubicacion_empresa: '',
  puesto_actual: '',
  area_departamento: '',
  jefe_inmediato: '',
  contacto_jefe: '',
  
  // Sección V: Evaluación de la Formación
  promueve_pensamiento_critico: 'Sí',
  aspectos_valorados: '',
  sugerencias_plan_estudios: '',
  competencias_faltantes: '',
  calificacion_formacion: 10,
  recomendaria_institucion: true,
  razon_recomendacion: '',
  participacion_vinculacion: false,
  tipo_vinculacion: '',
  comentarios_adicionales: '',
})

// Condicionales
const mostrarCualGrupoEtnico = computed(() => form.pertenece_grupo_etnico)
const mostrarCualLengua = computed(() => form.habla_lengua_originaria)
const mostrarPracticas = computed(() => form.realizo_practicas)
const mostrarTitulacion = computed(() => form.tiene_titulo)
const mostrarPosgrado = computed(() => form.estudios_posgrado)
const mostrarMovilidad = computed(() => form.participo_movilidad)
const mostrarDatosEmpleo = computed(() => form.trabaja_actualmente)
const mostrarMotivoNoTrabaja = computed(() => !form.trabaja_actualmente)
const mostrarVinculacion = computed(() => form.participacion_vinculacion)

// Enviar formulario
const submitForm = () => {
  form.post('/encuesta-laboral/store', {
    onSuccess: () => {
      alert('Cuestionario de Seguimiento enviado correctamente')
    },
    onError: (errors) => {
      console.error('Errores:', errors)
    }
  })
}
 </script>

<template>
  <Head title="Encuesta Laboral" />
  <AppLayout :breadcrumbs="[{ title: 'Encuesta Laboral', href: '/encuesta-laboral' }]">
    <div class="container mx-auto px-4 py-8 max-w-5xl">
      <Card>
        <CardHeader>
          <CardTitle class="text-3xl">Cuestionario de Seguimiento de Egresados</CardTitle>
          <CardDescription>
            Este cuestionario tiene como propósito conocer tu situación laboral y académica actual. Por favor, completa todas las secciones.
          </CardDescription>
        </CardHeader>
        <CardContent>
          <form @submit.prevent="submitForm" class="space-y-8">
            
            <!-- SECCIÓN I: DATOS GENERALES -->
            <div class="border-b pb-6">
              <h3 class="text-2xl font-bold mb-4 text-blue-600">I. Datos Generales</h3>
              
              <div class="space-y-4">
                <div>
                  <Label for="nombre_completo">1. Nombre completo *</Label>
                  <Input id="nombre_completo" v-model="form.nombre_completo" required />
                  <span v-if="form.errors.nombre_completo" class="text-sm text-red-600">{{ form.errors.nombre_completo }}</span>
                </div>

                <div>
                  <Label for="genero">2. Género *</Label>
                  <select id="genero" v-model="form.genero" required class="flex h-9 w-full rounded-md border border-input bg-transparent px-3 py-1 text-sm shadow-sm">
                    <option value="Mujer">Mujer</option>
                    <option value="Hombre">Hombre</option>
                    <option value="No binario">No binario</option>
                    <option value="Prefiero no decirlo">Prefiero no decirlo</option>
                    <option value="Otro">Otro</option>
                  </select>
                </div>

                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                  <div>
                    <Label for="edad">3. Edad *</Label>
                    <Input id="edad" v-model="form.edad" type="number" min="18" max="100" required />
                  </div>
                  <div>
                    <Label for="curp">4. CURP</Label>
                    <Input id="curp" v-model="form.curp" maxlength="18" />
                  </div>
                </div>

                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                  <div>
                    <Label for="telefono">5. Teléfono *</Label>
                    <Input id="telefono" v-model="form.telefono" type="tel" required />
                  </div>
                  <div>
                    <Label for="email">6. Correo Electrónico *</Label>
                    <Input id="email" v-model="form.email" type="email" required />
                  </div>
                </div>

                <div>
                  <Label for="estado_civil_id">7. Estado Civil *</Label>
                  <select id="estado_civil_id" v-model="form.estado_civil_id" required class="flex h-9 w-full rounded-md border border-input bg-transparent px-3 py-1 text-sm shadow-sm">
                    <option value="">Selecciona...</option>
                    <option v-for="ec in estadosCiviles" :key="ec.id" :value="ec.id">{{ ec.nombre }}</option>
                  </select>
                </div>

                <div>
                  <Label for="residencia_actual">8. Residencia actual (ciudad/estado) *</Label>
                  <Input id="residencia_actual" v-model="form.residencia_actual" required />
                </div>

                <div>
                  <Label>9. ¿Pertenece a algún grupo étnico? *</Label>
                  <div class="flex gap-4 mt-1">
                    <label class="flex items-center gap-2">
                      <input type="radio" :value="true" v-model="form.pertenece_grupo_etnico" required class="w-4 h-4" />
                      Sí
                    </label>
                    <label class="flex items-center gap-2">
                      <input type="radio" :value="false" v-model="form.pertenece_grupo_etnico" required class="w-4 h-4" />
                      No
                    </label>
                  </div>
                  <div v-if="mostrarCualGrupoEtnico" class="mt-2">
                    <Input v-model="form.cual_grupo_etnico" placeholder="¿Cuál?" />
                  </div>
                </div>

                <div>
                  <Label>10. ¿Habla alguna lengua originaria? *</Label>
                  <div class="flex gap-4 mt-1">
                    <label class="flex items-center gap-2">
                      <input type="radio" :value="true" v-model="form.habla_lengua_originaria" required class="w-4 h-4" />
                      Sí
                    </label>
                    <label class="flex items-center gap-2">
                      <input type="radio" :value="false" v-model="form.habla_lengua_originaria" required class="w-4 h-4" />
                      No
                    </label>
                  </div>
                  <div v-if="mostrarCualLengua" class="mt-2">
                    <Input v-model="form.cual_lengua_originaria" placeholder="¿Cuál?" />
                  </div>
                </div>

                <div>
                  <Label for="comunidad_diversa">11. ¿Pertenece a alguna comunidad diversa? (LGBTQ+, discapacidad, etc.)</Label>
                  <Input id="comunidad_diversa" v-model="form.comunidad_diversa" placeholder="Especifique (opcional)" />
                </div>

                <div>
                  <Label>12. ¿Tiene hijos?</Label>
                  <div class="flex gap-4 mt-1">
                    <label class="flex items-center gap-2">
                      <input type="radio" :value="true" v-model="form.tiene_hijos" class="w-4 h-4" />
                      Sí
                    </label>
                    <label class="flex items-center gap-2">
                      <input type="radio" :value="false" v-model="form.tiene_hijos" class="w-4 h-4" />
                      No
                    </label>
                  </div>
                  <div v-if="form.tiene_hijos" class="mt-2">
                    <Label for="num_hijos">¿Cuántos?</Label>
                    <Input id="num_hijos" v-model="form.num_hijos" type="number" min="1" />
                  </div>
                </div>

                <div>
                  <Label for="dependientes_economicos">13. ¿Cuántas personas dependen económicamente de usted?</Label>
                  <Input id="dependientes_economicos" v-model="form.dependientes_economicos" type="number" min="0" />
                </div>
              </div>
            </div>

            <!-- SECCIÓN II: TRAYECTORIA ACADÉMICA -->
            <div class="border-b pb-6">
              <h3 class="text-2xl font-bold mb-4 text-green-600">II. Trayectoria Académica</h3>
              
              <div class="space-y-4">
                <div>
                  <Label for="programa_academico">14. Programa académico del que egresó *</Label>
                  <Input id="programa_academico" v-model="form.programa_academico" required />
                </div>

                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                  <div>
                    <Label for="fecha_ingreso">15. Fecha de ingreso *</Label>
                    <Input id="fecha_ingreso" v-model="form.fecha_ingreso" type="date" required />
                  </div>
                  <div>
                    <Label for="fecha_egreso">16. Fecha de egreso *</Label>
                    <Input id="fecha_egreso" v-model="form.fecha_egreso" type="date" required />
                  </div>
                </div>

                <div>
                  <Label>17. ¿Realizó prácticas profesionales durante sus estudios? *</Label>
                  <div class="flex gap-4 mt-1">
                    <label class="flex items-center gap-2">
                      <input type="radio" :value="true" v-model="form.realizo_practicas" required class="w-4 h-4" />
                      Sí
                    </label>
                    <label class="flex items-center gap-2">
                      <input type="radio" :value="false" v-model="form.realizo_practicas" required class="w-4 h-4" />
                      No
                    </label>
                  </div>
                  <div v-if="mostrarPracticas" class="mt-2">
                    <Label for="descripcion_practicas">Describa brevemente dónde y qué actividades realizó</Label>
                    <textarea id="descripcion_practicas" v-model="form.descripcion_practicas" rows="2" class="flex w-full rounded-md border border-input bg-transparent px-3 py-2 text-sm"></textarea>
                  </div>
                </div>

                <div>
                  <Label>18. ¿Cuenta ya con su título profesional? *</Label>
                  <div class="flex gap-4 mt-1">
                    <label class="flex items-center gap-2">
                      <input type="radio" :value="true" v-model="form.tiene_titulo" required class="w-4 h-4" />
                      Sí
                    </label>
                    <label class="flex items-center gap-2">
                      <input type="radio" :value="false" v-model="form.tiene_titulo" required class="w-4 h-4" />
                      No
                    </label>
                  </div>
                  <div v-if="mostrarTitulacion" class="mt-2">
                    <Label for="fecha_titulacion">Fecha de titulación</Label>
                    <Input id="fecha_titulacion" v-model="form.fecha_titulacion" type="date" />
                  </div>
                </div>

                <div>
                  <Label>19. ¿Continuó o está cursando estudios de posgrado? *</Label>
                  <div class="flex gap-4 mt-1">
                    <label class="flex items-center gap-2">
                      <input type="radio" :value="true" v-model="form.estudios_posgrado" required class="w-4 h-4" />
                      Sí
                    </label>
                    <label class="flex items-center gap-2">
                      <input type="radio" :value="false" v-model="form.estudios_posgrado" required class="w-4 h-4" />
                      No
                    </label>
                  </div>
                  <div v-if="mostrarPosgrado" class="mt-2 space-y-2">
                    <div>
                      <Label for="nivel_posgrado">20. Nivel (Especialidad, Maestría, Doctorado)</Label>
                      <select id="nivel_posgrado" v-model="form.nivel_posgrado" class="flex h-9 w-full rounded-md border border-input bg-transparent px-3 py-1 text-sm shadow-sm">
                        <option value="">Selecciona...</option>
                        <option value="Especialidad">Especialidad</option>
                        <option value="Maestría">Maestría</option>
                        <option value="Doctorado">Doctorado</option>
                      </select>
                    </div>
                    <div>
                      <Label for="area_posgrado">21. Área del posgrado</Label>
                      <Input id="area_posgrado" v-model="form.area_posgrado" placeholder="Ej: Educación, Tecnología, Salud..." />
                    </div>
                    <div>
                      <Label for="institucion_posgrado">Institución</Label>
                      <Input id="institucion_posgrado" v-model="form.institucion_posgrado" />
                    </div>
                    <div>
                      <Label for="status_posgrado">Estatus</Label>
                      <select id="status_posgrado" v-model="form.status_posgrado" class="flex h-9 w-full rounded-md border border-input bg-transparent px-3 py-1 text-sm shadow-sm">
                        <option value="">Selecciona...</option>
                        <option value="En curso">En curso</option>
                        <option value="Terminado">Terminado</option>
                        <option value="Titulado">Titulado</option>
                      </select>
                    </div>
                  </div>
                </div>

                <div>
                  <Label>22. ¿Participó en programas de movilidad estudiantil? *</Label>
                  <div class="flex gap-4 mt-1">
                    <label class="flex items-center gap-2">
                      <input type="radio" :value="true" v-model="form.participo_movilidad" required class="w-4 h-4" />
                      Sí
                    </label>
                    <label class="flex items-center gap-2">
                      <input type="radio" :value="false" v-model="form.participo_movilidad" required class="w-4 h-4" />
                      No
                    </label>
                  </div>
                  <div v-if="mostrarMovilidad" class="mt-2 space-y-2">
                    <div>
                      <Label for="tipo_movilidad">23. Tipo (Nacional, Internacional)</Label>
                      <select id="tipo_movilidad" v-model="form.tipo_movilidad" class="flex h-9 w-full rounded-md border border-input bg-transparent px-3 py-1 text-sm shadow-sm">
                        <option value="">Selecciona...</option>
                        <option value="Nacional">Nacional</option>
                        <option value="Internacional">Internacional</option>
                      </select>
                    </div>
                    <div>
                      <Label for="pais_movilidad">País/Estado</Label>
                      <Input id="pais_movilidad" v-model="form.pais_movilidad" placeholder="Nombre del país o estado" />
                    </div>
                    <div>
                      <Label for="duracion_movilidad">24. Duración</Label>
                      <Input id="duracion_movilidad" v-model="form.duracion_movilidad" placeholder="Ej: 6 meses, 1 año..." />
                    </div>
                  </div>
                </div>
              </div>
            </div>

            <!-- SECCIÓN III: INSERCIÓN LABORAL -->
            <div class="border-b pb-6">
              <h3 class="text-2xl font-bold mb-4 text-purple-600">III. Inserción Laboral</h3>
              
              <div class="space-y-4">
                <div>
                  <Label>25. ¿Actualmente se encuentra trabajando? *</Label>
                  <div class="flex gap-4 mt-1">
                    <label class="flex items-center gap-2">
                      <input type="radio" :value="true" v-model="form.trabaja_actualmente" required class="w-4 h-4" />
                      Sí
                    </label>
                    <label class="flex items-center gap-2">
                      <input type="radio" :value="false" v-model="form.trabaja_actualmente" required class="w-4 h-4" />
                      No
                    </label>
                  </div>
                </div>

                <div v-if="mostrarMotivoNoTrabaja">
                  <Label for="motivo_no_trabaja">26. Si no trabaja, ¿cuál es el motivo principal?</Label>
                  <select id="motivo_no_trabaja" v-model="form.motivo_no_trabaja" class="flex h-9 w-full rounded-md border border-input bg-transparent px-3 py-1 text-sm shadow-sm">
                    <option value="">Selecciona...</option>
                    <option value="Buscando empleo">Buscando empleo</option>
                    <option value="Estudiando">Estudiando</option>
                    <option value="Salud">Problemas de salud</option>
                    <option value="Familiar">Razones familiares</option>
                    <option value="Otro">Otro</option>
                  </select>
                </div>

                <div v-if="mostrarDatosEmpleo" class="space-y-4 p-4 bg-purple-50 dark:bg-purple-900/20 rounded-lg">
                  <div>
                    <Label for="tiempo_primer_empleo">27. ¿Cuánto tiempo le tomó conseguir su primer empleo después de egresar?</Label>
                    <select id="tiempo_primer_empleo" v-model="form.tiempo_primer_empleo" class="flex h-9 w-full rounded-md border border-input bg-transparent px-3 py-1 text-sm shadow-sm">
                      <option value="">Selecciona...</option>
                      <option value="Menos de 6 meses">Menos de 6 meses</option>
                      <option value="6-12 meses">6-12 meses</option>
                      <option value="1-2 años">1-2 años</option>
                      <option value="Más de 2 años">Más de 2 años</option>
                      <option value="Trabajaba antes de egresar">Trabajaba antes de egresar</option>
                    </select>
                  </div>

                  <div>
                    <Label for="rango_salario">28. Rango de salario mensual</Label>
                    <select id="rango_salario" v-model="form.rango_salario" class="flex h-9 w-full rounded-md border border-input bg-transparent px-3 py-1 text-sm shadow-sm">
                      <option value="">Selecciona...</option>
                      <option value="$5,000-$10,000">$5,000 - $10,000</option>
                      <option value="$10,001-$15,000">$10,001 - $15,000</option>
                      <option value="$15,001-$20,000">$15,001 - $20,000</option>
                      <option value="$20,001-$30,000">$20,001 - $30,000</option>
                      <option value="Más de $30,000">Más de $30,000</option>
                    </select>
                  </div>

                  <div>
                    <Label>29. ¿Su trabajo actual tiene relación con su carrera?</Label>
                    <div class="flex gap-4 mt-1">
                      <label class="flex items-center gap-2">
                        <input type="radio" :value="true" v-model="form.relacion_carrera" class="w-4 h-4" />
                        Sí
                      </label>
                      <label class="flex items-center gap-2">
                        <input type="radio" :value="false" v-model="form.relacion_carrera" class="w-4 h-4" />
                        No
                      </label>
                    </div>
                  </div>

                  <div>
                    <Label for="tipo_contrato">30. Tipo de contrato</Label>
                    <select id="tipo_contrato" v-model="form.tipo_contrato" class="flex h-9 w-full rounded-md border border-input bg-transparent px-3 py-1 text-sm shadow-sm">
                      <option value="">Selecciona...</option>
                      <option value="Indefinido">Indefinido</option>
                      <option value="Temporal">Temporal</option>
                      <option value="Por proyecto">Por proyecto</option>
                      <option value="Honorarios">Honorarios</option>
                      <option value="Independiente">Independiente</option>
                    </select>
                  </div>

                  <div>
                    <Label for="jornada_laboral">31. Jornada laboral</Label>
                    <select id="jornada_laboral" v-model="form.jornada_laboral" class="flex h-9 w-full rounded-md border border-input bg-transparent px-3 py-1 text-sm shadow-sm">
                      <option value="">Selecciona...</option>
                      <option value="Tiempo completo">Tiempo completo</option>
                      <option value="Medio tiempo">Medio tiempo</option>
                      <option value="Por horas">Por horas</option>
                    </select>
                  </div>

                  <div>
                    <Label for="medio_obtencion_empleo">32. ¿Cómo obtuvo su empleo actual?</Label>
                    <Input id="medio_obtencion_empleo" v-model="form.medio_obtencion_empleo" placeholder="Ej: Bolsa de trabajo, contactos, redes sociales..." />
                  </div>

                  <div>
                    <Label for="cambios_empleo">33. ¿Cuántas veces ha cambiado de empleo desde que egresó?</Label>
                    <Input id="cambios_empleo" v-model="form.cambios_empleo" type="number" min="0" />
                  </div>

                  <div>
                    <Label for="satisfaccion_laboral">34. Nivel de satisfacción con su trabajo actual</Label>
                    <select id="satisfaccion_laboral" v-model="form.satisfaccion_laboral" class="flex h-9 w-full rounded-md border border-input bg-transparent px-3 py-1 text-sm shadow-sm">
                      <option value="">Selecciona...</option>
                      <option value="Muy satisfecho">Muy satisfecho</option>
                      <option value="Satisfecho">Satisfecho</option>
                      <option value="Neutral">Neutral</option>
                      <option value="Insatisfecho">Insatisfecho</option>
                      <option value="Muy insatisfecho">Muy insatisfecho</option>
                    </select>
                  </div>
                </div>
              </div>
            </div>

            <!-- SECCIÓN IV: DATOS DEL EMPLEADOR -->
            <div v-if="mostrarDatosEmpleo" class="border-b pb-6">
              <h3 class="text-2xl font-bold mb-4 text-orange-600">IV. Datos del Empleador</h3>
              
              <div class="space-y-4">
                <div>
                  <Label for="nombre_empresa">35. Nombre de la empresa o institución</Label>
                  <Input id="nombre_empresa" v-model="form.nombre_empresa" />
                </div>

                <div>
                  <Label for="sector_empresa">36. Sector</Label>
                  <select id="sector_empresa" v-model="form.sector_empresa" class="flex h-9 w-full rounded-md border border-input bg-transparent px-3 py-1 text-sm shadow-sm">
                    <option value="">Selecciona...</option>
                    <option value="Público">Público</option>
                    <option value="Privado">Privado</option>
                    <option value="Social">Social</option>
                    <option value="Otro">Otro</option>
                  </select>
                </div>

                <div>
                  <Label for="giro_empresa">37. Giro o actividad principal</Label>
                  <Input id="giro_empresa" v-model="form.giro_empresa" placeholder="Ej: Tecnología, Educación, Salud..." />
                </div>

                <div>
                  <Label for="ubicacion_empresa">38. Ubicación (ciudad/estado)</Label>
                  <Input id="ubicacion_empresa" v-model="form.ubicacion_empresa" />
                </div>

                <div>
                  <Label for="puesto_actual">39. Puesto que desempeña</Label>
                  <Input id="puesto_actual" v-model="form.puesto_actual" />
                </div>

                <div>
                  <Label for="area_departamento">40. Área o departamento</Label>
                  <Input id="area_departamento" v-model="form.area_departamento" />
                </div>

                <div>
                  <Label for="jefe_inmediato">41. Nombre de su jefe inmediato</Label>
                  <Input id="jefe_inmediato" v-model="form.jefe_inmediato" />
                </div>

                <div>
                  <Label for="contacto_jefe">42. Contacto del jefe inmediato (email o teléfono)</Label>
                  <Input id="contacto_jefe" v-model="form.contacto_jefe" />
                </div>
              </div>
            </div>

            <!-- SECCIÓN V: EVALUACIÓN DE LA FORMACIÓN PROFESIONAL -->
            <div class="border-b pb-6">
              <h3 class="text-2xl font-bold mb-4 text-red-600">V. Evaluación de la Formación Profesional</h3>
              
              <div class="space-y-4">
                <div>
                  <Label>43. ¿Considera que la universidad promovió el pensamiento crítico y la solución de problemas? *</Label>
                  <div class="flex gap-4 mt-1">
                    <label class="flex items-center gap-2">
                      <input type="radio" value="Sí" v-model="form.promueve_pensamiento_critico" required class="w-4 h-4" />
                      Sí
                    </label>
                    <label class="flex items-center gap-2">
                      <input type="radio" value="No" v-model="form.promueve_pensamiento_critico" required class="w-4 h-4" />
                      No
                    </label>
                    <label class="flex items-center gap-2">
                      <input type="radio" value="Parcialmente" v-model="form.promueve_pensamiento_critico" required class="w-4 h-4" />
                      Parcialmente
                    </label>
                  </div>
                </div>

                <div>
                  <Label for="aspectos_valorados">44. ¿Qué aspectos valora más de su formación profesional?</Label>
                  <textarea id="aspectos_valorados" v-model="form.aspectos_valorados" rows="3" class="flex w-full rounded-md border border-input bg-transparent px-3 py-2 text-sm" placeholder="Ej: Conocimientos técnicos, valores, prácticas..."></textarea>
                </div>

                <div>
                  <Label for="sugerencias_plan_estudios">45. ¿Qué sugerencias haría para mejorar el plan de estudios de su carrera?</Label>
                  <textarea id="sugerencias_plan_estudios" v-model="form.sugerencias_plan_estudios" rows="3" class="flex w-full rounded-md border border-input bg-transparent px-3 py-2 text-sm"></textarea>
                </div>

                <div>
                  <Label for="competencias_faltantes">46. ¿Qué competencias considera que le faltaron desarrollar durante su formación?</Label>
                  <textarea id="competencias_faltantes" v-model="form.competencias_faltantes" rows="3" class="flex w-full rounded-md border border-input bg-transparent px-3 py-2 text-sm"></textarea>
                </div>

                <div>
                  <Label for="calificacion_formacion">47. Del 1 al 10, ¿cómo calificaría su formación profesional? *</Label>
                  <Input id="calificacion_formacion" v-model="form.calificacion_formacion" type="number" min="1" max="10" required />
                </div>

                <div>
                  <Label>48. ¿Recomendaría su institución a otros estudiantes? *</Label>
                  <div class="flex gap-4 mt-1">
                    <label class="flex items-center gap-2">
                      <input type="radio" :value="true" v-model="form.recomendaria_institucion" required class="w-4 h-4" />
                      Sí
                    </label>
                    <label class="flex items-center gap-2">
                      <input type="radio" :value="false" v-model="form.recomendaria_institucion" required class="w-4 h-4" />
                      No
                    </label>
                  </div>
                  <div class="mt-2">
                    <Label for="razon_recomendacion">¿Por qué?</Label>
                    <textarea id="razon_recomendacion" v-model="form.razon_recomendacion" rows="2" class="flex w-full rounded-md border border-input bg-transparent px-3 py-2 text-sm"></textarea>
                  </div>
                </div>

                <div>
                  <Label>50. ¿Ha participado en actividades de vinculación con la institución después de egresar? *</Label>
                  <div class="flex gap-4 mt-1">
                    <label class="flex items-center gap-2">
                      <input type="radio" :value="true" v-model="form.participacion_vinculacion" required class="w-4 h-4" />
                      Sí
                    </label>
                    <label class="flex items-center gap-2">
                      <input type="radio" :value="false" v-model="form.participacion_vinculacion" required class="w-4 h-4" />
                      No
                    </label>
                  </div>
                  <div v-if="mostrarVinculacion" class="mt-2">
                    <Label for="tipo_vinculacion">Especifique (conferencias, proyectos, etc.)</Label>
                    <Input id="tipo_vinculacion" v-model="form.tipo_vinculacion" />
                  </div>
                </div>

                <div>
                  <Label for="comentarios_adicionales">52. Comentarios adicionales</Label>
                  <textarea id="comentarios_adicionales" v-model="form.comentarios_adicionales" rows="4" class="flex w-full rounded-md border border-input bg-transparent px-3 py-2 text-sm" placeholder="Cualquier comentario que desee agregar..."></textarea>
                </div>
              </div>
            </div>

            <!-- Botones -->
            <div class="flex justify-between items-center pt-4">
              <Link 
                href="/acuses-seguimiento?from=encuesta-laboral"
                class="text-blue-600 hover:text-blue-700 underline"
              >
                Ver Acuses de Seguimiento
              </Link>
              
              <Button 
                type="submit" 
                :disabled="form.processing"
                class="bg-green-600 hover:bg-green-700 text-white px-8"
              >
                {{ form.processing ? 'Enviando...' : 'Enviar Cuestionario de Seguimiento' }}
              </Button>
            </div>
          </form>
        </CardContent>
      </Card>
    </div>
  </AppLayout>
</template>
