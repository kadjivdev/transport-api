<?php

it('true to be true', function () {
    expect(true)->toBeTrue();
});

it('false to be false', function () {
    expect("gogo")->toBeFalse();
});
