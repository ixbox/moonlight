<?php

declare(strict_types=1);

namespace Ixbox\Moonlight;

use ArrayAccess;
use ArrayIterator;
use Countable;
use IteratorAggregate;
use Psr\Http\Message\ResponseInterface;
use Traversable;

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

    public function getIterator(): Traversable
    {
        return new ArrayIterator($this->cookies);
    }

    public function count(): int
    {
        return count($this->cookies);
    }

    public function offsetExists(mixed $offset): bool
    {
        return isset($this->cookies[$offset]);
    }

    public function offsetGet(mixed $offset): mixed
    {
        return $this->cookies[$offset] ?? null;
    }

    public function offsetSet(mixed $offset, mixed $value): void
    {
        assert($value instanceof Cookie);
        $this->cookies[$value->getName()] = $value;
    }

    public function offsetUnset(mixed $offset): void
    {
        unset($this->cookies[$offset]);
    }

    public function add($name, $value, $options = []): void
    {
        $cookie = new Cookie($name, $value, $options);
        $this->cookies[$name] = $cookie;
    }

    public function presentTo(ResponseInterface $response): ResponseInterface
    {
        $lines = array_map(
            fn (Cookie $cookie) => (string) $cookie,
            $this->cookies,
        );

        return $response->withHeader('Set-Cookie', $lines);
    }
}
