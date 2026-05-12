<?php

test('returns a successful response', function () {
    $response = $this->get(route('home'));

    $response->assertSuccessful();
});

test('home page exposes only light and dark appearance modes', function () {
    $response = $this->get(route('home'));

    $response->assertSuccessful();
    $response->assertSee('Skip to content');
    $response->assertSee('Light');
    $response->assertSee('Dark');
    $response->assertDontSee('System');
    $response->assertSee("window.localStorage.setItem('flux.appearance', 'light')", false);
});
