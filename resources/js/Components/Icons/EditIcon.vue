<template>
  <svg xmlns="http://www.w3.org/2000/svg" :class="sizeClass" fill="none" viewBox="0 0 24 24" stroke="currentColor"
    :stroke-width="strokeWidth">
    <path stroke-linecap="round" stroke-linejoin="round"
      d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z" />
  </svg>
</template>

<script setup>
import { computed } from 'vue';

const props = defineProps({
  size: {
    type: String,
    default: 'sm', // sm | md | lg
  },
  strokeWidth: {
    type: [Number, String],
    default: 2,
  },
});

const sizeClass = computed(() => {
  return {
    sm: 'h-3.5 w-3.5',
    md: 'h-5 w-5',
    lg: 'h-6 w-6',
  }[props.size] || 'h-3.5 w-3.5';
});
</script>