<template>
  <div class="min-h-screen bg-gray-100 flex flex-col">

    <!-- NAVBAR -->
    <header class="h-16 bg-white border-b flex items-center justify-between px-6">
      <div class="flex items-center space-x-3">
        <div class="w-9 h-9 rounded-lg bg-gradient-to-r from-blue-600 to-indigo-600
                    text-white flex items-center justify-center font-bold shadow">
          A
        </div>
        <h1 class="text-lg font-semibold text-gray-800">Admin Dashboard</h1>
      </div>

      <button
        @click="confirmLogout"
        class="text-sm px-4 py-2 rounded-lg border border-gray-300
               text-gray-700 hover:bg-red-50 hover:text-red-600 transition"
      >
        Logout
      </button>
    </header>

    <!-- BODY -->
    <div class="flex flex-1">

      <!-- SIDEBAR -->
      <aside class="w-64 bg-white border-r hidden md:flex flex-col">
        <div class="p-6 text-xs uppercase text-gray-400 font-semibold">
          Management
        </div>

        <nav class="px-4 space-y-2 text-sm">

          <!-- User Management -->
          <button
            @click="activeMenu='users'"
            class="w-full flex items-center space-x-3 px-4 py-3 rounded-lg transition"
            :class="activeMenu==='users'
              ? 'bg-gradient-to-r from-blue-600 to-indigo-600 text-white shadow'
              : 'text-gray-700 hover:bg-blue-50'">
            <span>👤</span>
            <span>User Management</span>
          </button>

          <!-- Business Management -->
          <button
            @click="activeMenu='businesses'"
            class="w-full flex items-center space-x-3 px-4 py-3 rounded-lg transition"
            :class="activeMenu==='businesses'
              ? 'bg-gradient-to-r from-blue-600 to-indigo-600 text-white shadow'
              : 'text-gray-700 hover:bg-blue-50'">
            <span>🏢</span>
            <span>Business Management</span>
          </button>

          <!-- Service Providers -->
          <button
            @click="activeMenu='providers'"
            class="w-full flex items-center space-x-3 px-4 py-3 rounded-lg transition"
            :class="activeMenu==='providers'
              ? 'bg-gradient-to-r from-blue-600 to-indigo-600 text-white shadow'
              : 'text-gray-700 hover:bg-blue-50'">
            <span>🛠️</span>
            <span>Service Providers</span>
          </button>

          <!-- Reports -->
          <button
            @click="activeMenu='reports'"
            class="w-full flex items-center space-x-3 px-4 py-3 rounded-lg transition"
            :class="activeMenu==='reports'
              ? 'bg-gradient-to-r from-blue-600 to-indigo-600 text-white shadow'
              : 'text-gray-700 hover:bg-blue-50'">
            <span>📊</span>
            <span>Reports</span>
          </button>

          <!-- Settings -->
          <button
            @click="activeMenu='settings'"
            class="w-full flex items-center space-x-3 px-4 py-3 rounded-lg transition"
            :class="activeMenu==='settings'
              ? 'bg-gradient-to-r from-blue-600 to-indigo-600 text-white shadow'
              : 'text-gray-700 hover:bg-blue-50'">
            <span>⚙️</span>
            <span>Settings</span>
          </button>

        </nav>

        <div class="mt-auto p-4 text-xs text-gray-400">
          © 2026 Admin System
        </div>
      </aside>

      <!-- CONTENT -->
      <main class="flex-1 p-4 sm:p-6">
        <UserManagement v-if="activeMenu==='users'" />
        <BusinessManagement v-if="activeMenu==='businesses'" />
        <ServiceProviderManagement v-if="activeMenu==='providers'" />
        <ReportsDashboard v-if="activeMenu==='reports'" />
        <SettingsDashboard v-if="activeMenu==='settings'" />
      </main>

    </div>
  </div>
</template>

<script>
import axios from 'axios'
import Swal from 'sweetalert2'
import UserManagement from './UserManagement.vue'
import BusinessManagement from './BusinessManagement.vue'
import ServiceProviderManagement from './ServiceProviderManagement.vue'
import ReportsDashboard from './ReportsDashboard.vue'
import SettingsDashboard from './SettingsDashboard.vue'

export default {
  components: {
    UserManagement,
    BusinessManagement,
    ServiceProviderManagement,
    ReportsDashboard,
    SettingsDashboard
  },

  data() {
    return {
      activeMenu: 'users'
    }
  },

  methods: {
    confirmLogout() {
      Swal.fire({
        title: 'Logout?',
        text: 'Are you sure you want to logout?',
        icon: 'warning',
        showCancelButton: true,
        confirmButtonColor: '#2563eb',
        cancelButtonColor: '#d33',
        confirmButtonText: 'Yes, logout'
      }).then(result => {
        if (result.isConfirmed) {
          axios.post('/logout')
            .then(() => {
              Swal.fire(
                'Logged out',
                'You have been logged out successfully.',
                'success'
              ).then(() => {
                window.location.href = '/login'
              })
            })
            .catch(() => {
              Swal.fire('Error', 'Logout failed', 'error')
            })
        }
      })
    }
  }
}
</script>
