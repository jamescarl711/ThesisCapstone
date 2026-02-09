<template>
  <div class="layout">
    <!-- Sidebar -->
    <aside class="sidebar">
      <h2 class="logo">HRPro</h2>
      <ul>
        <li :class="{ active: activeMenu==='Dashboard' }" @click="changeMenu('Dashboard')">Dashboard</li>
        <li :class="{ active: activeMenu==='Assigned Requests' }" @click="navigateTo('Assigned Requests', '/hr/assigned-requests')">Assigned Requests</li>
        <li :class="{ active: activeMenu==='Accepted Requests' }" @click="navigateTo('Accepted Requests', '/hr/accepted-requests')">Accepted Requests</li>
        <li :class="{ active: activeMenu==='Rejected Requests' }" @click="navigateTo('Rejected Requests', '/hr/rejected-requests')">Rejected Requests</li>
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

      <div class="dashboard">
        <!-- Dashboard -->
        <section v-if="activeMenu==='Dashboard'">
          <div class="flex flex-col gap-6">
            <div class="flex flex-col gap-4 lg:flex-row lg:items-center lg:justify-between">
              <div>
                <p class="text-xs uppercase tracking-[0.2em] text-slate-400">Plumbing &amp; Siphoning Suite</p>
                <h2 class="text-2xl font-semibold text-slate-900">Field Operations &amp; Crew Management</h2>
                <p class="text-sm text-slate-500 mt-1">Coordinate crews, service calls, and technical personnel with confidence.</p>
              </div>
              <div class="flex flex-wrap gap-3">
                <button type="button"
                        class="rounded-full bg-white px-4 py-2 text-sm font-medium text-slate-700 shadow-sm ring-1 ring-slate-200 transition hover:bg-slate-100"
                        @click="exportServiceSummary">
                  Export Service Summary
                </button>
                <button type="button"
                        class="rounded-full bg-slate-900 px-4 py-2 text-sm font-medium text-white shadow-sm transition hover:bg-slate-800"
                        @click="createDispatchReport">
                  Create Dispatch Report
                </button>
              </div>
            </div>

            <div class="grid gap-4 md:grid-cols-2 xl:grid-cols-4">
              <div class="rounded-2xl bg-white p-5 shadow-sm ring-1 ring-slate-200">
                <p class="text-xs uppercase tracking-[0.2em] text-slate-400">Total Technicians</p>
                <div class="mt-3 flex items-center justify-between">
                  <h3 class="text-2xl font-semibold text-slate-900">{{ stats.totalEmployees }}</h3>
                  <span class="rounded-full bg-emerald-50 px-2 py-1 text-xs font-medium text-emerald-600">+{{ stats.monthlyGrowth }}%</span>
                </div>
                <p class="mt-2 text-xs text-slate-500">Active, probationary, and on-call technicians.</p>
              </div>
              <div class="rounded-2xl bg-white p-5 shadow-sm ring-1 ring-slate-200">
                <p class="text-xs uppercase tracking-[0.2em] text-slate-400">Open Roles</p>
                <div class="mt-3 flex items-center justify-between">
                  <h3 class="text-2xl font-semibold text-slate-900">{{ stats.openPositions }}</h3>
                  <span class="rounded-full bg-indigo-50 px-2 py-1 text-xs font-medium text-indigo-600">{{ stats.priorityRoles }} Priority</span>
                </div>
                <p class="mt-2 text-xs text-slate-500">Hiring across field, safety, and dispatch.</p>
              </div>
              <div class="rounded-2xl bg-white p-5 shadow-sm ring-1 ring-slate-200">
                <p class="text-xs uppercase tracking-[0.2em] text-slate-400">Crews on Leave</p>
                <div class="mt-3 flex items-center justify-between">
                  <h3 class="text-2xl font-semibold text-slate-900">{{ stats.onLeave }}</h3>
                  <span class="rounded-full bg-amber-50 px-2 py-1 text-xs font-medium text-amber-600">{{ stats.leaveType }}</span>
                </div>
                <p class="mt-2 text-xs text-slate-500">Planned leave and approved absences.</p>
              </div>
              <div class="rounded-2xl bg-white p-5 shadow-sm ring-1 ring-slate-200">
                <p class="text-xs uppercase tracking-[0.2em] text-slate-400">Safety Compliance</p>
                <div class="mt-3 flex items-center justify-between">
                  <h3 class="text-2xl font-semibold text-slate-900">{{ stats.complianceRate }}%</h3>
                  <span class="rounded-full bg-slate-100 px-2 py-1 text-xs font-medium text-slate-600">{{ stats.auditWindow }}</span>
                </div>
                <p class="mt-2 text-xs text-slate-500">PPE and safety training completion.</p>
              </div>
            </div>

            <div class="grid gap-6 lg:grid-cols-2">
              <section class="rounded-2xl bg-white p-6 shadow-sm ring-1 ring-slate-200">
                <h3 class="text-lg font-semibold text-slate-900">Service Operations</h3>
                <p class="text-sm text-slate-500 mt-1">Core tasks and critical workflows for the week.</p>
                <div class="mt-4 space-y-3">
                  <div v-for="task in operationalTasks" :key="task.id"
                       class="flex items-center justify-between rounded-2xl border border-slate-200 bg-slate-50 px-4 py-3">
                    <div>
                      <p class="text-sm font-semibold text-slate-900">{{ task.title }}</p>
                      <p class="text-xs text-slate-500">{{ task.owner }} - Due {{ task.due }}</p>
                    </div>
                    <span :class="taskBadgeClass(task.priority)"
                          class="rounded-full px-3 py-1 text-xs font-semibold uppercase tracking-wide">{{ task.priority }}</span>
                  </div>
                </div>
              </section>

              <section class="rounded-2xl bg-white p-6 shadow-sm ring-1 ring-slate-200">
                <h3 class="text-lg font-semibold text-slate-900">Team Overview</h3>
                <p class="text-sm text-slate-500 mt-1">Snapshot of field and support coverage.</p>
                <div class="mt-4 space-y-3">
                  <div v-for="team in teamSummary" :key="team.name"
                       class="rounded-2xl border border-slate-200 bg-slate-50 px-4 py-3">
                    <div class="flex items-center justify-between">
                      <p class="text-sm font-semibold text-slate-900">{{ team.name }}</p>
                      <p class="text-xs font-medium text-slate-500">{{ team.count }} people</p>
                    </div>
                    <div class="mt-2 h-2 w-full rounded-full bg-slate-200">
                      <div class="h-2 rounded-full bg-slate-900" :style="{ width: team.coverage + '%' }"></div>
                    </div>
                  </div>
                </div>
              </section>
            </div>

            <div class="grid gap-6 lg:grid-cols-1">
              <section class="rounded-2xl bg-white p-6 shadow-sm ring-1 ring-slate-200">
                <div class="flex items-center justify-between">
                  <h3 class="text-lg font-semibold text-slate-900">Recent Service Activity</h3>
                  <button class="rounded-full border border-slate-200 px-3 py-1 text-xs text-slate-600 transition hover:border-slate-300 hover:text-slate-900">
                    View All
                  </button>
                </div>
                <div class="mt-4 space-y-4">
                  <div v-for="activity in recentActivities" :key="activity.id"
                       class="flex items-start gap-3 rounded-2xl border border-slate-200 bg-slate-50 px-4 py-3">
                    <div class="h-9 w-9 rounded-full bg-white text-sm font-semibold text-slate-700 ring-1 ring-slate-200 flex items-center justify-center">
                      {{ activity.initials }}
                    </div>
                    <div>
                      <p class="text-sm font-semibold text-slate-900">{{ activity.title }}</p>
                      <p class="text-xs text-slate-500">{{ activity.description }}</p>
                      <p class="text-[11px] text-slate-400 mt-1">{{ activity.timestamp }}</p>
                    </div>
                  </div>
                </div>
              </section>
            </div>
          </div>
        </section>

        <!-- Assign Requests + Reports removed per request -->
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref, onMounted } from 'vue';
import { router } from '@inertiajs/vue3';
import axios from 'axios';
import Swal from 'sweetalert2';

const activeMenu = ref('Dashboard');

const loading = ref(true);
const stats = ref({
  totalEmployees: 0,
  monthlyGrowth: 0,
  openPositions: 0,
  priorityRoles: 0,
  onLeave: 0,
  leaveType: "On Leave",
  complianceRate: 0,
  auditWindow: "Safety Audit",
});
const teamSummary = ref([]);
const operationalTasks = ref([]);
const recentActivities = ref([]);

function changeMenu(menu) {
  activeMenu.value = menu;
}

function navigateTo(menu, url) {
  activeMenu.value = menu;
  router.visit(url);
}

const taskBadgeClass = (priority) => {
  if (priority === "High") return "bg-rose-50 text-rose-600";
  if (priority === "Medium") return "bg-amber-50 text-amber-600";
  return "bg-emerald-50 text-emerald-600";
};


const exportServiceSummary = () => {
  try {
    const lines = [
      ["Metric", "Value"],
      ["Total Technicians", stats.value.totalEmployees],
      ["Monthly Growth (%)", stats.value.monthlyGrowth],
      ["Open Roles", stats.value.openPositions],
      ["Priority Roles", stats.value.priorityRoles],
      ["Crews on Leave", stats.value.onLeave],
      ["Leave Type", stats.value.leaveType],
      ["Safety Compliance (%)", stats.value.complianceRate],
      ["Audit Window", stats.value.auditWindow],
    ];
    const csv = lines.map((row) => row.join(",")).join("\n");
    const blob = new Blob([csv], { type: "text/csv;charset=utf-8;" });
    const url = URL.createObjectURL(blob);
    const link = document.createElement("a");
    link.href = url;
    link.download = "service-summary.csv";
    document.body.appendChild(link);
    link.click();
    link.remove();
    URL.revokeObjectURL(url);
    Swal.fire({
      icon: "success",
      title: "Exported",
      text: "Service summary downloaded.",
      timer: 1200,
      showConfirmButton: false,
    });
  } catch (error) {
    Swal.fire({
      icon: "error",
      title: "Export failed",
      text: "Unable to download the service summary.",
    });
  }
};

const createDispatchReport = () => {
  try {
    const report = [
      "Dispatch Report",
      "----------------",
      ...operationalTasks.value.map(
        (task) => `- ${task.title} | Owner: ${task.owner} | Due: ${task.due} | Priority: ${task.priority}`
      ),
    ].join("\n");
    const blob = new Blob([report], { type: "text/plain;charset=utf-8;" });
    const url = URL.createObjectURL(blob);
    const link = document.createElement("a");
    link.href = url;
    link.download = "dispatch-report.txt";
    document.body.appendChild(link);
    link.click();
    link.remove();
    URL.revokeObjectURL(url);
    Swal.fire({
      icon: "success",
      title: "Report created",
      text: "Dispatch report downloaded.",
      timer: 1200,
      showConfirmButton: false,
    });
  } catch (error) {
    Swal.fire({
      icon: "error",
      title: "Report failed",
      text: "Unable to download the dispatch report.",
    });
  }
};

const fetchDashboardData = async () => {
  loading.value = true;
  try {
    const res = await axios.get('/hr/dashboard-data');
    stats.value = res.data.stats;
    teamSummary.value = res.data.teamSummary || [];
    operationalTasks.value = res.data.operationalTasks || [];
    recentActivities.value = res.data.recentActivities || [];
  } catch (error) {
    Swal.fire('Error', 'Failed to load dashboard data', 'error');
  } finally {
    loading.value = false;
  }
};


onMounted(() => {
  fetchDashboardData();
});

// FULL LOGOUT
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
    await axios.post('/logout'); // Laravel route
    window.location.href = '/login';
  } catch(err) {
    console.error(err);
    Swal.fire('Error','Logout failed','error');
  }
}

// Assign Requests + Reports logic removed per request
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
.card { background:#fff; padding:24px; border-radius:16px; box-shadow:0 10px 20px rgba(0,0,0,.08); margin-bottom:20px; }
.table { width:100%; border-collapse:collapse; }
th, td { padding:8px; border:1px solid #e5e7eb; }
button { cursor:pointer; }
</style>
