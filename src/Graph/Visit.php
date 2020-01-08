<?php
declare(strict_types = 1);

namespace Innmind\ObjectGraph\Graph;

use Innmind\ObjectGraph\Node;
use Innmind\Immutable\Map;

interface Visit
{
    /**
     * @var Map<object, Node> $nodes
     *
     * @return Map<object, Node>
     */
    public function __invoke(
        Map $nodes,
        object $object,
        self $visit
    ): Map;
}
