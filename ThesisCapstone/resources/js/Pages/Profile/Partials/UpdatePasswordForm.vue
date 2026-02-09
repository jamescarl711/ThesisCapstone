<template>
  <div>
    <form @submit.prevent="updatePassword" class="space-y-2">
      <input
        v-model="form.current_password"
        type="password"
        placeholder="Current Password"
        class="border px-3 py-2 w-full rounded"
        required
      />
      <input
        v-model="form.new_password"
        type="password"
        placeholder="New Password"
        class="border px-3 py-2 w-full rounded"
        required
      />
      <input
        v-model="form.confirm_password"
        type="password"
        placeholder="Confirm Password"
        class="border px-3 py-2 w-full rounded"
        required
      />
      <button
        type="submit"
        class="bg-yellow-500 text-white px-4 py-2 rounded hover:bg-yellow-600"
      >
        Update Password
      </button>
    </form>
  </div>
</template>

<script>
import { ref } from 'vue'
import axios from 'axios'

export default {
  setup() {
    const form = ref({
      current_password: '',
      new_password: '',
      confirm_password: ''
    })

    const updatePassword = async () => {
      if (form.value.new_password !== form.value.confirm_password) {
        alert('Passwords do not match')
        return
      }

      try {
        await axios.put('/user/password/update', form.value)
        alert('Password updated successfully!')
        form.value.current_password = ''
        form.value.new_password = ''
        form.value.confirm_password = ''
      } catch (err) {
        console.error(err)
        alert('Failed to update password')
      }
    }

    return { form, updatePassword }
  }
}
</script>
