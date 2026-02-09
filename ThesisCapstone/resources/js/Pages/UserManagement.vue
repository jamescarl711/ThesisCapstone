<template>
  <div class="p-6 bg-gray-50 min-h-screen space-y-6">

    <!-- HEADER -->
    <div class="flex flex-col md:flex-row md:items-center md:justify-between gap-4">
      <h2 class="text-3xl font-bold text-gray-800">User Management</h2>

      <!-- Add User Button -->
      <button
        @click="showAddModal = true"
        class="px-5 py-2 rounded-lg bg-gradient-to-r from-blue-600 to-indigo-600
               text-white font-semibold shadow-lg hover:opacity-90 transition"
      >
        + Add New User
      </button>
    </div>

    <!-- FILTER TABS -->
    <div class="flex flex-wrap gap-2 mt-4">
      <button @click="filter='all'" :class="tabClass('all')">All</button>
      <button @click="filter='approved'" :class="tabClass('approved')">Approved</button>
      <button @click="filter='pending'" :class="tabClass('pending')">Pending</button>
    </div>

    <!-- USERS TABLE -->
    <div class="bg-white rounded-xl shadow-lg overflow-hidden mt-4">
      <table class="min-w-full text-sm">
        <thead class="bg-gray-100 text-gray-600 uppercase text-xs border-b">
          <tr>
            <th class="px-6 py-3 text-left">Full Name</th>
            <th class="px-6 py-3 text-left">Email</th>
            <th class="px-6 py-3 text-left">Role</th>
            <th class="px-6 py-3 text-center">Status</th>
            <th class="px-6 py-3 text-right">Actions</th>
          </tr>
        </thead>
        <tbody class="divide-y">
          <tr
            v-for="user in paginatedUsers"
            :key="user.id"
            class="hover:bg-gray-50 transition"
          >
            <td class="px-6 py-4 font-medium text-gray-800">{{ user.name }}</td>
            <td class="px-6 py-4 text-gray-600">{{ user.email }}</td>
            <td class="px-6 py-4">
              <span class="px-2 py-1 rounded-full text-xs font-semibold bg-indigo-100 text-indigo-700">
                {{ user.role_label }}
              </span>
            </td>
            <td class="px-6 py-4 text-center">
              <span
                v-if="user.is_approved"
                class="px-3 py-1 rounded-full text-xs font-semibold bg-green-100 text-green-700"
              >Approved</span>
              <span
                v-else
                class="px-3 py-1 rounded-full text-xs font-semibold bg-yellow-100 text-yellow-700"
              >Pending</span>
            </td>
            <td class="px-6 py-4 text-right space-x-2">
              <button
                v-if="!user.is_approved"
                @click="openViewUser(user.id)"
                class="px-3 py-1 rounded-md text-xs font-medium bg-blue-50 text-blue-700 hover:bg-blue-100 transition"
              >
                View
              </button>
            </td>
          </tr>

          <tr v-if="filteredUsers.length === 0">
            <td colspan="5" class="py-8 text-center text-gray-500">No users found.</td>
          </tr>
        </tbody>
      </table>
    </div>

    <!-- PAGINATION -->
    <div class="flex justify-between items-center mt-4">
      <p class="text-sm text-gray-500">Page {{ currentPage }} of {{ totalPages }}</p>
      <div class="space-x-2">
        <button
          @click="prevPage"
          :disabled="currentPage === 1"
          class="px-4 py-2 rounded border text-sm disabled:opacity-40 disabled:cursor-not-allowed hover:bg-gray-100 transition"
        >Previous</button>
        <button
          @click="nextPage"
          :disabled="currentPage === totalPages"
          class="px-4 py-2 rounded border text-sm disabled:opacity-40 disabled:cursor-not-allowed hover:bg-gray-100 transition"
        >Next</button>
      </div>
    </div>

    <!-- ADD USER MODAL -->
    <div
      v-if="showAddModal"
      @click.self="showAddModal=false"
      class="fixed inset-0 flex items-center justify-center bg-black/50 z-50"
    >
      <div class="bg-white rounded-lg p-6 w-full max-w-md shadow-lg relative">
        <h3 class="text-lg font-semibold mb-4">Add New User</h3>
        <form @submit.prevent="addUser" class="space-y-4">

          <div>
            <label class="block mb-1 font-medium">First Name</label>
            <input v-model="newUser.first_name" type="text" class="w-full border px-3 py-2 rounded" required />
          </div>

          <div>
            <label class="block mb-1 font-medium">Middle Initial</label>
            <input v-model="newUser.middle_initial" type="text" maxlength="1" class="w-full border px-3 py-2 rounded" />
          </div>

          <div>
            <label class="block mb-1 font-medium">Last Name</label>
            <input v-model="newUser.last_name" type="text" class="w-full border px-3 py-2 rounded" required />
          </div>

          <div>
            <label class="block mb-1 font-medium">Email</label>
            <input v-model="newUser.email" type="email" class="w-full border px-3 py-2 rounded" required />
          </div>

          <div>
            <label class="block mb-1 font-medium">Password</label>
            <input v-model="newUser.password" type="password" class="w-full border px-3 py-2 rounded" required minlength="8" />
          </div>

          <div>
            <label class="block mb-1 font-medium">Confirm Password</label>
            <input v-model="newUser.password_confirmation" type="password" class="w-full border px-3 py-2 rounded" required minlength="8" />
          </div>

          <div>
            <label class="block mb-1 font-medium">Role</label>
            <select v-model="newUser.role" class="w-full border px-3 py-2 rounded" required>
              <option value="admin">Admin</option>
              <option value="hr">HR</option>
              <option value="finance">Finance</option>
              <option value="procurement">Procurement</option>
            </select>
          </div>

          <div class="flex justify-end space-x-2 mt-4">
            <button type="button" @click="showAddModal=false" class="px-4 py-2 border rounded">Cancel</button>
            <button type="submit" class="px-4 py-2 bg-blue-600 text-white rounded hover:bg-blue-700">Add User</button>
          </div>

        </form>
      </div>
    </div>

    <!-- VIEW USER MODAL -->
    <ViewUser
      ref="viewUserModal"
      @user-approved="handleApproved"
      @user-rejected="handleRejected"
    />

  </div>
</template>

<script>
import axios from 'axios'
import Swal from 'sweetalert2'
import ViewUser from './ViewUser.vue'

export default {
  components: { ViewUser },

  data() {
    return {
      users: [],
      filter: 'all',
      showAddModal: false,
      currentPage: 1,
      perPage: 10,
      newUser: {
        first_name: '',
        middle_initial: '',
        last_name: '',
        email: '',
        password: '',
        password_confirmation: '',
        role: 'admin',
      },
      roleNames: {
        admin: 'Admin',
        hr: 'HR',
        finance: 'Finance',
        procurement: 'Procurement',
        user: 'User',
        business: 'Business',
        serviceprovider: 'Service Provider'
      }
    }
  },

  computed: {
    filteredUsers() {
      const visible = this.users.filter(u => u.role !== 'employee')
      if(this.filter==='approved') return visible.filter(u=>u.is_approved)
      if(this.filter==='pending') return visible.filter(u=>!u.is_approved)
      return visible
    },
    totalPages() {
      return Math.ceil(this.filteredUsers.length / this.perPage) || 1
    },
    paginatedUsers() {
      const start = (this.currentPage-1)*this.perPage
      return this.filteredUsers.slice(start,start+this.perPage)
    }
  },

  watch: {
    filter() { this.currentPage = 1 }
  },

  mounted() {
    this.fetchUsers()
  },

  methods: {
    tabClass(type){
      return [
        'px-4 py-2 rounded-lg text-sm font-medium transition',
        this.filter===type
          ? 'bg-gradient-to-r from-blue-600 to-indigo-600 text-white shadow-md'
          : 'bg-white border border-gray-300 text-gray-600 hover:bg-gray-50'
      ]
    },

    fetchUsers(){
      axios.get('/admin/users')
        .then(res=>{
          this.users = res.data.map(u=>({
            ...u,
            name: `${u.first_name || ''} ${u.middle_initial ? u.middle_initial+'. ' : ''}${u.last_name || ''}`.trim(),
            role_label: this.roleNames[u.role]||u.role,
            has_viewed: u.has_viewed||false
          }))
        })
        .catch(()=>Swal.fire('Error','Failed to fetch users','error'))
    },

    openViewUser(id){
      this.$refs.viewUserModal.openUserModal(id)
      const user = this.users.find(u=>u.id===id)
      if(user) user.has_viewed = true
      axios.post(`/admin/users/${id}/mark-viewed`).catch(()=>{})
    },

    handleApproved(payload){
      const user = this.users.find(u=>u.id===payload.id)
      if(user) user.is_approved = payload.is_approved
    },

    handleRejected(id){
      this.users = this.users.filter(u=>u.id!==id)
      if(this.currentPage > this.totalPages) this.currentPage = this.totalPages
    },


    addUser(){
      axios.post('/admin/users',this.newUser)
        .then(res=>{
          const u=res.data
          this.users.unshift({
            ...u,
            name: `${u.first_name || ''} ${u.middle_initial ? u.middle_initial+'. ' : ''}${u.last_name || ''}`.trim(),
            role_label:this.roleNames[u.role]||u.role,
            has_viewed:false
          })
          this.showAddModal=false
          this.currentPage=1
          this.newUser = { first_name:'',middle_initial:'',last_name:'',email:'',password:'',password_confirmation:'',role:'admin' }
          Swal.fire('Success','User added successfully','success')
        })
        .catch(err=>{
          const firstError = err.response?.data?.errors ? Object.values(err.response.data.errors)[0][0] : 'Failed to add user'
          Swal.fire('Error',firstError,'error')
        })
    },

    nextPage(){ if(this.currentPage<this.totalPages) this.currentPage++ },
    prevPage(){ if(this.currentPage>1) this.currentPage-- },
  }
}
</script>
