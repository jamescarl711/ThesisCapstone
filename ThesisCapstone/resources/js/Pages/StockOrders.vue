<template>
  <div>
    <h2 class="text-2xl font-bold mb-6">Stock Orders</h2>

    <div v-if="loading" class="text-gray-500">Loading stock orders...</div>

    <div v-else>
      <div v-if="orders.length" class="space-y-5">
        <div v-for="order in orders" :key="order.id" class="bg-white rounded-xl shadow p-5">

          <div class="flex justify-between items-center mb-2">
            <p class="font-semibold text-lg">{{ order.material_name }}</p>
            <span class="text-xs px-3 py-1 rounded-full" :class="statusClass(order.status)">
              {{ order.status }}
            </span>
          </div>

          <div class="text-sm text-gray-600 space-y-1 mb-2">
            <p><strong>Quantity:</strong> {{ order.quantity }}</p>
            <p><strong>Expected Date:</strong> {{ order.expected_date }}</p>
            <p><strong>Arrival Date:</strong> {{ order.arrival_date || 'Not arrived' }}</p>
          </div>

          <button v-if="order.status !== 'received'" @click="markReceived(order)" class="bg-green-500 hover:bg-green-600 text-white px-3 py-1 rounded-md text-sm">
            Mark as Received
          </button>
        </div>
      </div>
      <p v-else class="italic text-gray-500">No stock orders yet.</p>
    </div>
  </div>
</template>

<script setup>
import { ref, onMounted } from 'vue'
import axios from 'axios'
import Swal from 'sweetalert2'

const orders = ref([])
const loading = ref(true)

const fetchOrders = async () => {
  loading.value = true
  try {
    const res = await axios.get('/procurement/stock-orders')
    orders.value = res.data
  } catch {
    Swal.fire('Error', 'Failed to load stock orders', 'error')
  } finally {
    loading.value = false
  }
}

const markReceived = async (order) => {
  try {
    await axios.post(`/procurement/mark-received/${order.id}`)
    Swal.fire('Success', 'Stock marked as received', 'success')
    fetchOrders()
  } catch {
    Swal.fire('Error', 'Failed to update stock', 'error')
  }
}

const statusClass = (status) => {
  if (status === 'ordered') return 'bg-yellow-100 text-yellow-700'
  if (status === 'received') return 'bg-green-100 text-green-700'
  return 'bg-gray-100 text-gray-700'
}

onMounted(fetchOrders)
</script>

<style scoped>
</style>
