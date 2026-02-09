<template>
  <div>
    <!-- Modal Background -->
    <transition name="fade">
      <div v-if="showModal" class="fixed inset-0 bg-black/50 flex items-center justify-center z-50 p-4">
        <div class="bg-white w-full max-w-3xl rounded-lg shadow-lg max-h-[90vh] overflow-y-auto relative">

          <!-- Header -->
          <div class="flex justify-between items-center p-4 border-b sticky top-0 bg-white z-10">
            <h2 class="text-2xl font-semibold text-teal-700">User Details</h2>
            <button @click="closeModal" class="text-3xl font-bold text-gray-500 hover:text-red-500">&times;</button>
          </div>

          <div class="p-6 space-y-6">

            <!-- Personal Info Card -->
            <div class="bg-gray-50 p-6 rounded-lg shadow-md">
              <h3 class="text-lg font-semibold mb-4 text-teal-600">Personal Information</h3>
              <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                <div>
                  <p class="text-sm text-gray-500">Full Name</p>
                  <p class="font-medium">{{ userFullName }}</p>
                </div>
                <div>
                  <p class="text-sm text-gray-500">Email</p>
                  <p class="font-medium">{{ user.email }}</p>
                </div>
                <div v-if="user.role === 'user'">
                  <p class="text-sm text-gray-500">Contact Number</p>
                  <p class="font-medium">{{ user.contact_number }}</p>
                </div>
                <div>
                  <p class="text-sm text-gray-500">Role</p>
                  <p class="font-medium capitalize">{{ user.role }}</p>
                </div>
              </div>
            </div>

            <!-- Business Info Card -->
            <div v-if="user.business" class="bg-gray-50 p-6 rounded-lg shadow-md">
              <h3 class="text-lg font-semibold mb-4 text-teal-600">Business Information</h3>
              <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                <div>
                  <p class="text-sm text-gray-500">Business Name</p>
                  <p class="font-medium">{{ user.business.business_name }}</p>
                </div>
                <div>
                  <p class="text-sm text-gray-500">Owner</p>
                  <p class="font-medium">{{ user.business.owner_name }}</p>
                </div>
                <div>
                  <p class="text-sm text-gray-500">Category</p>
                  <p class="font-medium capitalize">{{ user.business.category }}</p>
                </div>
                <div>
                  <p class="text-sm text-gray-500">Contact Number</p>
                  <p class="font-medium">{{ user.business.contact_number }}</p>
                </div>
                <div class="col-span-2">
                  <p class="text-sm text-gray-500">Address</p>
                  <p class="font-medium">{{ user.business.address }}</p>
                </div>
              </div>

              <!-- Uploaded Documents Card -->
              <div class="mt-6 p-4 bg-white rounded-lg border shadow-sm">
                <h4 class="font-semibold text-gray-700 mb-4 text-teal-500">Uploaded Documents</h4>
                <div class="grid grid-cols-2 md:grid-cols-4 gap-4">
                  <div v-if="user.business.bir_registration">
                    <p class="text-xs text-gray-500 mb-1">BIR Registration</p>
                    <button type="button" class="w-full" @click="openImagePreview(docUrl(user.business.bir_registration))">
                      <img :src="docUrl(user.business.bir_registration)" alt="BIR" class="w-full h-32 object-cover rounded border cursor-zoom-in" />
                    </button>
                  </div>
                  <div v-if="user.business.dti_registration">
                    <p class="text-xs text-gray-500 mb-1">DTI Registration</p>
                    <button type="button" class="w-full" @click="openImagePreview(docUrl(user.business.dti_registration))">
                      <img :src="docUrl(user.business.dti_registration)" alt="DTI" class="w-full h-32 object-cover rounded border cursor-zoom-in" />
                    </button>
                  </div>
                  <div v-if="user.business.mayor_permit">
                    <p class="text-xs text-gray-500 mb-1">Mayor Permit</p>
                    <button type="button" class="w-full" @click="openImagePreview(docUrl(user.business.mayor_permit))">
                      <img :src="docUrl(user.business.mayor_permit)" alt="Mayor Permit" class="w-full h-32 object-cover rounded border cursor-zoom-in" />
                    </button>
                  </div>
                  <div v-if="user.business.business_permit">
                    <p class="text-xs text-gray-500 mb-1">Business Permit</p>
                    <button type="button" class="w-full" @click="openImagePreview(docUrl(user.business.business_permit))">
                      <img :src="docUrl(user.business.business_permit)" alt="Business Permit" class="w-full h-32 object-cover rounded border cursor-zoom-in" />
                    </button>
                  </div>
                </div>
              </div>
            </div>

            <!-- Service Provider Info Card -->
            <div v-if="user.service_provider" class="bg-gray-50 p-6 rounded-lg shadow-md">
              <h3 class="text-lg font-semibold mb-4 text-teal-600">Service Provider Information</h3>
              <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                <div>
                  <p class="text-sm text-gray-500">Category</p>
                  <p class="font-medium">{{ user.service_provider.category }}</p>
                </div>
                <div>
                  <p class="text-sm text-gray-500">Description</p>
                  <p class="font-medium">{{ user.service_provider.service_description }}</p>
                </div>
                <div>
                  <p class="text-sm text-gray-500">Experience Years</p>
                  <p class="font-medium">{{ user.service_provider.experience_years }}</p>
                </div>
                <div>
                  <p class="text-sm text-gray-500">Available</p>
                  <p class="font-medium">{{ user.service_provider.is_available ? 'Yes' : 'No' }}</p>
                </div>
              </div>
            </div>

            <!-- Actions -->
            <div class="pt-6 border-t">
              <div class="text-xs text-gray-400 mb-3">
                Status: {{ user && user.is_approved ? 'Approved' : 'Pending' }}
              </div>
              <div class="flex justify-end gap-3">
                <button
                  @click="approveUser"
                  class="px-4 py-2 rounded bg-emerald-600 text-white hover:bg-emerald-700"
                >
                  Accept
                </button>
                <button
                  @click="rejectUser"
                  class="px-4 py-2 rounded bg-rose-600 text-white hover:bg-rose-700"
                >
                  Reject
                </button>
                <button @click="closeModal" class="px-4 py-2 border rounded hover:bg-gray-100">Close</button>
              </div>
            </div>

          </div>
        </div>
      </div>
    </transition>
  </div>
</template>

<script setup>
import { ref, computed } from 'vue'
import axios from 'axios'
import Swal from 'sweetalert2'

const showModal = ref(false)
const user = ref({})
const emit = defineEmits(['user-approved', 'user-rejected'])

const docUrl = (value) => {
  if (!value) return ''
  const url = String(value)
  if (url.startsWith('http://') || url.startsWith('https://') || url.startsWith('/')) return url
  return `/storage/${url}`
}

function openUserModal(userId) {
  showModal.value = true
  axios.get(`/admin/users/${userId}`)
    .then(res => { user.value = res.data })
    .catch(() => {
      Swal.fire('Error','Failed to fetch user data','error')
      showModal.value = false
    })
}

const closeModal = () => {
  showModal.value = false
  user.value = {}
}

const userFullName = computed(() => {
  if(!user.value) return ''
  const u = user.value
  const middle = u.middle_initial ? u.middle_initial + '. ' : ''
  return [u.first_name, middle, u.last_name].join(' ').trim()
})

const openImagePreview = (src) => {
  if (!src) return
  Swal.fire({
    imageUrl: src,
    imageAlt: 'Uploaded document',
    showConfirmButton: false,
    showCloseButton: true,
    width: '70vw',
    padding: '0',
    background: 'transparent',
    backdrop: 'rgba(15, 23, 42, 0.85)'
  })
}

const approveUser = async () => {
  const confirm = await Swal.fire({
    title: 'Approve this user?',
    icon: 'question',
    showCancelButton: true,
    confirmButtonText: 'Yes, approve'
  })
  if (!confirm.isConfirmed) return

  try {
    const res = await axios.post(`/admin/users/${user.value.id}/toggle-approval`)
    user.value.is_approved = res.data.is_approved
    emit('user-approved', { id: user.value.id, is_approved: user.value.is_approved })
    Swal.fire('Approved', 'User approved successfully.', 'success')
    closeModal()
  } catch {
    Swal.fire('Error', 'Failed to approve user', 'error')
  }
}

const rejectUser = async () => {
  const result = await Swal.fire({
    title: 'Reject user?',
    input: 'textarea',
    inputLabel: 'Reason',
    showCancelButton: true,
    confirmButtonText: 'Reject',
    inputValidator: v => !v && 'Reason is required'
  })
  if (!result.isConfirmed) return

  try {
    await axios.post(`/admin/users/${user.value.id}/reject`, { reason: result.value })
    emit('user-rejected', user.value.id)
    Swal.fire('Rejected', 'User rejected successfully.', 'success')
    closeModal()
  } catch (err) {
    const msg = err.response?.data?.message || 'Failed to reject user'
    Swal.fire('Error', msg, 'error')
  }
}

defineExpose({ openUserModal })
</script>

<style scoped>
.fade-enter-active, .fade-leave-active { transition: opacity 0.25s ease; }
.fade-enter-from, .fade-leave-to { opacity: 0; }
</style>
