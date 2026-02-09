<template>
  <!-- Modal Background -->
  <div class="fixed inset-0 flex items-center justify-center bg-black bg-opacity-50 z-50">
    <!-- Modal Content -->
    <div class="bg-white p-6 rounded shadow-lg w-full max-w-md">
      <h3 class="text-xl font-bold mb-4">Edit Profile</h3>

      <form @submit.prevent="saveChanges">
        <!-- Name -->
        <div class="mb-4">
          <label class="block mb-1 font-medium">Name</label>
          <input
            v-model="form.name"
            type="text"
            class="w-full border px-3 py-2 rounded"
            required
          />
        </div>

        <!-- Email -->
        <div class="mb-4">
          <label class="block mb-1 font-medium">Email</label>
          <input
            v-model="form.email"
            type="email"
            class="w-full border px-3 py-2 rounded"
            required
          />
        </div>

        <!-- Password -->
        <div class="mb-4">
          <label class="block mb-1 font-medium">Password (leave blank to keep current)</label>
          <input
            v-model="form.password"
            type="password"
            class="w-full border px-3 py-2 rounded"
          />
        </div>

        <!-- Buttons -->
        <div class="flex justify-end gap-2">
          <button
            type="button"
            @click="$emit('close')"
            class="px-4 py-2 rounded border hover:bg-gray-100"
          >
            Cancel
          </button>
          <button
            type="submit"
            class="px-4 py-2 rounded bg-blue-500 text-white hover:bg-blue-600"
          >
            Save
          </button>
        </div>
      </form>
    </div>
  </div>
</template>

<script setup>
import { reactive } from 'vue'

const props = defineProps({
  user: {
    type: Object,
    required: true
  }
})

const emit = defineEmits(['close', 'updated'])

// Reactive copy of user for v-model
const form = reactive({ ...props.user, password: '' })

const saveChanges = () => {
  const payload = { name: form.name, email: form.email }
  if (form.password) payload.password = form.password
  emit('updated', payload)
}
</script>
