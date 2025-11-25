<script setup lang="ts">
import { Head, usePage, router } from '@inertiajs/vue3';
import { ref } from 'vue';
import { Button } from '@/components/ui/button';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';

const form = ref({ name:'', email:'', role:'Administrador general', days:'' });
const submitting = ref(false);
const page = usePage();
const invitations = page.props.invitations as any[];

function submit(){
  submitting.value = true;
  router.post('/admin/invitations', form.value, {
    onFinish:()=>{submitting.value=false; form.value={name:'',email:'',role:'Administrador general',days:''};}
  });
}

function resend(id:number){
  router.post(`/admin/invitations/${id}/resend`);
}
function destroy(id:number){
  router.delete(`/admin/invitations/${id}`);
}
</script>
<template>
  <Head title="Invitaciones" />
  <div class="space-y-6 p-6">
    <h1 class="text-2xl font-semibold">Invitar Administrador</h1>
    <div class="grid gap-4 md:grid-cols-4">
      <div class="flex flex-col gap-2">
        <Label>Nombre</Label>
        <Input v-model="form.name" />
      </div>
      <div class="flex flex-col gap-2">
        <Label>Correo</Label>
        <Input v-model="form.email" type="email" />
      </div>
      <div class="flex flex-col gap-2">
        <Label>Rol</Label>
        <select v-model="form.role" class="h-9 rounded border px-2 text-sm">
          <option value="Administrador general">Administrador General</option>
          <option value="Administrador de unidad">Administrador de unidad</option>
          <option value="Administrador academico">Administrador académico</option>
        </select>
      </div>
      <div class="flex flex-col gap-2">
        <Label>Días expiración (opcional)</Label>
        <Input v-model="form.days" type="number" min="1" max="30" />
      </div>
    </div>
    <Button :disabled="submitting" @click="submit">Enviar invitación</Button>

    <h2 class="text-xl font-medium mt-8">Invitaciones</h2>
    <div class="overflow-x-auto">
      <table class="min-w-full text-sm">
        <thead>
          <tr class="text-left border-b">
            <th class="py-2 pr-4">Nombre</th>
            <th class="py-2 pr-4">Email</th>
            <th class="py-2 pr-4">Rol</th>
            <th class="py-2 pr-4">Estado</th>
            <th class="py-2 pr-4">Expira</th>
            <th class="py-2 pr-4">Acciones</th>
          </tr>
        </thead>
        <tbody>
          <tr v-for="i in invitations" :key="i.id" class="border-b">
            <td class="py-2 pr-4">{{ i.name }}</td>
            <td class="py-2 pr-4">{{ i.email }}</td>
            <td class="py-2 pr-4">{{ i.role }}</td>
            <td class="py-2 pr-4">
              <span v-if="i.used_at" class="text-green-600">Usada</span>
              <span v-else-if="i.expires_at && new Date(i.expires_at) < new Date()" class="text-red-600">Expirada</span>
              <span v-else class="text-yellow-600">Activa</span>
            </td>
            <td class="py-2 pr-4">{{ i.expires_at ? new Date(i.expires_at).toLocaleString() : '-' }}</td>
            <td class="py-2 pr-4 flex gap-2">
              <Button size="sm" variant="outline" @click="resend(i.id)" :disabled="i.used_at">Reenviar</Button>
              <Button size="sm" variant="destructive" @click="destroy(i.id)">Eliminar</Button>
            </td>
          </tr>
        </tbody>
      </table>
    </div>
  </div>
</template>
