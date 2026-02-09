<template>
  <div>
    <h2 class="text-2xl font-bold mb-6">My Profile</h2>
    <div class="bg-white rounded-2xl shadow p-8 max-w-5xl">
      <div class="grid grid-cols-1 md:grid-cols-4 gap-8">
        <div class="flex flex-col items-center">
          <img :src="user.photo_url || '/images/avatar-placeholder.png'" class="w-36 h-36 rounded-full object-cover border" />
        </div>
        <div class="md:col-span-3 grid grid-cols-1 md:grid-cols-2 gap-6">
          <ProfileItem label="Full Name">
            {{ user.first_name }} {{ user.middle_initial ? user.middle_initial + '.' : '' }} {{ user.last_name }}
          </ProfileItem>
          <ProfileItem label="Email">{{ user.email }}</ProfileItem>
          <ProfileItem label="Category">{{ provider.category }}</ProfileItem>
          <ProfileItem label="Experience">{{ provider.experience_years }} years</ProfileItem>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup>
import { defineProps } from 'vue'
const props = defineProps({ user: Object, provider: Object })
</script>

<script>
export default {
  components: {
    ProfileItem: {
      props: ['label'],
      template: `<div><p class="text-xs text-gray-500">{{ label }}</p><p class="font-medium"><slot /></p></div>`
    }
  }
}
</script>
