<template>
  <div class="min-h-screen bg-slate-50 text-slate-900">
    <div class="px-8 py-10">
      <div class="max-w-5xl mx-auto space-y-8">
        <div class="text-center">
          <p class="text-xs uppercase tracking-[0.2em] text-slate-400">Careers</p>
          <h1 class="text-3xl font-semibold text-slate-900">Open Roles</h1>
          <p class="text-sm text-slate-500 mt-2">Join our plumbing and siphoning field operations team.</p>
        </div>

        <div v-if="jobs.length === 0" class="rounded-2xl border border-slate-200 bg-white p-8 text-center">
          <p class="text-sm text-slate-500">No open roles at the moment. Please check back soon.</p>
        </div>

        <div v-else class="grid gap-4">
          <article v-for="job in jobs" :key="job.id" class="rounded-2xl border border-slate-200 bg-white p-6 shadow-sm">
            <div class="flex flex-col gap-3 md:flex-row md:items-start md:justify-between">
              <div>
                <h2 class="text-lg font-semibold text-slate-900">{{ job.title }}</h2>
                <p class="text-sm text-slate-500">{{ job.team }} · {{ job.status }}</p>
                <p v-if="job.preferred_start_date" class="text-xs text-slate-400 mt-1">
                  Preferred start: {{ job.preferred_start_date }}
                </p>
              </div>
              <span class="rounded-full bg-slate-900 px-3 py-1 text-xs font-semibold uppercase tracking-wide text-white">
                Open
              </span>
            </div>
            <p v-if="job.notes" class="text-sm text-slate-600 mt-4">{{ job.notes }}</p>
            <p v-if="job.published_at" class="text-[11px] text-slate-400 mt-3">Posted {{ job.published_at }}</p>
          </article>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup>
defineProps({
  jobs: {
    type: Array,
    default: () => [],
  },
});
</script>
