<?php
declare(strict_types = 1);

namespace Tests\Innmind\ObjectGraph;

use Innmind\ObjectGraph\{
    Graph,
    Node,
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
        $graph = new Graph;
        $leaf = new Foo;
        $a = new Foo($leaf);
        $b = new Foo($leaf);
        $root = new Foo($a, $b);

        $node = $graph($root);

        $this->assertInstanceOf(Node::class, $node);
        $this->assertCount(2, $node->relations());
        [$a, $b] = $node->relations()->toPrimitive();
        $this->assertSame('a', (string) $a->property());
        $this->assertSame(Foo::class, (string) $a->node()->class());
        $this->assertSame('b', (string) $b->property());
        $this->assertSame(Foo::class, (string) $b->node()->class());
        $this->assertCount(1, $a->node()->relations());
        $this->assertCount(1, $b->node()->relations());
        $this->assertSame(
            $a->node()->relations()->current()->node(),
            $b->node()->relations()->current()->node()
        );
    }

    public function testCyclicGraph()
    {
        $graph = new Graph;

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
        $this->assertSame('foo', (string) $node->relations()->current()->property());
        $this->assertSame(
            $node,
            $node->relations()->current()->node()->relations()->current()->node()
        );
    }

    public function testDiscoverObjectsInIterables()
    {
        $graph = new Graph;

        $a = new class {
            public $map;
            public $set;
            public $ints = [42];

            public function __construct()
            {
                $a = new \stdClass;
                $b = new \stdClass;
                $this->map = Map::of('object', 'mixed')
                    ($a, $b)
                    ($b, 'foo');
                $this->set = Set::of('object', $b);
            }
        };

        $node = $graph($a);

        $nodeRelations = $node->relations();
        $this->assertCount(2, $nodeRelations);
        $this->assertSame('map', (string) $nodeRelations->current()->property());
        $map = $nodeRelations->current()->node();
        $mapRelations = $map->relations();
        $this->assertCount(2, $mapRelations);
        $this->assertSame('0', (string) $mapRelations->current()->property());
        $pair1 = $mapRelations->current()->node();
        $pair1Relations = $pair1->relations();
        $this->assertCount(2, $pair1Relations);
        $this->assertSame('key', (string) $pair1Relations->current()->property());
        $innerA = $pair1Relations->current()->node();
        $pair1Relations->next();
        $this->assertSame('value', (string) $pair1Relations->current()->property());
        $innerB = $pair1Relations->current()->node();
        $mapRelations->next();
        $this->assertSame('1', (string) $mapRelations->current()->property());
        $pair2 = $mapRelations->current()->node();
        $pair2Relations = $pair2->relations();
        $this->assertCount(1, $pair2Relations);
        $this->assertSame('key', (string) $pair2Relations->current()->property());
        $this->assertSame($innerB, $pair2Relations->current()->node());
        $nodeRelations->next();
        $this->assertSame('set', (string) $nodeRelations->current()->property());
        $set = $nodeRelations->current()->node();
        $this->assertSame('0', (string) $set->relations()->current()->property());
        $pair = $set->relations()->current()->node();
        $this->assertCount(1, $pair->relations());
        $this->assertSame('value', (string) $pair->relations()->current()->property());
        $this->assertSame($innerB, $pair->relations()->current()->node());
    }

    public function testHighlightPathToLeaf()
    {
        $graph = new Graph;
        $leaf = new Foo;
        $a = new Foo($leaf);
        $b = new Foo($leaf, new Foo);
        $root = new Foo($a, $b);

        $node = $graph($root);
        $node->highlightPathTo($leaf);

        $this->assertInstanceOf(Node::class, $node);
        [$a, $b] = $node->relations()->toPrimitive();
        $this->assertTrue($a->highlighted());
        $this->assertTrue($b->highlighted());
        $this->assertTrue($a->node()->highlighted());
        $this->assertTrue($b->node()->highlighted());
        $this->assertTrue($a->node()->relations()->current()->highlighted());
        $this->assertTrue($b->node()->relations()->current()->highlighted());
        $this->assertTrue($a->node()->relations()->current()->node()->highlighted());
        $this->assertTrue($b->node()->relations()->current()->node()->highlighted());
        [$a, $b] = $b->node()->relations()->toPrimitive();
        $this->assertFalse($b->highlighted());
        $this->assertFalse($b->node()->highlighted());
    }
}
