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
use function Innmind\Immutable\{
    unwrap,
    first,
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
        [$a, $b] = unwrap($node->relations());
        $this->assertSame('a', $a->property()->toString());
        $this->assertSame(Foo::class, $a->node()->class()->toString());
        $this->assertSame('b', $b->property()->toString());
        $this->assertSame(Foo::class, $b->node()->class()->toString());
        $this->assertCount(1, $a->node()->relations());
        $this->assertCount(1, $b->node()->relations());
        $this->assertSame(
            first($a->node()->relations())->node(),
            first($b->node()->relations())->node(),
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
        $this->assertSame('foo', first($node->relations())->property()->toString());
        $this->assertSame(
            $node,
            first(first($node->relations())->node()->relations())->node(),
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
                $this->map = new \SplObjectStorage;
                $this->map->attach($a, $b);
                $this->map->attach($b, 'foo');
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
        [$a, $b] = unwrap($node->relations());
        $this->assertTrue($a->highlighted());
        $this->assertTrue($b->highlighted());
        $this->assertTrue($a->node()->highlighted());
        $this->assertTrue($b->node()->highlighted());
        $this->assertTrue(first($a->node()->relations())->highlighted());
        $this->assertTrue(first($b->node()->relations())->highlighted());
        $this->assertTrue(first($a->node()->relations())->node()->highlighted());
        $this->assertTrue(first($b->node()->relations())->node()->highlighted());
        [$a, $b] = unwrap($b->node()->relations());
        $this->assertFalse($b->highlighted());
        $this->assertFalse($b->node()->highlighted());
    }
}
