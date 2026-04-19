<?php

declare(strict_types=1);

namespace CampusLearn\Moderation;

use App\Enums\ContentState;
use App\Enums\ModerationActionType;
use CampusLearn\Support\Exceptions\InvalidStateTransition;

final class ModerationStateMachine
{
    /**
     * @var array<string, array<string, ContentState>>
     */
    private array $transitions;

    public function __construct()
    {
        $this->transitions = [
            ContentState::Visible->value => [
                ModerationActionType::Hide->value => ContentState::Hidden,
                ModerationActionType::Lock->value => ContentState::Locked,
            ],
            ContentState::Hidden->value => [
                ModerationActionType::Restore->value => ContentState::Visible,
                ModerationActionType::Lock->value => ContentState::Locked,
            ],
            ContentState::Locked->value => [
                ModerationActionType::Unlock->value => ContentState::Visible,
            ],
        ];
    }

    public function transition(ContentState $from, ModerationActionType $action): ContentState
    {
        $row = $this->transitions[$from->value] ?? [];
        if (! array_key_exists($action->value, $row)) {
            throw new InvalidStateTransition('moderation', $from->value, $action->value);
        }
        return $row[$action->value];
    }

    public function canTransition(ContentState $from, ModerationActionType $action): bool
    {
        return array_key_exists($action->value, $this->transitions[$from->value] ?? []);
    }
}
