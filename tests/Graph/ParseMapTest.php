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
use function Innmind\Immutable\unwrap;
use PHPUnit\Framework\TestCase;

class ParseMapTest extends TestCase
{
    public function testInterface()
    {
        $this->assertInstanceOf(Visit::class, new ParseMap);
    }

    public function testDoesntParseIfAlreadyParsed()
    {
        $map = Map::of('mixed', 'mixed')
            (new \stdClass, 42);
        $visit = new ParseMap;
        $nodes = Map::of('object', Node::class)
            ($map, $node = new Node($map));

        $this->assertTrue($node->relations()->empty());
        $this->assertSame($nodes, $visit($nodes, $map, $visit));
        $this->assertTrue($node->relations()->empty());
    }

    public function testDoesntParseIfNotAnExpectedStructure()
    {
        $visit = new ParseMap;
        $nodes = Map::of('object', Node::class);

        $this->assertSame($nodes, $visit($nodes, new \stdClass, $visit));
    }

    public function testParse()
    {
        $map = Map::of('mixed', 'mixed')
            ($key = new \stdClass, 42)
            (42, $value = new \stdClass)
            ($value, $key);
        $visit = new ParseMap;
        $nodes = Map::of('object', Node::class);

        $newNodes = $visit($nodes, $map, new ExtractProperties);

        $this->assertNotSame($nodes, $newNodes);
        $node = $newNodes->get($map);
        $relations = unwrap($node->relations());
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
