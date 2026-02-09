<?php

namespace App\Http\Controllers;

use App\Models\JobPost;
use App\Models\User;
use App\Notifications\JobPostPublished;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Notification;
use Inertia\Inertia;

class JobPostController extends Controller
{
    public function index()
    {
        return Inertia::render('JobsBoard', [
            'jobs' => JobPost::query()
                ->where('is_published', true)
                ->orderByDesc('published_at')
                ->get()
                ->map(fn (JobPost $job) => [
                    'id' => $job->id,
                    'title' => $job->title,
                    'team' => $job->team,
                    'status' => $job->status,
                    'preferred_start_date' => optional($job->preferred_start_date)->format('Y-m-d'),
                    'notes' => $job->notes,
                    'published_at' => optional($job->published_at)->format('M d, Y'),
                ]),
        ]);
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'role' => ['required', 'string', 'max:255'],
            'team' => ['required', 'string', 'max:255'],
            'status' => ['nullable', 'string', 'max:255'],
            'preferred_start_date' => ['nullable', 'date'],
            'notes' => ['nullable', 'string', 'max:2000'],
        ]);

        $jobPost = JobPost::create([
            'title' => $data['role'],
            'team' => $data['team'],
            'status' => $data['status'] ?? 'Active',
            'preferred_start_date' => $data['preferred_start_date'] ?? null,
            'notes' => $data['notes'] ?? null,
            'posted_by' => optional($request->user())->id,
            'is_published' => true,
            'published_at' => now(),
        ]);

        $this->notifyAdmins($jobPost);
        $this->postToLinkedInIfConfigured($jobPost);

        return back()->with('success', 'Open role published.');
    }

    private function notifyAdmins(JobPost $jobPost): void
    {
        $notification = new JobPostPublished($jobPost);

        $adminUsers = User::query()->where('role', 'admin')->get();
        if ($adminUsers->isNotEmpty()) {
            Notification::send($adminUsers, $notification);
        }

        $emails = collect(explode(',', (string) env('HR_ADMIN_EMAILS', '')))
            ->map(fn ($email) => trim($email))
            ->filter();

        if ($emails->isNotEmpty()) {
            Notification::route('mail', $emails->all())->notify($notification);
        }
    }

    private function postToLinkedInIfConfigured(JobPost $jobPost): void
    {
        $token = env('LINKEDIN_ACCESS_TOKEN');
        $organizationUrn = env('LINKEDIN_ORG_URN');
        $companyApplyUrl = env('LINKEDIN_COMPANY_APPLY_URL');
        $industryUrn = env('LINKEDIN_INDUSTRY_URN');
        $location = env('LINKEDIN_LOCATION');

        if (!$token || !$organizationUrn || !$companyApplyUrl || !$industryUrn || !$location) {
            $jobPost->update([
                'linkedin_status' => 'skipped',
                'linkedin_error' => 'Missing LinkedIn configuration.',
            ]);
            return;
        }

        $payload = [
            'integrationContext' => $organizationUrn,
            'companyApplyUrl' => $companyApplyUrl,
            'externalJobPostingId' => (string) $jobPost->id,
            'jobPostingOperationType' => 'CREATE',
            'title' => $jobPost->title,
            'description' => $jobPost->notes ?: 'Open role for field services operations.',
            'listedAt' => now()->timestamp * 1000,
            'location' => $location,
            'industries' => [$industryUrn],
            'listingType' => 'BASIC',
            'workplaceTypes' => ['Onsite'],
        ];

        $response = Http::withToken($token)
            ->acceptJson()
            ->post('https://api.linkedin.com/v2/simpleJobPostings', $payload);

        if ($response->successful()) {
            $jobPost->update([
                'linkedin_job_posting_id' => $response->json('id'),
                'linkedin_task_urn' => $response->json('task'),
                'linkedin_status' => 'submitted',
                'linkedin_error' => null,
            ]);
            return;
        }

        $jobPost->update([
            'linkedin_status' => 'failed',
            'linkedin_error' => $response->body(),
        ]);
    }
}
