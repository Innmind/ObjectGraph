<?php
declare(strict_types = 1);

namespace Innmind\ObjectGraph\Node;

use Innmind\ObjectGraph\Exception\DomainException;
use Innmind\Immutable\Str;

final class ClassName
{
    private $value;

    public function __construct(string $value)
    {
        if (Str::of($value)->empty()) {
            throw new DomainException;
        }

        $this->value = $value;
    }

    public function __toString(): string
    {
        return $this->value;
    }
}
