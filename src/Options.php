<?php

declare(strict_types=1);

namespace Ixbox\Moonlight;

use InvalidArgumentException;

/**
 * Validates cookie attribute options.
 *
 * @internal
 *
 * @phpstan-type CookieOptions array{
 *     expires?: int,
 *     path?: string,
 *     domain?: string,
 *     secure?: bool,
 *     httponly?: bool,
 *     samesite?: 'Lax'|'Strict'|'None',
 *     partitioned?: bool,
 * }
 */
final class Options
{
    private const KEYS = ['expires', 'path', 'domain', 'secure', 'httponly', 'samesite', 'partitioned'];

    private function __construct()
    {
    }

    /**
     * @param array<mixed> $options
     * @return CookieOptions
     * @throws InvalidArgumentException
     */
    public static function validate(array $options): array
    {
        $validated = [];
        foreach ($options as $key => $value) {
            $validated[$key] = match ($key) {
                'expires' => self::expires($value),
                'path', 'domain' => self::attributeValue($key, $value),
                'secure', 'httponly', 'partitioned' => self::boolean($key, $value),
                'samesite' => self::sameSite($value),
                default => throw new InvalidArgumentException(sprintf(
                    'Unknown cookie option "%s". Allowed options: %s',
                    $key,
                    implode(', ', self::KEYS),
                )),
            };
        }

        /** @var CookieOptions $validated */
        return $validated;
    }

    private static function expires(mixed $value): int
    {
        if (!is_int($value) || $value < 0) {
            throw new InvalidArgumentException('Option "expires" must be an integer greater than or equal to 0');
        }
        return $value;
    }

    /**
     * Rejects ";" and control characters, which would inject attributes or headers.
     */
    private static function attributeValue(string $key, mixed $value): string
    {
        if (!is_string($value) || preg_match('/[;\x00-\x1F\x7F]/', $value) === 1) {
            throw new InvalidArgumentException(sprintf(
                'Option "%s" must be a string without ";" or control characters',
                $key,
            ));
        }
        return $value;
    }

    private static function boolean(string $key, mixed $value): bool
    {
        if (!is_bool($value)) {
            throw new InvalidArgumentException(sprintf('Option "%s" must be a boolean', $key));
        }
        return $value;
    }

    private static function sameSite(mixed $value): string
    {
        if (!in_array($value, Cookie::SAME_SITE_VALUES, true)) {
            throw new InvalidArgumentException('Option "samesite" must be one of Lax, Strict, or None');
        }
        return $value;
    }

    /**
     * @throws InvalidArgumentException
     */
    public static function validateName(string $name): void
    {
        if ($name === '') {
            throw new InvalidArgumentException('Cookie name must not be empty');
        }
        if (preg_match('/[=,;\s\x00-\x1F\x7F]/', $name) === 1) {
            throw new InvalidArgumentException(
                'Cookie name must not contain "=", ",", ";", whitespace, or control characters',
            );
        }
    }
}
