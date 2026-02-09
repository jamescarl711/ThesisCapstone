<template>
  <div>
    <h4 class="font-semibold mb-2">Additional Info</h4>
    <form @submit.prevent="updateInfo" class="space-y-2">
      <input
        v-model="form.phone"
        type="text"
        placeholder="Phone Number"
        class="border px-3 py-2 w-full rounded"
      />
      <input
        v-model="form.address"
        type="text"
        placeholder="Address"
        class="border px-3 py-2 w-full rounded"
      />
      <button
        type="submit"
        class="bg-blue-500 text-white px-4 py-2 rounded hover:bg-blue-600"
      >
        Save
      </button>
    </form>
  </div>
</template>

<script>
import { ref } from 'vue'
import axios from 'axios'

export default {
  props: ['user'],
  setup(props, { emit }) {
    const form = ref({
      phone: props.user.phone || '',
      address: props.user.address || ''
    })

    const updateInfo = async () => {
      try {
        const res = await axios.put('/user/profile/update-info', form.value)
        emit('updated', res.data)
      } catch (err) {
        console.error(err)
      }
    }

    return { form, updateInfo }
  }
}
</script>
