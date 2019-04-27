<?php
declare(strict_types = 1);

namespace Tests\Innmind\ObjectGraph\Visitor;

use Innmind\ObjectGraph\{
    Visitor\AccessObjectNode,
    Graph,
    Exception\ObjectNotFound,
};
use Fixtures\Innmind\ObjectGraph\Foo;
use PHPUnit\Framework\TestCase;

class AccessObjectNodeTest extends TestCase
{
    public function testAccessNode()
    {
        $graph = new Graph;
        $leaf = new Foo;
        $a = new Foo($leaf);
        $b = new Foo($leaf);
        $root = new Foo($a, $b);

        $node = $graph($root);

        $target = (new AccessObjectNode($leaf))($node);

        $this->assertSame(
            $node->relations()->current()->node()->relations()->current()->node(), // Foo -> a -> Foo -> a -> Foo
            $target
        );
    }

    public function testThrowWhenObjectNotInGraph()
    {
        $graph = new Graph;
        $leaf = new Foo;
        $a = new Foo($leaf);
        $b = new Foo($leaf);
        $root = new Foo($a, $b);

        $node = $graph($root);

        $this->expectException(ObjectNotFound::class);

        (new AccessObjectNode(new \stdClass))($node);
    }
}
