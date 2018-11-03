<?php
declare(strict_types = 1);

namespace Tests\Innmind\ObjectGraph;

use Innmind\ObjectGraph\{
    Node,
    Relation,
    Relation\Property,
};
use PHPUnit\Framework\TestCase;

class NodeTest extends TestCase
{
    public function testInterface()
    {
        $node = new Node(new \stdClass);

        $this->assertNull($node->relate(new Relation(
            new Property('bar'),
            new Node(new \stdClass)
        )));
    }
}
