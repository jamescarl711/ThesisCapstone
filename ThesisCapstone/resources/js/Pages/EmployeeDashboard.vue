<template>
  <div class="layout">
    <aside class="sidebar">
      <h2 class="logo">HRPro</h2>
      <ul>
        <li :class="{ active: activeMenu==='Dashboard' }" @click="navigateTo('Dashboard', '/employee/dashboard')">Dashboard</li>
        <li :class="{ active: activeMenu==='My Profile' }" @click="navigateTo('My Profile', '/employee/profile')">My Profile</li>
        <li :class="{ active: activeMenu==='Notifications' }" @click="navigateTo('Notifications', '/employee/notifications')">Notifications</li>
        <li :class="{ active: activeMenu==='My Payslip' }" @click="navigateTo('My Payslip', '/employee/payslips')">My Payslip</li>
        <li :class="{ active: activeMenu==='Assigned Requests' }" @click="navigateTo('Assigned Requests', '/employee/assigned-requests')">Assigned Requests</li>
        <li class="logout-btn" @click="logout">Logout</li>
      </ul>
    </aside>

    <div class="main">
      <nav class="navbar">
        <h1>{{ activeMenu }}</h1>
        <div class="navbar-right" v-if="profile.first_name || profile.last_name">
          <span class="text-xs uppercase tracking-[0.2em] text-slate-400">Welcome</span>
          <span class="text-sm font-semibold text-slate-900">
            {{ profile.first_name }} {{ profile.last_name }}
          </span>
        </div>
      </nav>

      <div class="dashboard">
        <section class="card">
          <h2 class="text-xl font-semibold text-slate-900">Welcome</h2>
          <p class="mt-2 text-sm text-slate-500">
            Your employee account is ready. Updates and tasks will appear here.
          </p>

          <div class="mt-6 grid gap-4 md:grid-cols-2">
            <div class="rounded-xl border border-slate-200 bg-slate-50 p-4">
              <p class="text-xs uppercase tracking-[0.2em] text-slate-400">Status</p>
              <p class="mt-2 text-sm font-semibold text-slate-900">{{ employeeData?.status || 'Active' }}</p>
            </div>
            <div class="rounded-xl border border-slate-200 bg-slate-50 p-4">
              <p class="text-xs uppercase tracking-[0.2em] text-slate-400">Team</p>
              <p class="mt-2 text-sm text-slate-600">{{ employeeData?.team || 'Unassigned' }}</p>
            </div>
          </div>
        </section>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref, onMounted } from "vue";
import { router } from "@inertiajs/vue3";
import axios from "axios";
import Swal from "sweetalert2";

const activeMenu = ref("Dashboard");
const loading = ref(true);
const profile = ref({
  first_name: "",
  middle_initial: "",
  last_name: "",
  email: "",
  contact_number: "",
});
const employeeData = ref(null);

const navigateTo = (menu, url) => {
  activeMenu.value = menu;
  router.visit(url);
};

const fetchDashboardData = async () => {
  loading.value = true;
  try {
    const res = await axios.get("/employee/dashboard-data");
    profile.value = res.data.profile;
    employeeData.value = res.data.employee;
  } catch (err) {
    Swal.fire("Error", "Failed to load dashboard data.", "error");
  } finally {
    loading.value = false;
  }
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

onMounted(() => {
  fetchDashboardData();
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
.navbar { background:#fff; padding:16px; border-bottom:1px solid #e5e7eb; }
.navbar { display:flex; align-items:center; justify-content:space-between; }
.navbar-right { text-align:right; display:flex; flex-direction:column; gap:2px; }
.dashboard { padding:24px; }
.card { background:#fff; padding:24px; border-radius:16px; box-shadow:0 10px 20px rgba(0,0,0,.08); }
</style>
