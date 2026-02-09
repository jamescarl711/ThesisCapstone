<?php

namespace App\Notifications;

use App\Models\JobPost;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;
use Illuminate\Notifications\Messages\MailMessage;

class JobPostPublished extends Notification
{
    use Queueable;

    public function __construct(private JobPost $jobPost)
    {
    }

    public function via(object $notifiable): array
    {
        return ['mail', 'database'];
    }

    public function toMail(object $notifiable): MailMessage
    {
        $startDate = optional($this->jobPost->preferred_start_date)->format('M d, Y') ?? 'TBD';
        $publishedAt = optional($this->jobPost->published_at)->format('M d, Y');

        return (new MailMessage)
            ->subject('New Open Role Posted')
            ->line('A new open role has been published.')
            ->line('Role: ' . $this->jobPost->title)
            ->line('Team: ' . $this->jobPost->team)
            ->line('Status: ' . $this->jobPost->status)
            ->line('Start Date: ' . $startDate)
            ->when($publishedAt, fn (MailMessage $message) => $message->line('Published: ' . $publishedAt))
            ->action('View Careers Page', url('/careers'));
    }

    public function toDatabase(object $notifiable): array
    {
        return [
            'job_post_id' => $this->jobPost->id,
            'title' => $this->jobPost->title,
            'team' => $this->jobPost->team,
            'status' => $this->jobPost->status,
            'published_at' => $this->jobPost->published_at,
        ];
    }
}
