<template>
  <div class="layout">
    <aside class="sidebar">
      <h2>Finance</h2>
      <ul>
        <li class="active">Project Expenses</li>
        <li>Revenue Tracking</li>
        <li>Accounts Payable/Receivable</li>
        <li>Cash Flow Forecast</li>
        <li>Profitability & Cost Breakdown</li>
      </ul>
    </aside>

    <div class="main">
      <nav class="navbar">
        <h1>Finance Dashboard</h1>
        <button @click="logout">Logout</button>
      </nav>

      <div class="dashboard">
        <section class="card">
          <h2>Project Expenses vs Budget</h2>
          <canvas id="budgetChart"></canvas>
        </section>

        <section class="card">
          <h2>Cash Flow</h2>
          <canvas id="cashFlowChart"></canvas>
        </section>

        <section class="card">
          <h2>Cost Breakdown</h2>
          <canvas id="costChart"></canvas>
        </section>
      </div>
    </div>
  </div>
</template>

<script setup>
import { onMounted } from "vue";
import { Chart, registerables } from "chart.js";
Chart.register(...registerables);

// FIXED LOGOUT
function logout() {
  window.location.href = '/login';
}

onMounted(() => {
  const ctx1 = document.getElementById("budgetChart").getContext("2d");
  new Chart(ctx1, {
    type: "bar",
    data: {
      labels: ["Project A","Project B","Project C"],
      datasets: [
        { label: "Budget", data:[5000,7000,6000], backgroundColor:"#555" },
        { label: "Actual", data:[4800,7200,5800], backgroundColor:"#888" }
      ]
    }
  });

  const ctx2 = document.getElementById("cashFlowChart").getContext("2d");
  new Chart(ctx2, {
    type: "line",
    data: {
      labels: ["Jan","Feb","Mar","Apr","May"],
      datasets: [
        { label: "Cash Flow", data:[2000,2500,2200,2800,3000], borderColor:"#2e2e2e", fill:false }
      ]
    }
  });

  const ctx3 = document.getElementById("costChart").getContext("2d");
  new Chart(ctx3, {
    type: "pie",
    data: {
      labels: ["Labor","Materials","Overhead"],
      datasets: [{ data:[40,35,25], backgroundColor:["#555","#888","#bbb"] }]
    }
  });
});
</script>

<style scoped>
* { font-family: "Times New Roman", serif; margin:0; padding:0; box-sizing:border-box; }
.layout { display:flex; height:100vh; }
.sidebar { width:200px; background:#f5f5f5; padding:20px; }
.sidebar h2 { margin-bottom:20px; font-size:18px; }
.sidebar ul { list-style:none; }
.sidebar li { padding:8px; margin-bottom:5px; cursor:pointer; }
.sidebar li.active, .sidebar li:hover { font-weight:bold; text-decoration:underline; }
.main { flex:1; display:flex; flex-direction:column; overflow-y:auto; padding:20px; }
.navbar { height:50px; display:flex; justify-content:space-between; align-items:center; border-bottom:1px solid #ccc; padding:0 20px; }
.navbar button { padding:5px 10px; cursor:pointer; }
.dashboard { margin-top:20px; display:flex; flex-direction:column; gap:20px; }
.card { padding:15px; border:1px solid #ccc; border-radius:6px; background:#fff; }
</style>
