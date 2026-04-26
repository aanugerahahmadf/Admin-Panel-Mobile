<?php

use Tests\TestCase;

test('the application returns a successful response', function (): void {
    /** @var TestCase $this */
    $response = $this->get('/');

    $response->assertStatus(200);
});
