<?php

declare(strict_types=1);

namespace App\Services;

use App\Enums\ContentState;
use App\Enums\ModerationActionType;
use App\Enums\PostState;
use App\Models\Comment;
use App\Models\ModerationAction;
use App\Models\Post;
use App\Models\Thread;
use App\Models\User;
use App\Support\AuditLogger;
use CampusLearn\Moderation\ModerationStateMachine;
use CampusLearn\Support\Exceptions\InvalidStateTransition;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;

final class ModerationService
{
    public function __construct(
        private readonly ModerationStateMachine $machine,
        private readonly AuditLogger $audit,
        private readonly NotificationOrchestrator $notifier,
    ) {
    }

    /**
     * Apply a moderation action on a Thread/Post/Comment.
     *
     * @throws InvalidStateTransition on illegal transitions.
     */
    public function apply(User $moderator, Model $target, ModerationActionType $action, ?string $notes): Model
    {
        $targetType = $this->targetType($target);

        return DB::transaction(function () use ($moderator, $target, $action, $notes, $targetType): Model {
            $currentState = $this->readState($target);
            $next         = $this->machine->transition($currentState, $action);

            if ($target instanceof Thread) {
                $target->state = $next;
            } elseif ($target instanceof Post || $target instanceof Comment) {
                if ($next === ContentState::Locked) {
                    throw new InvalidStateTransition('moderation', $currentState->value, $action->value);
                }
                $target->state = $next === ContentState::Hidden ? PostState::Hidden : PostState::Visible;
            }
            $target->save();

            ModerationAction::create([
                'moderator_id' => $moderator->id,
                'target_type'  => $targetType,
                'target_id'    => $target->getKey(),
                'action'       => $action,
                'notes'        => $notes,
                'created_at'   => now(),
            ]);

            $this->audit->record($moderator->id, 'moderation.' . $action->value, $targetType, (int) $target->getKey(), [
                'from'  => $currentState->value,
                'to'    => $next->value,
                'notes' => $notes,
            ]);

            $authorId = $this->authorId($target);
            if ($authorId !== null && $authorId !== $moderator->id) {
                if ($action === ModerationActionType::Hide) {
                    $this->notifier->notify('moderation.content-hidden', [$authorId], [
                        'target_type' => $targetType,
                        'notes'       => (string) $notes,
                    ]);
                }
                if ($action === ModerationActionType::Lock && $target instanceof Thread) {
                    $this->notifier->notify('moderation.content-locked', [$authorId], [
                        'title' => $target->title,
                    ]);
                }
            }

            return $target->fresh();
        });
    }

    private function readState(Model $target): ContentState
    {
        if ($target instanceof Thread) {
            return $target->state instanceof ContentState ? $target->state : ContentState::from((string) $target->state);
        }
        $val = $target->state instanceof PostState ? $target->state->value : (string) $target->state;
        return $val === PostState::Hidden->value ? ContentState::Hidden : ContentState::Visible;
    }

    private function targetType(Model $target): string
    {
        return match (true) {
            $target instanceof Thread  => 'thread',
            $target instanceof Post    => 'post',
            $target instanceof Comment => 'comment',
            default                    => strtolower(class_basename($target)),
        };
    }

    private function authorId(Model $target): ?int
    {
        $v = $target->getAttribute('author_id');
        return $v === null ? null : (int) $v;
    }
}
