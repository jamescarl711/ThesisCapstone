<template>
  <div>
    <h2 class="text-2xl font-bold mb-4">Pending Applications</h2>

    <!-- Loading -->
    <div v-if="loading">Loading...</div>

    <div v-else-if="applications.length === 0">
      No pending applications.
    </div>

    <!-- Applications Table -->
    <table v-else class="min-w-full bg-white shadow rounded">
      <thead class="bg-gray-100">
        <tr>
          <th class="p-2 text-left">Applicant</th>
          <th class="p-2 text-left">Category</th>
          <th class="p-2 text-left">Actions</th>
        </tr>
      </thead>
      <tbody>
        <tr
          v-for="app in applications"
          :key="app.id"
          class="border-t"
        >
          <td class="p-2">{{ app.user_name }}</td>
          <td class="p-2 capitalize">{{ app.category }}</td>
          <td class="p-2">
            <button
              @click="openView(app)"
              class="bg-gray-700 text-white px-3 py-1 rounded"
            >
              View
            </button>
          </td>
        </tr>
      </tbody>
    </table>

    <!-- VIEW MODAL -->
    <div
      v-if="showModal && selected"
      class="fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center z-50"
    >
      <div class="bg-white w-full max-w-3xl p-6 rounded relative">
        <button
          class="absolute top-3 right-4 text-xl"
          @click="closeModal"
        >
          &times;
        </button>

        <h3 class="text-xl font-bold mb-4">
          Application Details
        </h3>

        <!-- DETAILS -->
        <table class="w-full border">
          <tbody>
            <tr class="border-t">
              <td class="p-2 font-semibold w-40">Name</td>
              <td class="p-2">{{ selected?.user_name }}</td>
            </tr>

            <tr class="border-t">
              <td class="p-2 font-semibold">Category</td>
              <td class="p-2 capitalize">{{ selected?.category }}</td>
            </tr>

            <tr class="border-t">
              <td class="p-2 font-semibold">Experience</td>
              <td class="p-2">
                {{ selected?.experience_years || 'N/A' }} years
              </td>
            </tr>

            <tr class="border-t">
              <td class="p-2 font-semibold">Description</td>
              <td class="p-2">
                {{ selected?.service_description || 'N/A' }}
              </td>
            </tr>

            <tr v-if="selected?.valid_id" class="border-t">
              <td class="p-2 font-semibold">Valid ID</td>
              <td class="p-2">
                <a
                  :href="`/storage/${selected.valid_id}`"
                  target="_blank"
                  class="text-blue-600 underline"
                >
                  View ID
                </a>
              </td>
            </tr>
          </tbody>
        </table>

        <!-- ACTIONS -->
        <div class="flex justify-end gap-3 mt-6">
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
import { ref } from 'vue'
import Swal from 'sweetalert2'

defineProps({
  applications: {
    type: Array,
    required: true
  },
  loading: {
    type: Boolean,
    default: false
  }
})

const emit = defineEmits(['review'])

const showModal = ref(false)
const selected = ref({}) // default to empty object to avoid null errors

/* OPEN MODAL */
const openView = (app) => {
  selected.value = { ...app }
  showModal.value = true
}

/* CLOSE MODAL */
const closeModal = () => {
  showModal.value = false
  selected.value = {}
}

const handleReview = async ({ id, action, reason = null }) => {
  try {
    await axios.post(`/business/service-providers/${id}/review`, {
      action,
      reason
    })

    // 🔥 REMOVE from list immediately (NO refresh needed)
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

/* APPROVE */
const handleApprove = async () => {
  const confirm = await Swal.fire({
    title: 'Approve this application?',
    text: 'The applicant will become a service provider.',
    icon: 'question',
    showCancelButton: true,
    confirmButtonText: 'Yes, approve'
  })

  if (!confirm.isConfirmed) return

  emit('review', {
    id: selected.value.id,
    action: 'approve'
  })

  closeModal()
}

/* REJECT */
const handleReject = async () => {
  const result = await Swal.fire({
    title: 'Reject Application',
    input: 'textarea',
    inputLabel: 'Reason for rejection',
    inputPlaceholder: 'Type reason here...',
    showCancelButton: true,
    inputValidator: (value) => {
      if (!value) return 'Reason is required'
    }
  })

  if (!result.isConfirmed || !result.value) return

  emit('review', {
    id: selected.value.id,
    action: 'reject',
    reason: result.value
  })

  closeModal()
}
</script>
