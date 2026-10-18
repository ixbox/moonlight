<?php

declare(strict_types=1);

namespace Ixbox\Moonlight;

use ArrayAccess;
use ArrayIterator;
use Countable;
use InvalidArgumentException;
use IteratorAggregate;
use Psr\Http\Message\ResponseInterface;
use Traversable;

/**
 * A mutable collection of cookies keyed by cookie name.
 *
 * @implements ArrayAccess<string, Cookie>
 * @implements IteratorAggregate<string, Cookie>
 */
final class GiftBox implements ArrayAccess, Countable, IteratorAggregate
{
    /** @var array<string, Cookie> */
    private array $cookies = [];

    /**
     * @param iterable<mixed, Cookie> $cookies
     * @throws InvalidArgumentException
     */
    public function __construct(iterable $cookies = [])
    {
        foreach ($cookies as $cookie) {
            $this->put(self::ensureCookie($cookie));
        }
    }

    /**
     * Puts a cookie into the box, replacing any cookie with the same name.
     */
    public function put(Cookie $cookie): void
    {
        $this->cookies[$cookie->getName()] = $cookie;
    }

    /**
     * Creates a cookie and puts it into the box.
     *
     * @param array<string, mixed> $options
     * @throws InvalidArgumentException
     */
    public function add(string $name, string $value = '', array $options = []): void
    {
        $this->put(new Cookie($name, $value, $options));
    }

    /**
     * Adds a Set-Cookie header line for each cookie, keeping existing Set-Cookie headers.
     *
     * @template T of ResponseInterface
     * @param T $response
     * @return T
     */
    public function presentTo(ResponseInterface $response): ResponseInterface
    {
        foreach ($this->cookies as $cookie) {
            /** @var T $response */
            $response = $response->withAddedHeader('Set-Cookie', (string) $cookie);
        }
        return $response;
    }

    /**
     * @return Traversable<string, Cookie>
     */
    public function getIterator(): Traversable
    {
        return new ArrayIterator($this->cookies);
    }

    public function count(): int
    {
        return count($this->cookies);
    }

    /**
     * @param string $offset
     */
    public function offsetExists(mixed $offset): bool
    {
        return isset($this->cookies[$offset]);
    }

    /**
     * @param string $offset
     */
    public function offsetGet(mixed $offset): ?Cookie
    {
        return $this->cookies[$offset] ?? null;
    }

    /**
     * Use `$box[] = $cookie` or `$box['name'] = $cookie`.
     * The offset, when given, must match the cookie name.
     *
     * @param string|null $offset
     * @param Cookie $value
     * @throws InvalidArgumentException
     */
    public function offsetSet(mixed $offset, mixed $value): void
    {
        $cookie = self::ensureCookie($value);
        if ($offset !== null && $offset !== $cookie->getName()) {
            throw new InvalidArgumentException(sprintf(
                'Offset "%s" does not match the cookie name "%s"',
                $offset,
                $cookie->getName(),
            ));
        }
        $this->put($cookie);
    }

    /**
     * @param string $offset
     */
    public function offsetUnset(mixed $offset): void
    {
        unset($this->cookies[$offset]);
    }

    /**
     * Guards against non-Cookie values, which PHP cannot reject by type for ArrayAccess or iterables.
     *
     * @throws InvalidArgumentException
     */
    private static function ensureCookie(mixed $value): Cookie
    {
        if (!$value instanceof Cookie) {
            throw new InvalidArgumentException(sprintf(
                'GiftBox accepts only %s, %s given',
                Cookie::class,
                get_debug_type($value),
            ));
        }
        return $value;
    }
}
