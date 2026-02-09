
<template>
  <div>
    <label class="block text-sm font-medium text-gray-700 mb-1">{{ label }}</label>
    <input type="file" @change="handleFile" class="w-full border rounded px-3 py-2" />
  </div>
</template>

<script setup>
import { ref, watch } from 'vue'

defineProps({
  label: { type: String, required: true },
  modelValue: { type: null, default: null }
})

const emit = defineEmits(['update:modelValue'])

const handleFile = (event) => {
  const file = event.target.files[0]
  emit('update:modelValue', file)
}
</script>
