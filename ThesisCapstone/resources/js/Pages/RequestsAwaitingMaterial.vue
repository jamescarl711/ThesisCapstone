<template>
  <div class="min-h-screen bg-gray-100 p-6">
    <h2 class="text-2xl font-bold mb-6">Requests Awaiting Material</h2>

    <div v-if="loading" class="text-gray-500">Loading requests...</div>

    <div v-else>
      <table class="min-w-full bg-white shadow rounded-xl overflow-hidden text-left text-sm">
        <thead class="bg-gray-100 uppercase text-xs text-gray-700 tracking-wider">
          <tr>
            <th class="px-4 py-3">Customer</th>
            <th class="px-4 py-3">Service</th>
            <th class="px-4 py-3">Business</th>
            <th class="px-4 py-3">Preferred Date</th>
            <th class="px-4 py-3">Address</th>
            <th class="px-4 py-3">Status</th>
            <th class="px-4 py-3">Materials Needed</th>
            <th class="px-4 py-3 text-center">Action</th>
          </tr>
        </thead>

        <tbody>
          <tr
            v-for="req in requests"
            :key="req.id"
            class="border-t hover:bg-gray-50 transition"
          >
            <td class="px-4 py-3 font-medium">{{ fullName(req) }}</td>
            <td class="px-4 py-3 font-semibold text-indigo-600">{{ req.service_type }}</td>
            <td class="px-4 py-3">{{ req.business_name }}</td>
            <td class="px-4 py-3">{{ formatDate(req.preferred_date) }}</td>
            <td class="px-4 py-3 max-w-[200px] truncate">{{ req.address_text }}</td>
            <td class="px-4 py-3">
              <span class="px-3 py-1 rounded-full text-xs font-semibold" :class="statusClass(req.status)">
                {{ req.status }}
              </span>
            </td>

            <!-- Materials Needed -->
            <td class="px-4 py-3">
              <div>
                <!-- Dropdown to add materials -->
                <select v-model="req.tempMaterial" @change="selectMaterial(req)" class="border rounded px-2 py-1 text-sm w-full">
                  <option disabled value="">Select material</option>
                  <option
                    v-for="m in getMaterialsForService(req.service_type)"
                    :key="m"
                    :value="m"
                  >
                    {{ m }}
                  </option>
                </select>

                <!-- List of selected materials below -->
                <ul class="mt-2 space-y-1">
                  <li
                    v-for="(mat, index) in req.selectedMaterials"
                    :key="index"
                    class="flex justify-between items-center bg-indigo-100 text-indigo-700 px-2 py-1 rounded text-sm"
                  >
                    {{ mat }}
                    <button @click="removeMaterial(req, index)" class="text-red-500 font-bold text-xs">×</button>
                  </li>
                </ul>
              </div>
            </td>

            <!-- Action Button -->
            <td class="px-4 py-3 text-center">
              <button
                @click="markReady(req)"
                class="bg-green-500 hover:bg-green-600 text-white px-4 py-2 rounded-lg text-xs font-semibold shadow"
              >
                Mark Job Ready
              </button>
            </td>
          </tr>
        </tbody>
      </table>

      <div v-if="!requests.length" class="text-center text-gray-500 py-10">
        📦 No requests awaiting materials
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref, onMounted } from 'vue'
import axios from 'axios'
import Swal from 'sweetalert2'

const requests = ref([])
const loading = ref(true)

const serviceMaterials = {
  plumbing: ['Pipe', 'Valve', 'Teflon Tape', 'Wrench', 'Faucet'],
  siphoning: ['Vacuum Pump', 'Hose', 'Bucket', 'Gloves', 'Sealant'],
  both: ['Pipe', 'Valve', 'Teflon Tape', 'Wrench', 'Vacuum Pump', 'Hose', 'Gloves']
}

// Helpers
const fullName = r => `${r.first_name ?? ''} ${r.middle_initial ? r.middle_initial + '.' : ''} ${r.last_name ?? ''}`
const formatDate = date => date ? new Date(date).toLocaleDateString('en-US', { year:'numeric', month:'long', day:'numeric' }) : '—'
const statusClass = status => {
  const classes = {
    accepted: 'bg-green-100 text-green-700',
    rejected: 'bg-red-100 text-red-700',
    assigned: 'bg-blue-100 text-blue-700',
    awaiting_material: 'bg-yellow-100 text-yellow-700',
    job_ready: 'bg-teal-100 text-teal-700',
    in_progress: 'bg-purple-100 text-purple-700',
    completed: 'bg-gray-200 text-gray-700'
  }
  return classes[status] || 'bg-gray-100 text-gray-700'
}

// Get materials for service type
const getMaterialsForService = (type) => {
  return serviceMaterials[type?.toLowerCase()] || []
}

// Fetch requests
const fetchRequests = async () => {
  loading.value = true
  try {
    const res = await axios.get('/procurement/requests-awaiting-material')
    requests.value = res.data.map(r => ({ ...r, selectedMaterials: [], tempMaterial: '' }))
  } catch {
    Swal.fire('Error', 'Failed to fetch requests', 'error')
  } finally {
    loading.value = false
  }
}

// Add material from dropdown
const selectMaterial = (req) => {
  if (req.tempMaterial && !req.selectedMaterials.includes(req.tempMaterial)) {
    req.selectedMaterials.push(req.tempMaterial)
  }
  req.tempMaterial = ''
}

// Remove material
const removeMaterial = (req, index) => {
  req.selectedMaterials.splice(index, 1)
}

// Mark job ready
const markReady = async (req) => {
  if (!req.selectedMaterials.length) {
    Swal.fire('Select Materials', 'Please select at least one material', 'warning')
    return
  }

  try {
    await axios.post(`/procurement/mark-job-ready/${req.id}`, {
      materials: req.selectedMaterials
    })
    Swal.fire('Success', 'Request marked as Job Ready', 'success')
    fetchRequests()
  } catch {
    Swal.fire('Error', 'Failed to update request', 'error')
  }
}

onMounted(fetchRequests)
</script>

<style scoped>
table th, table td {
  white-space: nowrap;
  overflow: hidden;
  text-overflow: ellipsis;
}

select {
  min-width: 150px;
}

ul {
  list-style: none;
  padding: 0;
  margin: 0;
}

button:focus {
  outline: none;
}
</style>
