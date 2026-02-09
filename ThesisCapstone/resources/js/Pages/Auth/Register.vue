<script setup>
import TextInput from '@/Components/TextInput.vue'
import PrimaryButton from '@/Components/PrimaryButton.vue'
import SectionTitle from '@/Components/SectionTitle.vue'
import { useForm, router } from '@inertiajs/vue3'
import { ref, computed, watch, onBeforeUnmount } from 'vue'
import axios from 'axios'
import Swal from 'sweetalert2'
import { route } from 'ziggy-js'

/* ================= STATE ================= */
const showModal = ref(false)
const otpModal = ref(false)
const step = ref(1)
const reviewChecked = ref(false)
const passwordStrength = ref('')
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
  government_id: null,
  business_name: '',
  business_name_1: '',
  business_owner: '',
  business_owner_first: '',
  business_owner_middle: '',
  business_owner_last: '',
  address: '',
  address_unit: '',
  address_street: '',
  address_barangay: '',
  address_city: '',
  address_province: '',
  address_postal: '',
  category: '',
  business_type: 'Individual',
  business_ownership: '',
  years_in_operation: '',
  operating_hours: '',
  bir_registration: null,
  dti_registration: null,
  mayor_permit: null,
  business_permit: null,
  sanitary_permit: null,
  latitude: '',
  longitude: '',
})

/* ================= OTP STATE ================= */
const otpSent = ref(false)
const otpVerified = ref(false)
const otpCode = ref('')
const emailForOtp = ref('')
const sendingOtp = ref(false)
const verifyingOtp = ref(false)
const resendSeconds = ref(0)
let resendTimer = null
const OTP_TIMEOUT_MS = 10000

/* ================= PASSWORD VISIBILITY ================= */
const showPassword = ref(false)
const showPasswordConfirm = ref(false)
const contactAlerted = ref(false)
const submitProgress = ref(0)

/* ================= FILE PREVIEWS ================= */
const previewUrls = ref({
  government_id: '',
  bir_registration: '',
  dti_registration: '',
  mayor_permit: '',
  business_permit: '',
  sanitary_permit: '',
})

const isImage = (file) => !!file && !!file.type && file.type.startsWith('image/')

const setPreview = (key, file) => {
  if (previewUrls.value[key]) URL.revokeObjectURL(previewUrls.value[key])
  previewUrls.value[key] = file ? URL.createObjectURL(file) : ''
}

/* ================= CATEGORY & TYPE ================= */
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
const totalSteps = computed(() => form.role==='business'?7:5)
const modalTitle = computed(() => form.role==='business'?'Business Registration':'User Registration')
const mapUrl = computed(() => {
  const lat = form.latitude||14.33
  const lng = form.longitude||120.95
  return `https://www.google.com/maps?q=${lat},${lng}&z=19&output=embed`
})

const businessOwnerFull = computed(() => {
  const first = (form.business_owner_first || '').trim()
  const middle = (form.business_owner_middle || '').trim()
  const last = (form.business_owner_last || '').trim()
  return [first, middle, last].filter(Boolean).join(' ')
})

const businessNameFull = computed(() => {
  const a = (form.business_name_1 || '').trim()
  return [a].filter(Boolean).join(' ')
})

const addressFull = computed(() => {
  const parts = [
    form.address_unit,
    form.address_street,
    form.address_barangay,
    form.address_city,
    form.address_province,
    form.address_postal,
  ].map(v => (v || '').trim()).filter(Boolean)
  return parts.join(', ')
})



/* ================= NAME FIELD ERRORS ================= */
const nameErrors = (val) => {
  const v = (val || '').trim()
  const hasNumber = /[0-9]/.test(v)
  const hasSpecial = /[^A-Za-z0-9\s]/.test(v)
  return { number: hasNumber, special: hasSpecial }
}

/* ================= METHODS ================= */
const openModal = (roleType) => {
  form.role = roleType
  step.value = 1
  showModal.value = true
  // Get location silently
  if(navigator.geolocation){
    navigator.geolocation.getCurrentPosition(
      pos => {
        form.latitude = pos.coords.latitude.toFixed(6)
        form.longitude = pos.coords.longitude.toFixed(6)
        locationEnabled.value = true
        locationReady.value = true
      },
      ()=>{ locationEnabled.value=false; locationReady.value=false },
      { enableHighAccuracy:true, timeout:15000, maximumAge:0 }
    )
  }
}

const closeModal = () => {
  showModal.value=false
  otpModal.value=false
  step.value=1
  reviewChecked.value=false
  otpSent.value=false
  otpVerified.value=false
  otpCode.value=''
  resendSeconds.value=0
  if(resendTimer){ clearInterval(resendTimer); resendTimer=null }
  passwordStrength.value=''
  form.reset()
  form.clearErrors()
}

const nextStep = async () => {
  Swal.fire({
    title: 'Loading...',
    allowOutsideClick: false,
    didOpen: () => {
      Swal.showLoading()
    }
  })
  await new Promise((resolve)=>setTimeout(resolve, 1500))
  Swal.close()
  if(!validateStep(step.value)) return

  // Email & contact step triggers OTP
  if((form.role==='user' && step.value===2) || (form.role==='business' && step.value===2)){
    if(!otpVerified.value){
      otpModal.value = true
      await sendOtp() // send OTP after modal is shown
      return
    }
  }
  step.value++
}
const prevStep = ()=> step.value>1 && step.value--

/* ================= OTP ================= */
const sendOtp = async () => {
  const email = (form.email || '').trim().toLowerCase()
  if(!email){
    Swal.fire('Oops','Please enter email first','warning')
    return
  }
  if(!form.contact_number){
    Swal.fire('Oops','Please enter contact number','warning')
    return
  }
  if(resendSeconds.value>0){
    Swal.fire('Oops',`Please wait ${resendSeconds.value}s before resending OTP`,'warning')
    return
  }

  sendingOtp.value=true
  emailForOtp.value = email
  try{
    await axios.post(
      route('register.send-otp'),
      { email, role: form.role },
      { timeout: OTP_TIMEOUT_MS }
    )
    otpSent.value=true
    resendSeconds.value = 60
    if(resendTimer) clearInterval(resendTimer)
    resendTimer = setInterval(()=>{
      if(resendSeconds.value<=0){
        clearInterval(resendTimer)
        resendTimer = null
        return
      }
      resendSeconds.value -= 1
    }, 1000)
  }catch(err){
    if(err?.code === 'ECONNABORTED'){
      Swal.fire('Error','Server is taking too long. Check backend/DB and try again.','error')
      return
    }
    const status = err?.response?.status
    const emailErr = err?.response?.data?.errors?.email?.[0]
    const contactErr = err?.response?.data?.errors?.contact_number?.[0]
    const msg = err?.response?.data?.message

    if(emailErr || contactErr || (status === 409) || (msg && /taken|exists|already/i.test(msg))){
      const errorText = contactErr ? 'Contact number already taken' : 'Email already taken'
      Swal.fire('Oops', errorText, 'error')
      form.email = ''
      form.contact_number = ''
      otpModal.value = false
      otpSent.value = false
      return
    }

    console.log(err)
    Swal.fire('Error', msg || 'Failed to send OTP. Try again', 'error')
    form.email = ''
    form.contact_number = ''
    otpModal.value = false
  }finally{ sendingOtp.value=false }
}

const verifyOtp = async () => {
  const otp = (otpCode.value || '').trim()
  if(!otp){
    Swal.fire('Oops','Please enter OTP','warning')
    return
  }
  verifyingOtp.value=true
  try{
    await axios.post(
      route('register.verify-otp'),
      { email: (emailForOtp.value || '').trim().toLowerCase(), otp },
      { timeout: OTP_TIMEOUT_MS }
    )
    otpVerified.value=true
    otpModal.value=false
    Swal.fire('Success','OTP verified!','success')
    nextStep()
  }catch(err){
    const msg = err?.response?.data?.message
    if(err?.code === 'ECONNABORTED'){
      Swal.fire('Error','Server is taking too long. Check backend/DB and try again.','error')
      return
    }
    Swal.fire('Error', msg || 'Invalid OTP', 'error')
  }finally{ verifyingOtp.value=false }
}

const stepClass = (n) => {
  if(step.value > n) return 'bg-teal-500 text-white shadow-lg'
  if(step.value === n) return 'bg-teal-300 text-white shadow-md'
  return 'bg-gray-200 text-gray-500'
}

const isIndividualDisabled = computed(() => form.business_ownership === 'Corporation')
const selectBusinessType = (val) => {
  if(val === 'Individual' && isIndividualDisabled.value) return
  form.business_type = val
}

/* ================= VALIDATION ================= */
const validateStep = (s)=>{
  if(s===1){
    if(form.role==='user' && (!form.first_name || !form.last_name)){
      Swal.fire('Oops','Please fill in your full name','warning')
      return false
    }
    if(
      form.role==='business' &&
      (!form.business_owner_first || !form.business_owner_last ||
       !form.business_name_1 || !form.address_street || !form.address_barangay || !form.address_city ||
       !form.business_ownership || !form.years_in_operation)
    ){
      Swal.fire('Oops','Please fill in owner name, business name and address','warning')
      return false
    }
  }

  if((s===2 && form.role==='user') || (s===2 && form.role==='business')){
    const emailOk = /@/.test((form.email || '').trim())
    if(!emailOk){
      Swal.fire('Oops','Email must be in @gmail.com format','warning')
      return false
    }

    const contactOk = /^\d{1,12}$/.test(form.contact_number || '')
    if(!contactOk){
      Swal.fire('Oops','Contact number must be numbers only and max 12 digits','warning')
      return false
    }
  }

  if((s===4 && form.role==='user') || (s===6 && form.role==='business')){
    if(!form.password || form.password!==form.password_confirmation){
      Swal.fire('Oops','Password is missing or does not match','warning')
      return false
    }
  }

  if(s===3 && form.role==='business' && !form.category){
    Swal.fire('Oops','Please select a business category','warning')
    return false
  }

  if(s===5 && form.role==='business'){
    if(!form.bir_registration || !form.dti_registration || !form.mayor_permit || !form.business_permit || !form.sanitary_permit){
      Swal.fire('Oops','Please upload all required documents','warning')
      return false
    }
  }

  return true
}

/* ================= SUBMIT ================= */
const submit = ()=>{
  if(!locationEnabled.value || !form.latitude || !form.longitude){
    Swal.fire('Oops','Location is not ready','warning')
    return
  }
  if(!reviewChecked.value){
    Swal.fire('Oops','Please confirm that you have reviewed your information','warning')
    return
  }

  let progressTimer = null
  let fakeProgressTimer = null
  const stopProgressTimer = () => {
    if(progressTimer){
      clearInterval(progressTimer)
      progressTimer = null
    }
  }
  const stopFakeProgress = () => {
    if(fakeProgressTimer){
      clearInterval(fakeProgressTimer)
      fakeProgressTimer = null
    }
  }

  const updateSubmitProgress = (val) => {
    const p = Math.max(0, Math.min(100, Math.round(val || 0)))
    submitProgress.value = p
    const container = Swal.getHtmlContainer()
    if(!container) return
    const bar = container.querySelector('.swal-progress-bar')
    const text = container.querySelector('.swal-progress-text')
    if(bar) bar.style.width = `${p}%`
    if(text) text.textContent = `${p}%`
  }

  Swal.fire({
    title: 'Submitting...',
    html: `
      <div style="margin-top:12px;">
        <div style="width:100%;height:10px;background:#e5e7eb;border-radius:999px;overflow:hidden;">
          <div class="swal-progress-bar" style="height:100%;width:0%;background:#14b8a6;transition:width .2s ease;"></div>
        </div>
        <div class="swal-progress-text" style="margin-top:8px;font-size:14px;color:#0f766e;">0%</div>
      </div>
    `,
    showConfirmButton: false,
    allowOutsideClick: false,
    allowEscapeKey: false,
    didOpen: () => {
      updateSubmitProgress(0)
      let current = 0
      fakeProgressTimer = setInterval(()=>{
        // Smoothly move to 90% while waiting for real progress
        current = Math.min(90, current + 2)
        updateSubmitProgress(current)
      }, 120)
    },
  })

  form.business_owner = businessOwnerFull.value
  form.business_name = businessNameFull.value
  form.address = addressFull.value
  form.post(route('register'), {
    forceFormData:true,
    onProgress: (e)=>{
      stopProgressTimer()
      stopFakeProgress()
      updateSubmitProgress(e?.percentage || 0)
    },
    onSuccess:()=>{
      stopProgressTimer()
      stopFakeProgress()
      updateSubmitProgress(100)
      setTimeout(()=>{
        Swal.close()
        showModal.value = false
        Swal.fire('Success','Registration complete!','success').then(()=>router.visit(route('login')))
      }, 300)
    },
    onError:()=>{
      stopProgressTimer()
      stopFakeProgress()
      Swal.close()
    },
    onFinish:()=>{
      stopProgressTimer()
      stopFakeProgress()
    }
  })
}

/* ================= PASSWORD STRENGTH ================= */
watch(()=>form.password,(v)=>{
  if(!v){
    passwordStrength.value=''
    return
  }

  const hasLetter = /[A-Za-z]/.test(v)
  const hasNumber = /\d/.test(v)
  const hasSpecial = /[^A-Za-z0-9]/.test(v)
  const hasAll = hasLetter && hasNumber && hasSpecial

  if(v.length < 8) passwordStrength.value='Weak'
  else if(hasAll) passwordStrength.value='Strong'
  else passwordStrength.value='Medium'
})

/* ================= CONTACT NUMBER LIMIT ================= */
watch(()=>form.contact_number,(val)=>{
  if(!val) return
  if(/[^0-9]/.test(val)){
    if(!contactAlerted.value){
      Swal.fire('Oops','Contact number must be numbers only','warning')
      contactAlerted.value = true
      setTimeout(()=>{ contactAlerted.value=false }, 500)
    }
  }
  const digits=val.replace(/\D/g,'').slice(0,12)
  if(digits!==val) form.contact_number=digits
})

watch(()=>form.business_ownership,(val)=>{
  if(val === 'Corporation'){
    form.business_type = 'Company'
  }
})

watch(()=>form.government_id,(f)=>setPreview('government_id', f))
watch(()=>form.bir_registration,(f)=>setPreview('bir_registration', f))
watch(()=>form.dti_registration,(f)=>setPreview('dti_registration', f))
watch(()=>form.mayor_permit,(f)=>setPreview('mayor_permit', f))
watch(()=>form.business_permit,(f)=>setPreview('business_permit', f))
watch(()=>form.sanitary_permit,(f)=>setPreview('sanitary_permit', f))

onBeforeUnmount(()=>{
  Object.values(previewUrls.value).forEach((url)=>{
    if(url) URL.revokeObjectURL(url)
  })
  if(resendTimer) clearInterval(resendTimer)
})

</script>

<template>
<div class="min-h-screen flex flex-col items-center justify-center bg-gradient-to-b from-teal-50 to-white p-4">
  <h1 class="text-4xl font-extrabold mb-8 text-teal-700">Registration Form</h1>

  <!-- ROLE SELECTION -->
  <div class="flex gap-12">
    <div @click="openModal('user')" class="role-card cursor-pointer hover:shadow-xl transition-transform transform hover:scale-105">User</div>
    <div @click="openModal('business')" class="role-card cursor-pointer hover:shadow-xl transition-transform transform hover:scale-105">Business</div>
  </div>

  <!-- MODAL -->
  <transition name="fade">
    <div v-if="showModal" class="fixed inset-0 bg-black/40 flex items-center justify-center p-4 z-50">
      <div class="bg-white w-full max-w-5xl rounded-3xl p-8 shadow-2xl overflow-y-auto max-h-[90vh] relative">

        <div class="flex justify-between items-center mb-6">
          <h2 class="text-2xl font-bold text-teal-700">{{ modalTitle }}</h2>
          <button @click="closeModal" class="text-3xl font-bold text-gray-500 hover:text-gray-900">&times;</button>
        </div>
        
        <!-- STEP INDICATORS -->
    <div class="flex items-center mb-6 gap-2">
      <template v-for="n in totalSteps" :key="n">
        <!-- Circle -->
        <div :class="['w-10 h-10 rounded-full flex items-center justify-center font-semibold transition-colors duration-500', stepClass(n)]">
          {{ n }}
        </div>
        <!-- Line -->
        <div v-if="n < totalSteps" class="flex-1 h-1 rounded"
            :class="step.value > n ? 'bg-teal-500' : 'bg-gray-300'"></div>
      </template>
    </div>


        <form @submit.prevent="submit" class="space-y-6">

          <!-- USER STEPS -->
          <div v-if="form.role==='user'">
            <div v-if="step===1">
              <SectionTitle title="Basic Info"/>
              <div class="form-box">
                <label>First Name</label>
                <TextInput v-model="form.first_name"/>
                <p v-if="nameErrors(form.first_name).number" class="text-red-500 text-xs mt-1">Numbers are not allowed</p>
                <p v-if="nameErrors(form.first_name).special" class="text-red-500 text-xs">Special characters are not allowed</p>
              </div>
              <div class="form-box">
                <label>Middle Initial</label>
                <TextInput v-model="form.middle_initial" maxlength="1"/>
                <p v-if="nameErrors(form.middle_initial).number" class="text-red-500 text-xs mt-1">Numbers are not allowed</p>
                <p v-if="nameErrors(form.middle_initial).special" class="text-red-500 text-xs">Special characters are not allowed</p>
              </div>
              <div class="form-box">
                <label>Last Name</label>
                <TextInput v-model="form.last_name"/>
                <p v-if="nameErrors(form.last_name).number" class="text-red-500 text-xs mt-1">Numbers are not allowed</p>
                <p v-if="nameErrors(form.last_name).special" class="text-red-500 text-xs">Special characters are not allowed</p>
              </div>
            </div>

            <div v-if="step===2">
              <SectionTitle title="Email & Contact"/>
              <div class="form-box"><label>Email Address</label><TextInput v-model="form.email"/></div>
              <div class="form-box"><label>Number</label>
                <TextInput
                  v-model="form.contact_number"
                  inputmode="numeric"
                  pattern="[0-9]*"
                  maxlength="11"
                />
              </div>
            </div>

            <div v-if="step===3">
              <SectionTitle title="Government ID"/>
              <div class="form-box"><label>Upload ID</label><input type="file" @change="e=>form.government_id=e.target.files[0]" class="file-input"/></div>
            </div>

            <div v-if="step===4">
              <SectionTitle title="Set Password"/>
              <div class="form-box">
                <label>Password</label>
                <div class="password-field">
                  <TextInput v-model="form.password" :type="showPassword ? 'text' : 'password'"/>
                  <button type="button" class="toggle-btn" @click="showPassword = !showPassword" aria-label="Toggle password visibility">
                    <svg v-if="!showPassword" class="eye-icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                      <path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z"/>
                      <circle cx="12" cy="12" r="3"/>
                    </svg>
                    <svg v-else class="eye-icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                      <path d="M17.94 17.94A10.94 10.94 0 0 1 12 20c-7 0-11-8-11-8a21.86 21.86 0 0 1 5.06-6.88"/>
                      <path d="M1 1l22 22"/>
                      <path d="M9.88 9.88A3 3 0 0 0 12 15a3 3 0 0 0 2.12-.88"/>
                      <path d="M14.12 14.12A3 3 0 0 0 9.88 9.88"/>
                      <path d="M23 12s-1.64 3.28-4.12 5.88"/>
                    </svg>
                  </button>
                </div>
              </div>
              <div class="form-box">
                <label>Confirm Password</label>
                <div class="password-field">
                  <TextInput v-model="form.password_confirmation" :type="showPasswordConfirm ? 'text' : 'password'"/>
                  <button type="button" class="toggle-btn" @click="showPasswordConfirm = !showPasswordConfirm" aria-label="Toggle confirm password visibility">
                    <svg v-if="!showPasswordConfirm" class="eye-icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                      <path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z"/>
                      <circle cx="12" cy="12" r="3"/>
                    </svg>
                    <svg v-else class="eye-icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                      <path d="M17.94 17.94A10.94 10.94 0 0 1 12 20c-7 0-11-8-11-8a21.86 21.86 0 0 1 5.06-6.88"/>
                      <path d="M1 1l22 22"/>
                      <path d="M9.88 9.88A3 3 0 0 0 12 15a3 3 0 0 0 2.12-.88"/>
                      <path d="M14.12 14.12A3 3 0 0 0 9.88 9.88"/>
                      <path d="M23 12s-1.64 3.28-4.12 5.88"/>
                    </svg>
                  </button>
                </div>
              </div>
              <p class="text-sm text-gray-500 mt-1">Password Strength: <span :class="{'text-red-500':passwordStrength==='Weak','text-yellow-500':passwordStrength==='Medium','text-green-500':passwordStrength==='Strong'}">{{ passwordStrength }}</span></p>
            </div>

            <div v-if="step===5">
              <SectionTitle title="Review & Edit Info"/>
              <div class="space-y-3">
                <div class="form-box"><label>First Name</label><TextInput v-model="form.first_name"/></div>
                <div class="form-box"><label>Middle Initial</label><TextInput v-model="form.middle_initial" maxlength="1"/></div>
                <div class="form-box"><label>Last Name</label><TextInput v-model="form.last_name"/></div>
                <div class="form-box"><label>Email</label><TextInput v-model="form.email"/></div>
                <div class="form-box"><label>Contact Number</label>
                  <TextInput
                    v-model="form.contact_number"
                    inputmode="numeric"
                    pattern="[0-9]*"
                    maxlength="11"
                  />
                </div>
                <div class="form-box">
                  <label>Upload ID</label>
                  <div class="file-review">
                    <div class="file-name">{{ form.government_id ? form.government_id.name : 'No file chosen' }}</div>
                    <img v-if="isImage(form.government_id) && previewUrls.government_id" :src="previewUrls.government_id" class="file-preview" />
                    <input id="government_id_file_review" type="file" class="file-hidden" @change="e=>form.government_id=e.target.files[0]" />
                    <label for="government_id_file_review" class="file-change-btn">Change file</label>
                  </div>
                </div>
              </div>
              <label class="flex items-center gap-2 mt-3"><input type="checkbox" v-model="reviewChecked"/> I have reviewed my info</label>
              <iframe :src="mapUrl" class="w-full h-64 mt-4 rounded-lg border"></iframe>
            </div>
          </div>

          <!-- BUSINESS STEPS -->
          <div v-if="form.role==='business'">
            <div v-if="step===1">
              <SectionTitle title="Business Info"/>
              <div class="owner-grid">
                <div class="form-box">
                  <label>First Name</label>
                  <TextInput v-model="form.business_owner_first"/>
                  <p v-if="nameErrors(form.business_owner_first).number" class="text-red-500 text-xs mt-1">Numbers are not allowed</p>
                  <p v-if="nameErrors(form.business_owner_first).special" class="text-red-500 text-xs">Special characters are not allowed</p>
                </div>
                <div class="form-box">
                  <label>Middle Initial (Optional)</label>
                  <TextInput v-model="form.business_owner_middle" maxlength="1"/>
                  <p v-if="nameErrors(form.business_owner_middle).number" class="text-red-500 text-xs mt-1">Numbers are not allowed</p>
                  <p v-if="nameErrors(form.business_owner_middle).special" class="text-red-500 text-xs">Special characters are not allowed</p>
                </div>
                <div class="form-box">
                  <label>Last Name</label>
                  <TextInput v-model="form.business_owner_last"/>
                  <p v-if="nameErrors(form.business_owner_last).number" class="text-red-500 text-xs mt-1">Numbers are not allowed</p>
                  <p v-if="nameErrors(form.business_owner_last).special" class="text-red-500 text-xs">Special characters are not allowed</p>
                </div>
              </div>
              <div class="biz-grid">
                <div class="form-box"><label>Business Name</label><TextInput v-model="form.business_name_1"/></div>
                <div class="form-box">
                  <label>Business Ownership</label>
                  <select v-model="form.business_ownership" class="select-input">
                    <option value="" disabled>Select Type</option>
                    <option value="Corporation">Corporation</option>
                    <option value="Sole proprietorship">Sole proprietorship</option>
                    <option value="Partnership">Partnership</option>
                  </select>
                </div>
                <div class="form-box">
                  <label>Years in Operation</label>
                  <select v-model="form.years_in_operation" class="select-input">
                    <option value="" disabled>Select</option>
                    <option v-for="y in 50" :key="y" :value="String(y)">{{ y }}</option>
                  </select>
                </div>
              </div>
              <div class="address-grid">
                <div class="form-box"><label>House/Unit No.</label><TextInput v-model="form.address_unit"/></div>
                <div class="form-box"><label>Street</label><TextInput v-model="form.address_street"/></div>
                <div class="form-box"><label>Barangay</label><TextInput v-model="form.address_barangay"/></div>
                <div class="form-box"><label>City/Municipality</label><TextInput v-model="form.address_city"/></div>
                <div class="form-box"><label>Province</label><TextInput v-model="form.address_province"/></div>
                <div class="form-box"><label>Postal Code</label><TextInput v-model="form.address_postal"/></div>
              </div>
            </div>

            <div v-if="step===2">
              <SectionTitle title="Email & Contact"/>
              <div class="form-box"><label>Business Email Address</label><TextInput v-model="form.email"/></div>
              <div class="form-box"><label>Business Contact Number</label>
                <TextInput
                  v-model="form.contact_number"
                  inputmode="numeric"
                  pattern="[0-9]*"
                  maxlength="11"
                />
              </div>
            </div>

            <div v-if="step===3">
              <SectionTitle title="Category"/>
              <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                <div v-for="cat in categories" :key="cat.value" @click="form.category=cat.value" :class="['select-card', form.category===cat.value?'active':'']">
                  <h3 class="font-semibold text-lg">{{cat.label}}</h3>
                  <p class="text-gray-500 text-sm mt-1">{{cat.desc}}</p>
                </div>
              </div>
            </div>

            <div v-if="step===4">
              <SectionTitle title="Business Type"/>
              <div class="flex gap-4">
                <div
                  v-for="t in businessTypes"
                  :key="t.value"
                  @click="selectBusinessType(t.value)"
                  :class="[
                    'select-card',
                    form.business_type===t.value?'active':'',
                    t.value==='Individual' && isIndividualDisabled ? 'disabled-card' : ''
                  ]"
                >{{t.label}}</div>
              </div>
            </div>

            <div v-if="step===5">
              <SectionTitle title="Upload Documents"/>
              <div class="form-box"><label>BIR Registration</label><input type="file" required @change="e=>form.bir_registration=e.target.files[0]" class="file-input"/></div>
              <div class="form-box"><label>DTI Registration</label><input type="file" required @change="e=>form.dti_registration=e.target.files[0]" class="file-input"/></div>
              <div class="form-box"><label>Mayor Permit</label><input type="file" required @change="e=>form.mayor_permit=e.target.files[0]" class="file-input"/></div>
              <div class="form-box"><label>Business Permit</label><input type="file" required @change="e=>form.business_permit=e.target.files[0]" class="file-input"/></div>
              <div class="form-box"><label>Sanitary Permit</label><input type="file" required @change="e=>form.sanitary_permit=e.target.files[0]" class="file-input"/></div>
            </div>

            <div v-if="step===6">
              <SectionTitle title="Set Password"/>
              <div class="form-box">
                <label>Password</label>
                <div class="password-field">
                  <TextInput v-model="form.password" :type="showPassword ? 'text' : 'password'"/>
                  <button type="button" class="toggle-btn" @click="showPassword = !showPassword" aria-label="Toggle password visibility">
                    <svg v-if="!showPassword" class="eye-icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                      <path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z"/>
                      <circle cx="12" cy="12" r="3"/>
                    </svg>
                    <svg v-else class="eye-icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                      <path d="M17.94 17.94A10.94 10.94 0 0 1 12 20c-7 0-11-8-11-8a21.86 21.86 0 0 1 5.06-6.88"/>
                      <path d="M1 1l22 22"/>
                      <path d="M9.88 9.88A3 3 0 0 0 12 15a3 3 0 0 0 2.12-.88"/>
                      <path d="M14.12 14.12A3 3 0 0 0 9.88 9.88"/>
                      <path d="M23 12s-1.64 3.28-4.12 5.88"/>
                    </svg>
                  </button>
                </div>
              </div>
              <div class="form-box">
                <label>Confirm Password</label>
                <div class="password-field">
                  <TextInput v-model="form.password_confirmation" :type="showPasswordConfirm ? 'text' : 'password'"/>
                  <button type="button" class="toggle-btn" @click="showPasswordConfirm = !showPasswordConfirm" aria-label="Toggle confirm password visibility">
                    <svg v-if="!showPasswordConfirm" class="eye-icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                      <path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z"/>
                      <circle cx="12" cy="12" r="3"/>
                    </svg>
                    <svg v-else class="eye-icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                      <path d="M17.94 17.94A10.94 10.94 0 0 1 12 20c-7 0-11-8-11-8a21.86 21.86 0 0 1 5.06-6.88"/>
                      <path d="M1 1l22 22"/>
                      <path d="M9.88 9.88A3 3 0 0 0 12 15a3 3 0 0 0 2.12-.88"/>
                      <path d="M14.12 14.12A3 3 0 0 0 9.88 9.88"/>
                      <path d="M23 12s-1.64 3.28-4.12 5.88"/>
                    </svg>
                  </button>
                </div>
              </div>
              <p class="text-sm text-gray-500 mt-1">Password Strength: <span :class="{'text-red-500':passwordStrength==='Weak','text-yellow-500':passwordStrength==='Medium','text-green-500':passwordStrength==='Strong'}">{{ passwordStrength }}</span></p>
            </div>

            <!-- BUSINESS REVIEW STEP -->
            <div v-if="step===7">
              <SectionTitle title="Review & Edit Info"/>
              <div class="space-y-3">
                <div class="owner-grid">
                  <div class="form-box">
                    <label>First Name</label>
                    <TextInput v-model="form.business_owner_first"/>
                    <p v-if="nameErrors(form.business_owner_first).number" class="text-red-500 text-xs mt-1">Numbers are not allowed</p>
                    <p v-if="nameErrors(form.business_owner_first).special" class="text-red-500 text-xs">Special characters are not allowed</p>
                  </div>
                  <div class="form-box">
                    <label>Middle Initial (Optional)</label>
                    <TextInput v-model="form.business_owner_middle" maxlength="1"/>
                    <p v-if="nameErrors(form.business_owner_middle).number" class="text-red-500 text-xs mt-1">Numbers are not allowed</p>
                    <p v-if="nameErrors(form.business_owner_middle).special" class="text-red-500 text-xs">Special characters are not allowed</p>
                  </div>
                  <div class="form-box">
                    <label>Last Name</label>
                    <TextInput v-model="form.business_owner_last"/>
                    <p v-if="nameErrors(form.business_owner_last).number" class="text-red-500 text-xs mt-1">Numbers are not allowed</p>
                    <p v-if="nameErrors(form.business_owner_last).special" class="text-red-500 text-xs">Special characters are not allowed</p>
                  </div>
                </div>
                <div class="biz-grid">
                  <div class="form-box"><label>Business Name</label><TextInput v-model="form.business_name_1"/></div>
                  <div class="form-box">
                    <label>Business Ownership</label>
                    <select v-model="form.business_ownership" class="select-input">
                      <option value="" disabled>Select Type</option>
                      <option value="Corporation">Corporation</option>
                      <option value="Sole proprietorship">Sole proprietorship</option>
                      <option value="Partnership">Partnership</option>
                    </select>
                  </div>
                  <div class="form-box">
                    <label>Years in Operation</label>
                    <select v-model="form.years_in_operation" class="select-input">
                      <option value="" disabled>Select</option>
                      <option v-for="y in 50" :key="y" :value="String(y)">{{ y }}</option>
                    </select>
                  </div>
                </div>
                <div class="address-grid">
                  <div class="form-box"><label>House/Unit No.</label><TextInput v-model="form.address_unit"/></div>
                  <div class="form-box"><label>Street</label><TextInput v-model="form.address_street"/></div>
                  <div class="form-box"><label>Barangay</label><TextInput v-model="form.address_barangay"/></div>
                  <div class="form-box"><label>City/Municipality</label><TextInput v-model="form.address_city"/></div>
                  <div class="form-box"><label>Province</label><TextInput v-model="form.address_province"/></div>
                  <div class="form-box"><label>Postal Code</label><TextInput v-model="form.address_postal"/></div>
                </div>
                <div class="form-box"><label>Email</label><TextInput v-model="form.email"/></div>
                <div class="form-box"><label>Contact Number</label>
                  <TextInput
                    v-model="form.contact_number"
                    inputmode="numeric"
                    pattern="[0-9]*"
                    maxlength="11"
                  />
                </div>

                <div class="form-box"><label>Category</label>
                  <select v-model="form.category" class="w-full border p-2 rounded min-h-[3rem]">
                    <option value="" disabled>Select Category</option>
                    <option v-for="cat in categories" :key="cat.value" :value="cat.value">{{ cat.label }}</option>
                  </select>
                </div>

                <div class="form-box"><label>Business Type</label>
                  <select v-model="form.business_type" class="w-full border p-2 rounded min-h-[3rem]">
                    <option value="" disabled>Select Type</option>
                    <option v-for="t in businessTypes" :key="t.value" :value="t.value" :disabled="t.value==='Individual' && isIndividualDisabled">{{ t.label }}</option>
                  </select>
                </div>

                <div class="form-box">
                  <label>BIR Registration</label>
                  <div class="file-review">
                    <div class="file-name">{{ form.bir_registration ? form.bir_registration.name : 'No file chosen' }}</div>
                    <img v-if="isImage(form.bir_registration) && previewUrls.bir_registration" :src="previewUrls.bir_registration" class="file-preview" />
                    <input id="bir_registration_file_review" type="file" class="file-hidden" @change="e=>form.bir_registration=e.target.files[0]" />
                    <label for="bir_registration_file_review" class="file-change-btn">Change file</label>
                  </div>
                </div>
                <div class="form-box">
                  <label>DTI Registration</label>
                  <div class="file-review">
                    <div class="file-name">{{ form.dti_registration ? form.dti_registration.name : 'No file chosen' }}</div>
                    <img v-if="isImage(form.dti_registration) && previewUrls.dti_registration" :src="previewUrls.dti_registration" class="file-preview" />
                    <input id="dti_registration_file_review" type="file" class="file-hidden" @change="e=>form.dti_registration=e.target.files[0]" />
                    <label for="dti_registration_file_review" class="file-change-btn">Change file</label>
                  </div>
                </div>
                <div class="form-box">
                  <label>Mayor Permit</label>
                  <div class="file-review">
                    <div class="file-name">{{ form.mayor_permit ? form.mayor_permit.name : 'No file chosen' }}</div>
                    <img v-if="isImage(form.mayor_permit) && previewUrls.mayor_permit" :src="previewUrls.mayor_permit" class="file-preview" />
                    <input id="mayor_permit_file_review" type="file" class="file-hidden" @change="e=>form.mayor_permit=e.target.files[0]" />
                    <label for="mayor_permit_file_review" class="file-change-btn">Change file</label>
                  </div>
                </div>
                <div class="form-box">
                  <label>Business Permit</label>
                  <div class="file-review">
                    <div class="file-name">{{ form.business_permit ? form.business_permit.name : 'No file chosen' }}</div>
                    <img v-if="isImage(form.business_permit) && previewUrls.business_permit" :src="previewUrls.business_permit" class="file-preview" />
                    <input id="business_permit_file_review" type="file" class="file-hidden" @change="e=>form.business_permit=e.target.files[0]" />
                    <label for="business_permit_file_review" class="file-change-btn">Change file</label>
                  </div>
                </div>
                <div class="form-box">
                  <label>Sanitary Permit</label>
                  <div class="file-review">
                    <div class="file-name">{{ form.sanitary_permit ? form.sanitary_permit.name : 'No file chosen' }}</div>
                    <img v-if="isImage(form.sanitary_permit) && previewUrls.sanitary_permit" :src="previewUrls.sanitary_permit" class="file-preview" />
                    <input id="sanitary_permit_file_review" type="file" class="file-hidden" @change="e=>form.sanitary_permit=e.target.files[0]" />
                    <label for="sanitary_permit_file_review" class="file-change-btn">Change file</label>
                  </div>
                </div>
              </div>

              <label class="flex items-center gap-2 mt-3"><input type="checkbox" v-model="reviewChecked"/> I have reviewed my info</label>
              <iframe :src="mapUrl" class="w-full h-64 mt-4 rounded-lg border"></iframe>
            </div>
          </div>

          <!-- FOOTER -->
          <div class="flex justify-between mt-6">
            <button v-if="step>1" type="button" @click="prevStep" class="px-5 py-2 border rounded hover:bg-gray-100">Back</button>
            <div class="ml-auto flex gap-3">
              <button v-if="step<totalSteps" type="button" @click="nextStep" class="px-5 py-2 bg-teal-500 text-white rounded hover:bg-teal-600">Next</button>
              <PrimaryButton v-else :disabled="form.processing || !locationReady" type="submit">Submit</PrimaryButton>
            </div>
          </div>

        </form>
      </div>
    </div>
  </transition>

  <!-- OTP MODAL -->
  <transition name="fade">
      <div v-if="otpModal" class="fixed inset-0 bg-black/50 flex items-center justify-center p-4 z-50">
      <div class="bg-white w-full max-w-md rounded-2xl p-6 shadow-2xl">
        <h3 class="text-xl font-bold text-teal-700 mb-4">Enter OTP</h3>
        <p class="text-gray-600 text-sm mb-4">We sent a 6-digit code to <strong>{{ emailForOtp }}</strong></p>
        <TextInput v-model="otpCode" placeholder="Enter OTP"/>
        <div class="flex justify-between items-center mt-4 gap-2">
          <button
            type="button"
            @click="sendOtp"
            :disabled="sendingOtp || resendSeconds>0"
            class="px-3 py-2 border rounded hover:bg-gray-100 disabled:opacity-50 disabled:cursor-not-allowed"
          >
            {{ resendSeconds>0 ? `Resend (${resendSeconds}s)` : (sendingOtp ? 'Sending...' : 'Resend OTP') }}
          </button>
          <div class="flex gap-2">
          <button @click="otpModal=false" class="px-4 py-2 border rounded hover:bg-gray-100">Cancel</button>
          <PrimaryButton @click="verifyOtp" :disabled="verifyingOtp">{{ verifyingOtp ? 'Verifying...' : 'Verify' }}</PrimaryButton>
          </div>
        </div>
      </div>
    </div>
  </transition>

</div>
</template>

<style scoped>
.fade-enter-active, .fade-leave-active { transition: opacity 0.3s ease; }
.fade-enter-from, .fade-leave-to { opacity: 0; }

.role-card {
  width:280px; height:280px; background:white; border-radius:1.5rem;
  display:flex; align-items:center; justify-content:center;
  font-size:1.5rem; font-weight:600; color:teal;
  box-shadow:0 15px 25px rgba(0,0,0,0.1); transition:0.3s;
}

.select-card {
  padding:20px; border:1px solid #ddd; border-radius:16px; cursor:pointer; transition:0.25s; text-align:center; font-size:1.15rem;
}
.select-card.active {background:#14b8a6; color:white; border-color:#14b8a6; box-shadow:0 5px 15px rgba(0,0,0,0.1);}
.disabled-card { opacity:0.5; cursor:not-allowed; pointer-events:none; }

.form-box { display:flex; flex-direction:column; gap:0.25rem; margin-bottom:0.75rem; }
.form-box label { font-weight:600; color:#0f766e; }
.file-input { padding:0.5rem; border:1px solid #ddd; border-radius:0.5rem; cursor:pointer; }
.file-hidden {
  position:absolute;
  width:1px;
  height:1px;
  padding:0;
  margin:-1px;
  overflow:hidden;
  clip:rect(0, 0, 0, 0);
  white-space:nowrap;
  border:0;
}
.file-review {
  border:1px solid #e5e7eb;
  border-radius:0.75rem;
  padding:0.75rem;
  display:flex;
  flex-direction:column;
  gap:0.5rem;
}
.file-name { color:#111827; font-size:0.95rem; }
.file-preview {
  width:100%;
  max-height:220px;
  object-fit:contain;
  border:1px solid #e5e7eb;
  border-radius:0.5rem;
  background:#f8fafc;
}
.file-change-btn {
  align-self:flex-start;
  background:#14b8a6;
  color:white;
  padding:0.35rem 0.75rem;
  border-radius:0.5rem;
  cursor:pointer;
  font-size:0.9rem;
}
.select-input {
  width:100%;
  border:1px solid #cbd5e1;
  border-radius:0.5rem;
  padding:0.55rem 0.75rem;
  background:#fff;
  min-height:2.75rem;
}
.select-input:focus {
  outline:none;
  border-color:#6366f1;
  box-shadow:0 0 0 2px rgba(99,102,241,0.2);
}

.owner-grid { display:grid; grid-template-columns:1fr; gap:0.75rem; margin-bottom:0.75rem; }
@media (min-width: 768px){
  .owner-grid { grid-template-columns:1.2fr 0.8fr 1.2fr; }
}

.address-grid { display:grid; grid-template-columns:1fr; gap:0.75rem; margin-bottom:0.75rem; }
@media (min-width: 768px){
  .address-grid { grid-template-columns:1fr 1fr 1fr; }
}

.info-grid { display:grid; grid-template-columns:1fr; gap:0.75rem; margin-bottom:0.75rem; }
@media (min-width: 768px){
  .info-grid { grid-template-columns:1fr 1fr; }
}

.biz-grid { display:grid; grid-template-columns:1fr; gap:0.75rem; margin-bottom:0.75rem; }
@media (min-width: 768px){
  .biz-grid { grid-template-columns:1fr 1fr 1fr; }
}

.password-field { position:relative; display:flex; align-items:center; width:100%; }
.toggle-btn {
  position:absolute; right:10px; top:50%; transform:translateY(-50%);
  background:transparent; border:none; cursor:pointer; color:#0f766e; padding:4px;
}
.eye-icon { width:20px; height:20px; }
.password-field :deep(input){ width:100%; padding-right:40px; }

</style>
