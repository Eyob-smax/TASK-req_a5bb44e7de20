<?php

declare(strict_types=1);

namespace Tests\Domain\Auth;

use App\Enums\RoleName;
use App\Enums\ScopeType;
use CampusLearn\Auth\Contracts\ScopeResolver;
use CampusLearn\Auth\Grant;
use CampusLearn\Auth\ScopeContext;
use CampusLearn\Auth\ScopeResolutionService;
use PHPUnit\Framework\TestCase;

final class ScopeResolutionServiceTest extends TestCase
{
    public function testAdministratorGlobalOverride(): void
    {
        $resolver = $this->resolver([new Grant(RoleName::Administrator, ScopeType::Global, null)]);
        $service = new ScopeResolutionService($resolver);
        $this->assertTrue($service->canPerform(1, RoleName::Teacher, ScopeContext::section(42)));
    }

    public function testTeacherSectionMatchesOwnSection(): void
    {
        $resolver = $this->resolver([new Grant(RoleName::Teacher, ScopeType::Section, 42)]);
        $service = new ScopeResolutionService($resolver);
        $this->assertTrue($service->canPerform(1, RoleName::Teacher, ScopeContext::section(42)));
        $this->assertFalse($service->canPerform(1, RoleName::Teacher, ScopeContext::section(99)));
    }

    public function testTeacherSectionCoversGradeItemInSameSection(): void
    {
        $resolver = $this->resolver([new Grant(RoleName::Teacher, ScopeType::Section, 42)]);
        $service = new ScopeResolutionService($resolver);
        $this->assertTrue($service->canPerform(
            userId: 1,
            requiredRole: RoleName::Teacher,
            context: ScopeContext::gradeItem(9001),
            ancestry: ['section' => 42],
        ));
    }

    public function testRegistrarTermScopedToSameTerm(): void
    {
        $resolver = $this->resolver([new Grant(RoleName::Registrar, ScopeType::Term, 7)]);
        $service = new ScopeResolutionService($resolver);
        $this->assertTrue($service->canPerform(1, RoleName::Registrar, ScopeContext::term(7)));
        $this->assertTrue($service->canPerform(
            userId: 1,
            requiredRole: RoleName::Registrar,
            context: ScopeContext::section(101),
            ancestry: ['term' => 7],
        ));
        $this->assertFalse($service->canPerform(1, RoleName::Registrar, ScopeContext::term(8)));
    }

    public function testStudentRolePresence(): void
    {
        $resolver = $this->resolver([new Grant(RoleName::Student, ScopeType::Global, null)]);
        $service = new ScopeResolutionService($resolver);
        $this->assertTrue($service->hasRole(1, RoleName::Student));
        $this->assertFalse($service->hasRole(1, RoleName::Teacher));
    }

    public function testUnionOfGrants(): void
    {
        $resolver = $this->resolver([
            new Grant(RoleName::Teacher, ScopeType::Section, 42),
            new Grant(RoleName::Registrar, ScopeType::Term, 9),
        ]);
        $service = new ScopeResolutionService($resolver);
        $this->assertTrue($service->canPerform(1, RoleName::Teacher, ScopeContext::section(42)));
        $this->assertTrue($service->canPerform(1, RoleName::Registrar, ScopeContext::term(9)));
    }

    /**
     * @param Grant[] $grants
     */
    private function resolver(array $grants): ScopeResolver
    {
        return new class ($grants) implements ScopeResolver {
            /** @param Grant[] $grants */
            public function __construct(private readonly array $grants) {}

            public function activeGrantsFor(int $userId): array
            {
                return $this->grants;
            }
        };
    }
}
