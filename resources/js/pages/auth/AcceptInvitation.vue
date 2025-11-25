<script setup lang="ts">
import { Head, router } from '@inertiajs/vue3';
import { ref } from 'vue';
import { Button } from '@/components/ui/button';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';

const props = defineProps<{ token:string; email:string; name:string; role:string; expires_at:string|null }>();
const form = ref({ password:'', password_confirmation:'' });
const processing = ref(false);
function submit(){
  processing.value=true;
  router.post(`/invitation/accept/${props.token}`, form.value, { onFinish:()=>processing.value=false });
}
</script>
<template>
  <Head title="Aceptar invitación" />
  <div class="max-w-md mx-auto p-6 space-y-6">
    <h1 class="text-2xl font-semibold">Activar cuenta</h1>
    <p>Has sido invitado como <strong>{{ role }}</strong>.</p>
    <p>Email: {{ email }}</p>
    <div class="space-y-4">
      <div class="flex flex-col gap-2">
        <Label>Contraseña</Label>
        <Input type="password" v-model="form.password" />
      </div>
      <div class="flex flex-col gap-2">
        <Label>Confirmar contraseña</Label>
        <Input type="password" v-model="form.password_confirmation" />
      </div>
      <Button :disabled="processing" @click="submit">Guardar y continuar</Button>
    </div>
  </div>
</template>
