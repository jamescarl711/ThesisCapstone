<template>
  <div class="min-h-screen bg-gray-100 flex flex-col">
    <!-- NAVBAR -->
    <nav class="bg-white border-b px-6 py-4 flex justify-between items-center shadow-sm">
      <h1 class="text-xl font-bold text-gray-800">Service Provider Dashboard</h1>
      <div class="flex items-center gap-6">
        <div class="flex items-center gap-2">
          <span class="text-sm font-semibold" :class="provider?.is_available ? 'text-green-600' : 'text-red-500'">
            {{ provider?.is_available ? 'Available' : 'Busy' }}
          </span>
          <label class="switch">
            <input type="checkbox" :checked="provider?.is_available" @change="confirmAvailability" />
            <span class="slider"></span>
          </label>
        </div>
        <button @click="confirmLogout" class="bg-red-500 hover:bg-red-600 text-white px-4 py-2 rounded-md text-sm">Logout</button>
      </div>
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
        <div v-if="loading" class="text-gray-500">Loading dashboard...</div>

        <DashboardView v-if="!loading && section === 'dashboard'" :businesses="businesses" />
        <AssignedRequestsView v-if="!loading && section === 'assigned'" :assignedRequests="assignedRequests" @refresh-data="fetchData" />
        <ProfileView v-if="!loading && section === 'profile'" :user="user" :provider="provider" />
      </main>
    </div>
  </div>
</template>

<script setup>
import { ref, onMounted } from 'vue'
import Swal from 'sweetalert2'
import axios from 'axios'

// Child Views
import DashboardView from './DashboardView.vue'
import AssignedRequestsView from './AssignedRequestsView.vue'
import ProfileView from './ProfileView.vue'

const provider = ref(null)
const user = ref(null)
const businesses = ref([])
const assignedRequests = ref([])
const loading = ref(true)
const section = ref('dashboard')

const sidebarItems = [
  { key: 'dashboard', label: 'Dashboard' },
  { key: 'assigned', label: 'Assigned Requests' },
  { key: 'profile', label: 'Profile' },
]

const fetchData = async () => {
  loading.value = true
  try {
    const res = await axios.get('/service-provider/dashboard-data')
    provider.value = res.data.provider
    user.value = res.data.user
    businesses.value = res.data.businesses || []

    const assigned = await axios.get('/user/service-provider/assigned-requests')
    assignedRequests.value = assigned.data || []
  } catch {
    Swal.fire('Error', 'Failed to load dashboard', 'error')
  } finally {
    loading.value = false
  }
}

const confirmAvailability = async () => {
  const result = await Swal.fire({ title: 'Change availability?', showCancelButton: true })
  if (!result.isConfirmed) return
  const res = await axios.post('/service-provider/toggle')
  provider.value.is_available = res.data.is_available
}

const confirmLogout = async () => {
  const result = await Swal.fire({ title: 'Logout?', icon: 'warning', showCancelButton: true })
  if (result.isConfirmed) {
    await axios.post('/logout')
    window.location.href = '/login'
  }
}

onMounted(fetchData)
</script>

<style scoped>
.switch { position: relative; width: 40px; height: 20px; }
.switch input { display: none; }
.slider { position: absolute; inset: 0; background: #ccc; border-radius: 999px; }
.slider::before { content: ''; width: 16px; height: 16px; background: white; position: absolute; left: 2px; bottom: 2px; border-radius: 50%; transition: 0.2s; }
input:checked + .slider { background: #4f46e5; }
input:checked + .slider::before { transform: translateX(20px); }
</style>
