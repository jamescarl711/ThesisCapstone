<template>
  <div class="layout">
    <aside class="sidebar">
      <h2 class="logo">HRPro</h2>
      <ul>
        <li :class="{ active: activeMenu==='Dashboard' }" @click="navigateTo('Dashboard', '/hr')">Dashboard</li>
        <li :class="{ active: activeMenu==='Assigned Requests' }" @click="navigateTo('Assigned Requests', '/hr/assigned-requests')">Assigned Requests</li>
        <li :class="{ active: activeMenu==='Accepted Requests' }" @click="navigateTo('Accepted Requests', '/hr/accepted-requests')">Accepted Requests</li>
        <li :class="{ active: activeMenu==='Rejected Requests' }" @click="navigateTo('Rejected Requests', '/hr/rejected-requests')">Rejected Requests</li>
        <li :class="{ active: activeMenu==='Attendance Management' }" @click="navigateTo('Attendance Management', '/hr/attendance')">Attendance Management</li>
        <li :class="{ active: activeMenu==='Payroll / Compensation' }" @click="navigateTo('Payroll / Compensation', '/hr/payroll')">Payroll / Compensation</li>
        <li :class="{ active: activeMenu==='Recruitment / Onboarding' }" @click="navigateTo('Recruitment / Onboarding', '/hr/recruitment')">Recruitment / Onboarding</li>
        <li :class="{ active: activeMenu==='Reports' }" @click="navigateTo('Reports', '/hr/reports')">Reports</li>
        <li class="logout-btn" @click="logout">Logout</li>
      </ul>
    </aside>

    <div class="main">
      <nav class="navbar">
        <h1>{{ activeMenu }}</h1>
      </nav>

      <div class="dashboard">
        <div class="bg-white p-6 rounded-xl shadow">
          <h2 class="text-2xl font-bold mb-6">Accepted Requests</h2>

          <div v-if="loading" class="text-gray-500">Loading accepted requests...</div>

          <div v-else>
            <table v-if="requests.length" class="min-w-full bg-white shadow rounded">
              <thead class="bg-gray-100">
                <tr>
                  <th class="p-2 text-left">User</th>
                  <th class="p-2 text-left">Business</th>
                  <th class="p-2 text-left">Service</th>
                  <th class="p-2 text-left">Preferred Date</th>
                  <th class="p-2 text-left">Address</th>
                  <th class="p-2 text-left">Status</th>
                </tr>
              </thead>
              <tbody>
                <tr v-for="req in requests" :key="req.id" class="border-t">
                  <td class="p-2">
                    {{ req.user.first_name }} {{ req.user.middle_initial || '' }} {{ req.user.last_name }}
                  </td>
                  <td class="p-2">{{ req.business.business_name || 'N/A' }}</td>
                  <td class="p-2">{{ req.service_type }}</td>
                  <td class="p-2">{{ req.preferred_date || 'TBD' }}</td>
                  <td class="p-2">{{ req.address_text || 'N/A' }}</td>
                  <td class="p-2">
                    <span class="rounded-full bg-emerald-50 px-2 py-1 text-xs font-medium text-emerald-700">
                      {{ req.status }}
                    </span>
                  </td>
                </tr>
              </tbody>
            </table>
            <p v-else class="italic text-gray-500">No accepted requests found.</p>
          </div>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref, onMounted } from 'vue'
import { router } from '@inertiajs/vue3'
import axios from 'axios'
import Swal from 'sweetalert2'

const activeMenu = ref('Accepted Requests')
const loading = ref(true)
const requests = ref([])

function navigateTo(menu, url) {
  activeMenu.value = menu
  router.visit(url)
}

const logout = async () => {
  const result = await Swal.fire({
    title: "Logout?",
    text: "Are you sure you want to log out?",
    icon: "warning",
    showCancelButton: true,
    confirmButtonColor: "#0f172a",
    cancelButtonColor: "#94a3b8",
    confirmButtonText: "Yes, logout",
  })
  if (!result.isConfirmed) return
  try {
    await axios.post('/logout')
    window.location.href = '/login'
  } catch (err) {
    console.error(err)
    Swal.fire('Error', 'Logout failed', 'error')
  }
}

const fetchRequests = async () => {
  loading.value = true
  try {
    const res = await axios.get('/hr/service-requests/accepted')
    requests.value = res.data
  } catch {
    Swal.fire('Error', 'Failed to load accepted requests', 'error')
  } finally {
    loading.value = false
  }
}

onMounted(fetchRequests)
</script>

<style scoped>
.layout { display:flex; min-height:100vh; font-family:Inter, sans-serif; }
.sidebar { width:220px; background:#0f172a; color:#fff; padding:20px; }
.logo { font-weight:700; font-size:1.5rem; text-align:center; margin-bottom:24px; }
.sidebar ul { list-style:none; padding:0; }
.sidebar li { padding:12px; border-radius:8px; cursor:pointer; margin-bottom:6px; }
.sidebar li.active, .sidebar li:hover { background:#1e293b; }
.sidebar .logout-btn { margin-top:20px; background:#ef4444; text-align:center; }
.main { flex:1; display:flex; flex-direction:column; }
.navbar { background:#fff; padding:16px; border-bottom:1px solid #e5e7eb; }
.dashboard { padding:24px; }
</style>
