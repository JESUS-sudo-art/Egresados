<script setup lang="ts">
import type { HTMLAttributes } from 'vue';

defineOptions({
    inheritAttrs: false,
});

interface Props {
    className?: HTMLAttributes['class'];
}

defineProps<Props>();
</script>

<template>
    <svg
        xmlns="http://www.w3.org/2000/svg"
        viewBox="0 0 24 24"
        :class="className"
        v-bind="$attrs"
        fill="none"
        stroke="currentColor"
        stroke-width="2"
    >
        <!-- Icono de birrete de graduación -->
        <path d="M12 14L21 9L12 4L3 9L12 14Z" stroke-linecap="round" stroke-linejoin="round"/>
        <path d="M12 14V20" stroke-linecap="round" stroke-linejoin="round"/>
        <path d="M3 9V15C3 16.657 7.029 18 12 18C16.971 18 21 16.657 21 15V9" stroke-linecap="round" stroke-linejoin="round"/>
        <path d="M21 9L22 9.5V13" stroke-linecap="round" stroke-linejoin="round"/>
    </svg>
</template>
