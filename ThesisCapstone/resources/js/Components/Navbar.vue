<template>
  <nav class="navbar">
    <h1>{{ title }}</h1>
    <button @click="logout">Logout</button>
  </nav>
</template>

<script>
export default {
  props:['title'],
  methods:{
    logout(){
      alert('You have been logged out!');
      // Here you can redirect to login page if needed
    }
  }
}
</script>

<style scoped>
.navbar {
  height:60px;
  background:#2980b9;
  color:white;
  display:flex;
  justify-content:space-between;
  align-items:center;
  padding:0 20px;
  font-weight:bold;
}
button {
  background:#e74c3c;
  border:none;
  color:white;
  padding:6px 12px;
  border-radius:6px;
  cursor:pointer;
}
button:hover { background:#c0392b; }
</style>
