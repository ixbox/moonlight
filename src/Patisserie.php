<?php

declare(strict_types=1);

namespace Ixbox\Moonlight;

use InvalidArgumentException;

/**
 * A cookie factory that holds default attributes shared by the cookies it bakes.
 *
 * @phpstan-import-type CookieOptions from Options
 */
final class Patisserie
{
    use FluentMethod;

    /** @var CookieOptions */
    private array $options;

    /**
     * @param array<string, mixed> $options Default options. See {@see Options} for the accepted keys.
     * @throws InvalidArgumentException
     */
    public function __construct(array $options = [])
    {
        $this->options = Options::validate($options);
    }

    /**
     * Bakes a cookie. The given options override the defaults of this patisserie.
     *
     * @param array<string, mixed> $options
     * @throws InvalidArgumentException
     */
    public function bake(string $name, string $value = '', array $options = []): Cookie
    {
        return new Cookie($name, $value, array_merge($this->options, $options));
    }
}
