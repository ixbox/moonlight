<?php

declare(strict_types=1);

namespace Ixbox\Moonlight;

use InvalidArgumentException;

/**
 * Immutable "with*" setters for cookie attributes.
 *
 * @phpstan-import-type CookieOptions from Options
 */
trait FluentMethod
{
    /**
     * @return CookieOptions
     */
    public function getOptions(): array
    {
        return $this->options;
    }

    /**
     * @param int $expires Unix timestamp. 0 means a session cookie.
     * @throws InvalidArgumentException
     */
    public function withExpires(int $expires): static
    {
        return $this->withOption('expires', $expires);
    }

    /**
     * @throws InvalidArgumentException
     */
    public function withPath(string $path): static
    {
        return $this->withOption('path', $path);
    }

    /**
     * @throws InvalidArgumentException
     */
    public function withDomain(string $domain): static
    {
        return $this->withOption('domain', $domain);
    }

    public function withSecure(bool $secure = true): static
    {
        return $this->withOption('secure', $secure);
    }

    public function withHttpOnly(bool $httpOnly = true): static
    {
        return $this->withOption('httponly', $httpOnly);
    }

    /**
     * @param 'Lax'|'Strict'|'None' $sameSite
     * @throws InvalidArgumentException
     */
    public function withSameSite(string $sameSite): static
    {
        return $this->withOption('samesite', $sameSite);
    }

    public function withPartitioned(bool $partitioned = true): static
    {
        return $this->withOption('partitioned', $partitioned);
    }

    /**
     * @throws InvalidArgumentException
     */
    private function withOption(string $key, mixed $value): static
    {
        $options = $this->options;
        $options[$key] = $value;

        $clone = clone $this;
        $clone->options = Options::validate($options);
        return $clone;
    }
}
