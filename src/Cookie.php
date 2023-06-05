<?php

declare(strict_types=1);

namespace Ixbox\Moonlight;

use Stringable;

class Cookie implements Stringable
{
    use FluentMethod;

    const SAME_SITE_LAX = 'Lax';
    const SAME_SITE_STRICT = 'Strict';
    const SAME_SITE_NONE = 'None';

    const SAME_SITE_DEFAULT = self::SAME_SITE_LAX;

    const SAME_SITE_VALUES = [
        self::SAME_SITE_LAX,
        self::SAME_SITE_STRICT,
        self::SAME_SITE_NONE,
    ];

    /**
     * @param string $name
     * @param string $value
     * @param array{expires?:int,path?:string,domain?:string,secure?:bool,httponly?:bool,samesite?:string} $options
     */
    public function __construct(
        private string $name,
        private string $value,
        private array $options = [],
    ) {
        assert(is_int($this->options['expires'] ?? 0), 'Expires must be an integer');
        assert(($this->options['expires'] ?? 0) >= 0, 'Expires must be greater than or equal to 0');
        assert(is_string($this->options['path'] ?? ''), 'Path must be a string');
        assert(is_string($this->options['domain'] ?? ''), 'Domain must be a string');
        assert(is_bool($this->options['secure'] ?? false), 'Secure must be a boolean');
        assert(is_bool($this->options['httponly'] ?? false), 'HttpOnly must be a boolean');
        assert(in_array($this->options['samesite'] ?? self::SAME_SITE_DEFAULT, self::SAME_SITE_VALUES, true), 'SameSite must be one of Lax, Strict, or None');
    }

    /**
     * @return string
     */
    public function getName(): string
    {
        return $this->name;
    }

    /**
     * @return string
     */
    public function getValue(): string
    {
        return $this->value;
    }

    /**
     * @return array{expires?:int,path?:string,domain?:string,secure?:bool,httponly?:bool,samesite?:string}
     */
    public function getOptions(): array
    {
        return $this->options;
    }

    public function __toString(): string
    {
        $parts = [
            urlencode($this->name) . '=' . urlencode($this->value),
        ];

        $expires = $this->options['expires'] ?? -1;
        if ($expires >= 0) {
            $parts[] = 'Expires=' . gmdate(DATE_RFC7231, $expires);
            $maxAge = max(0, $expires - time());
            $parts[] = 'Max-Age=' . $maxAge;
        }

        $path = $this->options['path'] ?? '';
        if ($path !== '') {
            $parts[] = 'Path=' . $path;
        }

        $domain = $this->options['domain'] ?? '';
        if ($domain !== '') {
            $parts[] = 'Domain=' . $domain;
        }

        $secure = $this->options['secure'] ?? false;
        if ($secure) {
            $parts[] = 'Secure';
        }

        $httpOnly = $this->options['httponly'] ?? false;
        if ($httpOnly) {
            $parts[] = 'HttpOnly';
        }

        $sameSite = $this->options['samesite'] ?? '';
        if ($sameSite !== '') {
            $parts[] = 'SameSite=' . $sameSite;
        }

        return implode('; ', $parts);
    }
}
