<template>
  <svg xmlns="http://www.w3.org/2000/svg" :class="sizeClass" fill="none" viewBox="0 0 24 24" stroke="currentColor"
    :stroke-width="strokeWidth">
    <path stroke-linecap="round" stroke-linejoin="round"
      d="M18.364 18.364A9 9 0 005.636 5.636m12.728 12.728A9 9 0 015.636 5.636m12.728 12.728L5.636 5.636" />
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