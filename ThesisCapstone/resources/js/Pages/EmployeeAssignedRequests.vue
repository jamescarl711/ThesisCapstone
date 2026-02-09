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
          <h2 class="text-xl font-semibold text-slate-900">Assigned Requests</h2>
          <div v-if="loading" class="mt-4 text-sm text-slate-500">Loading requests...</div>
          <div v-else-if="assignedRequests.length === 0" class="mt-4 text-sm text-slate-500">No assigned requests.</div>
          <div v-else class="mt-4 space-y-3">
            <div v-for="req in assignedRequests" :key="req.id" class="rounded-xl border border-slate-200 bg-slate-50 p-4">
              <p class="text-sm font-semibold text-slate-900">{{ req.service_type }}</p>
              <p class="text-xs text-slate-500">{{ req.address_text }}</p>
              <p class="text-xs text-slate-500">Preferred: {{ req.preferred_date || 'TBD' }}</p>
              <div class="mt-2 flex flex-wrap items-center gap-2">
                <span
                  v-if="req.status !== 'assigned'"
                  class="inline-block rounded-full bg-slate-900 px-2 py-1 text-[11px] font-semibold uppercase tracking-wide text-white"
                >
                  {{ req.status }}
                </span>
                <div v-else class="flex gap-2">
                  <button
                    class="bg-emerald-600 hover:bg-emerald-700 text-white px-3 py-1 rounded-md text-xs font-semibold"
                    @click="updateRequest(req, 'accepted')"
                  >
                    Accept
                  </button>
                  <button
                    class="bg-rose-600 hover:bg-rose-700 text-white px-3 py-1 rounded-md text-xs font-semibold"
                    @click="updateRequest(req, 'rejected')"
                  >
                    Reject
                  </button>
                </div>
              </div>
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

const activeMenu = ref("Assigned Requests");
const loading = ref(true);
const profile = ref({
  first_name: "",
  middle_initial: "",
  last_name: "",
  email: "",
  contact_number: "",
});
const assignedRequests = ref([]);

const navigateTo = (menu, url) => {
  activeMenu.value = menu;
  router.visit(url);
};

const fetchAssignedRequests = async () => {
  loading.value = true;
  try {
    const res = await axios.get("/employee/dashboard-data");
    profile.value = res.data.profile;
    assignedRequests.value = res.data.assigned_requests || [];
  } catch (err) {
    Swal.fire("Error", "Failed to load assigned requests.", "error");
  } finally {
    loading.value = false;
  }
};

const updateRequest = async (req, status) => {
  const confirm = await Swal.fire({
    title: status === 'accepted' ? 'Accept this request?' : 'Reject this request?',
    icon: 'warning',
    showCancelButton: true,
    confirmButtonColor: '#0f172a',
    cancelButtonColor: '#94a3b8',
  });
  if (!confirm.isConfirmed) return;

  try {
    await axios.patch(`/employee/assigned-requests/${req.id}`, { status });
    await fetchAssignedRequests();
    Swal.fire('Success', `Request ${status}.`, 'success');
  } catch (err) {
    Swal.fire('Error', err.response?.data?.message || 'Failed to update request.', 'error');
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

onMounted(fetchAssignedRequests);
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
