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

      <div class="dashboard">
        <div class="px-8 py-8">
      <div class="flex flex-col gap-6">
            <div class="flex flex-col gap-4 lg:flex-row lg:items-center lg:justify-between">
              <div>
                <p class="text-xs uppercase tracking-[0.2em] text-slate-400">Recruitment / Onboarding</p>
                <h1 class="text-2xl font-semibold text-slate-900">Technician Hiring &amp; Onboarding</h1>
                <p class="text-sm text-slate-500 mt-1">Add roles, build teams, and track onboarding progress.</p>
              </div>
              <div class="flex flex-wrap gap-3">
                <button type="button"
                        class="rounded-full bg-white px-4 py-2 text-sm font-medium text-slate-700 shadow-sm ring-1 ring-slate-200 transition hover:bg-slate-100"
                        @click="exportHiringList">
                  Export Hiring List
                </button>
                <button type="button"
                        class="rounded-full border border-slate-900 bg-white px-4 py-2 text-sm font-semibold text-slate-900 transition hover:bg-slate-900 hover:text-white"
                        @click="openPublishModal">
                  Publish Open Role
                </button>
              </div>
            </div>

            <!-- Crew Management -->
            <section class="rounded-2xl bg-white p-6 shadow-sm ring-1 ring-slate-200">
              <div class="flex flex-col gap-4 lg:flex-row lg:items-center lg:justify-between">
                <div>
                  <h2 class="text-lg font-semibold text-slate-900">Crew Management</h2>
                  <p class="text-sm text-slate-500">Add technicians and track field teams.</p>
                  <button type="button"
                          class="mt-3 inline-flex items-center rounded-full bg-slate-900 px-4 py-2 text-xs font-semibold uppercase tracking-wide text-white transition hover:bg-slate-800"
                          @click="openAddModal">
                    Add Employee
                  </button>
                </div>
                <form class="flex flex-wrap items-center gap-3" autocomplete="off" @submit.prevent="applyFilters">
                  <input v-model="pendingSearch"
                    type="text"
                    placeholder="Search technicians..."
                    class="w-full rounded-full border border-slate-200 bg-slate-50 px-4 py-2 text-sm text-slate-700 focus:border-slate-400 focus:outline-none focus:ring-2 focus:ring-slate-200 lg:w-64" />
                  <select v-model="pendingDepartment"
                          class="rounded-full border border-slate-200 bg-white px-4 py-2 text-sm text-slate-700 focus:border-slate-400 focus:outline-none focus:ring-2 focus:ring-slate-200">
                    <option value="">All Teams</option>
                    <option v-for="dept in departments" :key="dept" :value="dept">{{ dept }}</option>
                  </select>
                  <button type="submit"
                          class="rounded-full border border-slate-200 px-4 py-2 text-xs font-semibold uppercase tracking-wide text-slate-700 transition hover:border-slate-300 hover:text-slate-900">
                    Search
                  </button>
                </form>
              </div>

              <div class="mt-6">

                <div class="space-y-4">
                  <div class="flex items-center justify-between">
                    <p class="text-sm font-semibold text-slate-900">Active Field Personnel</p>
                    <span class="rounded-full bg-slate-100 px-3 py-1 text-xs font-medium text-slate-600">{{ filteredEmployees.length }} listed</span>
                  </div>
                  <div class="w-full overflow-hidden rounded-2xl border border-slate-200 bg-white">
                    <table class="w-full text-sm">
                      <thead class="bg-slate-50 text-left text-xs uppercase tracking-wide text-slate-500">
                        <tr>
                          <th class="px-4 py-3">Employee</th>
                          <th class="px-4 py-3">Role</th>
                          <th class="px-4 py-3">Team</th>
                          <th class="px-4 py-3">Start Date</th>
                          <th class="px-4 py-3">Status</th>
                          <th class="px-4 py-3 text-right">Actions</th>
                        </tr>
                      </thead>
                      <tbody class="divide-y divide-slate-200">
                        <tr v-for="employee in pagedEmployees" :key="employee.id" class="bg-white">
                          <td class="px-4 py-3 font-semibold text-slate-900">{{ employee.fullName }}</td>
                          <td class="px-4 py-3 text-slate-600">{{ employee.role }}</td>
                          <td class="px-4 py-3 text-slate-600">{{ employee.department }}</td>
                          <td class="px-4 py-3 text-slate-500">{{ employee.startDate }}</td>
                          <td class="px-4 py-3">
                            <span :class="statusBadgeClass(employee.status)"
                                  class="rounded-full px-3 py-1 text-xs font-medium">{{ employee.status }}</span>
                          </td>
                          <td class="px-4 py-3">
                            <div class="flex justify-end gap-2">
                              <button class="rounded-full border border-slate-200 px-3 py-1 text-xs text-slate-600 transition hover:border-slate-300 hover:text-slate-900"
                                      @click="toggleStatus(employee.id)">
                                Toggle
                              </button>
                              <button class="rounded-full border border-rose-200 px-3 py-1 text-xs text-rose-600 transition hover:border-rose-300 hover:text-rose-700"
                                      @click="confirmRemove(employee.id)">
                                Remove
                              </button>
                            </div>
                          </td>
                        </tr>
                        <tr v-if="pagedEmployees.length === 0">
                          <td colspan="6" class="px-4 py-6 text-center text-slate-500">No employees found.</td>
                        </tr>
                      </tbody>
                    </table>
                  </div>
                  <div class="flex items-center justify-between pt-2">
                    <p class="text-xs text-slate-500">
                      Page {{ currentPage }} of {{ totalPages }}
                    </p>
                    <div class="flex items-center gap-2">
                      <button type="button"
                              class="rounded-full border border-slate-200 px-3 py-1 text-xs text-slate-600 transition hover:border-slate-300 hover:text-slate-900 disabled:opacity-50"
                              :disabled="currentPage === 1"
                              @click="prevPage">
                        Prev
                      </button>
                      <button type="button"
                              class="rounded-full border border-slate-200 px-3 py-1 text-xs text-slate-600 transition hover:border-slate-300 hover:text-slate-900 disabled:opacity-50"
                              :disabled="currentPage === totalPages"
                              @click="nextPage">
                        Next
                      </button>
                    </div>
                  </div>
                </div>
              </div>
            </section>
            <div v-if="showAddModal" class="fixed inset-0 z-50 flex items-center justify-center px-4 py-6">
              <div class="absolute inset-0 bg-slate-900/60 backdrop-blur-sm" @click="closeAddModal"></div>
              <div class="relative z-10 w-full max-w-4xl rounded-3xl bg-white p-8 shadow-2xl">
                <div class="flex items-center justify-between">
                  <h3 class="text-sm font-semibold uppercase tracking-[0.2em] text-slate-500">Add Technician</h3>
                  <button type="button"
                          class="rounded-full border border-slate-200 px-3 py-1 text-xs text-slate-600 transition hover:border-slate-300 hover:text-slate-900"
                          @click="closeAddModal">
                    Close
                  </button>
                </div>
                <form class="mt-4" @submit.prevent="addEmployee">
                  <div
                    v-if="!publishOnlyMode"
                    class="flex items-center justify-between rounded-xl border border-slate-200 bg-white px-4 py-3 text-xs font-medium text-slate-500"
                  >
                    <span :class="wizardStep === 1 ? 'text-slate-900' : 'text-slate-400'">Step 1 - Personal Info</span>
                    <span :class="wizardStep === 2 ? 'text-slate-900' : 'text-slate-400'">Step 2 - Work Details</span>
                  </div>
<div v-if="wizardStep === 1" class="mt-4 grid gap-4">
                    <div class="grid gap-4 md:grid-cols-3">
                      <div>
                        <input v-model="employeeForm.givenName"
                               type="text"
                               placeholder="Given Name"
                               class="w-full rounded-xl border border-slate-200 bg-white px-4 py-2 text-sm text-slate-700 focus:border-slate-400 focus:outline-none focus:ring-2 focus:ring-slate-200" />
                        <p v-if="givenNameError" class="text-xs text-rose-600 mt-1">{{ givenNameError }}</p>
                      </div>
                      <div>
                        <input v-model="employeeForm.middleName"
                               type="text"
                               placeholder="Middle Name (Optional)"
                               class="w-full rounded-xl border border-slate-200 bg-white px-4 py-2 text-sm text-slate-700 focus:border-slate-400 focus:outline-none focus:ring-2 focus:ring-slate-200" />
                        <p v-if="middleNameError" class="text-xs text-rose-600 mt-1">{{ middleNameError }}</p>
                      </div>
                      <div>
                        <input v-model="employeeForm.lastName"
                               type="text"
                               placeholder="Last Name"
                               class="w-full rounded-xl border border-slate-200 bg-white px-4 py-2 text-sm text-slate-700 focus:border-slate-400 focus:outline-none focus:ring-2 focus:ring-slate-200" />
                        <p v-if="lastNameError" class="text-xs text-rose-600 mt-1">{{ lastNameError }}</p>
                      </div>
                    </div>
                    <div>
                      <input v-model="employeeForm.email"
                             type="email"
                             placeholder="Email Address"
                             class="w-full rounded-xl border border-slate-200 bg-white px-4 py-2 text-sm text-slate-700 focus:border-slate-400 focus:outline-none focus:ring-2 focus:ring-slate-200" />
                      <p v-if="emailError" class="text-xs text-rose-600 mt-1">{{ emailError }}</p>
                    </div>
                    <div class="relative">
                      <input v-model="employeeForm.password"
                             :type="showPassword ? 'text' : 'password'"
                             placeholder="Password"
                             class="w-full rounded-xl border border-slate-200 bg-white px-4 py-2 pr-20 text-sm text-slate-700 focus:border-slate-400 focus:outline-none focus:ring-2 focus:ring-slate-200" />
                      <button type="button"
                              class="absolute right-3 top-1/2 -translate-y-1/2 text-xs font-medium text-slate-600 hover:text-slate-900"
                              @click="togglePassword">
                        {{ showPassword ? "Hide" : "Show" }}
                      </button>
                    </div>
                    <div class="flex items-center justify-between">
                      <p class="text-xs text-slate-500">
                        For stronger passwords, use a mix of letters, numbers, and special characters.
                      </p>
                      <p v-if="passwordStrengthLabel" class="text-xs font-semibold" :class="passwordStrengthClass">
                        {{ passwordStrengthLabel }}
                      </p>
                    </div>
                    <button type="button"
                            class="w-full rounded-xl bg-slate-900 px-4 py-2 text-sm font-semibold text-white transition hover:bg-slate-800"
                            @click="goToWorkStep">
                      Next: Work Details
                    </button>
                  </div>
                  <div v-else class="mt-4 grid gap-4">
                    <select v-model="employeeForm.role"
                            class="w-full rounded-xl border border-slate-200 bg-white px-4 py-2 text-sm text-slate-700 focus:border-slate-400 focus:outline-none focus:ring-2 focus:ring-slate-200">
                      <option value="">Select Role / Specialty</option>
                      <option v-for="role in allowedRoles" :key="role" :value="role">{{ role }}</option>
                    </select>
                    <input v-model="employeeForm.department"
                           type="text"
                           readonly
                           placeholder="Select Role to auto-fill Team"
                           class="w-full rounded-xl border border-slate-200 bg-slate-100 px-4 py-2 text-sm text-slate-700 focus:outline-none" />
                    <div v-if="!publishOnlyMode" class="grid grid-cols-2 gap-3">
                      <select v-model="employeeForm.status"
                              class="w-full rounded-xl border border-slate-200 bg-white px-4 py-2 text-sm text-slate-700 focus:border-slate-400 focus:outline-none focus:ring-2 focus:ring-slate-200">
                        <option value="Active">Active</option>
                        <option value="Probation">Probation</option>
                        <option value="On Leave">On Leave</option>
                      </select>
                      <input v-model="employeeForm.startDate"
                             type="date"
                             :min="yearStart"
                             :max="yearEnd"
                             required
                             class="w-full rounded-xl border border-slate-200 bg-white px-4 py-2 text-sm text-slate-700 focus:border-slate-400 focus:outline-none focus:ring-2 focus:ring-slate-200" />
                    </div>
                    <input v-else
                           v-model="employeeForm.startDate"
                           type="date"
                           :min="yearStart"
                           :max="yearEnd"
                           required
                           class="w-full rounded-xl border border-slate-200 bg-white px-4 py-2 text-sm text-slate-700 focus:border-slate-400 focus:outline-none focus:ring-2 focus:ring-slate-200" />
                    <textarea v-model="employeeForm.notes"
                              rows="3"
                              placeholder="Notes (optional)"
                              class="w-full resize-none rounded-xl border border-slate-200 bg-white px-4 py-2 text-sm text-slate-700 focus:border-slate-400 focus:outline-none focus:ring-2 focus:ring-slate-200"></textarea>
                    <div class="flex items-center gap-3">
                      <button v-if="!publishOnlyMode"
                              type="button"
                              class="w-full rounded-xl border border-slate-200 px-4 py-2 text-sm font-semibold text-slate-600 transition hover:border-slate-300 hover:text-slate-900"
                              @click="wizardStep = 1">
                        Back
                      </button>
                      <button v-if="publishOnlyMode"
                              type="button"
                              class="w-full rounded-xl border border-slate-900 px-4 py-2 text-sm font-semibold text-slate-900 transition hover:bg-slate-900 hover:text-white disabled:cursor-not-allowed disabled:opacity-50"
                              :disabled="!employeeForm.role"
                              @click="publishOpenRole">
                        Publish Open Role
                      </button>
                      <button v-if="!publishOnlyMode"
                              type="submit"
                              class="w-full rounded-xl bg-slate-900 px-4 py-2 text-sm font-semibold text-white transition hover:bg-slate-800">
                        Add Technician
                      </button>
                    </div>
                    <p class="text-xs text-slate-500">Tip: Update team and status for better dispatch analytics.</p>
                  </div>
                </form>
              </div>
            </div>
          </div>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref, computed, watch, onMounted } from "vue";
import { router } from "@inertiajs/vue3";
import axios from "axios";
import Swal from "sweetalert2";

const activeMenu = ref("Recruitment / Onboarding");

function navigateTo(menu, url) {
  activeMenu.value = menu;
  router.visit(url);
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

let publishProgressTimer = null;

const departments = ref([
  "Field Operations",
  "Siphoning Crew",
  "Plumbing Installations",
  "Dispatch & Scheduling",
  "Safety & Compliance",
  "Customer Service",
]);

const allowedRoles = ref([
  "Field Operations Lead",
  "Plumbing Technician",
  "Siphoning Technician",
  "Drain Cleaner / Jetter Operator",
  "Septic Tank Service Technician",
  "Plumbing Installation Technician",
  "Pipefitter",
  "Leak Detection Specialist",
  "Maintenance Technician",
  "Dispatch Coordinator",
  "Customer Service Representative",
  "Safety Officer",
  "Fleet & Equipment Technician",
  "Warehouse / Inventory Assistant",
  "Quality Control Inspector",
]);

const roleTeamMap = {
  "Field Operations Lead": "Field Operations",
  "Plumbing Technician": "Plumbing Installations",
  "Siphoning Technician": "Siphoning Crew",
  "Drain Cleaner / Jetter Operator": "Siphoning Crew",
  "Septic Tank Service Technician": "Siphoning Crew",
  "Plumbing Installation Technician": "Plumbing Installations",
  Pipefitter: "Plumbing Installations",
  "Leak Detection Specialist": "Field Operations",
  "Maintenance Technician": "Field Operations",
  "Dispatch Coordinator": "Dispatch & Scheduling",
  "Customer Service Representative": "Customer Service",
  "Safety Officer": "Safety & Compliance",
  "Fleet & Equipment Technician": "Field Operations",
  "Warehouse / Inventory Assistant": "Customer Service",
  "Quality Control Inspector": "Safety & Compliance",
};

const setTeamFromRole = (role) => {
  employeeForm.value.department = roleTeamMap[role] || "";
};
const employeeForm = ref({
  givenName: "",
  middleName: "",
  lastName: "",
  email: "",
  role: "",
  department: "",
  status: "Active",
  startDate: "",
  notes: "",
  password: "",
});

const employeeSearch = ref("");
const departmentFilter = ref("");
const pendingSearch = ref("");
const pendingDepartment = ref("");

const employees = ref([]);

const filteredEmployees = computed(() => {
  return employees.value.filter((employee) => {
    const matchesSearch =
      employee.fullName.toLowerCase().includes(employeeSearch.value.toLowerCase()) ||
      employee.role.toLowerCase().includes(employeeSearch.value.toLowerCase());
    const matchesDepartment = departmentFilter.value
      ? employee.department === departmentFilter.value
      : true;
    return matchesSearch && matchesDepartment;
  });
});

const applyFilters = () => {
  employeeSearch.value = pendingSearch.value;
  departmentFilter.value = pendingDepartment.value;
};

const invalidTypeMessage = (value) => {
  if (!value) return "";
  const hasNumber = /\d/.test(value);
  const hasSpecial = /[^a-zA-Z\s\d]/.test(value);
  if (hasNumber && hasSpecial) return "Numerical and special characters are not allowed.";
  if (hasNumber) return "Numerical characters are not allowed.";
  if (hasSpecial) return "Special characters are not allowed.";
  return "";
};

const givenNameError = computed(() => invalidTypeMessage(employeeForm.value.givenName));
const middleNameError = computed(() => invalidTypeMessage(employeeForm.value.middleName));
const lastNameError = computed(() => invalidTypeMessage(employeeForm.value.lastName));
const showEmailError = ref(false);
const emailError = computed(() => {
  if (!showEmailError.value) return "";
  const value = employeeForm.value.email || "";
  if (!value) return "Email is required.";
  const isValid = /^[^\s@]+@[^\s@]+\.[^\s@]+$/.test(value);
  return isValid ? "" : "Email must be valid.";
});
const passwordStrengthLabel = computed(() => {
  const value = employeeForm.value.password || "";
  if (!value) return "";
  const hasNumber = /\d/.test(value);
  const hasSpecial = /[^a-zA-Z0-9]/.test(value);
  if (value.length >= 10 && hasNumber && hasSpecial) return "Strong";
  if (value.length >= 6 && (hasNumber || hasSpecial)) return "Medium";
  return "Weak";
});

const passwordStrengthClass = computed(() => {
  if (passwordStrengthLabel.value === "Strong") return "text-emerald-600";
  if (passwordStrengthLabel.value === "Medium") return "text-amber-600";
  return "text-rose-600";
});

const isDateValid = (value) => {
  if (!value) return false;
  return value >= yearStart.value && value <= yearEnd.value;
};

const pageSize = ref(5);
const currentPage = ref(1);
const wizardStep = ref(1);
const showAddModal = ref(false);
const showPassword = ref(false);
const publishOnlyMode = ref(false);
const yearStart = ref("");
const yearEnd = ref("");
const publishStep1Disabled = computed(() => {
  return !!(
    (employeeForm.value.givenName && employeeForm.value.givenName.trim() !== "") ||
    (employeeForm.value.middleName && employeeForm.value.middleName.trim() !== "") ||
    (employeeForm.value.lastName && employeeForm.value.lastName.trim() !== "") ||
    (employeeForm.value.password && employeeForm.value.password.trim() !== "")
  );
});

const togglePassword = () => {
  showPassword.value = !showPassword.value;
};

const formatLocalDate = (date) => {
  const year = date.getFullYear();
  const month = String(date.getMonth() + 1).padStart(2, "0");
  const day = String(date.getDate()).padStart(2, "0");
  return `${year}-${month}-${day}`;
};

const syncTodayDateBounds = () => {
  const now = new Date();
  const dateString = formatLocalDate(now);
  yearStart.value = dateString;
  yearEnd.value = dateString;
};

const resetWizard = () => {
  wizardStep.value = 1;
  showPassword.value = false;
  showEmailError.value = false;
  publishOnlyMode.value = false;
};

const openAddModal = () => {
  syncTodayDateBounds();
  resetWizard();
  publishOnlyMode.value = false;
  showAddModal.value = true;
};

const openPublishModal = () => {
  syncTodayDateBounds();
  resetWizard();
  wizardStep.value = 2;
  publishOnlyMode.value = true;
  showAddModal.value = true;
};

const closeAddModal = () => {
  showAddModal.value = false;
  showEmailError.value = false;
};

const goToWorkStep = () => {
  const issues = [];
  if (!employeeForm.value.givenName) {
    issues.push("Given Name is required.");
  } else if (givenNameError.value) {
    issues.push(`Given Name: ${givenNameError.value}`);
  }

  if (employeeForm.value.middleName && middleNameError.value) {
    issues.push(`Middle Name: ${middleNameError.value}`);
  }

  if (!employeeForm.value.lastName) {
    issues.push("Last Name is required.");
  } else if (lastNameError.value) {
    issues.push(`Last Name: ${lastNameError.value}`);
  }
  showEmailError.value = true;
  if (!employeeForm.value.email || emailError.value) {
    issues.push(emailError.value || "Email is required.");
  }
  if (!employeeForm.value.password) {
    issues.push("Password is required.");
  } else if (passwordStrengthLabel.value === "Weak") {
    issues.push("Password is too weak. Use letters, numbers, and special characters.");
  }
  if (issues.length > 0) {
    Swal.fire({
      icon: "warning",
      title: "Please fix the following",
      html: `<ul style="text-align:left;margin:0;padding-left:18px;">${issues
        .map((issue) => `<li>${issue}</li>`)
        .join("")}</ul>`,
      confirmButtonColor: "#0f172a",
    });
    return;
  }
  wizardStep.value = 2;
};

const goToPublishStep = () => {
  wizardStep.value = 2;
};

const totalPages = computed(() => {
  return Math.max(1, Math.ceil(filteredEmployees.value.length / pageSize.value));
});

const pagedEmployees = computed(() => {
  const start = (currentPage.value - 1) * pageSize.value;
  return filteredEmployees.value.slice(start, start + pageSize.value);
});

const nextPage = () => {
  if (currentPage.value < totalPages.value) {
    currentPage.value += 1;
  }
};

const prevPage = () => {
  if (currentPage.value > 1) {
    currentPage.value -= 1;
  }
};

watch([employeeSearch, departmentFilter, employees], () => {
  currentPage.value = 1;
});

watch(
  () => employeeForm.value.role,
  (role) => {
    setTeamFromRole(role);
  }
);

const addEmployee = async () => {
  const issues = [];

  if (!employeeForm.value.givenName) {
    issues.push("Given Name is required.");
  } else if (givenNameError.value) {
    issues.push(`Given Name: ${givenNameError.value}`);
  }
  if (employeeForm.value.middleName && middleNameError.value) {
    issues.push(`Middle Name: ${middleNameError.value}`);
  }
  if (!employeeForm.value.lastName) {
    issues.push("Last Name is required.");
  } else if (lastNameError.value) {
    issues.push(`Last Name: ${lastNameError.value}`);
  }

  showEmailError.value = true;
  if (!employeeForm.value.email || emailError.value) {
    issues.push(emailError.value || "Email is required.");
  }
  if (!employeeForm.value.role) {
    issues.push("Role / Specialty is required.");
  } else if (!allowedRoles.value.includes(employeeForm.value.role)) {
    issues.push("Role / Specialty must be selected from the list.");
  }

  if (!employeeForm.value.password) {
    issues.push("Password is required.");
  } else if (passwordStrengthLabel.value === "Weak") {
    issues.push("Password is too weak. Use letters, numbers, and special characters.");
  }

  if (!employeeForm.value.department) {
    issues.push("Team is required.");
  }

  if (!employeeForm.value.startDate) {
    issues.push("Start Date is required.");
  } else if (!isDateValid(employeeForm.value.startDate)) {
    issues.push("Start Date must be today.");
  }

  if (issues.length > 0) {
    Swal.fire({
      icon: "warning",
      title: "Please fix the following",
      html: `<ul style="text-align:left;margin:0;padding-left:18px;">${issues
        .map((issue) => `<li>${issue}</li>`)
        .join("")}</ul>`,
      confirmButtonColor: "#0f172a",
    });
    return;
  }

  const fullName = [employeeForm.value.givenName, employeeForm.value.middleName, employeeForm.value.lastName]
    .filter(Boolean)
    .join(" ");

  try {
    const res = await axios.post("/hr/employees", {
      name: fullName,
      email: employeeForm.value.email,
      given_name: employeeForm.value.givenName,
      middle_name: employeeForm.value.middleName || null,
      last_name: employeeForm.value.lastName,
      password: employeeForm.value.password,
      role: employeeForm.value.role,
      team: employeeForm.value.department,
      status: employeeForm.value.status,
      start_date: employeeForm.value.startDate || null,
      notes: employeeForm.value.notes || null,
    });

    employees.value.unshift({
      id: res.data.id,
      fullName: res.data.name,
      role: res.data.role,
      department: res.data.team,
      status: res.data.status,
      startDate: res.data.start_date || employeeForm.value.startDate || "TBD",
      notes: res.data.notes,
    });
  } catch (error) {
    Swal.fire({
      icon: "error",
      title: "Save failed",
      text: "Unable to save employee to the database.",
    });
    return;
  }
  employeeForm.value = {
    givenName: "",
    middleName: "",
    lastName: "",
    email: "",
    role: "",
    department: "",
    status: "Active",
    startDate: "",
    notes: "",
    password: "",
  };
  closeAddModal();

  Swal.fire({
    icon: "success",
    title: "Technician added",
    text: "The new technician has been added to the roster.",
    timer: 1500,
    showConfirmButton: false,
  });
};

const confirmRemove = (employeeId) => {
  Swal.fire({
    title: "Remove technician?",
    text: "This action will remove the technician from the list.",
    icon: "warning",
    showCancelButton: true,
    confirmButtonColor: "#0f172a",
    confirmButtonText: "Yes, remove",
  }).then((result) => {
    if (result.isConfirmed) {
      axios
        .delete(`/hr/employees/${employeeId}`)
        .then(() => {
          employees.value = employees.value.filter((employee) => employee.id !== employeeId);
          Swal.fire({
            icon: "success",
            title: "Removed",
            text: "Technician was removed from the list.",
            timer: 1200,
            showConfirmButton: false,
          });
        })
        .catch(() => {
          Swal.fire({
            icon: "error",
            title: "Remove failed",
            text: "Unable to remove the employee.",
          });
        });
    }
  });
};

const toggleStatus = (employeeId) => {
  const target = employees.value.find((employee) => employee.id === employeeId);
  if (!target) return;
  const nextStatus = target.status === "Active" ? "On Leave" : "Active";

  axios
    .patch(`/hr/employees/${employeeId}`, { status: nextStatus })
    .then(() => {
      employees.value = employees.value.map((employee) => {
        if (employee.id !== employeeId) return employee;
        return { ...employee, status: nextStatus };
      });
    })
    .catch(() => {
      Swal.fire({
        icon: "error",
        title: "Update failed",
        text: "Unable to update employee status.",
      });
    });
};

const statusBadgeClass = (status) => {
  if (status === "Active") return "bg-emerald-50 text-emerald-600";
  if (status === "Probation") return "bg-amber-50 text-amber-600";
  return "bg-rose-50 text-rose-600";
};

const exportHiringList = () => {
  try {
    const lines = [
      ["Full Name", "Role", "Team", "Status", "Start Date", "Notes"],
      ...employees.value.map((employee) => [
        employee.fullName,
        employee.role,
        employee.department,
        employee.status,
        employee.startDate,
        employee.notes || "",
      ]),
    ];
    const csv = lines.map((row) => row.join(",")).join("\n");
    const blob = new Blob([csv], { type: "text/csv;charset=utf-8;" });
    const url = URL.createObjectURL(blob);
    const link = document.createElement("a");
    link.href = url;
    link.download = "hiring-list.csv";
    document.body.appendChild(link);
    link.click();
    link.remove();
    URL.revokeObjectURL(url);
    Swal.fire({
      icon: "success",
      title: "Exported",
      text: "Hiring list downloaded.",
      timer: 1200,
      showConfirmButton: false,
    });
  } catch (error) {
    Swal.fire({
      icon: "error",
      title: "Export failed",
      text: "Unable to download the hiring list.",
    });
  }
};

const publishOpenRole = () => {
  if (!employeeForm.value.role || !employeeForm.value.department) {
    Swal.fire({
      icon: "warning",
      title: "Missing details",
      text: "Please enter a role and select a team to publish.",
      confirmButtonColor: "#0f172a",
    });
    return;
  }
  if (!employeeForm.value.startDate) {
    Swal.fire({
      icon: "warning",
      title: "Missing start date",
      text: "Please select a start date to publish.",
      confirmButtonColor: "#0f172a",
    });
    return;
  }

  router.post(
    "/hr/recruitment/publish",
    {
      role: employeeForm.value.role,
      team: employeeForm.value.department,
      status: employeeForm.value.status,
      preferred_start_date: employeeForm.value.startDate || null,
      notes: employeeForm.value.notes || null,
    },
    {
      onStart: () => {
        let progressValue = 0;
        Swal.fire({
          title: "Publishing...",
          html:
            '<div style="font-size:12px;color:#64748b;">Please wait while we post the open role.</div>' +
            '<div style="height:8px;background:#e2e8f0;border-radius:999px;margin-top:12px;overflow:hidden;">' +
            '<div id="publish-progress-bar" style="height:8px;width:0%;background:#0f172a;border-radius:999px;"></div>' +
            "</div>" +
            '<div id="publish-progress-text" style="margin-top:8px;font-size:12px;color:#64748b;">0%</div>',
          showConfirmButton: false,
          allowOutsideClick: false,
          allowEscapeKey: false,
        });

        if (publishProgressTimer) {
          clearInterval(publishProgressTimer);
        }
        publishProgressTimer = setInterval(() => {
          progressValue = Math.min(progressValue + 8, 92);
          const container = Swal.getHtmlContainer();
          const bar = container?.querySelector("#publish-progress-bar");
          const text = container?.querySelector("#publish-progress-text");
          if (bar) bar.style.width = `${progressValue}%`;
          if (text) text.textContent = `${progressValue}%`;
        }, 200);
      },
      onSuccess: () => {
        if (publishProgressTimer) {
          clearInterval(publishProgressTimer);
          publishProgressTimer = null;
        }
        Swal.fire({
          icon: "success",
          title: "Published",
          text: "Open role posted and notification sent successfully.",
          timer: 1400,
          showConfirmButton: false,
        });
      },
      onError: () => {
        if (publishProgressTimer) {
          clearInterval(publishProgressTimer);
          publishProgressTimer = null;
        }
        Swal.fire({
          icon: "error",
          title: "Publish failed",
          text: "Unable to publish the open role.",
        });
      },
      onFinish: () => {
        if (publishProgressTimer) {
          clearInterval(publishProgressTimer);
          publishProgressTimer = null;
        }
      },
    }
  );
};

const fetchEmployees = async () => {
  try {
    const res = await axios.get("/hr/employees");
    employees.value = res.data.map((employee) => ({
      id: employee.id,
      fullName: employee.name,
      role: employee.role,
      department: employee.team,
      status: employee.status,
      startDate: employee.start_date || "TBD",
      notes: employee.notes,
    }));
  } catch (error) {
    Swal.fire({
      icon: "error",
      title: "Load failed",
      text: "Unable to load employees.",
    });
  }
};

onMounted(() => {
  fetchEmployees();
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
.dashboard { padding:24px; }
</style>
