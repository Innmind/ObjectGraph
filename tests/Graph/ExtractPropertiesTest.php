<?php
declare(strict_types = 1);

namespace Tests\Innmind\ObjectGraph\Graph;

use Innmind\ObjectGraph\{
    Graph\ExtractProperties,
    Graph\Visit,
    Graph\ParseIterable,
    Graph\Delegate,
    Node,
};
use Innmind\Immutable\Map;
use function Innmind\Immutable\unwrap;
use PHPUnit\Framework\TestCase;

class ExtractPropertiesTest extends TestCase
{
    public function testInterface()
    {
        $this->assertInstanceOf(Visit::class, new ExtractProperties);
    }

    public function testDoesntExtractIfAlreayDone()
    {
        $object = new class {
            public $prop;

            public function __construct()
            {
                $this->prop = new \stdClass;
            }
        };
        $extract = new ExtractProperties;
        $nodes = Map::of('object', Node::class)
            ($object, $node = new Node($object));

        $this->assertTrue($node->relations()->empty());
        $this->assertSame($nodes, $extract($nodes, $object, $extract));
        $this->assertTrue($node->relations()->empty());
    }

    public function testExtract()
    {
        $object = new class {
            public $prop;
            public $int = 42;
            public $set;

            public function __construct()
            {
                $this->prop = new \stdClass;
                $this->set = [new \stdClass];
            }
        };
        $extract = new ExtractProperties;
        $nodes = Map::of('object', Node::class);

        $newNodes = $extract(
            $nodes,
            $object,
            new Delegate(
                new ParseIterable,
                $extract,
            ),
        );

        $this->assertNotSame($nodes, $newNodes);
        $relations = unwrap($newNodes->get($object)->relations());
        $this->assertCount(2, $relations);
        $this->assertSame('prop', $relations[0]->property()->toString());
        $this->assertSame('set', $relations[1]->property()->toString());
        $this->assertCount(1, $relations[1]->node()->relations());
    }

    public function testDoesntExtractArraysWhenCantVisitThem()
    {
        $object = new class {
            public $prop;
            public $int = 42;
            public $set;

            public function __construct()
            {
                $this->prop = new \stdClass;
                $this->set = [new \stdClass];
            }
        };
        $extract = new ExtractProperties;
        $nodes = Map::of('object', Node::class);

        $newNodes = $extract(
            $nodes,
            $object,
            $extract,
        );

        $this->assertNotSame($nodes, $newNodes);
        $relations = unwrap($newNodes->get($object)->relations());
        $this->assertCount(1, $relations);
        $this->assertSame('prop', $relations[0]->property()->toString());
    }
}
