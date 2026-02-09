<template>
  <div>
    <h2 class="text-2xl font-bold mb-6">Registered Businesses</h2>
    <div v-if="businesses.length" class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
      <div v-for="b in businesses" :key="b.id" class="bg-white p-5 rounded-xl shadow">
        <p class="font-semibold text-lg">{{ b.business_name }}</p>
        <p class="text-sm text-gray-600">{{ b.address }}</p>
        <p class="text-sm mt-1">
          <strong>Status:</strong>
          <span :class="b.status === 'active' ? 'text-green-600' : 'text-gray-500'">{{ b.status }}</span>
        </p>
      </div>
    </div>
    <p v-else class="italic text-gray-500">No registered businesses found.</p>
  </div>
</template>

<script setup>
import { defineProps } from 'vue'
const props = defineProps({ businesses: Array })
</script>
