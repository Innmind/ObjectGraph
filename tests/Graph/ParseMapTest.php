<?php
declare(strict_types = 1);

namespace Tests\Innmind\ObjectGraph\Graph;

use Innmind\ObjectGraph\{
    Graph\ParseMap,
    Graph\Visit,
    Graph\ExtractProperties,
    Node,
};
use Innmind\Immutable\Map;
use PHPUnit\Framework\TestCase;

class ParseMapTest extends TestCase
{
    public function testInterface()
    {
        $this->assertInstanceOf(Visit::class, new ParseMap);
    }

    public function testDoesntParseIfAlreadyParsed()
    {
        $map = Map::of()
            (new \stdClass, 42);
        $visit = new ParseMap;
        $nodes = Map::of()
            ($map, $node = new Node($map));

        $this->assertTrue($node->relations()->empty());
        $this->assertSame($nodes, $visit($nodes, $map, $visit));
        $this->assertTrue($node->relations()->empty());
    }

    public function testDoesntParseIfNotAnExpectedStructure()
    {
        $visit = new ParseMap;
        $nodes = Map::of();

        $this->assertSame($nodes, $visit($nodes, new \stdClass, $visit));
    }

    public function testParse()
    {
        $map = Map::of()
            ($key = new \stdClass, 42)
            (42, $value = new \stdClass)
            ($value, $key);
        $visit = new ParseMap;
        $nodes = Map::of();

        $newNodes = $visit($nodes, $map, new ExtractProperties);

        $this->assertNotSame($nodes, $newNodes);
        $node = $newNodes->get($map)->match(
            static fn($node) => $node,
            static fn() => null,
        );
        $relations = $node->relations()->toList();
        $this->assertCount(4, $relations);
        $this->assertSame('key[0]', $relations[0]->property()->toString());
        $this->assertTrue($relations[0]->node()->comesFrom($key));
        $this->assertSame('value[1]', $relations[1]->property()->toString());
        $this->assertTrue($relations[1]->node()->comesFrom($value));
        $this->assertSame('key[2]', $relations[2]->property()->toString());
        $this->assertTrue($relations[2]->node()->comesFrom($value));
        $this->assertSame('value[2]', $relations[3]->property()->toString());
        $this->assertTrue($relations[3]->node()->comesFrom($key));
    }
}
