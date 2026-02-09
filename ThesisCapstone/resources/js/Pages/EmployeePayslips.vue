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
          <h2 class="text-xl font-semibold text-slate-900">My Payslip</h2>
          <div v-if="loading" class="mt-4 text-sm text-slate-500">Loading payslips...</div>
          <div v-else-if="payrolls.length === 0" class="mt-4 text-sm text-slate-500">No payslips yet.</div>
          <div v-else class="mt-4 space-y-4">
            <div v-for="pay in payrolls" :key="pay.id" class="rounded-2xl border border-slate-200 bg-white">
              <button type="button"
                      class="w-full px-5 py-4 text-left flex items-center justify-between"
                      @click="togglePayroll(pay.id)">
                <div>
                  <p class="text-sm font-semibold text-slate-900">Payslip - {{ pay.created_at }}</p>
                  <p class="text-xs text-slate-500">
                    Pay Date: {{ formatDate(pay.created_at) }} | Present {{ pay.days_present }} | Absent {{ pay.days_absent }} | Late {{ pay.late_days }}
                  </p>
                </div>
                <span class="text-xs font-semibold text-slate-600">
                  {{ expandedPayrolls[pay.id] ? "Hide" : "View" }}
                </span>
              </button>

              <div v-if="expandedPayrolls[pay.id]" class="border-t border-slate-200 px-5 py-5">
                <div class="grid gap-4 md:grid-cols-2 xl:grid-cols-4">
                  <div class="pay-card">
                    <p class="label">Days Present</p>
                    <p class="value">{{ pay.days_present }}</p>
                  </div>
                  <div class="pay-card">
                    <p class="label">Days Absent</p>
                    <p class="value">{{ pay.days_absent }}</p>
                  </div>
                  <div class="pay-card">
                    <p class="label">Late Days</p>
                    <p class="value">{{ pay.late_days }}</p>
                  </div>
                  <div class="pay-card dark">
                    <p class="label">Total Working Days</p>
                    <p class="value">{{ pay.days_present }} / {{ totalWorkingDays }}</p>
                  </div>
                </div>

                <div class="mt-6">
                  <div class="flex items-center justify-between mb-3">
                    <p class="text-sm font-semibold text-slate-700">Income / Salary</p>
                    <span class="text-xs text-slate-500">Computed fields</span>
                  </div>
                  <div class="grid gap-4 md:grid-cols-2 xl:grid-cols-4">
                    <div class="pay-card">
                      <p class="label">Daily Rate</p>
                      <p class="value">{{ dailyRate }}</p>
                    </div>
                    <div class="pay-card">
                      <p class="label">Basic Salary</p>
                      <p class="value">{{ basicSalary(pay).toFixed(2) }}</p>
                    </div>
                    <div class="pay-card">
                      <p class="label">Overtime & Holiday</p>
                      <p class="value">{{ overtimeHoliday.toFixed(2) }}</p>
                    </div>
                    <div class="pay-card">
                      <p class="label">Bonuses & Incentives</p>
                      <p class="value">{{ bonuses(pay).toFixed(2) }}</p>
                    </div>
                    <div class="pay-card">
                      <p class="label">Transport Allowance</p>
                      <p class="value">{{ Number(pay.transport_allowance).toFixed(2) }}</p>
                    </div>
                    <div class="pay-card">
                      <p class="label">Meal Allowance</p>
                      <p class="value">{{ Number(pay.meal_allowance).toFixed(2) }}</p>
                    </div>
                    <div class="pay-card highlight">
                      <p class="label">Total Income</p>
                      <p class="value">{{ totalIncome(pay).toFixed(2) }}</p>
                    </div>
                  </div>
                </div>

                <div class="mt-6">
                  <p class="text-sm font-semibold text-slate-700 mb-3">Deductions</p>
                  <div class="grid gap-4 md:grid-cols-2 xl:grid-cols-4">
                    <div class="pay-card">
                      <p class="label">Salary Loan</p>
                      <p class="value">{{ Number(pay.salary_loan).toFixed(2) }}</p>
                    </div>
                    <div class="pay-card">
                      <p class="label">SSS Loan</p>
                      <p class="value">{{ Number(pay.sss_loan).toFixed(2) }}</p>
                    </div>
                    <div class="pay-card">
                      <p class="label">Health Insurance</p>
                      <p class="value">{{ Number(pay.health_insurance).toFixed(2) }}</p>
                    </div>
                    <div class="pay-card">
                      <p class="label">Life Insurance</p>
                      <p class="value">{{ Number(pay.life_insurance).toFixed(2) }}</p>
                    </div>
                    <div class="pay-card">
                      <p class="label">Leave Without Pay</p>
                      <p class="value">{{ leaveWithoutPay(pay).toFixed(2) }}</p>
                    </div>
                    <div class="pay-card">
                      <p class="label">Tardiness / Late Arrival</p>
                      <p class="value">{{ tardinessPenalty(pay).toFixed(2) }}</p>
                    </div>
                    <div class="pay-card">
                      <p class="label">Other Deductions</p>
                      <p class="value">0.00</p>
                    </div>
                    <div class="pay-card danger">
                      <p class="label">Total Deductions</p>
                      <p class="value">{{ totalDeductions(pay).toFixed(2) }}</p>
                    </div>
                  </div>
                </div>

                <div class="mt-6 net-pay">
                  <span>NET PAY</span>
                  <span class="amount">{{ netPay(pay).toFixed(2) }}</span>
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

const activeMenu = ref("My Payslip");
const loading = ref(true);
const profile = ref({
  first_name: "",
  middle_initial: "",
  last_name: "",
  email: "",
  contact_number: "",
});
const payrolls = ref([]);
const expandedPayrolls = ref({});

const totalWorkingDays = 15;
const dailyRate = 500;
const overtimeHoliday = 150;

const navigateTo = (menu, url) => {
  activeMenu.value = menu;
  router.visit(url);
};

const num = (value) => {
  const n = Number(value);
  return Number.isFinite(n) ? n : 0;
};

const basicSalary = (pay) => num(pay.days_present) * dailyRate;
const bonuses = (pay) => num(pay.days_present) * 100;
const leaveWithoutPay = (pay) => num(pay.days_absent) * 150;
const tardinessPenalty = (pay) => num(pay.late_days) * 100;

const totalIncome = (pay) =>
  basicSalary(pay) +
  overtimeHoliday +
  bonuses(pay) +
  num(pay.transport_allowance) +
  num(pay.meal_allowance);

const totalDeductions = (pay) =>
  num(pay.salary_loan) +
  num(pay.sss_loan) +
  num(pay.health_insurance) +
  num(pay.life_insurance) +
  leaveWithoutPay(pay) +
  tardinessPenalty(pay);

const netPay = (pay) => totalIncome(pay) - totalDeductions(pay);

const togglePayroll = (id) => {
  expandedPayrolls.value = {
    ...expandedPayrolls.value,
    [id]: !expandedPayrolls.value[id],
  };
};

const formatDate = (value) => {
  if (!value) return "N/A";
  const date = new Date(value);
  if (Number.isNaN(date.getTime())) return value;
  return date.toLocaleDateString("en-US", { month: "short", day: "numeric", year: "numeric" });
};

const fetchPayslips = async () => {
  loading.value = true;
  try {
    const res = await axios.get("/employee/dashboard-data");
    profile.value = res.data.profile;
    payrolls.value = res.data.payrolls || [];
  } catch (err) {
    Swal.fire("Error", "Failed to load payslips.", "error");
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

onMounted(fetchPayslips);
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
.pay-card { border:1px solid #e2e8f0; border-radius:14px; padding:14px; background:#fff; }
.pay-card .label { font-size:11px; text-transform:uppercase; letter-spacing:.2em; color:#94a3b8; }
.pay-card .value { margin-top:6px; font-size:16px; font-weight:600; color:#0f172a; }
.pay-card.dark { background:#0f172a; border-color:#0f172a; }
.pay-card.dark .label, .pay-card.dark .value { color:#fff; }
.pay-card.highlight { background:#ecfdf3; border-color:#86efac; }
.pay-card.highlight .value { color:#166534; }
.pay-card.danger { background:#fef2f2; border-color:#fecaca; }
.pay-card.danger .value { color:#b91c1c; }
.net-pay { background:linear-gradient(90deg,#0f172a,#111827); color:#fff; border-radius:16px; padding:18px 20px; display:flex; align-items:center; justify-content:space-between; font-weight:700; }
.net-pay .amount { font-size:22px; }
</style>
