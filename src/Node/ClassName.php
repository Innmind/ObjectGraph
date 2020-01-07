<?php
declare(strict_types = 1);

namespace Innmind\ObjectGraph\Node;

use Innmind\ObjectGraph\NamespacePattern;
use Innmind\Immutable\Str;

final class ClassName
{
    private string $value;

    public function __construct(object $object)
    {
        $this->value = \get_class($object);
    }

    public function in(NamespacePattern $namespace): bool
    {
        $namespace = (string) $namespace;
        $self = Str::of($this->value);

        return $self->contains($namespace) && $self->position($namespace) === 0;
    }

    public function __toString(): string
    {
        return $this->value;
    }
}
