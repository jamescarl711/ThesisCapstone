<template>
  <div class="max-w-2xl mx-auto mt-8">
    <h2 class="text-2xl font-bold mb-6">Profile</h2>

    <!-- Not logged in -->
    <div v-if="!user">
      <p>You are not logged in. Please register or login.</p>
    </div>

    <!-- Profile Info -->
    <div v-else class="bg-white shadow rounded p-6 mb-4">
      <p><strong>Name:</strong> {{ user.name || 'N/A' }}</p>
      <p><strong>Email:</strong> {{ user.email || 'N/A' }}</p>
      <p><strong>Password:</strong> ********</p>

      <button
        @click="openModal"
        class="mt-4 bg-blue-500 text-white px-4 py-2 rounded hover:bg-blue-600"
      >
        Edit Profile
      </button>
    </div>

    <!-- Edit Modal -->
    <EditProfileModal
      v-if="showModal && user"
      :user="user"
      @close="showModal = false"
      @updated="handleUpdate"
    />
  </div>
</template>

<script setup>
import { ref } from 'vue'
import { usePage, router } from '@inertiajs/vue3'
import Swal from 'sweetalert2'
import EditProfileModal from './EditProfileModal.vue'

const page = usePage()
const user = ref(page.props.auth.user || null) // use backend-provided auth.user
const showModal = ref(false)

const openModal = () => {
  showModal.value = true
}

// Handle modal updates
const handleUpdate = async (updatedUser) => {
  try {
    await router.put(route('profile.update'), updatedUser, {
      preserveScroll: true,
    })

    // Update frontend immediately
    user.value = { ...user.value, ...updatedUser }
    showModal.value = false

    Swal.fire({
      icon: 'success',
      title: 'Profile Updated',
      text: 'Your profile has been successfully updated!',
      timer: 2000,
      showConfirmButton: false
    })
  } catch (error) {
    console.error(error)
    Swal.fire({
      icon: 'error',
      title: 'Update Failed',
      text: 'Something went wrong. Please try again.',
    })
  }
}
</script>
