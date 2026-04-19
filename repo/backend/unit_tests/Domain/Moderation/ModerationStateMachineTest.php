<?php

declare(strict_types=1);

namespace Tests\Domain\Moderation;

use App\Enums\ContentState;
use App\Enums\ModerationActionType;
use CampusLearn\Moderation\ModerationStateMachine;
use CampusLearn\Support\Exceptions\InvalidStateTransition;
use PHPUnit\Framework\TestCase;

final class ModerationStateMachineTest extends TestCase
{
    public function testHideFromVisible(): void
    {
        $fsm = new ModerationStateMachine();
        $this->assertSame(ContentState::Hidden, $fsm->transition(ContentState::Visible, ModerationActionType::Hide));
    }

    public function testRestoreHiddenToVisible(): void
    {
        $fsm = new ModerationStateMachine();
        $this->assertSame(ContentState::Visible, $fsm->transition(ContentState::Hidden, ModerationActionType::Restore));
    }

    public function testLockFromVisible(): void
    {
        $fsm = new ModerationStateMachine();
        $this->assertSame(ContentState::Locked, $fsm->transition(ContentState::Visible, ModerationActionType::Lock));
    }

    public function testUnlockFromLocked(): void
    {
        $fsm = new ModerationStateMachine();
        $this->assertSame(ContentState::Visible, $fsm->transition(ContentState::Locked, ModerationActionType::Unlock));
    }

    public function testIllegalHideFromLocked(): void
    {
        $fsm = new ModerationStateMachine();
        $this->expectException(InvalidStateTransition::class);
        $fsm->transition(ContentState::Locked, ModerationActionType::Hide);
    }

    public function testIllegalRestoreFromVisible(): void
    {
        $fsm = new ModerationStateMachine();
        $this->expectException(InvalidStateTransition::class);
        $fsm->transition(ContentState::Visible, ModerationActionType::Restore);
    }
}
