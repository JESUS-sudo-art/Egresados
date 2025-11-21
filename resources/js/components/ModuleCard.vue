<script setup lang="ts">
import { defineProps } from 'vue';

const props = defineProps({
  title: { type: String, required: true },
  subtitle: { type: String, default: '' },
  color: { type: String, default: 'green' },
  to: { type: String, default: null },
});
</script>

<template>
  <component :is="to ? 'a' : 'div'" :href="to" class="group block">
    <div
      class="flex h-full flex-col justify-center rounded-lg border p-4 shadow-sm transition-transform hover:-translate-y-1"
      :class="{
        'bg-emerald-600 text-white border-emerald-700': color === 'green',
        'bg-amber-100 text-amber-800 border-amber-300': color === 'amber',
        'bg-teal-50 text-teal-800 border-teal-200': color === 'teal',
        'bg-slate-50 text-slate-900 border-slate-200': color === 'default'
      }"
    >
      <div class="text-sm font-semibold leading-tight">{{ title }}</div>
      <div v-if="subtitle" class="mt-1 text-xs opacity-90">{{ subtitle }}</div>
    </div>
  </component>
</template>
