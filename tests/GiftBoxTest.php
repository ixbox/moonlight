<?php

declare(strict_types=1);

use Ixbox\Moonlight\Cookie;
use Ixbox\Moonlight\GiftBox;
use Nyholm\Psr7\Response;

test('Cookie ごとに Set-Cookie ヘッダーを追加する', function () {
    $box = new GiftBox();
    $box->add('a', '1', ['path' => '/']);
    $box[] = new Cookie('b', '2', ['httponly' => true]);

    $response = $box->presentTo(new Response());

    expect($response->getHeader('Set-Cookie'))->toBe(['a=1; Path=/', 'b=2; HttpOnly']);
});

test('既存の Set-Cookie ヘッダーを残す', function () {
    $response = (new Response())->withHeader('Set-Cookie', 'existing=1');

    $response = (new GiftBox([new Cookie('a', '1')]))->presentTo($response);

    expect($response->getHeader('Set-Cookie'))->toBe(['existing=1', 'a=1']);
});

test('空の箱ならレスポンスを変更しない', function () {
    $response = new Response();

    expect((new GiftBox())->presentTo($response))->toBe($response);
});

test('同じ名前の Cookie は後から入れたものに置き換わる', function () {
    $second = new Cookie('a', '2');
    $box = new GiftBox(['first' => new Cookie('a', '1'), 'second' => $second]);

    expect($box)->toHaveCount(1)
        ->and($box['a'])->toBe($second);
});

test('名前で取得と削除ができる', function () {
    $box = new GiftBox([new Cookie('a', '1'), new Cookie('b', '2')]);

    expect(isset($box['a']))->toBeTrue()
        ->and($box['missing'])->toBeNull();

    unset($box['a']);

    expect(isset($box['a']))->toBeFalse()
        ->and(array_keys(iterator_to_array($box)))->toBe(['b']);
});

test('キーと Cookie 名が一致すれば代入できる', function () {
    $cookie = new Cookie('a', '1');
    $box = new GiftBox();
    $box['a'] = $cookie;

    expect($box['a'])->toBe($cookie);
});

test('キーと Cookie 名が異なる代入を拒否する', function () {
    $box = new GiftBox();

    expect(function () use ($box) {
        $box['a'] = new Cookie('b', '1');
    })->toThrow(InvalidArgumentException::class);
});

test('Cookie 以外の代入を拒否する', function () {
    $box = new GiftBox();

    expect(function () use ($box) {
        $box[] = 'a=1';
    })->toThrow(InvalidArgumentException::class);
});

test('コンストラクタに Cookie 以外を渡すと拒否する', function () {
    expect(fn () => new GiftBox(['a=1']))
        ->toThrow(InvalidArgumentException::class);
});
