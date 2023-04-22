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
        $nodes = Map::of()
            ($spl, $node = Node::of($spl));

        $this->assertTrue($node->relations()->empty());
        $this->assertSame($nodes, $visit($nodes, $spl, $visit));
        $this->assertTrue($node->relations()->empty());
    }

    public function testDoesntParseIfNotASplObjectStorage()
    {
        $visit = new ParseSplObjectStorage;
        $nodes = Map::of();

        $this->assertSame($nodes, $visit($nodes, new \stdClass, $visit));
    }

    public function testParse()
    {
        $spl = new \SplObjectStorage;
        $spl->attach($key = new \stdClass, $value = new \stdClass);
        $visit = new ParseSplObjectStorage;
        $nodes = Map::of();

        $newNodes = $visit($nodes, $spl, new ExtractProperties);

        $this->assertNotSame($nodes, $newNodes);
        $node = $newNodes->get($spl)->match(
            static fn($node) => $node,
            static fn() => null,
        );
        $relations = $node->relations()->toList();
        $this->assertCount(2, $relations);
        $this->assertSame('key[0]', $relations[0]->property()->toString());
        $this->assertTrue($relations[0]->node()->comesFrom($key));
        $this->assertSame('value[0]', $relations[1]->property()->toString());
        $this->assertTrue($relations[1]->node()->comesFrom($value));
    }
}
