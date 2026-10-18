<?php

declare(strict_types=1);

namespace Ixbox\Moonlight;

use InvalidArgumentException;
use Stringable;

/**
 * An immutable cookie that renders itself as a Set-Cookie header value.
 *
 * @phpstan-import-type CookieOptions from Options
 */
final class Cookie implements Stringable
{
    use FluentMethod;

    public const SAME_SITE_LAX = 'Lax';
    public const SAME_SITE_STRICT = 'Strict';
    public const SAME_SITE_NONE = 'None';

    public const SAME_SITE_VALUES = [
        self::SAME_SITE_LAX,
        self::SAME_SITE_STRICT,
        self::SAME_SITE_NONE,
    ];

    /** @var CookieOptions */
    private array $options;

    /**
     * @param array<string, mixed> $options See {@see Options} for the accepted keys.
     * @throws InvalidArgumentException
     */
    public function __construct(
        private string $name,
        private string $value = '',
        array $options = [],
    ) {
        Options::validateName($name);
        $this->options = Options::validate($options);
    }

    public function getName(): string
    {
        return $this->name;
    }

    public function getValue(): string
    {
        return $this->value;
    }

    public function withValue(string $value): static
    {
        $clone = clone $this;
        $clone->value = $value;
        return $clone;
    }

    /**
     * Returns a copy that tells the browser to delete this cookie.
     *
     * Path and Domain are kept, since the browser only deletes a cookie
     * whose name, path, and domain all match.
     */
    public function expire(): static
    {
        return $this->withValue('')->withExpires(1);
    }

    public function __toString(): string
    {
        $parts = [$this->name . '=' . rawurlencode($this->value)];

        $expires = $this->options['expires'] ?? 0;
        if ($expires > 0) {
            $parts[] = 'Expires=' . gmdate('D, d M Y H:i:s \G\M\T', $expires);
            $parts[] = 'Max-Age=' . max(0, $expires - time());
        }

        $path = $this->options['path'] ?? '';
        if ($path !== '') {
            $parts[] = 'Path=' . $path;
        }

        $domain = $this->options['domain'] ?? '';
        if ($domain !== '') {
            $parts[] = 'Domain=' . $domain;
        }

        if ($this->options['secure'] ?? false) {
            $parts[] = 'Secure';
        }

        if ($this->options['httponly'] ?? false) {
            $parts[] = 'HttpOnly';
        }

        $sameSite = $this->options['samesite'] ?? '';
        if ($sameSite !== '') {
            $parts[] = 'SameSite=' . $sameSite;
        }

        if ($this->options['partitioned'] ?? false) {
            $parts[] = 'Partitioned';
        }

        return implode('; ', $parts);
    }
}
