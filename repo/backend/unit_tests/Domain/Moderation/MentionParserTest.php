<?php

declare(strict_types=1);

namespace Tests\Domain\Moderation;

use CampusLearn\Moderation\MentionParser;
use PHPUnit\Framework\TestCase;

final class MentionParserTest extends TestCase
{
    private MentionParser $parser;

    protected function setUp(): void
    {
        $this->parser = new MentionParser();
    }

    public function testExtractsHandles(): void
    {
        $resolver = fn ($handles) => ['alice' => 1, 'bob' => 2];
        $result   = $this->parser->parse('Hello @alice and @bob!', $resolver);

        $this->assertContains(1, $result['user_ids']);
        $this->assertContains(2, $result['user_ids']);
        $this->assertEmpty($result['unknown']);
    }

    public function testDeduplicatesHandles(): void
    {
        $called = 0;
        $resolver = function ($handles) use (&$called) {
            $called++;
            return ['alice' => 1];
        };

        $result = $this->parser->parse('@alice @alice @alice', $resolver);
        $this->assertSame([1], $result['user_ids']);
        $this->assertSame(1, $called);
    }

    public function testUnresolvedHandlesReportedAsUnknown(): void
    {
        $resolver = fn ($handles) => [];
        $result   = $this->parser->parse('Hi @ghost!', $resolver);

        $this->assertEmpty($result['user_ids']);
        $this->assertContains('ghost', $result['unknown']);
    }

    public function testIgnoresBodyWithNoAt(): void
    {
        $resolver = fn ($handles) => [];
        $result   = $this->parser->parse('No mentions here.', $resolver);

        $this->assertSame(['user_ids' => [], 'handles' => [], 'unknown' => []], $result);
    }

    public function testHandleWithDotUnderscore(): void
    {
        $resolver = fn ($handles) => ['john.doe' => 5];
        $result   = $this->parser->parse('@john.doe is here', $resolver);

        $this->assertSame([5], $result['user_ids']);
    }
}
