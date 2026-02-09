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
          <h2 class="text-xl font-bold text-gray-800">Reports</h2>
          <p class="text-gray-500 mt-2">System reports and analytics will appear here...</p>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref } from 'vue'
import { router } from '@inertiajs/vue3'
import axios from 'axios'
import Swal from 'sweetalert2'

const activeMenu = ref('Reports')

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
