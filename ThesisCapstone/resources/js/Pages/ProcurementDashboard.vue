<template>
  <div class="min-h-screen bg-gray-100 flex flex-col">

    <!-- NAVBAR -->
    <nav class="bg-white border-b px-6 py-4 flex justify-between items-center shadow-sm">
      <h1 class="text-xl font-bold text-gray-800">Procurement Dashboard</h1>
      <button @click="logout" class="bg-red-500 hover:bg-red-600 text-white px-4 py-2 rounded-md text-sm">
        Logout
      </button>
    </nav>

    <div class="flex flex-1">

      <!-- SIDEBAR -->
      <aside class="w-64 bg-white border-r px-4 py-6 shrink-0">
        <p class="text-xs uppercase tracking-wider text-gray-400 mb-6">Navigation</p>
        <ul class="space-y-2">
          <li v-for="item in sidebarItems" :key="item.key" @click="section = item.key"
              class="px-3 py-2 rounded-md cursor-pointer transition"
              :class="section === item.key ? 'bg-blue-50 text-blue-700 font-semibold' : 'text-gray-700 hover:bg-gray-100'">
            {{ item.label }}
          </li>
        </ul>
      </aside>

      <!-- MAIN CONTENT -->
      <main class="flex-1 p-6 overflow-y-auto">
        <component :is="currentComponent" />
      </main>

    </div>
  </div>
</template>

<script setup>
import { ref, computed } from 'vue'
import RequestsAwaitingMaterial from './RequestsAwaitingMaterial.vue'
import StockOrders from './StockOrders.vue'

const section = ref('dashboard')

const sidebarItems = [
  { key: 'dashboard', label: 'Dashboard' },
  { key: 'requests', label: 'Requests Awaiting Material' },
  { key: 'stocks', label: 'Stock Orders' },
]

const currentComponent = computed(() => {
  if (section.value === 'requests') return RequestsAwaitingMaterial
  if (section.value === 'stocks') return StockOrders
  return {
    template: `<div class="text-gray-600 font-semibold">Welcome to Procurement Dashboard</div>`
  }
})

const logout = async () => {
  await axios.post('/logout')
  window.location.href = '/login'
}
</script>

<style scoped>
</style>
