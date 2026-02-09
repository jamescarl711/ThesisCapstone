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
          <!-- Payroll / Compensation Section -->
          <section v-if="activeMenu==='Payroll / Compensation'" class="bg-white rounded-2xl shadow-sm ring-1 ring-slate-200 p-6">
            <div class="flex items-center justify-between mb-6">
              <div>
                <h2 class="text-lg font-semibold">Payroll / Compensation</h2>
                <p class="text-sm text-slate-500">Review attendance and generate payroll results.</p>
              </div>
              <span class="text-xs font-medium px-2.5 py-1 rounded-full bg-slate-100 text-slate-600">February 2026</span>
            </div>

          <!-- **INSERT: Employee Selection Button & Modal** -->
          <div class="flex items-center gap-3">
            <button @click="showEmployeeModal = true" class="bg-slate-900 hover:bg-slate-800 text-white text-sm font-semibold px-4 py-2 rounded-lg shadow-sm transition">
              Select Employee
            </button>
            <span v-if="selectedEmployee" class="text-sm text-slate-600">
              Selected: <span class="font-semibold text-slate-900">{{ selectedEmployee.name }}</span>
            </span>
          </div>

          <div v-if="showEmployeeModal" class="fixed inset-0 z-50 bg-slate-900/50 backdrop-blur-sm flex items-center justify-center px-4">
            <div class="bg-white rounded-2xl shadow-xl w-full max-w-sm p-5">
              <div class="flex items-center justify-between mb-4">
                <h3 class="text-base font-semibold">Select Employee</h3>
                <button @click="showEmployeeModal = false" class="text-slate-400 hover:text-slate-600 text-sm">Close</button>
              </div>
              <ul class="space-y-2">
                <li v-for="employee in employees" :key="employee.id">
                  <button @click="selectEmployee(employee)" class="w-full text-left px-3 py-2 rounded-lg bg-slate-50 hover:bg-slate-100 border border-slate-200 transition text-sm">
                    {{ employee.name }}
                  </button>
                </li>
              </ul>
            </div>
          </div>
          <!-- **END INSERT** -->

          <!-- Rest of Payroll Section (Attendance, Income, Deductions, Net Pay) -->
          <div v-if="selectedEmployee" class="mt-6 space-y-8">
            <h4 class="text-base font-semibold">Payroll for {{ selectedEmployee.name }}</h4>
            <div class="bg-white border border-slate-200 rounded-xl p-4">
              <label class="text-xs text-slate-500 block mb-2">Pay Date (15th and month-end)</label>
              <select
                v-model="payDate"
                class="w-full rounded-lg border border-slate-200 px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-slate-900/20"
              >
                <option value="" disabled>Select pay date</option>
                <option v-for="option in payDateOptions" :key="option.value" :value="option.value">
                  {{ option.label }}
                </option>
              </select>
            </div>
            <div v-if="!payDate" class="text-sm text-slate-500">
              Please select a Pay Date to view attendance, income, deductions, and net pay.
            </div>
            <!-- Attendance Input -->
            <div v-if="payDate">
              <p class="text-sm font-semibold text-slate-700 mb-3">Attendance</p>
                <div class="grid grid-cols-1 sm:grid-cols-2 xl:grid-cols-4 gap-4">
                  <div class="bg-slate-50 border border-slate-200 rounded-xl p-3">
                    <label class="block text-xs font-large text-slate-900 mb-2">Days Present</label>
                    <input
                      type="number"
                      v-model.number="attendance.daysPresent"
                      @input="limitDays('daysPresent')"
                      disabled
                      class="w-full rounded-lg border border-slate-200 px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-slate-900/20"
                    />
                  </div>
                  <div class="bg-slate-50 border border-slate-200 rounded-xl p-3">
                    <label class="block text-xs font-medium text-slate-500 mb-2">Days Absent</label>
                    <input
                      type="number"
                      v-model.number="attendance.daysAbsent"
                      @input="limitDays('daysAbsent')"
                      disabled
                      class="w-full rounded-lg border border-slate-200 px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-slate-900/20"
                    />
                  </div>
                  <div class="bg-slate-50 border border-slate-200 rounded-xl p-3">
                    <label class="block text-xs font-medium text-slate-500 mb-2">Late Days</label>
                    <input
                      type="number"
                      v-model.number="attendance.lateDays"
                      @input="limitDays('lateDays')"
                      disabled
                      class="w-full rounded-lg border border-slate-200 px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-slate-900/20"
                    />
                  </div>
                  <div class="bg-slate-50 border border-slate-200 rounded-xl p-3">
                    <label class="block text-xs font-medium text-slate-500 mb-2">Overtime (mins)</label>
                    <input
                      type="number"
                      v-model.number="attendance.overtimeMinutes"
                      disabled
                      class="w-full rounded-lg border border-slate-200 px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-slate-900/20"
                    />
                  </div>
                  <div class="bg-slate-900 text-white rounded-xl p-4 flex flex-col justify-center">
                    <span class="text-xs opacity-80">TOTAL WORKING DAYS</span>
                    <span class="text-2xl font-bold">
                      {{ attendance.daysPresent }} / {{ totalWorkingDays }}
                    </span>
                  </div>
                </div>
            </div>

            <!-- Income / Salary Section -->
            <div v-if="payDate">
              <div class="flex items-center justify-between mb-3">
                <h3 class="text-sm font-semibold text-slate-700">Income / Salary</h3>
                <span class="text-xs text-slate-500">Computed fields</span>
              </div>
              <div class="grid grid-cols-1 sm:grid-cols-2 xl:grid-cols-4 gap-4">
                <div class="bg-white border border-slate-200 rounded-xl p-4">
                  <label class="text-xs text-slate-500">Daily Rate</label>
                  <p class="text-lg font-semibold mt-1">{{ dailyRate }}</p>
                </div>
                <div class="bg-white border border-slate-200 rounded-xl p-4">
                  <label class="text-xs text-slate-500">Basic Salary</label>
                  <p class="text-lg font-semibold mt-1">{{ basicSalary.toFixed(2) }}</p>
                </div>
                <div class="bg-white border border-slate-200 rounded-xl p-4">
                  <label class="text-xs text-slate-500">Overtime & Holiday</label>
                  <p class="text-lg font-semibold mt-1">{{ overtimeHoliday.toFixed(2) }}</p>
                </div>
                <div class="bg-white border border-slate-200 rounded-xl p-4">
                  <label class="text-xs text-slate-500">Bonuses & Incentives</label>
                  <p class="text-lg font-semibold mt-1">{{ bonuses.toFixed(2) }}</p>
                </div>

                <div class="bg-slate-50 border border-slate-200 rounded-xl p-3">
                  <label class="block text-xs font-medium text-slate-500 mb-2">Transport Allowance</label>
                  <p class="text-lg font-semibold text-slate-900">200</p>
                </div>
                <div class="bg-slate-50 border border-slate-200 rounded-xl p-3">
                  <label class="block text-xs font-medium text-slate-500 mb-2">Meal Allowance</label>
                  <p class="text-lg font-semibold text-slate-900">200</p>
                </div>

                <div class="bg-emerald-50 border border-emerald-200 rounded-xl p-4">
        <span class="text-sm font-semibold">Total Income</span>
        <p class="text-2xl font-bold text-emerald-700">
          {{ totalIncome.toFixed(2) }}
        </p>
      </div>
              </div>
            </div>

            <!-- Deductions Section -->
            <div v-if="payDate">
              <h3 class="text-sm font-semibold text-slate-700 mb-3">Deductions</h3>
              <div class="grid grid-cols-1 sm:grid-cols-2 xl:grid-cols-4 gap-4">
                <div class="bg-white border border-slate-200 rounded-xl p-4">
                  <label class="text-xs text-slate-500">SSS</label>
                  <p class="text-lg font-semibold mt-1">{{ deductions.sss.toFixed(2) }}</p>
                </div>
                <div class="bg-white border border-slate-200 rounded-xl p-4">
                  <label class="text-xs text-slate-500">Pag-IBIG</label>
                  <p class="text-lg font-semibold mt-1">{{ deductions.pagibig.toFixed(2) }}</p>
                </div>
                <div class="bg-white border border-slate-200 rounded-xl p-4">
                  <label class="text-xs text-slate-500">PhilHealth</label>
                  <p class="text-lg font-semibold mt-1">{{ deductions.philhealth.toFixed(2) }}</p>
                </div>

                <div class="bg-slate-50 border border-slate-200 rounded-xl p-3">
                  <label class="block text-xs font-medium text-slate-500 mb-2">Salary Loan</label>
                  <input
                    type="number"
                    v-model.number="loans.salary"
                    @input="limit9999('salary')"
                    class="w-full rounded-lg border border-slate-200 px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-slate-900/20"
                  />
                </div>
                <div class="bg-slate-50 border border-slate-200 rounded-xl p-3">
                  <label class="block text-xs font-medium text-slate-500 mb-2">SSS Loan</label>
                  <input
                    type="number"
                    v-model.number="loans.sss"
                    @input="limit9999('sss')"
                    class="w-full rounded-lg border border-slate-200 px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-slate-900/20"
                  />
                </div>
      <div class="bg-slate-50 border border-slate-200 rounded-xl p-3">
  <label class="block text-xs font-medium text-slate-500 mb-2">
    Health Insurance
  </label>
  <input
    type="number"
    v-model.number="insurance.health"
    @input="limitInsurance('health')"
    class="w-full rounded-lg border border-slate-200 px-3 py-2 text-sm
           focus:outline-none focus:ring-2 focus:ring-slate-900/20"
  />
</div>
                <div class="bg-slate-50 border border-slate-200 rounded-xl p-3">
  <label class="block text-xs font-medium text-slate-500 mb-2">
    Life Insurance
  </label>
  <input
    type="number"
    v-model.number="insurance.life"
    @input="limitInsurance('life')"
    class="w-full rounded-lg border border-slate-200 px-3 py-2 text-sm
           focus:outline-none focus:ring-2 focus:ring-slate-900/20"
  />
</div>

                <div class="bg-white border border-slate-200 rounded-xl p-4">
                  <label class="text-xs text-slate-500">Leave Without Pay</label>
                  <p class="text-lg font-semibold mt-1">{{ penalties.leaveWithoutPay.toFixed(2) }}</p>
                </div>
                <div class="bg-white border border-slate-200 rounded-xl p-4">
                  <label class="text-xs text-slate-500">Tardiness / Late Arrival</label>
                  <p class="text-lg font-semibold mt-1">{{ penalties.tardiness.toFixed(2) }}</p>
                </div>
                <div class="bg-white border border-slate-200 rounded-xl p-4">
                  <label class="text-xs text-slate-500">Union / Cooperative Fees</label>
                  <p class="text-lg font-semibold mt-1">{{ deductions.union.toFixed(2) }}</p>
                </div>
                <div class="bg-white border border-slate-200 rounded-xl p-4">
                  <label class="text-xs text-slate-500">Other Deductions</label>
                  <p class="text-lg font-semibold mt-1">{{ deductions.others.toFixed(2) }}</p>
                </div>

                <div class="bg-rose-50 border border-rose-200 rounded-xl p-4">
        <span class="text-sm font-semibold text-rose-600">Total Deductions</span>
        <p class="text-2xl font-bold text-rose-700">
          {{ totalDeductions.toFixed(2) }}
        </p>
      </div>
              </div>
            </div>

            <!-- Net Pay -->
            <div v-if="payDate" class="bg-gradient-to-r from-slate-900 to-slate-800 text-white rounded-2xl p-6 flex justify-between items-center">
      <span class="text-lg font-semibold">NET PAY</span>
      <span class="text-3xl font-bold">
        {{ netPay.toFixed(2) }}
      </span>
    </div>

            <!-- Generate & Clear Payroll -->
            <div v-if="payDate" class="flex flex-wrap gap-3">
              <button @click="generatePayroll" class="bg-emerald-600 hover:bg-emerald-700 text-white text-sm font-semibold px-5 py-2.5 rounded-lg shadow-sm transition">
                Generate Payroll
              </button>
            </div>
          </div>

          </section>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref, computed, onMounted, watch } from "vue";
import { router } from "@inertiajs/vue3";
import axios from "axios";
import Swal from "sweetalert2";

/* ---------------- SIDEBAR ---------------- */
const activeMenu = ref("Payroll / Compensation");

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

/* ---------------- EMPLOYEES ---------------- */
const showEmployeeModal = ref(false);
const selectedEmployee = ref(null);
const employees = ref([]);
const payDate = ref("");
const payDateMin = ref("");
const payDateMax = ref("");
const payDateOptions = ref([]);

function selectEmployee(emp){
  selectedEmployee.value = emp;
  showEmployeeModal.value = false;
  syncPayDateBounds();
  fetchAttendanceSummary();
}

/* ---------------- PAYROLL DATA ---------------- */
const totalWorkingDays = 15;
const dailyRate = 500;
const overtimeRatePerTenMinutes = 5;

/* Attendance */
const attendance = ref({
  daysPresent: 0,
  daysAbsent: 0,
  lateDays: 0,
  overtimeMinutes: 0
});

/* Allowances */
const allowances = 400;

/* Loans */
const loans = ref({
  salary: 0,
  sss: 0
});

/* Insurance */
const insurance = ref({
  health: 0,
  life: 0
});

const lastValid = ref({
  attendance: { ...attendance.value },
  loans: { ...loans.value },
  insurance: { ...insurance.value }
});

const showCardModal = ref(false);
const cardModal = ref({ title: "", value: "" });

const num = (value) => {
  const n = Number(value);
  return Number.isFinite(n) ? n : 0;
};

/* ---------------- COMPUTED ---------------- */
const basicSalary = computed(() =>
  num(attendance.value.daysPresent) * dailyRate
);

const bonuses = computed(() =>
  num(attendance.value.daysPresent) * 100
);

const penalties = computed(() => ({
  leaveWithoutPay: num(attendance.value.daysAbsent) * 150,
  tardiness: num(attendance.value.lateDays) * 100
}));

const deductions = computed(() => ({
  sss: basicSalary.value * 0.055,
  pagibig: basicSalary.value * 0.02,
  philhealth: basicSalary.value * 0.035,
  union: 100,
  others: 50
}));

const overtimeHoliday = computed(() => {
  const minutes = num(attendance.value.overtimeMinutes);
  return (minutes / 10) * overtimeRatePerTenMinutes;
});

const totalIncome = computed(() =>
  basicSalary.value +
  overtimeHoliday.value +
  bonuses.value +
  allowances
);

const totalDeductions = computed(() => {
  const gov =
    deductions.value.sss +
    deductions.value.pagibig +
    deductions.value.philhealth;

  const misc =
    deductions.value.union +
    deductions.value.others;

  const loan =
    num(loans.value.salary) +
    num(loans.value.sss);

  const insure =
    num(insurance.value.health) +
    num(insurance.value.life);

  const penalty =
    penalties.value.leaveWithoutPay +
    penalties.value.tardiness;

  return gov + misc + loan + insure + penalty;
});

const netPay = computed(() =>
  totalIncome.value - totalDeductions.value
);

/* ---------------- LIMITERS ---------------- */
function limitDays(field){
  const current = Number(attendance.value[field]);
  if (!Number.isFinite(current)) {
    attendance.value[field] = 0;
    lastValid.value.attendance[field] = 0;
    return;
  }
  if (current < 0) attendance.value[field] = 0;
  if (current > totalWorkingDays)
    attendance.value[field] = totalWorkingDays;
  lastValid.value.attendance[field] = attendance.value[field];
}
function limit9999(field){
  const current = Number(loans.value[field]);
  if (!Number.isFinite(current)) {
    loans.value[field] = 0;
    lastValid.value.loans[field] = 0;
    return;
  }
  if (current < 0) loans.value[field] = 0;
  if (current > 9999) loans.value[field] = 9999;
  lastValid.value.loans[field] = loans.value[field];
}

function limitInsurance(field){
  const current = Number(insurance.value[field]);
  if (!Number.isFinite(current)) {
    insurance.value[field] = 0;
    lastValid.value.insurance[field] = 0;
    return;
  }
  if (current < 0) insurance.value[field] = 0;
  if (current > 999) insurance.value[field] = 999;
  lastValid.value.insurance[field] = insurance.value[field];
}

function openCardModal(title, value){
  cardModal.value = { title, value };
  showCardModal.value = true;
}

/* ---------------- ACTIONS ---------------- */
async function generatePayroll(){
  if (!payDate.value) {
    await Swal.fire({
      icon: "warning",
      title: "Missing Pay Date",
      text: "Please select a pay date before generating payroll.",
      confirmButtonText: "OK"
    });
    return;
  }
  if (netPay.value < 0) {
    await Swal.fire({
      icon: "warning",
      title: "Negative Net Pay",
      text: "Please review deductions before generating payroll.",
      confirmButtonText: "OK"
    });
    return;
  }
  if (!isValidPayDate(payDate.value)) {
    await Swal.fire({
      icon: "warning",
      title: "Invalid Pay Date",
      text: "Pay Date must be on the 15th or the last day of the month.",
      confirmButtonText: "OK"
    });
    return;
  }
  if (!selectedEmployee.value) {
    await Swal.fire({
      icon: "warning",
      title: "Select an employee",
      text: "Please choose an employee before generating payroll.",
      confirmButtonText: "OK"
    });
    return;
  }

  await Swal.fire({
    title: "Generating Payroll...",
    text: "Please wait.",
    allowOutsideClick: false,
    timer: 800,
    timerProgressBar: true,
    didOpen: () => {
      Swal.showLoading();
    }
  });
  try {
    await axios.post("/hr/payrolls", {
      employee_id: selectedEmployee.value.id,
      days_present: attendance.value.daysPresent,
      days_absent: attendance.value.daysAbsent,
      late_days: attendance.value.lateDays,
      transport_allowance: 200,
      meal_allowance: 200,
      salary_loan: loans.value.salary,
      sss_loan: loans.value.sss,
      health_insurance: insurance.value.health,
      life_insurance: insurance.value.life,
      pay_date: payDate.value || null,
    });

    await Swal.fire({
      icon: "success",
      title: "Payroll Generated",
      text: "Payroll saved successfully.",
      confirmButtonText: "OK"
    });
    clearPayroll();
  } catch (error) {
    await Swal.fire({
      icon: "error",
      title: "Save failed",
      text: "Unable to save payroll.",
      confirmButtonText: "OK"
    });
  }
}

function clearPayroll(){
  attendance.value = { daysPresent:0, daysAbsent:0, lateDays:0, overtimeMinutes:0 };
  loans.value = { salary:0, sss:0 };
  insurance.value = { health:0, life:0 };
  payDate.value = "";
  lastValid.value = {
    attendance: { ...attendance.value },
    loans: { ...loans.value },
    insurance: { ...insurance.value }
  };
}

const fetchEmployees = async () => {
  try {
    const res = await axios.get("/hr/employees");
    employees.value = res.data.map((employee) => ({
      id: employee.id,
      name: employee.name,
    }));
  } catch {
    Swal.fire("Error", "Failed to load employees", "error");
  }
};


const fetchAttendanceSummary = async () => {
  if (!selectedEmployee.value?.id) return;
  try {
    const res = await axios.get('/hr/attendance/summary', {
      params: {
        employee_id: selectedEmployee.value.id,
        pay_date: payDate.value || null,
      },
    });
    attendance.value.daysPresent = res.data.days_present ?? 0;
    attendance.value.daysAbsent = res.data.days_absent ?? 0;
    attendance.value.lateDays = res.data.late_days ?? 0;
    attendance.value.overtimeMinutes = res.data.overtime_minutes ?? 0;
  } catch (err) {
    Swal.fire('Error', 'Failed to load attendance summary.', 'error');
  }
};

watch(payDate, () => {
  fetchAttendanceSummary();
});
onMounted(() => {
  syncPayDateBounds();
  fetchEmployees();
});

const formatLocalDate = (date) => {
  const year = date.getFullYear();
  const month = String(date.getMonth() + 1).padStart(2, "0");
  const day = String(date.getDate()).padStart(2, "0");
  return `${year}-${month}-${day}`;
};

const syncPayDateBounds = () => {
  const now = new Date();
  const fifteenth = new Date(now.getFullYear(), now.getMonth(), 15);
  const lastDay = new Date(now.getFullYear(), now.getMonth() + 1, 0);
  payDateOptions.value = [
    {
      value: formatLocalDate(fifteenth),
      label: `${fifteenth.toLocaleString('en-US', { month: 'long' })} 15, ${fifteenth.getFullYear()}`,
    },
  ];
  payDateOptions.value.push({
    value: formatLocalDate(lastDay),
    label: `${lastDay.toLocaleString('en-US', { month: 'long' })} ${lastDay.getDate()}, ${lastDay.getFullYear()}`,
  });
  payDateMin.value = payDateOptions.value[0].value;
  payDateMax.value = payDateOptions.value[payDateOptions.value.length - 1].value;
  if (payDate.value && !isValidPayDate(payDate.value)) {
    payDate.value = "";
  }
};

const isValidPayDate = (value) => {
  if (!value) return false;
  const day = Number(value.split("-")[2]);
  const now = new Date();
  const lastDay = new Date(now.getFullYear(), now.getMonth() + 1, 0).getDate();
  return day === 15 || day === lastDay;
};
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
