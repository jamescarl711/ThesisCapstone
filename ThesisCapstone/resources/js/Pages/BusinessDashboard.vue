<template>
  <div class="min-h-screen flex flex-col bg-gray-100">

    <!-- NAVBAR -->
    <nav class="bg-white shadow px-6 py-4 flex justify-between items-center">
      <h1 class="text-xl font-bold">Business Dashboard</h1>
      <button
        @click="confirmLogout"
        class="bg-gray-100 px-3 py-1 rounded hover:bg-gray-200">
        Logout
      </button>
    </nav>

    <div class="flex flex-1">

      <!-- SIDEBAR (2 LANG) -->
      <aside class="w-64 bg-white p-6 border-r">
        <ul class="space-y-2">
          <li
            @click="section='applications'"
            :class="section==='applications' ? active : normal">
            Pending Applications
          </li>
          <li
            @click="section='requests'"
            :class="section==='requests' ? active : normal">
            Service Requests
          </li>
        </ul>
      </aside>

      <!-- MAIN -->
      <main class="flex-1 p-6">
        <PendingApplications
          v-if="section==='applications'"
          :applications="applications"
          :loading="loadingApplications"
          @review="reviewApplication"
          @view="openModal"
        />

        <ServiceRequests
          v-if="section==='requests'"
          :requests="requests"
          :loading="loadingRequests"
          @view="openModal"
        />
      </main>
    </div>

    <!-- MODAL (NASA LOOB NA, HINDI HIWALAY FILE) -->
    <div
      v-if="modalOpen"
      class="fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center z-50">
      <div class="bg-white p-6 rounded w-full max-w-lg relative">
        <button
          class="absolute top-2 right-2 text-xl"
          @click="modalOpen=false">
          &times;
        </button>

        <h3 class="text-xl font-bold mb-4">
          {{ selected.user_name || selected.service_name }}
        </h3>

        <!-- APPLICATION VIEW -->
        <div v-if="modalType==='application'">
          <p><strong>Category:</strong> {{ selected.category }}</p>
          <p><strong>Experience:</strong> {{ selected.experience_years || 'N/A' }} yrs</p>
          <p><strong>Description:</strong> {{ selected.service_description || 'N/A' }}</p>
        </div>

        <!-- REQUEST VIEW -->
        <div v-if="modalType==='request'">
          <p><strong>Service:</strong> {{ selected.service_name }}</p>
          <p><strong>Status:</strong> {{ selected.status }}</p>

          <div
            v-if="selected.status==='Pending'"
            class="flex gap-2 mt-4">
            <button
              @click="handleRequest('accept')"
              class="bg-green-600 text-white px-3 py-1 rounded">
              Accept
            </button>
            <button
              @click="handleRequest('reject')"
              class="bg-red-600 text-white px-3 py-1 rounded">
              Reject
            </button>
          </div>
        </div>

      </div>
    </div>

  </div>
</template>

<script setup>
import { ref, onMounted } from 'vue'
import axios from 'axios'
import Swal from 'sweetalert2'

import PendingApplications from './PendingApplications.vue'
import ServiceRequests from './ServiceRequests.vue'

/* UI */
const section = ref('applications')
const active = 'font-bold text-blue-600'
const normal = 'cursor-pointer hover:text-blue-500'

/* DATA */
const applications = ref([])
const requests = ref([])

const loadingApplications = ref(false)
const loadingRequests = ref(false)

/* MODAL */
const modalOpen = ref(false)
const selected = ref({})
const modalType = ref('')

/* FETCH */
const fetchApplications = async () => {
  loadingApplications.value = true
  const res = await axios.get('/business/provider-applications')
  applications.value = res.data
  loadingApplications.value = false
}

const fetchRequests = async () => {
  loadingRequests.value = true
  const res = await axios.get('/business/service-requests')
  requests.value = res.data
  loadingRequests.value = false
}

/* ACTIONS */
const reviewApplication = async ({ id, action, reason = null }) => {
  try {
    // confirm muna
    const confirm = await Swal.fire({
      title: action === 'approve'
        ? 'Approve this application?'
        : 'Reject this application?',
      icon: 'warning',
      showCancelButton: true
    })

    if (!confirm.isConfirmed) return

    // axios call
    await axios.post(`/business/provider-applications/${id}/review`, {
      action,
      reason
    })

    // ✅ INSTANT UI UPDATE (NO REFRESH)
    applications.value = applications.value.filter(app => app.id !== id)

    Swal.fire({
      icon: 'success',
      title: action === 'approve'
        ? 'Application Approved'
        : 'Application Rejected'
    })

  } catch (error) {
    console.error(error)

    Swal.fire({
      icon: 'error',
      title: 'Action failed',
      text: error.response?.data?.message || 'Something went wrong'
    })
  }
}


const handleRequest = async (action) => {
  let payload = { action }

  if (action === 'reject') {
    const res = await Swal.fire({
      title: 'Reason for rejection',
      input: 'textarea',
      showCancelButton: true
    })
    if (!res.value) return
    payload.reason = res.value
  }

  await axios.post(
    `/business/service-requests/${selected.value.id}/review`,
    payload
  )

  modalOpen.value = false
  fetchRequests()
}

const openModal = ({ item, type }) => {
  selected.value = item
  modalType.value = type
  modalOpen.value = true
}

const confirmLogout = async () => {
  await axios.post('/logout')
  window.location.href = '/login'
}

onMounted(() => {
  fetchApplications()
  fetchRequests()
})
</script>
