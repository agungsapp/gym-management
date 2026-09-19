<template>
  <svg xmlns="http://www.w3.org/2000/svg" :class="sizeClass" fill="none" viewBox="0 0 24 24" stroke="currentColor"
    :stroke-width="strokeWidth">
    <path stroke-linecap="round" stroke-linejoin="round"
      d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" />
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