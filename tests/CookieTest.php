<?php

declare(strict_types=1);

use Ixbox\Moonlight\Cookie;

test('全属性を Set-Cookie の値として出力する', function () {
    $cookie = new Cookie('Name', 'Value', [
        'expires' => 1,
        'path' => '/path',
        'domain' => 'example.com',
        'secure' => true,
        'httponly' => true,
        'samesite' => 'Strict',
        'partitioned' => true,
    ]);

    expect((string) $cookie)->toBe(
        'Name=Value; Expires=Thu, 01 Jan 1970 00:00:01 GMT; Max-Age=0; Path=/path; Domain=example.com;'
        . ' Secure; HttpOnly; SameSite=Strict; Partitioned',
    );
});

test('属性がなければ名前と値だけを出力する', function () {
    expect((string) new Cookie('Name', 'Value'))->toBe('Name=Value');
});

test('expires が 0 ならセッション Cookie になる', function () {
    expect((string) new Cookie('Name', 'Value', ['expires' => 0]))->toBe('Name=Value');
});

test('未来の expires には残り秒数の Max-Age が付く', function () {
    $expires = time() + 3600;

    expect((string) new Cookie('Name', 'Value', ['expires' => $expires]))
        ->toContain('Expires=' . gmdate('D, d M Y H:i:s \G\M\T', $expires))
        ->toMatch('/; Max-Age=(3599|3600)(;|$)/');
});

test('値をパーセントエンコードする', function () {
    expect((string) new Cookie('Name', 'a b;c=あ'))->toBe('Name=a%20b%3Bc%3D%E3%81%82');
});

test('with メソッドは元のインスタンスを変更しない', function () {
    $original = new Cookie('Name', 'Value');
    $changed = $original
        ->withValue('Other')
        ->withExpires(1)
        ->withPath('/')
        ->withDomain('example.com')
        ->withSecure()
        ->withHttpOnly()
        ->withSameSite(Cookie::SAME_SITE_NONE)
        ->withPartitioned();

    expect((string) $original)->toBe('Name=Value')
        ->and($changed->getValue())->toBe('Other')
        ->and($changed->getOptions())->toBe([
            'expires' => 1,
            'path' => '/',
            'domain' => 'example.com',
            'secure' => true,
            'httponly' => true,
            'samesite' => 'None',
            'partitioned' => true,
        ]);
});

test('false にした属性は出力しない', function () {
    $cookie = (new Cookie('Name', 'Value', ['secure' => true, 'httponly' => true]))
        ->withSecure(false)
        ->withHttpOnly(false);

    expect((string) $cookie)->toBe('Name=Value');
});

test('expire は値を空にして過去の日時を設定し Path と Domain を保つ', function () {
    $cookie = (new Cookie('Name', 'Value', ['path' => '/app', 'domain' => 'example.com']))->expire();

    expect((string) $cookie)
        ->toBe('Name=; Expires=Thu, 01 Jan 1970 00:00:01 GMT; Max-Age=0; Path=/app; Domain=example.com');
});

test('不正な名前を拒否する', function (string $name) {
    expect(fn () => new Cookie($name, 'Value'))
        ->toThrow(InvalidArgumentException::class);
})->with([
    '空文字' => '',
    'イコール' => 'a=b',
    'セミコロン' => 'a;b',
    'カンマ' => 'a,b',
    'スペース' => 'a b',
    '改行' => "a\r\nb",
]);

test('不正なオプションを拒否する', function (array $options) {
    expect(fn () => new Cookie('Name', 'Value', $options))
        ->toThrow(InvalidArgumentException::class);
})->with([
    '未知のキー' => [['httpOnly' => true]],
    '負の expires' => [['expires' => -1]],
    '文字列の expires' => [['expires' => '1']],
    'path にセミコロン' => [['path' => '/; Domain=evil.example']],
    'path に改行' => [['path' => "/\r\nX-Evil: 1"]],
    'domain に改行' => [['domain' => "example.com\nX-Evil: 1"]],
    '文字列の secure' => [['secure' => 'yes']],
    '小文字の samesite' => [['samesite' => 'lax']],
    '未知の samesite' => [['samesite' => 'Loose']],
]);

test('with メソッドでも検証する', function () {
    expect(fn () => (new Cookie('Name', 'Value'))->withPath("/\r\nX-Evil: 1"))
        ->toThrow(InvalidArgumentException::class);
});
