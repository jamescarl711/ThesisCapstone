<template>
  <div class="layout">
    <aside class="sidebar">
      <h2 class="logo">HRPro</h2>
      <ul>
        <li :class="{ active: activeMenu==='Dashboard' }" @click="navigateTo('Dashboard', '/employee/dashboard')">Dashboard</li>
        <li :class="{ active: activeMenu==='My Profile' }" @click="navigateTo('My Profile', '/employee/profile')">My Profile</li>
        <li :class="{ active: activeMenu==='Notifications' }" @click="navigateTo('Notifications', '/employee/notifications')">Notifications</li>
        <li :class="{ active: activeMenu==='My Payslip' }" @click="navigateTo('My Payslip', '/employee/payslips')">My Payslip</li>
        <li :class="{ active: activeMenu==='Attendance' }" @click="navigateTo('Attendance', '/employee/attendance')">Attendance</li>
        <li :class="{ active: activeMenu==='Assigned Requests' }" @click="navigateTo('Assigned Requests', '/employee/assigned-requests')">Assigned Requests</li>
        <li class="logout-btn" @click="logout">Logout</li>
      </ul>
    </aside>

    <div class="main">
      <nav class="navbar">
        <h1>{{ activeMenu }}</h1>
        <div class="navbar-right" v-if="employee?.name">
          <span class="text-xs uppercase tracking-[0.2em] text-slate-400">Employee</span>
          <span class="text-sm font-semibold text-slate-900">{{ employee.name }}</span>
        </div>
      </nav>

      <div class="dashboard">
        <section class="card">
          <div class="flex items-center justify-between">
            <div>
              <h2 class="text-xl font-semibold text-slate-900">Today</h2>
              <p class="text-sm text-slate-500">Scan the QR to time in or time out for your shift.</p>
            </div>
            <div class="text-xs text-slate-400 hidden md:block">
              
            </div>
          </div>

          <div class="mt-6 grid grid-cols-1 lg:grid-cols-5 gap-6">
            <div class="lg:col-span-2">
              <div class="qr-card">
                <div class="qr-title">Attendance QR</div>
                <div class="qr-description">Scan this QR code to mark your attendance</div>
                <div class="qr-shell">
                  <img v-if="qrImageUrl" :src="qrImageUrl" alt="Attendance QR" class="qr-image" />
                  <div v-else class="qr-fallback">QR unavailable</div>
                </div>
                <div class="qr-hint">
                  <p>Use your phone camera or QR scanner. </p>
                  <p>Tip: keep the QR steady and well-lit for faster scans.</p>
                </div>
              </div>
            </div>
            <div class="lg:col-span-3">
              <div class="calendar-card">
                <div class="calendar-header">
                  <span class="calendar-title">Calendar</span>
                  <span class="calendar-month">{{ calendarLabel }}</span>
                </div>
                <div class="calendar-weekdays">
                  <span v-for="d in weekDays" :key="d">{{ d }}</span>
                </div>
                <div class="calendar-grid">
                  <span
                    v-for="(cell, idx) in calendarCells"
                    :key="idx"
                    :class="[
                      'calendar-cell',
                      cell?.isToday ? 'is-today' : '',
                      cell?.isPast ? 'is-past' : '',
                      !cell?.day ? 'is-empty' : ''
                    ]"
                  >
                    {{ cell?.day || '' }}
                  </span>
                </div>
              </div>
            </div>
          </div>
        </section>

        <section class="card mt-6">
          <div class="flex items-center justify-between">
            <h3 class="text-lg font-semibold text-slate-900">Recent Attendance</h3>
            <button class="btn-light" @click="fetchRecords">Refresh</button>
          </div>

          <div class="mt-4 overflow-x-auto">
            <table class="min-w-full text-sm">
              <thead class="bg-slate-50 text-slate-600">
                <tr>
                  <th class="text-center px-4 py-3 font-semibold">Date</th>
                  <th class="text-center px-4 py-3 font-semibold">Status</th>
                  <th class="text-center px-4 py-3 font-semibold">Time In</th>
                  <th class="text-center px-4 py-3 font-semibold">Time Out</th>
                  <th class="text-center px-4 py-3 font-semibold">Hours</th>
                  <th class="text-center px-4 py-3 font-semibold">Overtime</th>
                </tr>
              </thead>
              <tbody>
                <tr v-if="loading" class="border-t">
                  <td class="px-4 py-4 text-slate-500 text-center" colspan="6">Loading...</td>
                </tr>
                <tr v-else-if="records.length === 0" class="border-t">
                  <td class="px-4 py-4 text-slate-500 text-center" colspan="6">No records yet.</td>
                </tr>
                <tr v-for="row in records" :key="row.id" class="border-t hover:bg-slate-50">
                  <td class="px-4 py-3 text-center">{{ formatDate(row.date) }}</td>
                  <td class="px-4 py-3 text-center">
                    <span :class="statusClass(row.status)" class="px-2 py-1 rounded-full text-xs font-semibold uppercase">
                      {{ row.status }}
                    </span>
                  </td>
                  <td class="px-4 py-3 text-center">{{ row.time_in || '-' }}</td>
                  <td class="px-4 py-3 text-center">{{ row.time_out || '-' }}</td>
                  <td class="px-4 py-3 text-center">{{ row.work_hours ?? '-' }}</td>
                  <td class="px-4 py-3 text-center">{{ formatMinutes(row.overtime_minutes) }}</td>
                </tr>
              </tbody>
            </table>
          </div>
        </section>

      </div>
    </div>
  </div>
</template>

<script setup>
import { onMounted, ref, computed } from "vue";
import { router } from "@inertiajs/vue3";
import axios from "axios";
import Swal from "sweetalert2";

const activeMenu = ref("Attendance");
const employee = ref(null);
const today = ref(null);
const records = ref([]);
const loading = ref(false);
const qr = ref({ token: '', expires_at: '' });
const calendarDate = ref(new Date());
const weekDays = ["Sun", "Mon", "Tue", "Wed", "Thu", "Fri", "Sat"];

const navigateTo = (menu, url) => {
  activeMenu.value = menu;
  router.visit(url);
};

const fetchStatus = async () => {
  const res = await axios.get("/employee/attendance/status");
  employee.value = res.data.employee;
  today.value = res.data.today;
};

const fetchRecords = async () => {
  loading.value = true;
  try {
    const res = await axios.get("/employee/attendance/records");
    records.value = res.data.data.data || [];
  } catch {
    Swal.fire("Error", "Failed to load attendance records.", "error");
  } finally {
    loading.value = false;
  }
};

const qrImageUrl = computed(() => {
  const target = qr.value.scan_url || qr.value.token;
  if (!target) return '';
  const data = encodeURIComponent(target);
  return `https://api.qrserver.com/v1/create-qr-code/?size=280x280&data=${data}`;
});

const calendarLabel = computed(() => {
  return calendarDate.value.toLocaleDateString("en-US", { month: "long", year: "numeric" });
});

const calendarCells = computed(() => {
  const year = calendarDate.value.getFullYear();
  const month = calendarDate.value.getMonth();
  const firstDay = new Date(year, month, 1);
  const start = firstDay.getDay();
  const daysInMonth = new Date(year, month + 1, 0).getDate();
  const todayDate = new Date();
  const todayStart = new Date(todayDate.getFullYear(), todayDate.getMonth(), todayDate.getDate()).getTime();
  const isSameMonth = todayDate.getFullYear() === year && todayDate.getMonth() === month;
  const cells = [];

  for (let i = 0; i < start; i += 1) {
    cells.push({ day: null, isToday: false });
  }
  for (let d = 1; d <= daysInMonth; d += 1) {
    const cellDate = new Date(year, month, d).getTime();
    cells.push({
      day: d,
      isToday: isSameMonth && todayDate.getDate() === d,
      isPast: cellDate < todayStart,
    });
  }
  while (cells.length % 7 !== 0) {
    cells.push({ day: null, isToday: false });
  }
  while (cells.length < 42) {
    cells.push({ day: null, isToday: false });
  }

  return cells;
});

const fetchQr = async (force = false) => {
  const res = await axios.get("/employee/attendance/qr", { params: { force } });
  qr.value = res.data;
};

const statusClass = (status) => {
  if (status === "present") return "bg-emerald-100 text-emerald-700";
  if (status === "late") return "bg-amber-100 text-amber-700";
  if (status === "absent") return "bg-rose-100 text-rose-700";
  return "bg-slate-200 text-slate-700";
};

const formatDate = (val) => {
  if (!val) return "";
  return new Date(val).toLocaleDateString();
};

const formatMinutes = (val) => {
  if (val === null || val === undefined || val === '') return '-';
  const total = Number(val);
  if (Number.isNaN(total)) return '-';
  const h = Math.floor(total / 60);
  const m = total % 60;
  const hh = String(h).padStart(2, '0');
  const mm = String(m).padStart(2, '0');
  return `${hh}:${mm}`;
};

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
    await axios.post("/logout");
    window.location.href = "/login";
  } catch (err) {
    console.error(err);
    Swal.fire("Error", "Logout failed", "error");
  }
};

onMounted(async () => {
  await fetchStatus();
  await fetchRecords();
  await fetchQr();
});
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
.navbar { background:#fff; padding:16px; border-bottom:1px solid #e5e7eb; display:flex; align-items:center; justify-content:space-between; }
.navbar-right { text-align:right; display:flex; flex-direction:column; gap:2px; }
.dashboard { padding:24px; }
.card { background:#fff; padding:24px; border-radius:16px; box-shadow:0 10px 20px rgba(0,0,0,.08); }
.stat-card { background:#f8fafc; border:1px solid #e2e8f0; border-radius:12px; padding:16px; }
.stat-label { font-size:0.7rem; letter-spacing:0.2em; text-transform:uppercase; color:#94a3b8; }
.stat-value { margin-top:6px; font-size:1rem; font-weight:600; color:#0f172a; }
.btn-primary { background:#0f172a; color:#fff; padding:8px 14px; border-radius:8px; font-size:0.85rem; }
.btn-primary:disabled { opacity:0.5; cursor:not-allowed; }
.btn-secondary { background:#fff; border:1px solid #e2e8f0; color:#0f172a; padding:8px 14px; border-radius:8px; font-size:0.85rem; }
.btn-secondary:disabled { opacity:0.5; cursor:not-allowed; }
.btn-submit { background:#14b8a6; color:#fff; padding:8px 14px; border-radius:8px; font-size:0.85rem; }
.btn-submit:disabled { opacity:0.5; cursor:not-allowed; }
.btn-light { border:1px solid #e2e8f0; padding:6px 12px; border-radius:8px; font-size:0.8rem; }
.qr-card { border:1px solid #e2e8f0; border-radius:10px; padding:10px; background:#f8fafc; }
.qr-title { font-size:0.8rem; font-weight:600; color:#0f172a; margin-bottom:10px; }
.qr-shell { background:#fff; border:1px solid #e2e8f0; border-radius:12px; padding:10px; display:flex; align-items:center; justify-content:center; }
.qr-description { font-size:0.75rem; color:#475569; margin-bottom:8px; }
.qr-image { width:220px; height:220px; object-fit:contain; image-rendering:pixelated; }
.qr-fallback { width:220px; height:220px; display:flex; align-items:center; justify-content:center; font-size:12px; color:#94a3b8; }
.qr-meta { margin-top:10px; font-size:12px; color:#475569; display:flex; justify-content:space-between; }
.qr-hint { margin-top:6px; font-size:11px; color:#94a3b8; }
.calendar-card { border:1px solid #e2e8f0; border-radius:16px; padding:14px; background:#ffffff; }
.calendar-header { display:flex; align-items:center; justify-content:space-between; margin-bottom:10px; }
.calendar-title { font-size:0.8rem; font-weight:600; color:#0f172a; }
.calendar-month { font-size:0.8rem; color:#64748b; }
.calendar-weekdays { display:grid; grid-template-columns:repeat(7, 1fr); gap:6px; font-size:11px; color:#94a3b8; text-align:center; margin-bottom:8px; }
.calendar-grid { display:grid; grid-template-columns:repeat(7, 1fr); gap:6px; }
.calendar-cell { height:32px; border-radius:8px; display:flex; align-items:center; justify-content:center; font-size:12px; color:#0f172a; background:#f8fafc; }
.calendar-cell.is-empty { background:transparent; }
.calendar-cell.is-today { background:#0f172a; color:#ffffff; font-weight:600; }
.calendar-cell.is-past {
  position: relative;
  color: #94a3b8;
}

</style>
