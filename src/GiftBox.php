<?php

declare(strict_types=1);

namespace Ixbox\Moonlight;

use ArrayAccess;
use ArrayIterator;
use Countable;
use IteratorAggregate;
use Psr\Http\Message\ResponseInterface;
use Traversable;

/**
 * @implements ArrayAccess<string,Cookie>
 * @implements IteratorAggregate<Cookie>
 */
class GiftBox implements ArrayAccess, Countable, IteratorAggregate
{
    /**
     * @param Cookie[] $cookies
     */
    public function __construct(
        private array $cookies = [],
    ) {
        assert(array_reduce(
            $cookies,
            fn (bool $carry, $cookie) => $carry && $cookie instanceof Cookie,
            true,
        ), 'All cookies must be instances of Cookie');
    }

    /**
     * @return Traversable<Cookie>
     */
    public function getIterator(): Traversable
    {
        return new ArrayIterator($this->cookies);
    }

    /**
     * @return int
     */
    public function count(): int
    {
        return count($this->cookies);
    }

    /**
     * @param string $offset
     * @return bool
     */
    public function offsetExists(mixed $offset): bool
    {
        return isset($this->cookies[$offset]);
    }

    /**
     * @param string $offset
     * @return Cookie|null
     */
    public function offsetGet(mixed $offset): ?Cookie
    {
        return $this->cookies[$offset] ?? null;
    }

    /**
     * @param string $offset
     * @param Cookie $value
     * @return void
     */
    public function offsetSet(mixed $offset, mixed $value): void
    {
        assert($value instanceof Cookie);
        $this->cookies[$value->getName()] = $value;
    }

    /**
     * @param string $offset
     * @return void
     */
    public function offsetUnset(mixed $offset): void
    {
        unset($this->cookies[$offset]);
    }

    /**
     * @param string $name
     * @param string $value
     * @param array{expires?:int,path?:string,domain?:string,secure?:bool,httponly?:bool,samesite?:string} $options
     * @return void
     */
    public function add(string $name, string $value = '', array $options = []): void
    {
        $cookie = new Cookie($name, $value, $options);
        $this->cookies[$name] = $cookie;
    }

    /**
     * @param ResponseInterface $response
     * @return ResponseInterface
     */
    public function presentTo(ResponseInterface $response): ResponseInterface
    {
        $lines = array_map(
            fn (Cookie $cookie) => (string) $cookie,
            $this->cookies,
        );

        return $response->withHeader('Set-Cookie', $lines);
    }
}
