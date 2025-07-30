<?php
declare(strict_types = 1);

namespace Tests\Innmind\ObjectGraph;

use Innmind\ObjectGraph\Lookup;
use Fixtures\Innmind\ObjectGraph\Foo;
use Innmind\BlackBox\PHPUnit\Framework\TestCase;

class LookupTest extends TestCase
{
    public function testInvokation()
    {
        // root
        //  |-> a
        //  |    |-> leaf <-|
        //  |-> b ----------|
        $leaf = new Foo;
        $a = new Foo($leaf);
        $b = new Foo($leaf);
        $root = new Foo($a, $b);

        $nodes = Lookup::of()($root)->nodes();

        $this->assertCount(4, $nodes);
        $fromLeaf = $nodes
            ->find(static fn($node) => $node->comesFrom($leaf))
            ->match(
                static fn($node) => $node,
                static fn() => null,
            );
        $this->assertNotNull($fromLeaf);
        $this->assertTrue($fromLeaf->relations()->empty());
        $fromA = $nodes
            ->find(static fn($node) => $node->comesFrom($a))
            ->match(
                static fn($node) => $node,
                static fn() => null,
            );
        $this->assertNotNull($fromA);
        $this->assertCount(1, $fromA->relations());
        $this->assertTrue(
            $fromA
                ->relations()
                ->find(static fn($relation) => $relation->property()->toString() === 'a')
                ->match(
                    static fn($relation) => $relation->refersTo($leaf),
                    static fn() => null,
                ),
        );
        $fromB = $nodes
            ->find(static fn($node) => $node->comesFrom($b))
            ->match(
                static fn($node) => $node,
                static fn() => null,
            );
        $this->assertNotNull($fromB);
        $this->assertCount(1, $fromB->relations());
        $this->assertTrue(
            $fromB
                ->relations()
                ->find(static fn($relation) => $relation->property()->toString() === 'a')
                ->match(
                    static fn($relation) => $relation->refersTo($leaf),
                    static fn() => null,
                ),
        );
        $fromRoot = $nodes
            ->find(static fn($node) => $node->comesFrom($root))
            ->match(
                static fn($node) => $node,
                static fn() => null,
            );
        $this->assertNotNull($fromRoot);
        $this->assertCount(2, $fromRoot->relations());
        $this->assertTrue(
            $fromRoot
                ->relations()
                ->find(static fn($relation) => $relation->property()->toString() === 'a')
                ->match(
                    static fn($relation) => $relation->refersTo($a),
                    static fn() => null,
                ),
        );
        $this->assertTrue(
            $fromRoot
                ->relations()
                ->find(static fn($relation) => $relation->property()->toString() === 'b')
                ->match(
                    static fn($relation) => $relation->refersTo($b),
                    static fn() => null,
                ),
        );
    }

    public function testCyclicGraph()
    {
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

        $nodes = Lookup::of()($a)->nodes();

        $this->assertCount(2, $nodes);
        $fromA = $nodes
            ->find(static fn($node) => $node->comesFrom($a))
            ->match(
                static fn($node) => $node,
                static fn() => null,
            );
        $this->assertNotNull($fromA);
        $this->assertCount(1, $fromA->relations());
        $this->assertTrue(
            $fromA
                ->relations()
                ->find(static fn($relation) => $relation->property()->toString() === 'foo')
                ->match(
                    static fn($relation) => $relation->refersTo($b),
                    static fn() => null,
                ),
        );
        $fromB = $nodes
            ->find(static fn($node) => $node->comesFrom($b))
            ->match(
                static fn($node) => $node,
                static fn() => null,
            );
        $this->assertNotNull($fromB);
        $this->assertCount(1, $fromB->relations());
        $this->assertTrue(
            $fromB
                ->relations()
                ->find(static fn($relation) => $relation->property()->toString() === 'bar')
                ->match(
                    static fn($relation) => $relation->refersTo($a),
                    static fn() => null,
                ),
        );
    }

    public function testTraverseLists()
    {
        $root = new class {
            private array $list;

            public function __construct()
            {
                $this->list = [
                    'foo' => new \stdClass,
                    'bar' => new \stdClass,
                ];
            }
        };

        $graph = Lookup::of()($root);

        // root + list prop as an ArrayObject + the 2 stdClass
        $this->assertCount(4, $graph->nodes());
    }

    public function testTraverseSplObjectStorage()
    {
        $spl = new \SplObjectStorage;
        $spl[new \stdClass] = 42;
        $spl[new \stdClass] = new \stdClass;

        $graph = Lookup::of()($spl);

        // SplObjectStorage + 2 keys + 1 value
        $this->assertCount(4, $graph->nodes());
    }
}
