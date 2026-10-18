<?php

declare(strict_types=1);

use Ixbox\Moonlight\Patisserie;

test('デフォルトの属性で Cookie を焼き、個別の指定で上書きできる', function () {
    $patisserie = (new Patisserie([
        'expires' => 1,
        'path' => '/path',
        'domain' => 'example.com',
        'secure' => true,
        'httponly' => true,
        'samesite' => 'Strict',
    ]))->withDomain('example.net');

    $cookie = $patisserie->bake('Name', 'Value', ['samesite' => 'None']);

    expect((string) $cookie)->toBe(
        'Name=Value; Expires=Thu, 01 Jan 1970 00:00:01 GMT; Max-Age=0; Path=/path; Domain=example.net;'
        . ' Secure; HttpOnly; SameSite=None',
    );
});

test('with メソッドは元の Patisserie を変更しない', function () {
    $original = new Patisserie(['path' => '/']);
    $original->withSecure()->withDomain('example.com');

    expect($original->getOptions())->toBe(['path' => '/']);
});

test('不正なデフォルトを拒否する', function () {
    expect(fn () => new Patisserie(['samesite' => 'lax']))
        ->toThrow(InvalidArgumentException::class);
});

test('不正な上書きを拒否する', function () {
    expect(fn () => (new Patisserie())->bake('Name', 'Value', ['expires' => -1]))
        ->toThrow(InvalidArgumentException::class);
});
