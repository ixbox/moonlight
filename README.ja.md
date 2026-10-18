# Moonlight

[English](README.md) | 日本語

PSR-7 のレスポンスに `Set-Cookie` ヘッダーを付けるための小さな PHP ライブラリです。

PSR-7 には Cookie を表す型がないため、`Set-Cookie` の文字列を自分で組み立てて `withAddedHeader()` で1行ずつ足す必要があります。Moonlight はこの手間を、Cookie をオブジェクトとして組み立てて `presentTo()` を呼ぶだけにします。

名前は森永製菓のクッキー[ムーンライト](https://www.morinaga.co.jp/products/detail.php?id=PRD2009-12-0005)にあやかっています。クラス名もお菓子屋さんに揃えています。

| クラス | 役割 |
|---|---|
| `Patisserie` | 共通の属性を持つ Cookie の工場。`bake()` で Cookie を焼く |
| `Cookie` | 焼き上がった1枚。`(string)` にすると `Set-Cookie` の値になる |
| `GiftBox` | Cookie の詰め合わせ。`presentTo()` でレスポンスに贈る |

## 動作要件

- PHP 8.2 以上
- `psr/http-message` 1.x または 2.x

## インストール

```sh
composer require ixbox/moonlight
```

## 使い方

```php
use Ixbox\Moonlight\GiftBox;
use Ixbox\Moonlight\Patisserie;

// サイト共通の属性を持つお店を用意する
$patisserie = new Patisserie([
    'path' => '/',
    'secure' => true,
    'httponly' => true,
    'samesite' => 'Lax',
]);

$box = new GiftBox();
$box[] = $patisserie->bake('session_id', $sessionId);
$box[] = $patisserie->bake('theme', 'dark', [
    'expires' => time() + 60 * 60 * 24 * 30,
    'httponly' => false, // JavaScript から読みたいので上書き
]);

$response = $box->presentTo($response);
// Set-Cookie: session_id=...; Path=/; Secure; HttpOnly; SameSite=Lax
// Set-Cookie: theme=dark; Expires=...; Max-Age=2592000; Path=/; Secure; SameSite=Lax
```

`Cookie` を直接作ることもできます。

```php
use Ixbox\Moonlight\Cookie;

$cookie = (new Cookie('lang', 'ja'))
    ->withPath('/')
    ->withExpires(time() + 3600)
    ->withSameSite(Cookie::SAME_SITE_STRICT);

echo $cookie; // lang=ja; Expires=...; Max-Age=3600; Path=/; SameSite=Strict
```

`Cookie` と `Patisserie` はイミュータブルです。`with*()` は変更したコピーを返し、元のオブジェクトは変わりません。

### Cookie を削除する

`expire()` は値を空にし、有効期限を過去に設定したコピーを返します。ブラウザは名前・Path・Domain がすべて一致する Cookie だけを削除するので、作ったときと同じ `Patisserie` から焼いて `expire()` すると確実です。

```php
$box[] = $patisserie->bake('session_id')->expire();
// Set-Cookie: session_id=; Expires=Thu, 01 Jan 1970 00:00:01 GMT; Max-Age=0; Path=/; Secure; HttpOnly; SameSite=Lax
```

## オプション

| キー | 型 | `with*` メソッド | 説明 |
|---|---|---|---|
| `expires` | `int` | `withExpires()` | 有効期限の Unix タイムスタンプ。`0` または未指定ならセッション Cookie（`setcookie()` と同じ） |
| `path` | `string` | `withPath()` | `Path` 属性 |
| `domain` | `string` | `withDomain()` | `Domain` 属性 |
| `secure` | `bool` | `withSecure()` | `Secure` 属性 |
| `httponly` | `bool` | `withHttpOnly()` | `HttpOnly` 属性 |
| `samesite` | `'Lax'\|'Strict'\|'None'` | `withSameSite()` | `SameSite` 属性。未指定なら出力しない（ブラウザは `Lax` として扱う） |
| `partitioned` | `bool` | `withPartitioned()` | `Partitioned` 属性（CHIPS） |

`expires` を指定すると、`Expires` と一緒に、文字列化した時点での残り秒数を `Max-Age` として出力します。

ブラウザは `SameSite=None` と `Partitioned` を `Secure` なしでは受け付けません。これらを使うときは `secure` も `true` にしてください。

## 値の検証

不正な値は `InvalidArgumentException` になります。`assert()` ではないので、本番環境でも検証されます。

- 未知のオプションキー（`httpOnly` のような綴り違いも含む）
- 型が違う値、負の `expires`、`Lax` / `Strict` / `None` 以外の `samesite`
- `;` や改行などの制御文字を含む `path` / `domain`（ヘッダーインジェクション対策）
- 空、または `=` `,` `;` 空白・制御文字を含む Cookie 名

Cookie の値は `rawurlencode()` でエンコードします（スペースは `%20`）。PHP の `$_COOKIE` でも JavaScript の `decodeURIComponent()` でも元に戻せます。

## GiftBox

`GiftBox` は Cookie を名前で管理します。同じ名前の Cookie を入れると後から入れたものに置き換わります。

```php
$box = new GiftBox([$cookieA, $cookieB]);
$box->add('name', 'value', ['path' => '/']); // その場で Cookie を作って入れる
$box[] = $cookie;                             // 名前をキーにして入れる
$box['name'];                                 // 取り出す（なければ null）
unset($box['name']);                          // 取り除く
count($box);
foreach ($box as $name => $cookie) { /* ... */ }
```

`presentTo()` は既存の `Set-Cookie` ヘッダーを残したまま、Cookie ごとに1行ずつ追加します。

## 開発

```sh
composer install
composer test     # Pest
composer analyse  # PHPStan (level max)
composer lint     # PHP_CodeSniffer (PSR-12)
composer check    # 上の3つをまとめて実行
```

## ライセンス

MIT
