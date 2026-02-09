<script setup>
import { Link } from '@inertiajs/vue3'
</script>

<template>
  <div class="min-h-screen flex items-center justify-center relative">

    <!-- LEFT HALF COLOR BACKGROUND -->
    <div class="absolute inset-0 w-1/2 bg-teal-300"></div>

    <!-- RIGHT HALF TRANSPARENT -->
    <div class="absolute inset-0 w-1/2 right-0 bg-transparent"></div>

    <!-- SLOT CONTENT -->
    <div class="relative z-10 w-full max-w-6xl px-6 py-10 flex flex-col items-center">
      <slot />
    </div>

  </div>
</template>

<style scoped>
/* Optional: you can add blur or subtle background effects here */
</style>
