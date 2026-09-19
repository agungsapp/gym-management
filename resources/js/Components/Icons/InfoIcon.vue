<template>
  <svg xmlns="http://www.w3.org/2000/svg" :class="sizeClass" fill="none" viewBox="0 0 24 24" stroke="currentColor"
    :stroke-width="strokeWidth">
    <path stroke-linecap="round" stroke-linejoin="round"
      d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
  </svg>
</template>

<script setup>
import { computed } from 'vue';

const props = defineProps({
  size: {
    type: String,
    default: 'sm',
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