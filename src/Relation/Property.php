<?php
declare(strict_types = 1);

namespace Innmind\ObjectGraph\Relation;

use Innmind\ObjectGraph\Exception\DomainException;
use Innmind\Immutable\Str;

final class Property
{
    private string $value;

    public function __construct(string $value)
    {
        if (Str::of($value)->empty()) {
            throw new DomainException;
        }

        $this->value = $value;
    }

    public function toString(): string
    {
        return $this->value;
    }
}
