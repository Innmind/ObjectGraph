<?php
declare(strict_types = 1);

namespace Innmind\ObjectGraph\Relation;

use Innmind\ObjectGraph\Exception\DomainException;
use Innmind\Immutable\Str;

/**
 * @psalm-immutable
 */
final class Property
{
    private string $value;

    private function __construct(string $value)
    {
        if (Str::of($value)->empty()) {
            throw new DomainException;
        }

        $this->value = $value;
    }

    /**
     * @psalm-pure
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
