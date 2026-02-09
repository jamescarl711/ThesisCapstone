<template>
  <div class="bg-white shadow-lg rounded-lg p-6 flex flex-col items-center w-full max-w-md mx-auto">
    <!-- Profile Photo -->
    <div class="relative">
      <img :src="provider.photo || placeholderPhoto" alt="Profile" class="w-32 h-32 rounded-full object-cover border-4 border-blue-500 shadow-md"/>
      <!-- Rating badge top-right -->
      <div class="absolute top-0 right-0 bg-yellow-400 text-white text-sm font-bold px-3 py-1 rounded-full shadow">
        ⭐ {{ averageRating }} / 5
      </div>
    </div>

    <!-- Name & Category -->
    <h2 class="text-2xl font-bold mt-4">{{ provider.name }}</h2>
    <p class="text-gray-500 mb-4 capitalize">{{ provider.category }}</p>

    <!-- Experience -->
    <p class="text-gray-700 mb-2"><strong>Experience:</strong> {{ provider.experience_years }} years</p>

    <!-- Contact Buttons -->
    <div class="flex gap-4 mt-4">
      <a :href="`tel:${provider.contact}`" class="bg-green-500 text-white px-4 py-2 rounded shadow hover:bg-green-600 flex items-center gap-1">
        📞 Call
      </a>
      <a :href="`mailto:${provider.email}`" class="bg-blue-500 text-white px-4 py-2 rounded shadow hover:bg-blue-600 flex items-center gap-1">
        ✉️ Email
      </a>
    </div>

    <!-- Location Snippet -->
    <div class="mt-4 w-full text-center text-gray-600">
      <p><strong>Location:</strong></p>
      <p>{{ provider.location.address }}</p>
    </div>

    <!-- Small Stats or Additional Info -->
    <div class="mt-4 w-full grid grid-cols-2 gap-4 text-center">
      <div class="bg-gray-50 p-3 rounded shadow">
        <p class="font-bold text-lg">{{ provider.reviews.length }}</p>
        <p class="text-gray-500 text-sm">Reviews</p>
      </div>
      <div class="bg-gray-50 p-3 rounded shadow">
        <p class="font-bold text-lg">{{ provider.services.length }}</p>
        <p class="text-gray-500 text-sm">Services</p>
      </div>
    </div>

  </div>
</template>

<script>
export default {
  props: ['provider'],
  data() {
    return {
      placeholderPhoto: "https://via.placeholder.com/150"
    }
  },
  computed: {
    averageRating() {
      if(!this.provider.reviews || this.provider.reviews.length === 0) return 0;
      const total = this.provider.reviews.reduce((sum,r) => sum + r.rating, 0);
      return (total / this.provider.reviews.length).toFixed(1);
    }
  }
}
</script>

<style scoped>
</style>
