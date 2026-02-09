<template>
  <div>
    <h2 class="text-2xl font-bold mb-4">My Service Requests</h2>

    <div v-if="loading">Loading...</div>
    <div v-else-if="requests.length===0">
      No service requests.
    </div>

    <table v-else class="min-w-full bg-white shadow rounded">
      <thead class="bg-gray-100">
        <tr>
          <th class="p-2 text-left">Business</th>
          <th class="p-2 text-left">Service Type</th>
          <th class="p-2 text-left">Status</th>
          <th class="p-2 text-left">Actions</th>
        </tr>
      </thead>
      <tbody>
        <tr v-for="req in requests" :key="req.id" class="border-t">
          <td class="p-2">{{ req.user_name }}</td>
          <td class="p-2">{{ req.service_type }}</td>
          <td class="p-2 capitalize">{{ req.status }}</td>
          <td class="p-2">
            <button
              @click="review(req)"
              class="bg-gray-700 text-white px-3 py-1 rounded"
            >
              View
            </button>
          </td>
        </tr>
      </tbody>
    </table>
  </div>
</template>

<script setup>
import { ref } from 'vue'
import Swal from 'sweetalert2'

defineProps({
  requests: Array,
  loading: Boolean
})

const emit = defineEmits(['review'])

const review = (req) => {
  emit('review', { id: req.id, action: 'view' })
}
</script>
