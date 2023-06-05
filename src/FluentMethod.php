<?php

namespace Ixbox\Moonlight;

/**
 * @property array{expires?:int,path?:string,domain?:string,secure?:bool,httponly?:bool,samesite?:string} $options
 */
trait FluentMethod
{
    /**
     * @param int $expires
     * @return static
     */
    public function withExpires(int $expires): static
    {
        assert($expires >= 0, 'Expires must be greater than or equal to 0');
        $clone = clone $this;
        $clone->options['expires'] = $expires;
        return $clone;
    }

    /**
     * @param string $path
     * @return static
     */
    public function withPath(string $path): static
    {
        $clone = clone $this;
        $clone->options['path'] = $path;
        return $clone;
    }

    /**
     * @param string $domain
     * @return static
     */
    public function withDomain(string $domain): static
    {
        $clone = clone $this;
        $clone->options['domain'] = $domain;
        return $clone;
    }

    /**
     * @param bool $secure
     * @return static
     */
    public function withSecure(bool $secure): static
    {
        $clone = clone $this;
        $clone->options['secure'] = $secure;
        return $clone;
    }

    /**
     * @param bool $httpOnly
     * @return static
     */
    public function withHttpOnly(bool $httpOnly): static
    {
        $clone = clone $this;
        $clone->options['httponly'] = $httpOnly;
        return $clone;
    }

    /**
     * @param string $sameSite
     * @return static
     */
    public function withSameSite(string $sameSite): static
    {
        assert(in_array($sameSite, Cookie::SAME_SITE_VALUES, true));
        $clone = clone $this;
        $clone->options['samesite'] = $sameSite;
        return $clone;
    }
}