<?php

use Illuminate\Foundation\Application;

test('Laravel application instance is created', function () {
    expect(app())->toBeInstanceOf(Application::class);
});

test('application has correct name', function () {
    expect(config('app.name'))->toBe('CampusLearn');
});

test('database connection is configured for mysql', function () {
    expect(config('database.default'))->toBeIn(['mysql', 'sqlite']);
});

test('queue connection is configured', function () {
    expect(config('queue.default'))->toBeIn(['database', 'sync']);
});
