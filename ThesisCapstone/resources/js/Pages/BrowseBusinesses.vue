<template>
  <div class="min-h-screen bg-gray-100 p-8">

    <!-- HEADER -->
    <div class="bg-gradient-to-r from-orange-500 to-orange-400 rounded-xl p-6 mb-10 shadow-md">
      <h2 class="text-3xl font-bold text-white">Browse Approved Businesses</h2>
    </div>

    <!-- BUSINESS CARDS GRID -->
    <div v-if="businesses.length" class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-6">
      <div
        v-for="b in businesses"
        :key="b.id"
        @click="openBusinessModal(b)"
        class="bg-white p-6 rounded-3xl shadow-md hover:shadow-xl transition cursor-pointer transform hover:-translate-y-1"
      >
        <div class="flex items-center mb-4">
          <img
            :src="`https://ui-avatars.com/api/?name=${b.business_name}&background=ddd&color=555&size=64`"
            class="w-16 h-16 rounded-full mr-4"
          />
          <div>
            <h3 class="text-lg font-semibold text-gray-800">{{ b.business_name }}</h3>
            <p class="text-gray-500 text-sm">Owner: {{ b.owner_name }}</p>
          </div>
        </div>
        <p class="text-gray-600"><strong>Category:</strong> {{ b.category }}</p>
        <p class="text-gray-600 mt-1"><strong>Business Type:</strong> {{ b.business_type }}</p>
      </div>
    </div>

    <p v-else class="text-gray-500 italic text-center mt-10">No approved businesses available.</p>

    <!-- BUSINESS DETAILS MODAL -->
    <transition name="fade">
      <div v-if="showBusinessModal" class="fixed inset-0 bg-black/60 flex items-center justify-center z-50 p-4">
        <div class="bg-white rounded-3xl shadow-2xl w-full max-w-4xl p-10 relative space-y-6">
          <button
            @click="closeBusinessModal"
            class="absolute top-6 right-8 text-gray-500 hover:text-gray-800 text-3xl font-bold"
          >&times;</button>

          <div class="bg-gradient-to-r from-orange-500 to-orange-400 rounded-xl p-6 mb-6 shadow-md text-white flex items-center space-x-6">
            <img
              :src="`https://ui-avatars.com/api/?name=${selectedBusiness.business_name}&background=fff&color=555&size=128`"
              class="w-24 h-24 rounded-full border border-gray-300 bg-white"
            />
            <div>
              <h3 class="text-3xl font-bold">{{ selectedBusiness.business_name }}</h3>
              <p class="text-lg">Owner: {{ selectedBusiness.owner_name }}</p>
              <p class="text-lg">Business Type: {{ selectedBusiness.business_type }}</p>
            </div>
          </div>

          <div class="grid grid-cols-1 sm:grid-cols-2 gap-6 text-gray-700">
            <p><strong>Address:</strong> {{ selectedBusiness.address }}</p>
            <p><strong>Contact:</strong> {{ selectedBusiness.contact_number }}</p>
            <p><strong>Category:</strong> {{ selectedBusiness.category }}</p>
            <p v-if="selectedBusiness.services && selectedBusiness.services.length">
              <strong>Services:</strong> {{ selectedBusiness.services.join(', ') }}
            </p>
          </div>

          <div v-if="selectedBusiness.latitude && selectedBusiness.longitude">
            <a
              :href="mapLink(selectedBusiness.latitude, selectedBusiness.longitude)"
              target="_blank"
              class="text-blue-600 hover:underline font-medium"
            >📍 View on Map</a>
          </div>

          <button
            @click="openRequestModal(selectedBusiness)"
            class="mt-6 w-full bg-blue-600 text-white py-3 rounded-xl hover:bg-blue-700 font-semibold transition"
          >
            Request Service
          </button>
        </div>
      </div>
    </transition>

    <!-- REQUEST SERVICE MODAL -->
    <transition name="fade-scale">
      <div v-if="showRequestModal" class="fixed inset-0 bg-black/60 flex items-center justify-center z-50 p-4 overflow-auto">
        <div class="bg-white rounded-3xl shadow-2xl w-full max-w-2xl p-8 relative space-y-6 transform transition-transform duration-300 scale-95 opacity-0"
             :class="{'scale-100 opacity-100': showRequestModal}"
             ref="requestModalRef">

          <button
            @click="closeRequestModal"
            class="absolute top-5 right-5 text-gray-500 hover:text-gray-800 text-3xl font-bold"
          >&times;</button>

          <h3 class="text-2xl md:text-3xl font-bold border-b pb-2 mb-6 text-gray-800 text-center">
            Request Service - {{ selectedBusiness.business_name }}
          </h3>

          <form @submit.prevent="submitServiceRequest" class="space-y-5">

            <!-- SERVICE TYPE -->
            <div>
              <label class="block text-gray-700 font-semibold mb-2">Service Type</label>
              <template v-if="selectedBusiness.category?.toLowerCase() === 'both'">
                <select v-model="requestForm.service_type"
                        class="w-full border border-gray-300 py-3 px-3 rounded-md focus:border-blue-600">
                  <option disabled value="">Select Service</option>
                  <option value="Plumbing">Plumbing</option>
                  <option value="Siphoning">Siphoning</option>
                </select>
              </template>
              <template v-else>
                <input v-model="requestForm.service_type"
                      readonly
                      class="w-full border border-gray-300 py-3 bg-gray-100 cursor-not-allowed rounded-md px-3"
              />
              </template>

            </div>

            <!-- USER ADDRESS -->
            <div>
              <label class="block text-gray-700 font-semibold mb-2">Your Address</label>
              <input v-model="requestForm.address"
                     placeholder="Enter your address"
                     class="w-full border border-gray-300 py-3 outline-none focus:border-blue-600 rounded-md px-3"
                     ref="addressInput"
              />
            </div>

            <!-- DESCRIPTION -->
            <div>
              <label class="block text-gray-700 font-semibold mb-2">
                Description <span class="text-sm text-gray-400">(max 200 characters)</span>
              </label>
              <textarea v-model="requestForm.description"
                        placeholder="Enter additional details"
                        maxlength="200"
                        class="w-full border border-gray-300 py-3 outline-none focus:border-blue-600 rounded-md px-3 resize-none"
                        @input="updateDescriptionCount"
              ></textarea>
              <p class="text-right text-sm text-gray-400">{{ descriptionCount }}/200</p>
            </div>

            <!-- PREFERRED DATE -->
            <div>
              <label class="block text-gray-700 font-semibold mb-2">Preferred Date</label>
              <input type="date"
                     v-model="requestForm.preferred_date"
                     :min="minDate"
                     class="w-full border border-gray-300 py-3 outline-none focus:border-blue-600 rounded-md px-3"
              />
            </div>

            <!-- MAP LINK -->
            <div v-if="selectedBusiness.latitude && selectedBusiness.longitude" class="text-sm">
              <a :href="mapLink(selectedBusiness.latitude, selectedBusiness.longitude)"
                 target="_blank"
                 class="text-blue-600 hover:underline font-medium"
              >📍 View Business Location</a>
            </div>

            <!-- ACTION BUTTONS -->
            <div class="flex justify-end gap-4 mt-6">
              <button type="button"
                      @click="closeRequestModal"
                      class="px-5 py-3 rounded-xl bg-gray-300 hover:bg-gray-400 font-semibold">
                Cancel
              </button>
              <button type="submit"
                      class="px-5 py-3 rounded-xl bg-blue-600 text-white hover:bg-blue-700 font-semibold disabled:opacity-50"
                      :disabled="!requestForm.service_type || !requestForm.address || !requestForm.preferred_date"
              >
                Submit Request
              </button>
            </div>

          </form>
        </div>
      </div>
    </transition>

  </div>
</template>

<script setup>
import { ref, reactive, onMounted, nextTick } from 'vue'
import axios from 'axios'
import Swal from 'sweetalert2'

// State
const businesses = ref([])
const authUser = reactive({
  id: null,
  first_name: '',
  middle_initial: '',
  last_name: '',
  email: '',
  contact_number: '',
  latitude: null,
  longitude: null,
  address: ''
})

// Modals
const showBusinessModal = ref(false)
const showRequestModal = ref(false)
const selectedBusiness = reactive({})
const requestForm = reactive({
  service_type: '',
  address: '',
  description: '',
  preferred_date: ''
})
const descriptionCount = ref(0)
const requestModalRef = ref(null)

// Fetch businesses
const fetchBusinesses = async () => {
  try {
    const res = await axios.get('/user/all-businesses')
    businesses.value = res.data
  } catch {
    businesses.value = []
  }
}

// Business modal
const openBusinessModal = (b) => {
  Object.assign(selectedBusiness, b)
  if(!selectedBusiness.services) {
    selectedBusiness.services = []
    if(selectedBusiness.category && selectedBusiness.category !== 'Both') selectedBusiness.services.push(selectedBusiness.category)
  }
  showBusinessModal.value = true
}
const closeBusinessModal = () => showBusinessModal.value = false


// Request modal
const openRequestModal = (b) => {
  Object.assign(selectedBusiness, b)

  // Auto select service type
  if(b.category?.toLowerCase() === 'both') requestForm.service_type = ''
  else requestForm.service_type = b.category

  requestForm.address = authUser.address || ''
  requestForm.description = ''
  
  // ✅ Set default preferred_date to today if empty
  requestForm.preferred_date = new Date().toISOString().split('T')[0]

  descriptionCount.value = 0

  showRequestModal.value = true
  nextTick(() => requestModalRef.value?.querySelector('input, select')?.focus())
}


const closeRequestModal = () => showRequestModal.value = false

// Description counter
const updateDescriptionCount = () => {
  descriptionCount.value = requestForm.description.length
}

// Map link
const mapLink = (lat, lng) => `https://www.google.com/maps?q=${lat},${lng}`

// Minimum date = today
const minDate = new Date().toISOString().split('T')[0]

// Submit request
const submitServiceRequest = async () => {
  if(!requestForm.service_type || !requestForm.address || !requestForm.preferred_date){
    return Swal.fire('Error','Please fill all required fields','error');
  }

  const payload = {
    business_id: selectedBusiness.id,
    service_type: requestForm.service_type,
    address_text: requestForm.address,
    notes: requestForm.description,
    preferred_date: requestForm.preferred_date,  // format: YYYY-MM-DD
    latitude: authUser.latitude,
    longitude: authUser.longitude
  };

  console.log('Submitting request:', payload); // ✅ Check payload

  try {
    const res = await axios.post('/user/service-requests', payload);
    Swal.fire('Success','Service request submitted','success');
    closeRequestModal();
  } catch (err) {
    console.log(err.response?.data);
    Swal.fire('Error', err.response?.data?.error || 'Failed to submit request', 'error');
  }
};

// Mounted
onMounted(async () => {
  try {
    const res = await axios.get('/user/profile')
    Object.assign(authUser, res.data)
  } catch {}
  fetchBusinesses()
})
</script>

<style>
.fade-enter-active, .fade-leave-active {
  transition: opacity 0.25s;
}
.fade-enter-from, .fade-leave-to {
  opacity: 0;
}

.fade-scale-enter-active, .fade-scale-leave-active {
  transition: all 0.25s ease;
}
.fade-scale-enter-from, .fade-scale-leave-to {
  opacity: 0;
  transform: scale(0.95);
}
.fade-scale-enter-to, .fade-scale-leave-from {
  opacity: 1;
  transform: scale(1);
}
</style>
