<template>
  <div>
    <button
      @click="deleteAccount"
      class="bg-red-600 text-white px-4 py-2 rounded hover:bg-red-700"
    >
      Delete Account
    </button>
  </div>
</template>

<script>
import axios from 'axios'

export default {
  setup() {
    const deleteAccount = async () => {
      if (!confirm('Are you sure you want to delete your account?')) return
      try {
        await axios.delete('/user/delete')
        alert('Account deleted successfully!')
        window.location.href = '/register'
      } catch (err) {
        console.error(err)
        alert('Failed to delete account')
      }
    }

    return { deleteAccount }
  }
}
</script>
