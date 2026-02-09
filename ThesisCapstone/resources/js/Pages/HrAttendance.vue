<template>
  <div class="layout">
    <!-- Sidebar -->
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

    <!-- Main -->
    <div class="main">
      <nav class="navbar">
        <h1>{{ activeMenu }}</h1>
      </nav>

      <main class="dashboard">
        <div class="flex flex-col md:flex-row md:items-center md:justify-between gap-4 mb-6">
          <div>
            <h1 class="text-2xl font-semibold text-slate-900">Attendance Management</h1>
            <p class="text-sm text-slate-500">Track daily attendance and update employee work hours.</p>
          </div>

        </div>


        <!-- Filters removed per request -->

        <!-- Table -->
        <div class="bg-white rounded-xl shadow-sm border border-slate-200 overflow-hidden">
          <div class="overflow-x-auto">
            <table class="min-w-full text-sm">
              <thead class="bg-slate-50 text-slate-600">
                <tr>
                  <th class="text-left px-4 py-3 font-semibold">Employee</th>
                  <th class="text-left px-4 py-3 font-semibold">Team</th>
                  <th class="text-left px-4 py-3 font-semibold">Date</th>
                  <th class="text-left px-4 py-3 font-semibold">Status</th>
                  <th class="text-left px-4 py-3 font-semibold">Time In</th>
                  <th class="text-left px-4 py-3 font-semibold">Time Out</th>
                  <th class="text-left px-4 py-3 font-semibold">Hours</th>
                  <th class="text-left px-4 py-3 font-semibold">Overtime</th>
                  <th class="text-right px-4 py-3 font-semibold">Actions</th>
                </tr>
              </thead>
              <tbody>
                <tr v-if="loading" class="border-t">
                  <td class="px-4 py-6 text-center text-slate-500" colspan="9">Loading attendance...</td>
                </tr>
                <tr v-else-if="records.length === 0" class="border-t">
                  <td class="px-4 py-6 text-center text-slate-500" colspan="9">No attendance records found.</td>
                </tr>
                <tr v-for="row in records" :key="row.id" class="border-t hover:bg-slate-50">
                  <td class="px-4 py-3">
                    <div class="font-medium text-slate-900">{{ row.employee?.name }}</div>
                    <div class="text-xs text-slate-500">{{ row.employee?.email || '—' }}</div>
                  </td>
                  <td class="px-4 py-3 text-slate-600">{{ row.employee?.team || '—' }}</td>
                  <td class="px-4 py-3 text-slate-600">{{ formatDate(row.date) }}</td>
                  <td class="px-4 py-3">
                    <span :class="statusClass(row.status)" class="px-2 py-1 rounded-full text-xs font-semibold uppercase">
                      {{ row.status }}
                    </span>
                  </td>
                  <td class="px-4 py-3 text-slate-600">{{ row.time_in || '—' }}</td>
                  <td class="px-4 py-3 text-slate-600">{{ row.time_out || '—' }}</td>
                  <td class="px-4 py-3 text-slate-600">{{ row.work_hours ?? '—' }}</td>
                  <td class="px-4 py-3 text-center">
                    <div class="flex items-end justify-center gap-3">
                      <div class="flex flex-col items-center gap-1">
                        <span class="text-[10px] uppercase tracking-widest text-slate-500">H</span>
                        <input
                          v-model="overtimeEdits[row.id].h"
                          type="number"
                          min="0"
                          max="24"
                          step="1"
                          class="w-16 rounded border border-slate-200 px-2 py-1 text-sm disabled:bg-slate-100 disabled:text-slate-400 text-center"
                          placeholder="0"
                          :disabled="!row.time_out"
                        />
                      </div>
                      <div class="flex flex-col items-center gap-1">
                        <span class="text-[10px] uppercase tracking-widest text-slate-500">M</span>
                        <input
                          v-model="overtimeEdits[row.id].m"
                          type="number"
                          min="0"
                        max="59"
                        step="10"
                        class="w-16 rounded border border-slate-200 px-2 py-1 text-sm disabled:bg-slate-100 disabled:text-slate-400 text-center"
                        placeholder="0"
                        :disabled="!row.time_out"
                      />
                      </div>
                    </div>
                  </td>
                  <td class="px-4 py-3 text-right">
                    <button
                      class="px-3 py-1 rounded bg-slate-900 text-white text-xs disabled:bg-slate-300 disabled:text-slate-500"
                      @click="saveOvertime(row)"
                      :disabled="!row.time_out"
                    >
                      Save
                    </button>
                  </td>
                </tr>
              </tbody>
            </table>
          </div>
          <div class="flex items-center justify-between px-4 py-3 text-xs text-slate-500 border-t">
            <span>Showing {{ pagination.from || 0 }} to {{ pagination.to || 0 }} of {{ pagination.total || 0 }}</span>
            <div class="space-x-2">
              <button class="px-2 py-1 border rounded" :disabled="!pagination.prev_page_url" @click="fetchAttendance(pagination.prev_page_url)">Prev</button>
              <button class="px-2 py-1 border rounded" :disabled="!pagination.next_page_url" @click="fetchAttendance(pagination.next_page_url)">Next</button>
            </div>
          </div>
        </div>

        <!-- Read-only: no modal -->
      </main>
    </div>
  </div>
</template>

<script setup>
import { onMounted, ref } from 'vue'
import { router } from '@inertiajs/vue3'
import axios from 'axios'
import Swal from 'sweetalert2'

const employees = ref([])
const records = ref([])
const pagination = ref({})
const overtimeEdits = ref({})
const loading = ref(false)

const filters = ref({
  employee_id: '',
  status: '',
  date_from: '',
  date_to: ''
})


const activeMenu = ref('Attendance Management')
const navigateTo = (menu, url) => {
  activeMenu.value = menu
  router.visit(url)
}

const fetchEmployees = async () => {
  const res = await axios.get('/hr/attendance/employees')
  employees.value = res.data
}

const fetchAttendance = async (url = '/hr/attendance/records') => {
  loading.value = true
  try {
    const res = await axios.get(url, { params: filters.value })
    records.value = res.data.data.data || []
    pagination.value = res.data.data
    records.value.forEach((row) => {
      if (!overtimeEdits.value[row.id]) {
        const totalMinutes = row.overtime_minutes ?? null
        if (totalMinutes === null || totalMinutes === '') {
          overtimeEdits.value[row.id] = { h: '', m: '' }
          return
        }
        const h = Math.floor(Number(totalMinutes) / 60)
        const m = Number(totalMinutes) % 60
        overtimeEdits.value[row.id] = { h: String(h), m: String(m) }
      }
    })
  } catch (e) {
    Swal.fire('Error', 'Failed to load attendance records', 'error')
  } finally {
    loading.value = false
  }
}

const saveOvertime = async (row) => {
  try {
    const entry = overtimeEdits.value[row.id] || { h: '', m: '' }
    const hRaw = entry.h === '' ? 0 : Number(entry.h)
    const mRaw = entry.m === '' ? 0 : Number(entry.m)

    if (Number.isNaN(hRaw) || Number.isNaN(mRaw) || hRaw < 0 || mRaw < 0 || mRaw > 59) {
      Swal.fire('Error', 'Overtime must be valid hours (H) and minutes (M).', 'error')
      return
    }
    if (mRaw % 10 !== 0) {
      Swal.fire('Error', 'Minutes must be in 10-minute increments.', 'error')
      return
    }

    const isEmpty = entry.h === '' && entry.m === ''
    const totalMinutes = (hRaw * 60) + mRaw
    if (!isEmpty && totalMinutes < 10) {
      Swal.fire('Error', 'Overtime must be at least 10 minutes.', 'error')
      return
    }
    const overtimeMinutes = isEmpty ? null : totalMinutes

    await axios.patch(`/hr/attendance/${row.id}`, {
      overtime_minutes: overtimeMinutes,
    })
    row.overtime_minutes = overtimeMinutes
    Swal.fire('Saved', 'Overtime updated.', 'success')
  } catch (e) {
    Swal.fire('Error', 'Failed to update overtime.', 'error')
  }
}


const resetFilters = () => {
  filters.value = { employee_id: '', status: '', date_from: '', date_to: '' }
  fetchAttendance()
}


const statusClass = (status) => {
  if (status === 'present') return 'bg-emerald-100 text-emerald-700'
  if (status === 'late') return 'bg-amber-100 text-amber-700'
  if (status === 'absent') return 'bg-rose-100 text-rose-700'
  return 'bg-slate-200 text-slate-700'
}

const formatDate = (val) => {
  if (!val) return ''
  return new Date(val).toLocaleDateString()
}

onMounted(() => {
  fetchEmployees()
  fetchAttendance()
})

const logout = async () => {
  const result = await Swal.fire({
    title: "Logout?",
    text: "Are you sure you want to log out?",
    icon: "warning",
    showCancelButton: true,
    confirmButtonColor: "#0f172a",
    cancelButtonColor: "#94a3b8",
    confirmButtonText: "Yes, logout",
  });
  if (!result.isConfirmed) return;
  try {
    await axios.post('/logout');
    window.location.href = '/login';
  } catch(err) {
    console.error(err);
    Swal.fire('Error','Logout failed','error');
  }
}
</script>

<style scoped>
.fade-enter-active, .fade-leave-active { transition: opacity .2s ease; }
.fade-enter-from, .fade-leave-to { opacity: 0; }
.layout { display:flex; min-height:100vh; font-family:Inter, sans-serif; }
.sidebar { width:220px; background:#0f172a; color:#fff; padding:20px; }
.logo { font-weight:700; font-size:1.5rem; text-align:center; margin-bottom:24px; }
.sidebar ul { list-style:none; padding:0; }
.sidebar li { padding:12px; border-radius:8px; cursor:pointer; margin-bottom:6px; }
.sidebar li.active, .sidebar li:hover { background:#1e293b; }
.sidebar .logout-btn { margin-top:20px; background:#ef4444; text-align:center; }
.main { flex:1; display:flex; flex-direction:column; }
.navbar { background:#fff; padding:16px; border-bottom:1px solid #e5e7eb; }
.dashboard { padding:24px; background:#f1f5f9; min-height:100vh; }
</style>
