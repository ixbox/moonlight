# Moonlight

English | [日本語](README.ja.md)

A small PHP library for adding `Set-Cookie` headers to PSR-7 responses.

PSR-7 has no type for cookies, so you have to build each `Set-Cookie` string yourself and add it line by line with `withAddedHeader()`. Moonlight lets you build cookies as objects and attach them all with a single `presentTo()` call.

The name comes from [Moonlight](https://www.morinaga.co.jp/products/detail.php?id=PRD2009-12-0005), a cookie made by the Japanese confectioner Morinaga. The class names follow the same pastry-shop theme:

| Class | Role |
|---|---|
| `Patisserie` | A cookie factory that holds shared attributes. `bake()` makes a cookie |
| `Cookie` | A single cookie. Casting it to string gives the `Set-Cookie` value |
| `GiftBox` | A box of cookies. `presentTo()` gives them to a response |

## Requirements

- PHP 8.2 or later
- `psr/http-message` 1.x or 2.x

## Installation

```sh
composer require ixbox/moonlight
```

## Usage

```php
use Ixbox\Moonlight\GiftBox;
use Ixbox\Moonlight\Patisserie;

// Open a shop with the attributes shared across the site
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
    'httponly' => false, // Override: JavaScript needs to read this one
]);

$response = $box->presentTo($response);
// Set-Cookie: session_id=...; Path=/; Secure; HttpOnly; SameSite=Lax
// Set-Cookie: theme=dark; Expires=...; Max-Age=2592000; Path=/; Secure; SameSite=Lax
```

You can also create a `Cookie` directly:

```php
use Ixbox\Moonlight\Cookie;

$cookie = (new Cookie('lang', 'en'))
    ->withPath('/')
    ->withExpires(time() + 3600)
    ->withSameSite(Cookie::SAME_SITE_STRICT);

echo $cookie; // lang=en; Expires=...; Max-Age=3600; Path=/; SameSite=Strict
```

`Cookie` and `Patisserie` are immutable. Each `with*()` method returns a modified copy and leaves the original unchanged.

### Deleting a cookie

`expire()` returns a copy with an empty value and an expiry date in the past. A browser deletes a cookie only when its name, Path, and Domain all match, so the safest way is to bake it from the same `Patisserie` you created it with and then call `expire()`:

```php
$box[] = $patisserie->bake('session_id')->expire();
// Set-Cookie: session_id=; Expires=Thu, 01 Jan 1970 00:00:01 GMT; Max-Age=0; Path=/; Secure; HttpOnly; SameSite=Lax
```

## Options

| Key | Type | `with*` method | Description |
|---|---|---|---|
| `expires` | `int` | `withExpires()` | Expiry as a Unix timestamp. `0` or omitted means a session cookie (same as `setcookie()`) |
| `path` | `string` | `withPath()` | `Path` attribute |
| `domain` | `string` | `withDomain()` | `Domain` attribute |
| `secure` | `bool` | `withSecure()` | `Secure` attribute |
| `httponly` | `bool` | `withHttpOnly()` | `HttpOnly` attribute |
| `samesite` | `'Lax'\|'Strict'\|'None'` | `withSameSite()` | `SameSite` attribute. Omitted from the output when not set (browsers then treat it as `Lax`) |
| `partitioned` | `bool` | `withPartitioned()` | `Partitioned` attribute (CHIPS) |

When `expires` is set, Moonlight also outputs `Max-Age`, the number of seconds remaining at the time the cookie is converted to a string.

Browsers reject `SameSite=None` and `Partitioned` without `Secure`. Set `secure` to `true` when you use them.

## Validation

Invalid values throw `InvalidArgumentException`. Validation does not use `assert()`, so it also runs in production.

- Unknown option keys, including misspellings such as `httpOnly`
- Values of the wrong type, a negative `expires`, or a `samesite` other than `Lax` / `Strict` / `None`
- A `path` or `domain` containing `;` or control characters such as line breaks (prevents header injection)
- A cookie name that is empty or contains `=`, `,`, `;`, whitespace, or control characters

Cookie values are encoded with `rawurlencode()` (a space becomes `%20`), so both PHP's `$_COOKIE` and JavaScript's `decodeURIComponent()` can decode them.

## GiftBox

`GiftBox` stores cookies by name. Putting in a cookie with the same name replaces the earlier one.

```php
$box = new GiftBox([$cookieA, $cookieB]);
$box->add('name', 'value', ['path' => '/']); // Create a cookie and put it in
$box[] = $cookie;                             // Put a cookie in, keyed by its name
$box['name'];                                 // Get a cookie (null if missing)
unset($box['name']);                          // Remove a cookie
count($box);
foreach ($box as $name => $cookie) { /* ... */ }
```

`presentTo()` adds one `Set-Cookie` line per cookie and keeps any existing `Set-Cookie` headers.

## Development

```sh
composer install
composer test     # Pest
composer analyse  # PHPStan (level max)
composer lint     # PHP_CodeSniffer (PSR-12)
composer check    # Run all three
```

## License

MIT
