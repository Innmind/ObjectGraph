<?php
declare(strict_types = 1);

namespace Tests\Innmind\ObjectGraph;

use Innmind\ObjectGraph\{
    Graph,
    Node,
    Visitor\AccessObjectNode,
};
use Fixtures\Innmind\ObjectGraph\Foo;
use Innmind\Immutable\{
    Map,
    Set,
};
use PHPUnit\Framework\TestCase;

class GraphTest extends TestCase
{
    public function testInvokation()
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

        $this->assertInstanceOf(Node::class, $node);
        $this->assertCount(2, $node->relations());
        [$a, $b] = $node->relations()->toList();
        $this->assertSame('a', $a->property()->toString());
        $this->assertSame(Foo::class, $a->node()->class()->toString());
        $this->assertSame('b', $b->property()->toString());
        $this->assertSame(Foo::class, $b->node()->class()->toString());
        $this->assertCount(1, $a->node()->relations());
        $this->assertCount(1, $b->node()->relations());
        $this->assertSame(
            $a
                ->node()
                ->relations()
                ->find(static fn() => true)
                ->match(
                    static fn($relation) => $relation->node(),
                    static fn() => null,
                ),
            $b
                ->node()
                ->relations()
                ->find(static fn() => true)
                ->match(
                    static fn($relation) => $relation->node(),
                    static fn() => null,
                ),
        );
    }

    public function testCyclicGraph()
    {
        $graph = new Graph;

        // a <-----|
        //  |-> b -|
        $a = new class {
            public $foo;
        };
        $b = new class {
            public $bar;
        };
        $a->foo = $b;
        $b->bar = $a;

        $node = $graph($a);

        $this->assertCount(1, $node->relations());
        $this->assertSame(
            'foo',
            $node->relations()->find(static fn() => true)->match(
                static fn($relation) => $relation->property()->toString(),
                static fn() => null,
            ),
        );
        $this->assertSame(
            $node,
            $node
                ->relations()
                ->find(static fn() => true)
                ->map(static fn($relation) => $relation->node()->relations())
                ->flatMap(static fn($relations) => $relations->find(static fn() => true))
                ->match(
                    static fn($relation) => $relation->node(),
                    static fn() => null,
                ),
        );
    }

    public function testDiscoverObjectsInIterables()
    {
        $graph = new Graph;

        $innerA = new \stdClass;
        $innerB = new \stdClass;
        $a = new class($innerA, $innerB) {
            public $map;
            public $set;
            public $ints = [42];

            public function __construct($a, $b)
            {
                $this->map = Map::of(
                    [$a, $b],
                    [$b, 'foo'],
                );
                $this->set = Set::objects($b);
            }
        };

        $node = $graph($a);

        $this->assertInstanceOf(Node::class, (new AccessObjectNode($innerA))($node));
        $this->assertInstanceOf(Node::class, (new AccessObjectNode($innerB))($node));

        $nodeRelations = $node->relations();
        $this->assertCount(2, $nodeRelations);
    }

    public function testHighlightPathToLeaf()
    {
        // root
        //  |-> a
        //  |    |-> leaf <-|
        //  |-> b ----------|
        //       |-> new Foo
        $graph = new Graph;
        $leaf = new Foo;
        $a = new Foo($leaf);
        $b = new Foo($leaf, new Foo);
        $root = new Foo($a, $b);

        $node = $graph($root);
        $node->highlightPathTo($leaf);

        $this->assertInstanceOf(Node::class, $node);
        [$a, $b] = $node->relations()->toList();
        $this->assertTrue($a->highlighted());
        $this->assertTrue($b->highlighted());
        $this->assertTrue($a->node()->highlighted());
        $this->assertTrue($b->node()->highlighted());
        $this->assertTrue(
            $a
                ->node()
                ->relations()
                ->find(static fn() => true)
                ->match(
                    static fn($relation) => $relation->highlighted(),
                    static fn() => null,
                ),
        );
        $this->assertTrue(
            $b
                ->node()
                ->relations()
                ->find(static fn() => true)
                ->match(
                    static fn($relation) => $relation->highlighted(),
                    static fn() => null,
                ),
        );
        $this->assertTrue(
            $a
                ->node()
                ->relations()
                ->find(static fn() => true)
                ->match(
                    static fn($relation) => $relation->node()->highlighted(),
                    static fn() => null,
                ),
        );
        $this->assertTrue(
            $b
                ->node()
                ->relations()
                ->find(static fn() => true)
                ->match(
                    static fn($relation) => $relation->node()->highlighted(),
                    static fn() => null,
                ),
        );
        [$a, $b] = $b->node()->relations()->toList();
        $this->assertFalse($b->highlighted());
        $this->assertFalse($b->node()->highlighted());
    }
}
