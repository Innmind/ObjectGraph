<?php
declare(strict_types = 1);

namespace Innmind\ObjectGraph\Graph;

use Innmind\ObjectGraph\Node;
use Innmind\Immutable\Map;

final class Delegate implements Visit
{
    /** @var list<Visit> */
    private array $visitors;

    public function __construct(Visit ...$visitors)
    {
        $this->visitors = $visitors;
    }

    /**
     * @var Map<object, Node> $nodes
     *
     * @return Map<object, Node>
     */
    public function __invoke(
        Map $nodes,
        object $object,
        Visit $visit
    ): Map {
        foreach ($this->visitors as $visit) {
            $nodes = $visit($nodes, $object, $this);
        }

        /** @var Map<object, Node> */
        return $nodes;
    }
}
