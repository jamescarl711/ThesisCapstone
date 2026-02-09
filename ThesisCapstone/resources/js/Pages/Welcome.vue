<script setup>
import { Head, Link } from '@inertiajs/vue3';

defineProps({
    canLogin: Boolean,
    canRegister: Boolean,
});
</script>

<template>
    <Head title="Plumbing and Siphoning" />

    <div class="relative min-h-screen flex flex-col text-gray-900 dark:text-white overflow-hidden">

        <!-- Background Video -->
        <video 
            autoplay 
            muted 
            loop 
            playsinline
            class="absolute top-0 left-0 w-full h-full object-cover z-0"
        >
            <source src="/videos/plumbing-bg.mp4" type="video/mp4" />
            Your browser does not support the video tag.
        </video>

        <!-- Overlay to make text readable -->
        <div class="absolute top-0 left-0 w-full h-full bg-black/40 z-10"></div>

        <!-- Navbar -->
        <nav class="relative z-20 bg-white/80 dark:bg-gray-800/80 shadow-md sticky top-0">
            <div class="max-w-7xl mx-auto px-6 py-4 flex justify-between items-center">
                
                <!-- Logo / Title -->
                <h1 class="text-2xl font-bold text-blue-600">
                    Plumbing & Siphoning
                </h1>

                <!-- Navigation Links -->
                <div class="space-x-4">
                    <!-- If user is logged in -->
                    <Link
                        v-if="$page.props.auth.user"
                        :href="route('dashboard')"
                        class="font-semibold text-gray-600 hover:text-gray-900 dark:text-gray-400 dark:hover:text-white"
                    >
                        Dashboard
                    </Link>

                    <!-- If user is NOT logged in -->
                    <template v-else>
                        <Link
                            v-if="canLogin"
                            :href="route('login')"
                            class="font-semibold text-gray-600 hover:text-gray-900 dark:text-gray-400 dark:hover:text-white"
                        >
                            Log in
                        </Link>

                        <Link
                            v-if="canRegister"
                            :href="route('register')"
                            class="font-semibold text-gray-600 hover:text-gray-900 dark:text-gray-400 dark:hover:text-white"
                        >
                            Register
                        </Link>
                    </template>
                </div>
            </div>
        </nav>

        <!-- Main Content -->
        <main class="relative z-20 flex-1 max-w-5xl mx-auto p-6 lg:p-8">

            <!-- Hero Section -->
            <div class="text-center mb-16">
                <svg
                    xmlns="http://www.w3.org/2000/svg"
                    fill="none"
                    viewBox="0 0 24 24"
                    stroke-width="1.5"
                    class="w-28 h-28 mx-auto text-blue-600 mb-6 animate-bounce"
                >
                    <path stroke-linecap="round" stroke-linejoin="round" d="M3 12l2-2m0 0l7-7 7 7M13 5v6h6"/>
                </svg>

                <h2 class="text-5xl font-extrabold mb-4 text-blue-100 dark:text-blue-400">
                    Welcome to Plumbing & Siphoning
                </h2>
                <p class="text-gray-200 dark:text-gray-300 text-lg max-w-2xl mx-auto">
                    Efficiently manage your plumbing operations, schedules, and permits with ease. Our system ensures reliability, compliance, and productivity.
                </p>
            </div>

            <!-- Mission, Vision, Core Values -->
            <div class="grid grid-cols-1 md:grid-cols-3 gap-8 text-left mb-16">
                <div class="p-6 bg-white/80 dark:bg-gray-800/80 rounded-xl shadow-lg hover:shadow-2xl transition transform hover:-translate-y-1">
                    <h3 class="text-xl font-semibold mb-2 text-gray-900 dark:text-white">Mission</h3>
                    <p class="text-gray-600 dark:text-gray-400 text-sm">
                        To provide efficient and reliable plumbing and siphoning services while ensuring safety, compliance, and customer satisfaction.
                    </p>
                </div>
                <div class="p-6 bg-white/80 dark:bg-gray-800/80 rounded-xl shadow-lg hover:shadow-2xl transition transform hover:-translate-y-1">
                    <h3 class="text-xl font-semibold mb-2 text-gray-900 dark:text-white">Vision</h3>
                    <p class="text-gray-600 dark:text-gray-400 text-sm">
                        To be the leading plumbing and siphoning management system known for innovation, transparency, and excellence in service delivery.
                    </p>
                </div>
                <div class="p-6 bg-white/80 dark:bg-gray-800/80 rounded-xl shadow-lg hover:shadow-2xl transition transform hover:-translate-y-1">
                    <h3 class="text-xl font-semibold mb-2 text-gray-900 dark:text-white">Core Values</h3>
                    <p class="text-gray-600 dark:text-gray-400 text-sm">
                        Integrity, Reliability, Innovation, Safety, Customer Focus, and Compliance drive everything we do.
                    </p>
                </div>
            </div>

        </main>

        <!-- Footer -->
        <footer class="relative z-20 bg-white/80 dark:bg-gray-800/80 shadow-inner mt-auto py-6">
            <div class="max-w-7xl mx-auto px-6 flex flex-col md:flex-row justify-between items-center text-sm text-gray-500 dark:text-gray-400 gap-2 md:gap-0">
                <div>
                    &copy; {{ new Date().getFullYear() }} Plumbing & Siphoning. All rights reserved.
                </div>
            </div>
        </footer>

    </div>
</template>
