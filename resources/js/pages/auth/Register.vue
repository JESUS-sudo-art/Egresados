<script setup lang="ts">
import InputError from '@/components/InputError.vue';
import TextLink from '@/components/TextLink.vue';
import { Button } from '@/components/ui/button';
import { Checkbox } from '@/components/ui/checkbox';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import AuthBase from '@/layouts/AuthLayout.vue';
import { login } from '@/routes';
import { store } from '@/routes/register';
import { Form, Head } from '@inertiajs/vue3';
import { LoaderCircle } from 'lucide-vue-next';
import { ref, watch, computed } from 'vue';

const props = defineProps<{
    canLogin: boolean;
    unidades: Array<{ id: number; nombre: string; carreras?: Array<{ id: number; nombre: string }> }>;
    carreras: Array<{ id: number; nombre: string }>;
}>();

const selectedUserType = ref('');
const selectedUnidadId = ref<number | null>(null);

// Filter carreras based on selected unidad
const carrerasFiltradas = computed(() => {
    if (!selectedUnidadId.value) return [];
    const unidad = props.unidades.find(u => u.id === selectedUnidadId.value);
    return unidad?.carreras || [];
});
</script>

<template>
    <AuthBase
        title="Crea una cuenta"
        description="Ingresa tus datos para registrarte"
    >
        <Head title="Registro" />

        <Form
            v-bind="store.form()"
            :reset-on-success="['password', 'password_confirmation']"
            v-slot="{ errors, processing }"
            class="flex flex-col gap-6"
        >
            <div class="grid gap-6">
                <div class="grid gap-2">
                    <Label for="nombre">Nombre</Label>
                    <Input
                        id="nombre"
                        type="text"
                        required
                        autofocus
                        :tabindex="1"
                        autocomplete="given-name"
                        name="nombre"
                        placeholder="Nombre (ej. Juan)"
                        pattern="^[A-ZÁÉÍÓÚÑÜ][a-záéíóúñüA-ZÁÉÍÓÚÑÜ\s]+$"
                        title="Debe iniciar con mayúscula y solo puede contener letras"
                    />
                    <InputError :message="errors.nombre" />
                    <p class="text-xs text-muted-foreground">Debe iniciar con mayúscula</p>
                </div>

                <div class="grid gap-2">
                    <Label for="apellidos">Apellidos</Label>
                    <Input
                        id="apellidos"
                        type="text"
                        required
                        :tabindex="2"
                        autocomplete="family-name"
                        name="apellidos"
                        placeholder="Apellidos (ej. Pérez García)"
                        pattern="^[A-ZÁÉÍÓÚÑÜ][a-záéíóúñüA-ZÁÉÍÓÚÑÜ\s]+$"
                        title="Debe iniciar con mayúscula y solo puede contener letras"
                    />
                    <InputError :message="errors.apellidos" />
                    <p class="text-xs text-muted-foreground">Debe iniciar con mayúscula</p>
                </div>

                <div class="grid gap-2">
                    <Label for="email">Correo electrónico</Label>
                    <Input
                        id="email"
                        type="email"
                        required
                        :tabindex="3"
                        autocomplete="email"
                        name="email"
                        placeholder="correo@ejemplo.com"
                    />
                    <InputError :message="errors.email" />
                </div>

                <div class="grid gap-2">
                    <Label for="fecha_nacimiento">Fecha de nacimiento</Label>
                    <Input
                        id="fecha_nacimiento"
                        type="date"
                        :tabindex="4"
                        name="fecha_nacimiento"
                    />
                    <InputError :message="errors.fecha_nacimiento" />
                </div>

                <div class="grid gap-2">
                    <Label for="estado_origen">Estado de origen</Label>
                    <Input
                        id="estado_origen"
                        type="text"
                        :tabindex="5"
                        name="estado_origen"
                        placeholder="Ej. Oaxaca"
                    />
                    <InputError :message="errors.estado_origen" />
                </div>

                <div class="grid gap-2">
                    <Label for="unidad_id">Unidad</Label>
                    <select
                        id="unidad_id"
                        name="unidad_id"
                        required
                        :tabindex="6"
                        v-model="selectedUnidadId"
                        class="flex h-9 w-full rounded-md border border-input bg-transparent px-3 py-1 text-sm shadow-sm transition-colors file:border-0 file:bg-transparent file:text-sm file:font-medium placeholder:text-muted-foreground focus-visible:outline-none focus-visible:ring-1 focus-visible:ring-ring disabled:cursor-not-allowed disabled:opacity-50"
                    >
                        <option value="">Selecciona tu unidad</option>
                        <option v-for="unidad in props.unidades" :key="unidad.id" :value="unidad.id">
                            {{ unidad.nombre }}
                        </option>
                    </select>
                    <InputError :message="errors.unidad_id" />
                </div>

                <div class="grid gap-2">
                    <Label for="carrera_id">Carrera</Label>
                    <select
                        id="carrera_id"
                        name="carrera_id"
                        required
                        :tabindex="7"
                        :disabled="!selectedUnidadId"
                        class="flex h-9 w-full rounded-md border border-input bg-transparent px-3 py-1 text-sm shadow-sm transition-colors file:border-0 file:bg-transparent file:text-sm file:font-medium placeholder:text-muted-foreground focus-visible:outline-none focus-visible:ring-1 focus-visible:ring-ring disabled:cursor-not-allowed disabled:opacity-50"
                    >
                        <option value="">{{ selectedUnidadId ? 'Selecciona tu carrera' : 'Primero selecciona una unidad' }}</option>
                        <option v-for="carrera in carrerasFiltradas" :key="carrera.id" :value="carrera.id">
                            {{ carrera.nombre }}
                        </option>
                    </select>
                    <InputError :message="errors.carrera_id" />
                </div>

                <div class="grid gap-2">
                    <Label for="user_type">Tipo de usuario</Label>
                    <select
                        id="user_type"
                        name="user_type"
                        required
                        :tabindex="8"
                        v-model="selectedUserType"
                        class="flex h-9 w-full rounded-md border border-input bg-transparent px-3 py-1 text-sm shadow-sm transition-colors file:border-0 file:bg-transparent file:text-sm file:font-medium placeholder:text-muted-foreground focus-visible:outline-none focus-visible:ring-1 focus-visible:ring-ring disabled:cursor-not-allowed disabled:opacity-50"
                    >
                        <option value="">Selecciona tu tipo de usuario</option>
                        <option value="Estudiantes">Estudiante</option>
                        <option value="Egresados">Egresado</option>
                    </select>
                    <InputError :message="errors.user_type" />
                </div>

                <div v-if="selectedUserType === 'Egresados'" class="grid gap-2">
                    <Label for="anio_egreso">Año de egreso</Label>
                    <Input
                        id="anio_egreso"
                        type="number"
                        name="anio_egreso"
                        :required="selectedUserType === 'Egresados'"
                        :tabindex="9"
                        placeholder="2024"
                        min="1980"
                        :max="new Date().getFullYear()"
                    />
                    <InputError :message="errors.anio_egreso" />
                </div>

                <div class="grid gap-2">
                    <Label for="password">Contraseña</Label>
                    <Input
                        id="password"
                        type="password"
                        required
                        :tabindex="10"
                        autocomplete="new-password"
                        name="password"
                        placeholder="Contraseña"
                    />
                    <InputError :message="errors.password" />
                </div>

                <div class="grid gap-2">
                    <Label for="password_confirmation">Confirmar contraseña</Label>
                    <Input
                        id="password_confirmation"
                        type="password"
                        required
                        :tabindex="11"
                        autocomplete="new-password"
                        name="password_confirmation"
                        placeholder="Confirmar contraseña"
                    />
                    <InputError :message="errors.password_confirmation" />
                </div>

                <Button
                    type="submit"
                    class="mt-6 w-full"
                    :tabindex="12"
                    :disabled="processing"
                >
                    <LoaderCircle
                        v-if="processing"
                        class="h-4 w-4 animate-spin"
                    />
                    Registrarse
                </Button>
            </div>

            <div class="text-center text-sm text-muted-foreground">
                ¿Ya tienes una cuenta?
                <TextLink
                    :href="login()"
                    class="underline underline-offset-4"
                    :tabindex="13"
                    >Inicia sesión</TextLink
                >
            </div>
        </Form>
    </AuthBase>
</template>
