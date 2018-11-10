<?php
declare(strict_types = 1);

namespace Innmind\ObjectGraph;

use Innmind\ObjectGraph\Exception\DomainException;
use Innmind\Immutable\Str;

final class NamespacePattern
{
    private $value;

    public function __construct(string $value)
    {
        if (!Str::of($value)->matches('/^[a-zA-Z][a-zA-Z0-9]+(\\\\[a-zA-Z][a-zA-Z0-9]+)*$/')) {
            throw new DomainException($value);
        }

        $this->value = $value;
    }

    public function __toString(): string
    {
        return $this->value;
    }
}
