<?php
declare(strict_types = 1);

namespace Innmind\ObjectGraph\Node;

use Innmind\ObjectGraph\NamespacePattern;
use Innmind\Immutable\Str;

/**
 * @psalm-immutable
 */
final class ClassName
{
    private string $value;

    private function __construct(object $object)
    {
        $this->value = \get_class($object);
    }

    /**
     * @psalm-pure
     */
    public static function of(object $object): self
    {
        return new self($object);
    }

    public function in(NamespacePattern $namespace): bool
    {
        $namespace = $namespace->toString();
        $self = Str::of($this->value);

        return $self->startsWith($namespace);
    }

    public function toString(): string
    {
        return $this->value;
    }
}
