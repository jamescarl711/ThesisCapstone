<template>
  <h3 class="text-lg font-semibold text-gray-700 mb-4">{{ title }}</h3>
</template>

<script setup>
defineProps({
  title: { type: String, required: true }
})
</script>
