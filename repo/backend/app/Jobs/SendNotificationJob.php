<?php

declare(strict_types=1);

namespace App\Jobs;

use App\Models\NotificationDelivery;
use CampusLearn\Notifications\Contracts\NotificationWriter;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Throwable;

final class SendNotificationJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public int $tries = 3;

    /**
     * @param int[]                $recipientIds
     * @param array<string, mixed> $payload
     */
    public function __construct(
        public readonly array $recipientIds,
        public readonly string $category,
        public readonly string $type,
        public readonly string $title,
        public readonly string $body,
        public readonly array $payload = [],
    ) {
    }

    public function handle(NotificationWriter $writer): void
    {
        foreach ($this->recipientIds as $userId) {
            $notificationId = null;
            $failureReason  = null;

            try {
                $notificationId = $writer->write(
                    userId: (int) $userId,
                    category: $this->category,
                    type: $this->type,
                    title: $this->title,
                    body: $this->body,
                    payload: $this->payload,
                );
            } catch (Throwable $e) {
                $failureReason = $e->getMessage();
            }

            NotificationDelivery::create([
                'notification_id' => $notificationId,
                'attempt_count'   => $this->attempts(),
                'last_attempt_at' => now(),
                'delivered_at'    => $notificationId !== null ? now() : null,
                'failure_reason'  => $failureReason,
            ]);
        }
    }

    public function failed(Throwable $exception): void
    {
        // Delivery rows already written per-recipient inside handle()
    }
}
