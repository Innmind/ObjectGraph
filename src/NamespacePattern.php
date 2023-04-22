<?php
declare(strict_types = 1);

namespace Innmind\ObjectGraph;

use Innmind\ObjectGraph\Exception\DomainException;
use Innmind\Immutable\Str;

/**
 * @psalm-immutable
 */
final class NamespacePattern
{
    private string $value;

    private function __construct(string $value)
    {
        if (!Str::of($value)->matches('/^[a-zA-Z][a-zA-Z0-9]+(\\\\[a-zA-Z][a-zA-Z0-9]+)*$/')) {
            throw new DomainException($value);
        }

        $this->value = $value;
    }

    /**
     * @psalm-pure
     *
     * @param literal-string $value
     *
     * @throws DomainException
     */
    public static function of(string $value): self
    {
        return new self($value);
    }

    public function toString(): string
    {
        return $this->value;
    }
}
