<?php

declare(strict_types=1);

namespace App\Services;

use App\Enums\NotificationCategory;
use App\Jobs\SendNotificationJob;
use App\Models\NotificationSubscription;
use App\Models\NotificationTemplate;
use Illuminate\Support\Facades\Bus;
use RuntimeException;

final class NotificationOrchestrator
{
    /**
     * Fan out a notification to the recipient set. Recipients whose subscription
     * is explicitly disabled for the template category are filtered out. Missing
     * subscription rows default to enabled.
     *
     * @param int[] $recipientIds
     * @param array<string, mixed> $placeholders
     * @return array{queued: int, skipped_subscription: int, skipped_no_template: bool}
     */
    public function notify(string $type, array $recipientIds, array $placeholders = []): array
    {
        $template = NotificationTemplate::where('type', $type)->first();
        if ($template === null) {
            return ['queued' => 0, 'skipped_subscription' => 0, 'skipped_no_template' => true];
        }

        $recipients = array_values(array_unique(array_map('intval', $recipientIds)));
        if ($recipients === []) {
            return ['queued' => 0, 'skipped_subscription' => 0, 'skipped_no_template' => false];
        }

        $category = $template->category instanceof NotificationCategory
            ? $template->category->value
            : (string) $template->category;

        $disabled = NotificationSubscription::query()
            ->whereIn('user_id', $recipients)
            ->where('category', $category)
            ->where('enabled', false)
            ->pluck('user_id')
            ->map(static fn ($v) => (int) $v)
            ->all();

        $effective = array_values(array_diff($recipients, $disabled));
        if ($effective === []) {
            return ['queued' => 0, 'skipped_subscription' => count($disabled), 'skipped_no_template' => false];
        }

        $title = $this->render($template->title_template ?? '', $placeholders);
        $body  = $this->render($template->body_template ?? '', $placeholders);

        $batchSize = max(1, (int) config('campuslearn.notifications.fanout_batch_size', 50));
        $queued    = 0;

        foreach (array_chunk($effective, $batchSize) as $chunk) {
            Bus::dispatch(new SendNotificationJob(
                recipientIds: $chunk,
                category: $category,
                type: $type,
                title: $title,
                body: $body,
                payload: $placeholders,
            ));
            $queued += count($chunk);
        }

        return [
            'queued'               => $queued,
            'skipped_subscription' => count($disabled),
            'skipped_no_template'  => false,
        ];
    }

    private function render(string $template, array $placeholders): string
    {
        if ($template === '' || $placeholders === []) {
            return $template;
        }
        $pairs = [];
        foreach ($placeholders as $key => $value) {
            if (! is_string($key)) {
                continue;
            }
            $pairs['{' . $key . '}'] = is_scalar($value) || $value === null ? (string) $value : json_encode($value);
        }
        return strtr($template, $pairs);
    }
}
