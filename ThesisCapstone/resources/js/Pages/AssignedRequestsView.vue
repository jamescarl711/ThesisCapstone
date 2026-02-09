<template>
  <div class="min-h-screen bg-gray-100 p-6">
    <h2 class="text-2xl font-bold mb-6">Assigned Requests</h2>

    <div v-if="loading" class="text-gray-500">
      Loading assigned requests...
    </div>

    <div v-else>
      <div v-if="assignedRequests.length" class="space-y-5">

        <div
          v-for="req in assignedRequests"
          :key="req.id"
          class="bg-white rounded-xl shadow p-5"
        >
          <!-- Header -->
          <div class="flex justify-between items-center mb-2">
            <p class="font-semibold text-lg">
              {{ req.first_name }}
              {{ req.middle_initial ? req.middle_initial + '.' : '' }}
              {{ req.last_name }}
            </p>

            <span
              class="text-xs px-3 py-1 rounded-full"
              :class="statusClass(req.status)"
            >
              {{ req.status }}
            </span>
          </div>

          <!-- Details -->
          <div class="text-sm text-gray-600 space-y-1 mb-3">
            <p>Service Type: {{ req.service_type }}</p>
            <p>Preferred Date: {{ formatDate(req.preferred_date) }}</p>
            <p>Address: {{ req.address_text }}</p>
          </div>

          <!-- ASSIGNED -->
          <div v-if="req.status === 'assigned'" class="flex gap-2">
            <button
              @click="updateRequest(req, 'awaiting_material')"
              class="bg-green-500 hover:bg-green-600 text-white px-3 py-1 rounded-md text-sm"
            >
              Accept
            </button>
            <button
              @click="updateRequest(req, 'rejected')"
              class="bg-red-500 hover:bg-red-600 text-white px-3 py-1 rounded-md text-sm"
            >
              Reject
            </button>
          </div>

          <!-- AWAITING MATERIAL -->
          <div v-else-if="req.status === 'awaiting_material'" class="text-sm text-purple-600 italic">
            Waiting for materials from procurement...
          </div>

          <!-- JOB READY -->
          <div v-else-if="req.status === 'job_ready'">
            <button
              @click="startJob(req)"
              class="bg-blue-500 hover:bg-blue-600 text-white px-4 py-1 rounded-md text-sm"
            >
              Start Job
            </button>
          </div>

          <!-- IN PROGRESS -->
          <div v-else-if="req.status === 'in_progress'">
            <div class="mb-2">
              <label class="block text-sm font-medium text-gray-700 mb-1">
                Upload Photos (Proof)
              </label>
              <input
                type="file"
                multiple
                @change="onFileChange($event, req)"
                class="block w-full text-sm text-gray-500"
              />
              <p class="text-xs text-gray-400 mt-1">
                Upload at least one photo
              </p>
            </div>

            <button
              @click="completeJob(req)"
              class="bg-green-500 hover:bg-green-600 text-white px-4 py-1 rounded-md text-sm"
            >
              Complete Job
            </button>
          </div>

          <!-- COMPLETED -->
          <div v-else-if="req.status === 'completed'" class="text-green-600 font-medium">
            ✔ Job Completed
          </div>

        </div>
      </div>

      <p v-else class="italic text-gray-500">
        No assigned requests found.
      </p>
    </div>
  </div>
</template>

<script setup>
import { ref, onMounted } from 'vue'
import axios from 'axios'
import Swal from 'sweetalert2'

const assignedRequests = ref([])
const loading = ref(true)

// FETCH REQUESTS
const fetchAssignedRequests = async () => {
  loading.value = true
  try {
    const res = await axios.get('/user/service-provider/assigned-requests')
    assignedRequests.value = res.data.map(r => ({
      ...r,
      photos: []
    }))
  } catch (err) {
    Swal.fire('Error', err.response?.data?.message || 'Failed to fetch assigned requests', 'error')
  } finally {
    loading.value = false
  }
}

// DATE FORMAT
const formatDate = (date) => {
  if (!date) return '—'
  return new Date(date).toLocaleDateString('en-US', {
    year: 'numeric',
    month: 'long',
    day: 'numeric'
  })
}

// STATUS BADGE
const statusClass = (status) => {
  return {
    assigned: 'bg-blue-100 text-blue-700',
    awaiting_material: 'bg-purple-100 text-purple-700',
    job_ready: 'bg-teal-100 text-teal-700',
    in_progress: 'bg-yellow-100 text-yellow-700',
    completed: 'bg-green-200 text-green-800',
    rejected: 'bg-red-100 text-red-700'
  }[status] || 'bg-gray-100 text-gray-700'
}

// UPDATE REQUEST (ACCEPT / REJECT / etc.)
const updateRequest = async (req, status) => {
  try {
    const res = await axios.post(`/service-provider/update-request/${req.id}`, { status })
    req.status = res.data.status  // use backend response
    Swal.fire('Success', `Status updated to "${req.status}"`, 'success')
  } catch (err) {
    Swal.fire('Error', err.response?.data?.message || 'Update failed', 'error')
  }
}


// FILE UPLOAD
const onFileChange = (event, req) => {
  req.photos = Array.from(event.target.files)
}

// START JOB
const startJob = async (req) => {
  try {
    await axios.post(`/service-provider/update-request/${req.id}`, {
      status: 'in_progress'
    })
    req.status = 'in_progress'
    Swal.fire('Started', 'Job is now in progress', 'success')
  } catch {
    Swal.fire('Error', 'Failed to start job', 'error')
  }
}

// COMPLETE JOB
const completeJob = async (req) => {
  if (!req.photos.length) {
    Swal.fire('Required', 'Please upload at least one photo', 'warning')
    return
  }

  const formData = new FormData()
  req.photos.forEach(file => formData.append('photos[]', file))

  try {
    await axios.post(
      `/service-provider/complete-job/${req.id}`,
      formData,
      { headers: { 'Content-Type': 'multipart/form-data' } }
    )
    req.status = 'completed'
    Swal.fire('Success', 'Job completed!', 'success')
  } catch (err) {
    Swal.fire('Error', err.response?.data?.message || 'Completion failed', 'error')
  }
}

onMounted(fetchAssignedRequests)
</script>
