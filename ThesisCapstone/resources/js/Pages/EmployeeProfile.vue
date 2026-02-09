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
          <h2 class="text-xl font-semibold text-slate-900">My Profile</h2>
          <div v-if="loading" class="mt-4 text-sm text-slate-500">Loading profile...</div>
          <form v-else class="mt-4 grid gap-4 md:grid-cols-2" @submit.prevent="saveProfile">
            <input v-model="profile.first_name" type="text" placeholder="First Name" class="input" required />
            <input v-model="profile.middle_initial" type="text" placeholder="Middle Initial" class="input" maxlength="1" />
            <input v-model="profile.last_name" type="text" placeholder="Last Name" class="input" required />
            <input
              v-model="profile.contact_number"
              type="text"
              inputmode="numeric"
              placeholder="Contact Number"
              maxlength="11"
              @input="onContactInput"
              class="input"
            />
            <input v-model="profile.email" type="email" placeholder="Email" class="input md:col-span-2" disabled />
            <div class="md:col-span-2">
              <button type="submit" class="btn-primary">Save Profile</button>
            </div>
          </form>
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

const activeMenu = ref("My Profile");
const loading = ref(true);
const profile = ref({
  first_name: "",
  middle_initial: "",
  last_name: "",
  email: "",
  contact_number: "",
});

const navigateTo = (menu, url) => {
  activeMenu.value = menu;
  router.visit(url);
};

const fetchProfile = async () => {
  loading.value = true;
  try {
    const res = await axios.get("/employee/dashboard-data");
    profile.value = res.data.profile;
  } catch (err) {
    Swal.fire("Error", "Failed to load profile.", "error");
  } finally {
    loading.value = false;
  }
};

const saveProfile = async () => {
  try {
    const res = await axios.put("/employee/profile", profile.value);
    profile.value = res.data;
    Swal.fire("Saved", "Profile updated.", "success");
  } catch (err) {
    Swal.fire("Error", "Unable to save profile.", "error");
  }
};

const onContactInput = () => {
  const value = String(profile.value.contact_number || "");
  const digitsOnly = value.replace(/\D/g, "").slice(0, 11);
  profile.value.contact_number = digitsOnly;
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

onMounted(fetchProfile);
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
.input { width:100%; border-radius:12px; border:1px solid #e2e8f0; padding:10px 14px; font-size:14px; color:#0f172a; background:#fff; }
.btn-primary { background:#0f172a; color:#fff; border-radius:999px; padding:10px 16px; font-size:14px; font-weight:600; }
</style>
