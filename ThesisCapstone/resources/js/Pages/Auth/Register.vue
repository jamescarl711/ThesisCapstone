<script setup>
import TextInput from '@/Components/TextInput.vue'
import PrimaryButton from '@/Components/PrimaryButton.vue'
import SectionTitle from '@/Components/SectionTitle.vue'
import { Head, useForm, router } from '@inertiajs/vue3'
import { ref, computed, watch } from 'vue'
import Swal from 'sweetalert2'
import axios from 'axios'
import { route } from 'ziggy-js'

/* ================= STATE ================= */
const showModal = ref(false)
const step = ref(1)
const reviewChecked = ref(false)
const passwordStrength = ref('')
const showPassword = ref(false)
const showConfirmPassword = ref(false)
const locationReady = ref(false)
const locationEnabled = ref(true)

/* ================= FORM ================= */
const form = useForm({
  role: '',
  first_name: '',
  middle_initial: '',
  last_name: '',
  email: '',
  contact_number: '',
  password: '',
  password_confirmation: '',
  business_name: '',
  business_owner: '',
  address: '',
  category: '',
  business_type: 'Individual',
  bir_registration: null,
  dti_registration: null,
  mayor_permit: null,
  business_permit: null,
  latitude: '',
  longitude: '',
})

/* ================= CATEGORY ================= */
const categories = [
  { label: 'Plumbing', value: 'plumbing', desc: 'Installation and repair of pipes, faucets, toilets, and water systems.' },
  { label: 'Siphoning', value: 'siphoning', desc: 'Septic tank cleaning, drainage, and waste removal services.' },
  { label: 'Plumbing & Siphoning', value: 'both', desc: 'Services include both plumbing and siphoning tasks.' },
]

const businessTypes = [
  { label: 'Individual', value: 'Individual' },
  { label: 'Company', value: 'Company' },
]

/* ================= COMPUTED ================= */
const totalSteps = computed(() => (form.role === 'business' ? 7 : 4))
const modalTitle = computed(() => (form.role === 'business' ? 'Business Registration' : 'User Registration'))
const mapUrl = computed(() => {
  const lat = form.latitude || 14.33
  const lng = form.longitude || 120.95
  return `https://www.google.com/maps?q=${lat},${lng}&z=19&output=embed`
})

/* ================= METHODS ================= */
const openModal = (roleType) => {
  // Check location first
  if(!navigator.geolocation){
    Swal.fire('Location Required','Your browser does not support location','warning')
    return
  }

  Swal.fire({
    title: 'Checking location...',
    text: 'Please allow location access',
    didOpen: () => Swal.showLoading(),
    allowOutsideClick: false,
    allowEscapeKey: false,
  })

  navigator.geolocation.getCurrentPosition(
    (pos) => {
      form.latitude = pos.coords.latitude.toFixed(6)
      form.longitude = pos.coords.longitude.toFixed(6)
      locationEnabled.value = true
      locationReady.value = true

      Swal.close()
      form.role = roleType
      step.value = 1
      showModal.value = true
    },
    (err) => {
      locationEnabled.value = false
      locationReady.value = false
      Swal.fire('Location Required','Please enable your device location before registering','warning')
    },
    { enableHighAccuracy:true, timeout:15000, maximumAge:0 }
  )
}

const closeModal = () => {
  showModal.value = false
  step.value = 1
  reviewChecked.value = false
  passwordStrength.value = ''
  showPassword.value = false
  showConfirmPassword.value = false
  form.reset('password','password_confirmation')
  form.clearErrors()
}

const nextStep = async () => {
  if (!validateStep(step.value)) return
  if(step.value===2){
    const exists = await checkEmailExists()
    if(exists) return
  }
  reviewChecked.value=false
  step.value++
}

const prevStep = () => step.value>1 && step.value--

const checkEmailExists = async()=>{
  if(!form.email) return false
  try{
    const res = await axios.post(route('check-email'), {email:form.email})
    if(res.data.exists){
      Swal.fire('Error','Email already exists','error')
      return true
    }
    return false
  }catch{
    Swal.fire('Error','Could not verify email','error')
    return true
  }
}

const validateStep = (s)=>{
  let ok = true
  let msg = ''
  if(s===1 && form.role==='business'){
    if(!form.business_owner) {ok=false; msg+='Contact Person required\n'}
    if(!form.business_name) {ok=false; msg+='Business Name required\n'}
    if(!form.address) {ok=false; msg+='Business Address required\n'}
  }
  if(s===2){
    if(!form.email) {ok=false; msg+='Email required\n'}
    if(!form.contact_number) {ok=false; msg+='Contact Number required\n'}
  }
  if(s===3 && form.role==='business' && !form.category){
    ok=false; msg+='Service Category required\n'
  }
  if(s===5 && form.role==='business'){
    if(!form.bir_registration) {ok=false; msg+='BIR Registration required\n'}
    if(!form.dti_registration) {ok=false; msg+='DTI Registration required\n'}
    if(!form.mayor_permit) {ok=false; msg+="Mayor's Permit required\n"}
    if(!form.business_permit) {ok=false; msg+='Business Permit required\n'}
  }
  if((s===3 && form.role==='user') || (s===6 && form.role==='business')){
    if(!form.password) {ok=false; msg+='Password required\n'}
    if(form.password!==form.password_confirmation){ok=false; msg+='Password confirmation does not match\n'}
  }
  if(!ok) Swal.fire('Error', msg, 'error')
  return ok
}

const submit = ()=>{
  if(!locationEnabled.value || !form.latitude || !form.longitude){
    Swal.fire('Location Required','Please enable your device location before registering','warning')
    return
  }

  if(!reviewChecked.value){
    Swal.fire('Error','Please check the box confirming your information before submitting','warning')
    return
  }

  form.post(route('register'), {
    forceFormData:true,
    onSuccess:()=> Swal.fire('Success','Registration submitted','success').then(()=>router.visit(route('login')))
  })
}

/* ================= PASSWORD STRENGTH ================= */
watch(()=>form.password,(v)=>{
  if(!v) passwordStrength.value=''
  else if(v.length<6) passwordStrength.value='Weak'
  else if(/[A-Z]/.test(v) && /\d/.test(v) && v.length>=8) passwordStrength.value='Strong'
  else passwordStrength.value='Medium'
})

/* ================= CONTACT NUMBER LIMIT (12 MAX) ================= */
watch(()=>form.contact_number,(val)=>{
  if(!val) return
  const digits = val.replace(/\D/g,'').slice(0,12)
  if(digits!==val) form.contact_number = digits
})
</script>


<template>
<Head title="Register" />

<div class="min-h-screen flex flex-col items-center justify-center bg-gradient-to-br from-teal-100 via-teal-50 to-teal-100 px-4 py-16">

  <h1 class="text-4xl font-bold text-gray-800 mb-12 text-center">Registration Form</h1>

  <!-- ROLE SELECTION -->
  <div class="flex flex-col md:flex-row gap-12 justify-center items-center">
    <div @click="openModal('user')" class="role-card">
      <img src="/images/user.png.png" alt="User" class="role-img"/>
      <h3 class="font-bold text-xl mt-4">User</h3>
      <p class="text-gray-600 italic mt-2 text-center">"Your journey starts here."</p>
    </div>
    <div @click="openModal('business')" class="role-card">
      <img src="/images/business.png.png" alt="Business" class="role-img"/>
      <h3 class="font-bold text-xl mt-4">Business Company</h3>
      <p class="text-gray-600 italic mt-2 text-center">"Grow your business with us."</p>
    </div>
  </div>

  <!-- MODAL -->
  <transition name="fade">
    <div v-if="showModal" class="fixed inset-0 z-50 flex items-center justify-center bg-black/50 p-4">
      <div class="bg-white w-full max-w-5xl rounded-3xl shadow-2xl max-h-[90vh] overflow-y-auto relative">

        <!-- HEADER -->
        <div class="sticky top-0 bg-white flex justify-between items-center px-8 py-5 border-b z-10">
          <h2 class="text-2xl font-bold text-teal-600">{{ modalTitle }}</h2>
          <button @click="closeModal" class="text-3xl hover:text-red-500">&times;</button>
        </div>

        <!-- STEP INDICATORS -->
        <div class="flex justify-between items-center px-8 mt-4 mb-6 relative">
          <div v-for="n in totalSteps" :key="n" class="flex-1 relative flex justify-center items-center">
            <div :class="['w-10 h-10 flex items-center justify-center rounded-full border-2 font-bold z-10', step>=n?'bg-teal-500 text-white border-teal-500':'bg-white text-gray-400 border-gray-300']">{{n}}</div>
            <div v-if="n<totalSteps" class="absolute top-1/2 left-full w-full h-0.5 bg-gray-300 -translate-y-1/2"></div>
          </div>
        </div>

        <!-- FORM -->
        <form @submit.prevent="submit" class="p-8 space-y-6">

          <!-- STEP 1 -->
          <div v-if="step===1" class="wizard-card">
            <SectionTitle title="Basic Information" />
            <div v-if="form.role==='user'" class="grid grid-cols-1 md:grid-cols-2 gap-4">
              <TextInput v-model="form.first_name" placeholder="First Name"/>
              <TextInput v-model="form.middle_initial" placeholder="Middle Initial" maxlength="1"/>
              <TextInput v-model="form.last_name" placeholder="Last Name"/>
            </div>
            <div v-if="form.role==='business'" class="grid grid-cols-1 md:grid-cols-2 gap-4">
              <TextInput v-model="form.business_owner" placeholder="Contact Person"/>
              <TextInput v-model="form.business_name" placeholder="Business Name"/>
              <TextInput v-model="form.address" placeholder="Business Address"/>
            </div>
          </div>

          <!-- STEP 2 -->
          <div v-if="step===2" class="wizard-card">
            <SectionTitle title="Contact Information" />
            <TextInput type="email" v-model="form.email" placeholder="Email" class="text-lg py-3 rounded-lg w-full"/>
            <TextInput v-model="form.contact_number" placeholder="Contact Number" class="text-lg py-3 rounded-lg w-full mt-4"/>
          </div>

          <!-- STEP 3 USER PASSWORD -->
          <div v-if="step===3 && form.role==='user'" class="wizard-card">
            <SectionTitle title="Security" />
            <div class="grid grid-cols-1 md:grid-cols-2 gap-4 mb-4">
              <div class="relative">
                <TextInput :type="showPassword?'text':'password'" v-model="form.password" placeholder="Password" class="text-lg py-3 pr-12 w-full"/>
                <span class="eye absolute top-3 right-3 cursor-pointer" @click="showPassword=!showPassword">👁</span>
              </div>
              <div class="relative">
                <TextInput :type="showConfirmPassword?'text':'password'" v-model="form.password_confirmation" placeholder="Confirm Password" class="text-lg py-3 pr-12 w-full"/>
                <span class="eye absolute top-3 right-3 cursor-pointer" @click="showConfirmPassword=!showConfirmPassword">👁</span>
              </div>
            </div>
            <p v-if="passwordStrength" :class="{'text-red-500':passwordStrength==='Weak','text-yellow-500':passwordStrength==='Medium','text-green-500':passwordStrength==='Strong'}">Strength: {{passwordStrength}}</p>
          </div>

          <!-- STEP 3 BUSINESS CATEGORY -->
          <div v-if="step===3 && form.role==='business'" class="wizard-card">
            <SectionTitle title="Service Category" />
            <div class="flex flex-col md:flex-row gap-6 mt-4">
              <div v-for="c in categories" :key="c.value" @click="form.category=c.value" class="select-card flex-1" :class="form.category===c.value?'active':''">
                <h3 class="font-bold text-lg">{{ c.label }}</h3>
                <p class="text-sm mt-2 opacity-90">{{ c.desc }}</p>
              </div>
            </div>
          </div>

          <!-- STEP 4 BUSINESS TYPE -->
          <div v-if="step===4 && form.role==='business'" class="wizard-card">
            <SectionTitle title="Business Type" />
            <select v-model="form.business_type" class="w-full border rounded-lg px-4 py-3 mt-2 text-lg">
              <option v-for="bt in businessTypes" :key="bt.value" :value="bt.value">{{bt.label}}</option>
            </select>
          </div>

          <!-- STEP 5 UPLOADS -->
          <div v-if="step===5 && form.role==='business'" class="wizard-card">
            <SectionTitle title="Uploads" />
            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
              <div class="flex flex-col">
                <label class="font-semibold">BIR Registration *</label>
                <input type="file" @change="e => form.bir_registration = e.target.files[0]" :placeholder="form.bir_registration?.name || 'Select file'" class="mt-1"/>
                <span v-if="form.bir_registration" class="text-sm text-gray-500 mt-1">Selected: {{ form.bir_registration.name }}</span>
              </div>
              <div class="flex flex-col">
                <label class="font-semibold">DTI Registration *</label>
                <input type="file" @change="e => form.dti_registration = e.target.files[0]" :placeholder="form.dti_registration?.name || 'Select file'" class="mt-1"/>
                <span v-if="form.dti_registration" class="text-sm text-gray-500 mt-1">Selected: {{ form.dti_registration.name }}</span>
              </div>
              <div class="flex flex-col">
                <label class="font-semibold">Mayor's Permit *</label>
                <input type="file" @change="e => form.mayor_permit = e.target.files[0]" :placeholder="form.mayor_permit?.name || 'Select file'" class="mt-1"/>
                <span v-if="form.mayor_permit" class="text-sm text-gray-500 mt-1">Selected: {{ form.mayor_permit.name }}</span>
              </div>
              <div class="flex flex-col">
                <label class="font-semibold">Business Permit *</label>
                <input type="file" @change="e => form.business_permit = e.target.files[0]" :placeholder="form.business_permit?.name || 'Select file'" class="mt-1"/>
                <span v-if="form.business_permit" class="text-sm text-gray-500 mt-1">Selected: {{ form.business_permit.name }}</span>
              </div>
            </div>
          </div>

          <!-- STEP 6 BUSINESS PASSWORD -->
          <div v-if="step===6 && form.role==='business'" class="wizard-card">
            <SectionTitle title="Security" />
            <div class="grid grid-cols-1 md:grid-cols-2 gap-4 mb-4">
              <div class="relative">
                <TextInput :type="showPassword?'text':'password'" v-model="form.password" placeholder="Password" class="text-lg py-3 pr-12 w-full"/>
                <span class="eye absolute top-3 right-3 cursor-pointer" @click="showPassword=!showPassword">👁</span>
              </div>
              <div class="relative">
                <TextInput :type="showConfirmPassword?'text':'password'" v-model="form.password_confirmation" placeholder="Confirm Password" class="text-lg py-3 pr-12 w-full"/>
                <span class="eye absolute top-3 right-3 cursor-pointer" @click="showConfirmPassword=!showConfirmPassword">👁</span>
              </div>
            </div>
            <p v-if="passwordStrength" :class="{'text-red-500':passwordStrength==='Weak','text-yellow-500':passwordStrength==='Medium','text-green-500':passwordStrength==='Strong'}">Strength: {{passwordStrength}}</p>
          </div>

         <!-- REVIEW -->
        <div v-if="step===totalSteps" class="wizard-card">
          <SectionTitle title="Review Your Information" />

          <div class="grid md:grid-cols-2 gap-4">
            <!-- USER FIELDS -->
            <template v-if="form.role==='user'">
              <div class="flex flex-col">
                <label class="font-semibold">First Name</label>
                <TextInput v-model="form.first_name" placeholder="First Name" class="mt-1"/>
              </div>
              <div class="flex flex-col">
                <label class="font-semibold">Middle Initial</label>
                <TextInput v-model="form.middle_initial" placeholder="Middle Initial" class="mt-1"/>
              </div>
              <div class="flex flex-col">
                <label class="font-semibold">Last Name</label>
                <TextInput v-model="form.last_name" placeholder="Last Name" class="mt-1"/>
              </div>
              <div class="flex flex-col">
                <label class="font-semibold">Email</label>
                <TextInput v-model="form.email" placeholder="Email" class="mt-1"/>
              </div>
              <div class="flex flex-col">
                <label class="font-semibold">Contact Number</label>
                <TextInput v-model="form.contact_number" placeholder="Contact Number" class="mt-1"/>
              </div>
            </template>

            <!-- BUSINESS FIELDS -->
            <template v-if="form.role==='business'">
              <div class="flex flex-col">
                <label class="font-semibold">Business Owner</label>
                <TextInput v-model="form.business_owner" placeholder="Business Owner" class="mt-1"/>
              </div>
              <div class="flex flex-col">
                <label class="font-semibold">Business Name</label>
                <TextInput v-model="form.business_name" placeholder="Business Name" class="mt-1"/>
              </div>
              <div class="flex flex-col">
                <label class="font-semibold">Business Address</label>
                <TextInput v-model="form.address" placeholder="Business Address" class="mt-1"/>
              </div>
              <div class="flex flex-col">
                <label class="font-semibold">Service Category</label>
                <TextInput v-model="form.category" placeholder="Service Category" class="mt-1"/>
              </div>
              <div class="flex flex-col">
                <label class="font-semibold">Business Type</label>
                <TextInput v-model="form.business_type" placeholder="Business Type" class="mt-1"/>
              </div>
              <div class="flex flex-col">
                <label class="font-semibold">Email</label>
                <TextInput v-model="form.email" placeholder="Email" class="mt-1"/>
              </div>
              <div class="flex flex-col">
                <label class="font-semibold">Contact Number</label>
                <TextInput v-model="form.contact_number" placeholder="Contact Number" class="mt-1"/>
              </div>
            </template>
          </div>

          <!-- FILES (business only) -->
          <template v-if="form.role==='business'">
            <div class="grid md:grid-cols-2 gap-4 mt-4">
              <div class="flex flex-col">
                <label class="font-semibold">BIR Registration</label>
                <div class="flex items-center gap-2 mt-1">
                  <span v-if="form.bir_registration" class="text-teal-500 underline cursor-pointer" @click="$refs.bir.click()">
                    {{ form.bir_registration.name }}
                  </span>
                  <span v-else class="text-gray-400">No file selected</span>
                  <button type="button" class="ml-2 text-sm text-blue-600 underline" @click="$refs.bir.click()">Edit</button>
                  <input type="file" ref="bir" class="hidden" @change="e => form.bir_registration = e.target.files[0]"/>
                </div>
              </div>

              <div class="flex flex-col">
                <label class="font-semibold">DTI Registration</label>
                <div class="flex items-center gap-2 mt-1">
                  <span v-if="form.dti_registration" class="text-teal-500 underline cursor-pointer" @click="$refs.dti.click()">
                    {{ form.dti_registration.name }}
                  </span>
                  <span v-else class="text-gray-400">No file selected</span>
                  <button type="button" class="ml-2 text-sm text-blue-600 underline" @click="$refs.dti.click()">Edit</button>
                  <input type="file" ref="dti" class="hidden" @change="e => form.dti_registration = e.target.files[0]"/>
                </div>
              </div>

              <div class="flex flex-col">
                <label class="font-semibold">Mayor's Permit</label>
                <div class="flex items-center gap-2 mt-1">
                  <span v-if="form.mayor_permit" class="text-teal-500 underline cursor-pointer" @click="$refs.mayor.click()">
                    {{ form.mayor_permit.name }}
                  </span>
                  <span v-else class="text-gray-400">No file selected</span>
                  <button type="button" class="ml-2 text-sm text-blue-600 underline" @click="$refs.mayor.click()">Edit</button>
                  <input type="file" ref="mayor" class="hidden" @change="e => form.mayor_permit = e.target.files[0]"/>
                </div>
              </div>

              <div class="flex flex-col">
                <label class="font-semibold">Business Permit</label>
                <div class="flex items-center gap-2 mt-1">
                  <span v-if="form.business_permit" class="text-teal-500 underline cursor-pointer" @click="$refs.business.click()">
                    {{ form.business_permit.name }}
                  </span>
                  <span v-else class="text-gray-400">No file selected</span>
                  <button type="button" class="ml-2 text-sm text-blue-600 underline" @click="$refs.business.click()">Edit</button>
                  <input type="file" ref="business" class="hidden" @change="e => form.business_permit = e.target.files[0]"/>
                </div>
              </div>
            </div>
          </template>

          <!-- MAP -->
          <div class="mt-6" v-if="form.role==='business'">
            <h3 class="font-semibold mb-2">Business Location</h3>
            <iframe :src="mapUrl" class="w-full h-72 rounded-xl border" loading="lazy"></iframe>
          </div>

          <!-- REVIEW CONFIRMATION -->
          <label class="flex items-center gap-2 text-lg font-semibold mt-4">
            <input type="checkbox" v-model="reviewChecked"/>
            I have reviewed my information
          </label>
        </div>

          <!-- FOOTER -->
          <div class="flex justify-between mt-6">
            <button v-if="step>1" type="button" @click="prevStep" class="px-4 py-2 border rounded hover:bg-gray-100">Back</button>
            <div class="ml-auto flex gap-2">
              <button v-if="step<totalSteps" type="button" @click="nextStep" class="px-4 py-2 bg-teal-500 text-white rounded hover:bg-teal-600 transition">Next</button>
              <PrimaryButton v-else :disabled="form.processing || !locationReady" type="submit">Submit</PrimaryButton>
            </div>
          </div>

        </form>
      </div>
    </div>
  </transition>
</div>
</template>

<style scoped>
.fade-enter-active, .fade-leave-active { transition: opacity 0.3s ease; }
.fade-enter-from, .fade-leave-to { opacity: 0; }

.role-card { width:280px;height:280px;background:white; border-radius:1.5rem; cursor:pointer; box-shadow:0 15px 25px rgba(0,0,0,0.1); display:flex; flex-direction:column; align-items:center; justify-content:center; padding:16px; transition:0.3s;}
.role-card:hover{transform:scale(1.05)}
.role-img{width:100%; height:110px; object-fit:contain; border-radius:1rem;}

.wizard-card { background:white; border-radius:1rem; padding:2rem; box-shadow:0 8px 20px rgba(0,0,0,0.05); }

.select-card {padding:20px; border:1px solid #ddd; border-radius:16px; cursor:pointer; transition:0.25s; text-align:center; font-size:1.15rem;}
.select-card.active {background:#14b8a6; color:white; border-color:#14b8a6;}

.upload-box {position:relative;}

.eye {position:absolute; right:1rem; top:50%; transform:translateY(-50%); cursor:pointer;}
</style>
