<?php
declare(strict_types = 1);

namespace Innmind\ObjectGraph\Graph;

use Innmind\ObjectGraph\Node;
use Innmind\Immutable\Map;

final class Delegate implements Visit
{
    private array $visitors;

    public function __construct(Visit ...$visitors)
    {
        $this->visitors = $visitors;
    }

    public function __invoke(
        Map $nodes,
        object $object,
        Visit $visit
    ): Map {
        foreach ($this->visitors as $visit) {
            $nodes = $visit($nodes, $object, $this);
        }

        return $nodes;
    }
}
