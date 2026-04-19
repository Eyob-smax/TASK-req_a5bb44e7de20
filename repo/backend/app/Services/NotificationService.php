<?php

declare(strict_types=1);

namespace App\Services;

use App\Enums\NotificationCategory;
use App\Models\Notification;
use App\Models\NotificationSubscription;
use CampusLearn\Notifications\UnreadCounter;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\DB;

final class NotificationService
{
    public function __construct(
        private readonly UnreadCounter $unreadCounter,
    ) {
    }

    /**
     * @return LengthAwarePaginator<Notification>
     */
    public function list(int $userId, ?string $category, bool $unreadOnly, int $perPage): LengthAwarePaginator
    {
        $query = Notification::query()->where('user_id', $userId)->orderByDesc('created_at');
        if ($category !== null && $category !== '') {
            $query->where('category', $category);
        }
        if ($unreadOnly) {
            $query->whereNull('read_at');
        }
        return $query->paginate(max(1, min(100, $perPage)));
    }

    /**
     * @return array{total: int, by_category: array<string, int>}
     */
    public function unreadCount(int $userId): array
    {
        return $this->unreadCounter->summarize($userId);
    }

    public function markOneRead(int $userId, int $notificationId): Notification
    {
        /** @var Notification $n */
        $n = Notification::where('user_id', $userId)->findOrFail($notificationId);
        if ($n->read_at === null) {
            $n->read_at = now();
            $n->save();
        }
        return $n;
    }

    /**
     * @param int[] $ids
     */
    public function markManyRead(int $userId, array $ids): int
    {
        if ($ids === []) {
            return 0;
        }
        return Notification::where('user_id', $userId)
            ->whereIn('id', $ids)
            ->whereNull('read_at')
            ->update(['read_at' => now()]);
    }

    public function markAllRead(int $userId, ?string $category = null): int
    {
        $q = Notification::where('user_id', $userId)->whereNull('read_at');
        if ($category !== null && $category !== '') {
            $q->where('category', $category);
        }
        return $q->update(['read_at' => now()]);
    }

    /**
     * @return array<string, bool>
     */
    public function getPreferences(int $userId): array
    {
        $map = [];
        foreach (NotificationCategory::cases() as $cat) {
            $map[$cat->value] = true;
        }
        $rows = NotificationSubscription::where('user_id', $userId)->get();
        foreach ($rows as $row) {
            $key       = $row->category instanceof NotificationCategory ? $row->category->value : (string) $row->category;
            $map[$key] = (bool) $row->enabled;
        }
        return $map;
    }

    /**
     * @param array<string, bool> $prefs Category value => enabled.
     * @return array<string, bool>
     */
    public function updatePreferences(int $userId, array $prefs): array
    {
        $valid = array_map(static fn (NotificationCategory $c) => $c->value, NotificationCategory::cases());

        DB::transaction(function () use ($userId, $prefs, $valid): void {
            foreach ($prefs as $category => $enabled) {
                if (! in_array($category, $valid, true)) {
                    continue;
                }
                NotificationSubscription::updateOrCreate(
                    ['user_id' => $userId, 'category' => $category],
                    ['enabled' => (bool) $enabled],
                );
            }
        });

        return $this->getPreferences($userId);
    }
}
