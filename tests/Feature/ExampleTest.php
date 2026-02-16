<?php

test('the health check returns a successful response', function () {
    $response = $this->get('/up');

    $response->assertSuccessful();
});
