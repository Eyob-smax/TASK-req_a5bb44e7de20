<?php

declare(strict_types=1);

use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

test('every response includes a correlation id header', function () {
    $response = $this->getJson('/api/health');

    $response->assertStatus(200);
    $correlationId = $response->headers->get('X-Correlation-Id');
    expect($correlationId)->not->toBeNull()->not->toBe('');
});

test('inbound X-Correlation-Id header is echoed verbatim', function () {
    $incoming = 'c0ffee00-1111-2222-3333-444455556666';
    $response = $this->withHeaders(['X-Correlation-Id' => $incoming])->getJson('/api/health');

    $response->assertStatus(200);
    expect($response->headers->get('X-Correlation-Id'))->toBe($incoming);
});
