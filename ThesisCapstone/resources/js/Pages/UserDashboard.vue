<template>
  <div class="min-h-screen flex flex-col bg-gray-100">

    <!-- NAVBAR -->
    <nav class="bg-white shadow-md px-6 py-4 flex justify-between items-center">
      <h1 class="text-2xl font-bold text-gray-800">User Dashboard</h1>
      <button @click="confirmLogout" class="bg-red-500 hover:bg-red-600 px-4 py-2 rounded text-white font-medium">
        Logout
      </button>
    </nav>

    <div class="flex flex-1 overflow-hidden">

      <!-- SIDEBAR -->
      <aside class="w-64 bg-white shadow-r px-4 py-6 flex-shrink-0">
        <ul class="space-y-2">
          <li @click="section='dashboard'" :class="menuClass('dashboard')" class="px-3 py-2 rounded cursor-pointer flex items-center gap-2">
            🏠 <span>Dashboard</span>
          </li>
          <li @click="section='browseBusinesses'" :class="menuClass('browseBusinesses')" class="px-3 py-2 rounded cursor-pointer flex items-center gap-2">
            🏢 <span>Browse Businesses</span>
          </li>
          <li @click="section='myRequests'" :class="menuClass('myRequests')" class="px-3 py-2 rounded cursor-pointer flex items-center gap-2">
            📄 <span>My Requests</span>
          </li>
          <li @click="section='profile'" :class="menuClass('profile')" class="px-3 py-2 rounded cursor-pointer flex items-center gap-2">
            ⚙️ <span>Profile</span>
          </li>
        </ul>
      </aside>

      <!-- MAIN CONTENT -->
      <main class="flex-1 p-8 overflow-y-auto">

        <!-- DASHBOARD -->
        <section v-if="section==='dashboard'" class="space-y-6">
          <div class="bg-white shadow-md rounded-2xl p-6">
            <h2 class="text-2xl font-bold text-gray-800 mb-2">Welcome, {{ authUser.first_name }}!</h2>
            
            <p v-if="!appStatus.hasApplied" class="text-gray-600">You are currently a Customer.</p>
            <p v-else-if="appStatus.pending" class="text-yellow-700 font-semibold">
              Your Service Provider application is pending.
            </p>
            <p v-else-if="appStatus.rejected" class="text-red-600 font-medium">
              Your Service Provider application was rejected.
              <span v-if="appStatus.reject_reason" class="block mt-1 text-sm text-gray-700 font-normal">
                Reason: {{ appStatus.reject_reason }}
              </span>
            </p>
            <p v-else-if="appStatus.approved" class="text-green-600 font-semibold">
              You are an approved Service Provider.
            </p>
          </div>
        </section>

        <!-- BROWSE BUSINESSES -->
        <section v-if="section==='browseBusinesses'">
          <BrowseBusinesses :authUser="authUser" />
        </section>

        <!-- MY REQUESTS -->
        <section v-if="section==='myRequests'" class="space-y-6">
          <h2 class="text-2xl font-bold text-gray-800 mb-4">My Service Requests</h2>
          <div v-if="serviceRequests.length" class="grid grid-cols-1 md:grid-cols-2 gap-6">
            <div v-for="req in serviceRequests" :key="req.id" class="bg-white p-5 rounded-2xl shadow-md hover:shadow-lg transition">
              <p><strong>Business:</strong> {{ req.user_name }}</p>
              <p><strong>Service Type:</strong> {{ req.service_type }}</p>
              <p><strong>Status:</strong> {{ req.status }}</p>
              <p><strong>Date:</strong> {{ req.created_at }}</p>
              <p v-if="req.latitude && req.longitude">
                <a :href="mapLink(req.latitude, req.longitude)" target="_blank" class="text-blue-600 hover:underline">View Location</a>
              </p>
            </div>
          </div>
          <p v-else class="text-gray-500 italic mt-4">No pending service requests.</p>
        </section>

        <!-- PROFILE -->
        <section v-if="section==='profile'" class="space-y-6">
          <div class="relative bg-gradient-to-r from-blue-600 to-indigo-500 rounded-2xl h-36 flex items-center justify-center shadow-lg">
            <h2 class="text-3xl font-bold text-white z-10">My Profile</h2>
            <div class="absolute inset-0 bg-black/20 rounded-2xl"></div>
          </div>

          <div class="bg-white rounded-2xl shadow-lg p-8 flex flex-col md:flex-row items-center md:items-start gap-6">
            <div class="relative">
              <img :src="`https://ui-avatars.com/api/?name=${authUser.first_name}+${authUser.last_name}&background=ddd&color=555&size=128`" 
                  class="w-32 h-32 rounded-full object-cover border-4 border-white shadow-md" />
            </div>

            <div class="flex-1 space-y-4">
              <div>
                <h3 class="text-2xl font-bold text-gray-800">{{ authUser.first_name }} {{ authUser.last_name }}</h3>
                <p class="text-gray-500 flex items-center gap-2 mt-1">📞 {{ authUser.contact_number }}</p>
                <p class="text-gray-500 flex items-center gap-2">✉️ {{ authUser.email }}</p>
                <p class="text-gray-500 flex items-center gap-2">📍 
                  <a :href="mapLink(authUser.latitude, authUser.longitude)" target="_blank" class="text-blue-600 hover:underline">
                    View Location
                  </a>
                </p>
              </div>

              <!-- SERVICE PROVIDER DETAILS -->
              <div v-if="appStatus.hasApplied" class="bg-gray-50 p-4 rounded-xl shadow-inner space-y-2">
                <h4 class="font-semibold text-gray-700">Service Provider Details</h4>
                <div class="grid grid-cols-2 gap-4">
                  <p><strong>Category:</strong> {{ spDetails.category }}</p>
                  <p><strong>Experience:</strong> {{ spDetails.experience_years }} years</p>
                  <p class="col-span-2"><strong>Description:</strong> {{ spDetails.service_description }}</p>
                  <p class="col-span-2">
                    <strong>Status:</strong> 
                    <span v-if="appStatus.pending" class="text-yellow-700 font-semibold">⏳ Pending</span>
                    <span v-else-if="appStatus.rejected" class="text-red-600 font-semibold">❌ Rejected</span>
                    <span v-else-if="appStatus.approved" class="text-green-600 font-semibold">✅ Approved</span>
                  </p>
                  <p v-if="appStatus.rejected && appStatus.reject_reason" class="col-span-2 text-gray-700 text-sm">
                    Reason: {{ appStatus.reject_reason }}
                  </p>
                </div>
              </div>

              <!-- ACTION BUTTONS -->
              <div class="flex flex-col md:flex-row gap-3 mt-4">
                <button @click="openAccountModal" class="flex-1 bg-gradient-to-r from-blue-500 to-blue-700 text-white py-3 rounded-xl shadow hover:opacity-90 transition font-semibold">
                  ⚙️ Edit Profile
                </button>
                <button v-if="!appStatus.pending && (!appStatus.hasApplied || appStatus.rejected)" 
                        @click="openApplyModal" 
                        class="flex-1 bg-gradient-to-r from-green-500 to-teal-500 text-white py-3 rounded-xl shadow font-semibold">
                  🧰 Apply as Service Provider
                </button>
              </div>

            </div>
          </div>
        </section>
      </main>
    </div>

    <!-- ACCOUNT MODAL -->
    <div v-if="showAccountModal" class="fixed inset-0 bg-black/50 flex items-center justify-center z-50">
      <div class="bg-white rounded-2xl p-6 w-96">
        <h3 class="text-xl font-bold mb-4">Edit Profile</h3>
        <form @submit.prevent="updateProfile" class="space-y-4">
          <div class="flex flex-col">
            <label>First Name</label>
            <input v-model="authUser.first_name" placeholder="First Name" class="w-full border rounded px-3 py-2" />
          </div>
          <div class="flex flex-col">
            <label>Last Name</label>
            <input v-model="authUser.last_name" placeholder="Last Name" class="w-full border rounded px-3 py-2" />
          </div>
          <div class="flex flex-col">
            <label>Contact Number</label>
            <input v-model="authUser.contact_number" placeholder="Contact Number" class="w-full border rounded px-3 py-2" />
          </div>
          <div class="flex justify-end gap-2">
            <button type="button" @click="closeAccountModal" class="px-4 py-2 rounded bg-gray-300 hover:bg-gray-400">Cancel</button>
            <button type="submit" class="px-4 py-2 rounded bg-blue-600 text-white hover:bg-blue-700">Save</button>
          </div>
        </form>
      </div>
    </div>

    <!-- APPLY MODAL -->
    <div v-if="showApplyModal" class="fixed inset-0 bg-black/50 flex items-center justify-center z-50">
      <div class="bg-white rounded-2xl p-6 w-96">
        <h3 class="text-xl font-bold mb-4">Apply as Service Provider</h3>
        <form @submit.prevent="submitServiceProvider" class="space-y-4">
          <div>
            <label>Select Business</label>
            <select v-model="spForm.business_id" class="w-full border rounded px-3 py-2" required>
              <option value="" disabled>Select Your Business</option>
              <option v-for="b in businesses" :key="b.id" :value="b.id">{{ b.business_name }}</option>
            </select>
          </div>
          <div>
            <label>Category</label>
            <select v-model="spForm.category" class="w-full border rounded px-3 py-2" required>
              <option value="" disabled>Select Category</option>
              <option value="plumbing">Plumbing</option>
              <option value="siphoning">Siphoning</option>
              <option value="both">Both</option>
            </select>
          </div>
          <div>
            <label>Experience (years)</label>
            <input type="number" v-model="spForm.experience_years" class="w-full border rounded px-3 py-2" required/>
          </div>
          <div>
            <label>Service Description</label>
            <textarea v-model="spForm.service_description" class="w-full border rounded px-3 py-2" required></textarea>
          </div>
          <div>
            <label>Upload Valid ID</label>
            <input type="file" @change="handleFileUpload" class="w-full" />
          </div>
          <div class="flex justify-end gap-2">
            <button type="button" @click="closeApplyModal" class="px-4 py-2 rounded bg-gray-300 hover:bg-gray-400">Cancel</button>
            <button type="submit" class="px-4 py-2 rounded bg-blue-600 text-white hover:bg-blue-700">Submit</button>
          </div>
        </form>
      </div>
    </div>

  </div>
</template>

<script setup>
import { ref, reactive, onMounted } from 'vue'
import axios from 'axios'
import Swal from 'sweetalert2'
import { router } from '@inertiajs/vue3'
import BrowseBusinesses from './BrowseBusinesses.vue'

/* STATE */
const section = ref('dashboard')
const businesses = ref([])
const serviceRequests = ref([])
const authUser = reactive({ id: null, first_name:'', last_name:'', email:'', contact_number:'', latitude:null, longitude:null })

const appStatus = reactive({ hasApplied:false, pending:false, rejected:false, approved:false, reject_reason:null })
const spForm = reactive({ business_id:null, category:'', experience_years:'', service_description:'', valid_id:null })
const spDetails = reactive({ category:'', experience_years:'', service_description:'' })

const showAccountModal = ref(false)
const showApplyModal = ref(false)

/* HELPERS */
const mapLink = (lat,lng)=>`https://www.google.com/maps?q=${lat},${lng}`
const handleFileUpload = e => spForm.valid_id = e.target.files[0]

/* FETCHERS */
const fetchProfile = async ()=>{
  try{ const res = await axios.get('/user/profile'); Object.assign(authUser,res.data) } catch(err){ console.error(err) }
}
const fetchBusinesses = async ()=>{
  try{ const res = await axios.get('/user/all-businesses'); businesses.value = res.data } catch(err){ businesses.value=[] }
}
const fetchServiceRequests = async ()=>{
  try{ const res = await axios.get('/user/service-requests'); serviceRequests.value = res.data } catch(err){ serviceRequests.value=[] }
}
const fetchApplicationStatus = async ()=>{
  try{
    const res = await axios.get('/user/application-status')
    const data = res.data
    appStatus.hasApplied = data.hasApplied
    appStatus.pending = data.pending
    appStatus.approved = data.approved
    appStatus.rejected = data.rejected
    appStatus.reject_reason = data.provider?.reject_reason || null

    if(appStatus.rejected && appStatus.reject_reason){
      Swal.fire({icon:'info',title:'Application Rejected',html:`Reason: <strong>${appStatus.reject_reason}</strong>`})
    }

    if(appStatus.approved){ router.visit('/service-provider/dashboard') }

  }catch(err){ console.error(err) }
}
const fetchSPDetails = async ()=>{
  if(!appStatus.hasApplied) return
  try{ const res = await axios.get('/user/service-provider-details'); Object.assign(spDetails,res.data) }catch(err){ console.error(err) }
}

/* ACTIONS */
const submitServiceProvider = async () => {
  try {
    const formData = new FormData();
    formData.append('business_id', spForm.business_id);
    formData.append('category', spForm.category);
    formData.append('service_description', spForm.service_description);
    formData.append('experience_years', spForm.experience_years);
    if (spForm.valid_id) formData.append('valid_id', spForm.valid_id);
    formData.append('latitude', authUser.latitude ?? '');
    formData.append('longitude', authUser.longitude ?? '');

    const res = await axios.post('/user/apply-service-provider', formData, {
      headers: { 'Content-Type': 'multipart/form-data' }
    });

    Swal.fire('Success', res.data.message || 'Application submitted!', 'success');
    closeApplyModal();

    await fetchApplicationStatus();
    await fetchSPDetails();
  } catch (err) {
    console.error("Submit failed", err);
    const msg = err.response?.data?.error 
                || Object.values(err.response?.data?.errors || {}).flat().join(', ') 
                || 'Failed to submit application';
    Swal.fire('Error', msg, 'error');
  }
}



const confirmLogout = ()=>{
  Swal.fire({title:'Logout',text:'Are you sure?',icon:'warning',showCancelButton:true,confirmButtonColor:'#f56565',cancelButtonColor:'#a0aec0',confirmButtonText:'Yes, logout'})
    .then(r=>{if(r.isConfirmed) logout()})
}
const logout = async ()=>{ await axios.post('/logout'); router.visit('/login') }

const openAccountModal = ()=>showAccountModal.value=true
const closeAccountModal = ()=>showAccountModal.value=false
const openApplyModal = ()=>showApplyModal.value=true
const closeApplyModal = ()=>showApplyModal.value=false

const updateProfile = async ()=>{
  try{ await axios.put('/user/profile',authUser); Swal.fire('Success','Profile updated','success'); closeAccountModal() }
  catch(err){ console.error(err); Swal.fire('Error','Failed to update profile','error') }
}

const menuClass=name=>section.value===name?'font-bold text-blue-600':'text-gray-700 hover:bg-gray-100'

/* INIT */
onMounted(async ()=>{
  await fetchProfile()
  await fetchApplicationStatus()
  await fetchSPDetails()
  fetchBusinesses()
  fetchServiceRequests()
})
</script>
