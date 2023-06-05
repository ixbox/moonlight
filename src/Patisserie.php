<?php

namespace Ixbox\Moonlight;

class Patisserie
{
    use FluentMethod;

    /**
     * @param array{expires?:int,path?:string,domain?:string,secure?:bool,httponly?:bool,samesite?:string} $options
     */
    public function __construct(
        private array $options = [],
    ) {
        assert(is_int($this->options['expires'] ?? 0), 'Expires must be an integer');
        assert(($this->options['expires'] ?? 0) >= 0, 'Expires must be greater than or equal to 0');
        assert(is_string($this->options['path'] ?? ''), 'Path must be a string');
        assert(is_string($this->options['domain'] ?? ''), 'Domain must be a string');
        assert(is_bool($this->options['secure'] ?? false), 'Secure must be a boolean');
        assert(is_bool($this->options['httponly'] ?? false), 'HttpOnly must be a boolean');
        assert(in_array($this->options['samesite'] ?? Cookie::SAME_SITE_DEFAULT, Cookie::SAME_SITE_VALUES, true), 'SameSite must be one of Lax, Strict, or None');
    }

    /**
     * @param string $name
     * @param string $value
     * @param array{expires?:int,path?:string,domain?:string,secure?:bool,httponly?:bool,samesite?:string} $options
     * @return Cookie
     */
    public function bake(string $name, string $value, array $options = []): Cookie
    {
        $options = array_merge($this->options, $options);

        return new Cookie(
            $name,
            $value,
            $options,
        );
    }
}
