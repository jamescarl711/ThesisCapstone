<!-- Components/Sidebar.vue -->
<template>
  <aside class="sidebar">
    <h2>{{ title }}</h2>
    <ul>
      <li v-for="item in menuItems" :key="item.name" @click="$emit('navigate', item.link)">
        {{ item.name }}
      </li>
    </ul>
  </aside>
</template>

<script>
export default {
  props: ['dashboard'], // "procurement", "hr", or "finance"
  computed: {
    title() {
      if (this.dashboard === 'procurement') return 'Procurement';
      if (this.dashboard === 'hr') return 'HR';
      if (this.dashboard === 'finance') return 'Finance';
      return 'Dashboard';
    },
    menuItems() {
      if (this.dashboard === 'procurement') {
        return [
          { name: 'Inventory Levels', link: 'inventory' },
          { name: 'Purchase Orders', link: 'po' },
          { name: 'Supplier Performance', link: 'supplier' },
          { name: 'Cost Tracking', link: 'cost' },
          { name: 'Top-used Materials', link: 'top_materials' }
        ];
      }
      if (this.dashboard === 'hr') {
        return [
          { name: 'Employee Headcount', link: 'headcount' },
          { name: 'Attendance & Work Hours', link: 'attendance' },
          { name: 'Training & Certification', link: 'training' },
          { name: 'Leaves & Absences', link: 'leaves' },
          { name: 'Performance Metrics', link: 'performance' },
          { name: 'Safety Incidents', link: 'safety' }
        ];
      }
      if (this.dashboard === 'finance') {
        return [
          { name: 'Project Expenses vs Budget', link: 'expenses' },
          { name: 'Revenue Tracking', link: 'revenue' },
          { name: 'Accounts Payable & Receivable', link: 'accounts' },
          { name: 'Cash Flow Forecast', link: 'cashflow' },
          { name: 'Profitability Metrics', link: 'profit' },
          { name: 'Cost Breakdown', link: 'costbreakdown' }
        ];
      }
      return [];
    }
  }
}
</script>

<style scoped>
.sidebar { width:220px; background:#2c3e50; color:white; padding:20px; height:100vh; display:flex; flex-direction:column; }
.sidebar h2 { font-size:18px; margin-bottom:20px; }
.sidebar ul { list-style:none; padding:0; }
.sidebar li { padding:10px 15px; margin-bottom:10px; cursor:pointer; border-radius:6px; transition:0.2s; }
.sidebar li:hover { background:#34495e; }
</style>
