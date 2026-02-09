<script setup>
import Checkbox from '@/Components/Checkbox.vue'
import InputError from '@/Components/InputError.vue'
import InputLabel from '@/Components/InputLabel.vue'
import PrimaryButton from '@/Components/PrimaryButton.vue'
import TextInput from '@/Components/TextInput.vue'
import { Head, Link, useForm, router } from '@inertiajs/vue3'
import Swal from 'sweetalert2'
import { ref } from 'vue'

defineProps({
  canResetPassword: Boolean,
  status: String,
})

/* ================= FORM ================= */
const form = useForm({
  email: '',
  password: '',
  remember: false,
})

const showPassword = ref(false)

/* ================= SUBMIT ================= */
const submit = () => {
  form.post(route('login'), {
    preserveScroll: true,
    onSuccess: (response) => {
      const role = response.role
      if (role) {
        Swal.fire({
          icon: 'success',
          title: 'Login Successful!',
          text: 'Welcome back 🎉',
          confirmButtonColor: '#4f46e5',
          confirmButtonText: 'Continue',
          allowOutsideClick: false,
        }).then(() => {
          switch (role) {
            case 'admin':
              router.visit(route('admin.dashboard'))
              break
            case 'finance':
              router.visit(route('finance.dashboard'))
              break
            case 'hr':
              router.visit(route('hr.dashboard'))
              break
            case 'procurement':
              router.visit(route('procurement.dashboard'))
              break
            case 'business':
              router.visit(route('business.dashboard'))
              break
            case 'serviceprovider':
              router.visit(route('serviceprovider.dashboard'))
              break
            default:
              router.visit(route('user.dashboard'))
          }
        })
      }
    },
    onError: (errors) => {
      Swal.fire({
        icon: 'error',
        title: 'Login Failed',
        text: errors?.error || 'Invalid email or password.',
        confirmButtonColor: '#dc2626',
      })
    },
    onFinish: () => form.reset('password'),
  })
}
</script>

<template>
  <Head title="Log in" />

  <!-- FULL SCREEN BACKGROUND -->
  <div class="min-h-screen flex items-center justify-center bg-gradient-to-br from-teal-200 via-teal-100 to-teal-200 px-4 py-16">

    <!-- LOGIN FORM CARD -->
    <div class="w-full max-w-md bg-white/50 backdrop-blur-md rounded-3xl shadow-2xl p-10 space-y-8">

      <!-- PAGE TITLE -->
      <h1 class="text-3xl font-bold text-gray-800 text-center">
        Log In
      </h1>

      <!-- STATUS MESSAGE -->
      <div v-if="status" class="font-medium text-sm text-green-600 text-center">
        {{ status }}
      </div>

      <!-- LOGIN FORM -->
      <form @submit.prevent="submit" class="space-y-6">

        <div>
          <InputLabel for="email" value="Email" />
          <TextInput
            id="email"
            type="email"
            class="mt-1 block w-full"
            v-model="form.email"
            required
            autofocus
            autocomplete="username"
          />
          <InputError class="mt-2" :message="form.errors.email" />
        </div>

        <div>
          <InputLabel for="password" value="Password" />
          <div class="relative">
            <TextInput
              id="password"
              :type="showPassword ? 'text' : 'password'"
              class="mt-1 block w-full pr-16"
              v-model="form.password"
              required
              autocomplete="current-password"
            />
            <button
              type="button"
              class="absolute right-3 top-1/2 -translate-y-1/2 text-xs font-medium text-gray-600 hover:text-gray-900"
              @click="showPassword = !showPassword"
            >
              {{ showPassword ? 'Hide' : 'Show' }}
            </button>
          </div>
          <InputError class="mt-2" :message="form.errors.password" />
        </div>

        <div class="flex items-center justify-between">
          <label class="flex items-center">
            <Checkbox name="remember" v-model:checked="form.remember" />
            <span class="ml-2 text-sm text-gray-600">Remember me</span>
          </label>

          <Link
            v-if="canResetPassword"
            :href="route('password.request')"
            class="underline text-sm text-gray-600 hover:text-gray-900"
          >
            Forgot your password?
          </Link>
        </div>

        <PrimaryButton
          class="w-full"
          :class="{ 'opacity-25': form.processing }"
          :disabled="form.processing"
        >
          Log in
        </PrimaryButton>
      </form>

      <!-- LOGIN TO REGISTER LINK -->
      <p class="text-center text-gray-700">
        Don't have an account?
        <Link
          href="/register"
          class="text-teal-600 font-semibold hover:text-teal-800 ml-1"
        >
          Register
        </Link>
      </p>

    </div>

  </div>
</template>

<style scoped>
/* Optional fade for modal or notifications if needed */
</style>
