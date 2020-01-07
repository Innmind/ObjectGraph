<?php
declare(strict_types = 1);

namespace Tests\Innmind\ObjectGraph\Visitor;

use Innmind\ObjectGraph\{
    Visitor\AccessObjectNode,
    Graph,
    Exception\ObjectNotFound,
};
use function Innmind\Immutable\first;
use Fixtures\Innmind\ObjectGraph\Foo;
use PHPUnit\Framework\TestCase;

class AccessObjectNodeTest extends TestCase
{
    public function testAccessNode()
    {
        // root
        //  |-> a
        //  |    |-> leaf <-|
        //  |-> b ----------|
        $graph = new Graph;
        $leaf = new Foo;
        $a = new Foo($leaf);
        $b = new Foo($leaf);
        $root = new Foo($a, $b);

        $node = $graph($root);

        $target = (new AccessObjectNode($leaf))($node);

        $this->assertSame(
            first(first($node->relations())->node()->relations())->node(), // Foo -> a -> Foo -> a -> Foo
            $target
        );
    }

    public function testThrowWhenObjectNotInGraph()
    {
        // root
        //  |-> a
        //  |    |-> leaf <-|
        //  |-> b ----------|
        $graph = new Graph;
        $leaf = new Foo;
        $a = new Foo($leaf);
        $b = new Foo($leaf);
        $root = new Foo($a, $b);

        $node = $graph($root);

        $this->expectException(ObjectNotFound::class);

        (new AccessObjectNode(new \stdClass))($node);
    }

    public function testAccessNodeWhenFoundOnFirstRelationship()
    {
        // object
        //  |-> dependency
        //  |    |-> subDependency <-|
        //  |-> alt -----------------|
        $object = new class {
            public $a;
            public $b;
        };
        $dependency = new class {
            public $should;
        };
        $alt = new class {
            public $shouldNot;
        };
        $subDependency = new class {
        };
        $object->a = $dependency;
        $object->b = $alt;
        $dependency->should = $subDependency;
        $alt->shouldNot = $subDependency;

        $node = (new AccessObjectNode($dependency))(
            (new Graph)($object)
        );

        $this->assertTrue($node->comesFrom($dependency));
    }
}
