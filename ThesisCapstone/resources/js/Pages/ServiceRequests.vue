<template>
  <div class="p-6">
    <h2 class="text-2xl font-bold mb-4">Service Requests</h2>

    <div v-if="loading">Loading...</div>
    <div v-else-if="requestList.length === 0">
      No service requests.
    </div>

    <table v-else class="min-w-full bg-white shadow rounded">
      <thead class="bg-gray-100">
        <tr>
          <th class="p-2 text-left">User</th>
          <th class="p-2 text-left">Service</th>
          <th class="p-2 text-left">Status</th>
          <th class="p-2 text-left">Actions</th>
        </tr>
      </thead>
      <tbody>
        <tr v-for="req in requestList" :key="req.id" class="border-t">
          <td class="p-2">{{ req.user_name }}</td>
          <td class="p-2">{{ req.service_name }}</td>
          <td class="p-2 capitalize">{{ req.status }}</td>
          <td class="p-2">
            <button
              @click="openView(req)"
              class="bg-gray-600 text-white px-2 rounded"
            >
              View
            </button>
          </td>
        </tr>
      </tbody>
    </table>

    <!-- VIEW MODAL -->
    <div
      v-if="showModal"
      class="fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center z-50"
    >
      <div class="bg-white w-full max-w-3xl p-6 rounded relative">
        <button
          class="absolute top-3 right-4 text-xl"
          @click="closeModal"
        >
          &times;
        </button>

        <h3 class="text-xl font-bold mb-4">Service Request Details</h3>

        <table class="w-full border">
          <tbody>
            <tr class="border-t">
              <td class="p-2 font-semibold w-40">User</td>
              <td class="p-2">{{ selected.user_name }}</td>
            </tr>
            <tr class="border-t">
              <td class="p-2 font-semibold">Service</td>
              <td class="p-2">{{ selected.service_name }}</td>
            </tr>
            <tr class="border-t">
              <td class="p-2 font-semibold">Status</td>
              <td class="p-2 capitalize">{{ selected.status }}</td>
            </tr>
            <tr class="border-t" v-if="selected.details">
              <td class="p-2 font-semibold">Details</td>
              <td class="p-2">{{ selected.details }}</td>
            </tr>
          </tbody>
        </table>

        <div class="flex justify-end gap-3 mt-6" v-if="selected.status==='pending'">
          <button
            @click="handleApprove"
            class="bg-green-600 text-white px-4 py-2 rounded"
          >
            Approve
          </button>

          <button
            @click="handleReject"
            class="bg-red-600 text-white px-4 py-2 rounded"
          >
            Reject
          </button>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref, onMounted } from 'vue'
import axios from 'axios'
import Swal from 'sweetalert2'

const showModal = ref(false)
const selected = ref({})
const requestList = ref([])
const loading = ref(true)

const fetchRequests = async () => {
  loading.value = true
  try {
    const res = await axios.get('/business/service-requests')
    console.log('API Response:', res.data) // ✅ log to check what the backend returns

    // Map fields to match frontend
    requestList.value = res.data.map(r => ({
      ...r,
      user_name: r.user_name || `${r.user?.first_name || ''} ${r.user?.middle_initial ? r.user.middle_initial+'. ' : ''}${r.user?.last_name || ''}`,
      service_name: r.service_name || r.service_type || (r.category || 'N/A'),
      details: r.details || r.notes || '',
    }))

    console.log('Mapped requestList:', requestList.value) // ✅ log mapped list
  } catch (err) {
    console.error('Error fetching requests:', err)
    Swal.fire('Error','Failed to fetch requests','error')
  } finally {
    loading.value = false
  }
}

const openView = (req) => {
  selected.value = { ...req }
  showModal.value = true
}

const closeModal = () => {
  showModal.value = false
  selected.value = {}
}

const reviewRequest = async ({ id, action, reason = null }) => {
  try {
    const payload = { action }
    if(action==='reject') payload.reason = reason

    await axios.post(`/business/service-requests/${id}/review`, payload)

    Swal.fire('Success','Request updated','success')

    requestList.value = requestList.value.filter(r => r.id !== id)
  } catch(err) {
    console.error(err.response?.data)
    Swal.fire('Error', err.response?.data?.message || 'Validation error','error')
  }
}

const handleApprove = async () => {
  const confirm = await Swal.fire({
    title:'Approve this request?',
    text:'The request will be marked as approved.',
    icon:'question',
    showCancelButton:true,
    confirmButtonText:'Yes, approve'
  })
  if(!confirm.isConfirmed) return

  await reviewRequest({id:selected.value.id, action:'approve'})
  closeModal()
}

const handleReject = async () => {
  const { value: reason } = await Swal.fire({
    title:'Reject Request',
    input:'textarea',
    inputLabel:'Reason for rejection',
    inputPlaceholder:'Type reason here...',
    showCancelButton:true,
    inputValidator:(val)=> !val?.trim() ? 'Reason is required' : null
  })

  if(!reason) return

  const confirm = await Swal.fire({
    title:'Are you sure?',
    text:'This will reject the request and notify the user.',
    icon:'warning',
    showCancelButton:true,
    confirmButtonText:'Yes, reject'
  })
  if(!confirm.isConfirmed) return

  await reviewRequest({id:selected.value.id, action:'reject', reason:reason.trim()})
  closeModal()
}

// Fetch on mount
onMounted(() => {
  fetchRequests()
})
</script>
