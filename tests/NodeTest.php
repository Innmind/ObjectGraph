<?php
declare(strict_types = 1);

namespace Tests\Innmind\ObjectGraph;

use Innmind\ObjectGraph\{
    Node,
    Node\ClassName,
    Relation,
    Relation\Property,
};
use PHPUnit\Framework\TestCase;

class NodeTest extends TestCase
{
    public function testInterface()
    {
        $node = new Node(new ClassName('Foo'));

        $this->assertNull($node->relate(new Relation(
            new Property('bar'),
            new Node(new ClassName('Bar'))
        )));
    }
}
