<template>
  <section>
    <!-- Header with title and Add Permit button aligned horizontally -->
    <div class="flex justify-between items-center mb-4">
      <h2 class="text-xl font-semibold text-gray-800">Business Permit Management</h2>
      <button @click="showModal = true" class="btn-primary">Add Permit</button>
    </div>

    <!-- MODAL -->
    <div v-if="showModal" class="fixed inset-0 bg-black bg-opacity-50 flex justify-center items-center z-50">
      <div class="bg-white rounded-lg shadow-lg w-1/2 p-6 relative">
        <h3 class="text-lg font-semibold mb-4">Add New Permit</h3>

        <form @submit.prevent="createPermit" class="space-y-3">
          <input v-model="newPermit.business_name" placeholder="Business Name" class="input" required />
          <input v-model="newPermit.owner_name" placeholder="Owner Name" class="input" required />
          <input v-model="newPermit.permit_number" placeholder="Permit Number" class="input" required />
          <input v-model="newPermit.business_type" placeholder="Business Type" class="input" />

          <select v-model="newPermit.status" class="input">
            <option>Pending</option>
            <option>Approved</option>
            <option>Rejected</option>
            <option>Suspended</option>
            <option>Expired</option>
          </select>

          <input type="file" @change="handleFileUpload" class="input" />

          <div class="flex justify-end gap-3 mt-4">
            <button type="button" class="btn-secondary" @click="showModal = false">Cancel</button>
            <button type="submit" class="btn-primary">Save</button>
          </div>
        </form>
      </div>
    </div>

    <!-- PERMIT TABLE -->
    <div class="bg-white rounded-lg shadow p-4 overflow-x-auto">
      <table class="w-full text-sm border-collapse">
        <thead class="bg-gray-100 text-gray-600">
          <tr>
            <th class="px-4 py-2 text-left">Business Name</th>
            <th class="px-4 py-2 text-left">Owner Name</th>
            <th class="px-4 py-2 text-left">Permit Number</th>
            <th class="px-4 py-2 text-left">Business Type</th>
            <th class="px-4 py-2 text-left">Status</th>
            <th class="px-4 py-2 text-left">File</th>
            <th class="px-4 py-2 text-right">Actions</th>
          </tr>
        </thead>
        <tbody>
          <tr v-for="permit in permits" :key="permit.id" class="border-t">
            <td class="px-4 py-2">{{ permit.business_name }}</td>
            <td class="px-4 py-2">{{ permit.owner_name }}</td>
            <td class="px-4 py-2">{{ permit.permit_number }}</td>
            <td class="px-4 py-2">{{ permit.business_type }}</td>
            <td class="px-4 py-2">
              <span
                :class="permit.status === 'Approved' ? 'bg-green-100 text-green-700' : permit.status === 'Rejected' ? 'bg-red-100 text-red-700' : 'bg-gray-100 text-gray-700'"
                class="text-xs px-2 py-0.5 rounded"
              >
                {{ permit.status }}
              </span>
            </td>
            <td class="px-4 py-2">
              <a v-if="permit.file" :href="`/storage/${permit.file}`" target="_blank" class="text-blue-600 underline text-xs">Download</a>
              <span v-else class="text-gray-400 text-xs">No file</span>
            </td>
            <td class="px-4 py-2 text-right space-x-2">
              <select v-model="permit.status" @change="updatePermitStatus(permit)" class="border rounded px-2 py-1 text-sm">
                <option>Pending</option>
                <option>Approved</option>
                <option>Rejected</option>
                <option>Suspended</option>
                <option>Expired</option>
              </select>
              <button @click="deletePermit(permit.id)" class="text-red-600 hover:underline text-sm">Delete</button>
            </td>
          </tr>
        </tbody>
      </table>
      <div v-if="permits.length === 0" class="p-4 text-center text-gray-500">No permits found.</div>
    </div>
  </section>
</template>

<script>
import axios from 'axios'
import Swal from 'sweetalert2'

export default {
  data() {
    return {
      permits: [],
      showModal: false,
      newPermit: {
        business_name: '',
        owner_name: '',
        permit_number: '',
        business_type: '',
        status: 'Pending',
        file: null
      }
    }
  },
  mounted() {
    this.fetchPermits()
  },
  methods: {
    async fetchPermits() {
      try {
        // Use correct API URL and include credentials for Sanctum
        const res = await axios.get('/api/permits', { withCredentials: true })
        if (Array.isArray(res.data)) this.permits = res.data
      } catch (err) {
        // Only show error if server actually fails
        console.error('Permit fetch error:', err)
      }
    },
    handleFileUpload(event) {
      this.newPermit.file = event.target.files[0]
    },
    async createPermit() {
      try {
        const formData = new FormData()
        for (let key in this.newPermit) {
          if (this.newPermit[key] !== null) formData.append(key, this.newPermit[key])
        }
        await axios.post('/api/permits', formData, { headers: { 'Content-Type': 'multipart/form-data' }, withCredentials: true })
        Swal.fire('Success', 'Permit added successfully', 'success')
        this.newPermit = { business_name:'', owner_name:'', permit_number:'', business_type:'', status:'Pending', file:null }
        this.showModal = false
        this.fetchPermits()
      } catch (err) {
        console.error('Create permit error:', err)
        Swal.fire('Error', 'Failed to add permit', 'error')
      }
    },
    async updatePermitStatus(permit) {
      try {
        await axios.put(`/api/permits/${permit.id}`, { status: permit.status }, { withCredentials: true })
        Swal.fire('Success', 'Permit status updated', 'success')
      } catch (err) {
        console.error('Update permit status error:', err)
        Swal.fire('Error', 'Failed to update permit status', 'error')
      }
    },
    async deletePermit(id) {
      const confirm = await Swal.fire({
        title: 'Are you sure?',
        text: 'This will delete the permit permanently!',
        icon: 'warning',
        showCancelButton: true,
        confirmButtonText: 'Yes, delete'
      })
      if (confirm.isConfirmed) {
        try {
          await axios.delete(`/api/permits/${id}`, { withCredentials: true })
          Swal.fire('Deleted!', 'Permit has been deleted', 'success')
          this.fetchPermits()
        } catch (err) {
          console.error('Delete permit error:', err)
          Swal.fire('Error', 'Failed to delete permit', 'error')
        }
      }
    }
  }
}
</script>

<style scoped>
.input {
  border: 1px solid #d1d5db;
  padding: 0.5rem;
  border-radius: 0.375rem;
  width: 100%;
}
.btn-primary {
  background-color: #2563eb;
  color: white;
  padding: 0.5rem 1rem;
  border-radius: 0.375rem;
  transition: background-color 0.2s;
}
.btn-primary:hover {
  background-color: #1d4ed8;
}
.btn-secondary {
  background-color: #9ca3af;
  color: white;
  padding: 0.5rem 1rem;
  border-radius: 0.375rem;
  transition: background-color 0.2s;
}
.btn-secondary:hover {
  background-color: #6b7280;
}
</style>
