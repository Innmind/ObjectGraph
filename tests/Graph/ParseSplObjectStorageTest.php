<?php
declare(strict_types = 1);

namespace Tests\Innmind\ObjectGraph\Graph;

use Innmind\ObjectGraph\{
    Graph\ParseSplObjectStorage,
    Graph\Visit,
    Graph\ExtractProperties,
    Node,
};
use Innmind\Immutable\{
    Map,
    Pair,
};
use function Innmind\Immutable\unwrap;
use PHPUnit\Framework\TestCase;

class ParseSplObjectStorageTest extends TestCase
{
    public function testInterface()
    {
        $this->assertInstanceOf(Visit::class, new ParseSplObjectStorage);
    }

    public function testDoesntParseIfAlreadyParsed()
    {
        $spl = new \SplObjectStorage;
        $spl->attach(new \stdClass, new \stdClass);
        $visit = new ParseSplObjectStorage;
        $nodes = Map::of('object', Node::class)
            ($spl, $node = new Node($spl));

        $this->assertTrue($node->relations()->empty());
        $this->assertSame($nodes, $visit($nodes, $spl, $visit));
        $this->assertTrue($node->relations()->empty());
    }

    public function testDoesntParseIfNotASplObjectStorage()
    {
        $visit = new ParseSplObjectStorage;
        $nodes = Map::of('object', Node::class);

        $this->assertSame($nodes, $visit($nodes, new \stdClass, $visit));
    }

    public function testParse()
    {
        $spl = new \SplObjectStorage;
        $spl->attach($key = new \stdClass, $value = new \stdClass);
        $visit = new ParseSplObjectStorage;
        $nodes = Map::of('object', Node::class);

        $newNodes = $visit($nodes, $spl, new ExtractProperties);

        $this->assertNotSame($nodes, $newNodes);
        $node = $newNodes->get($spl);
        $relations = unwrap($node->relations());
        $this->assertCount(1, $relations);
        $relations = unwrap($relations[0]->node()->relations());
        $this->assertCount(2, $relations);
        $this->assertTrue($relations[0]->node()->comesFrom($key));
        $this->assertTrue($relations[1]->node()->comesFrom($value));
    }
}
