<?php
declare(strict_types = 1);

namespace Tests\Innmind\ObjectGraph\Graph;

use Innmind\ObjectGraph\{
    Graph\ParseIterable,
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

class ParseIterableTest extends TestCase
{
    public function testInterface()
    {
        $this->assertInstanceOf(Visit::class, new ParseIterable);
    }

    public function testDoesntParseIfAlreadyParsed()
    {
        $iterable = (static function() {
            yield new \stdClass => new \stdClass;
        })();
        $visit = new ParseIterable;
        $nodes = Map::of('object', Node::class)
            ($iterable, $node = new Node($iterable));

        $this->assertTrue($node->relations()->empty());
        $this->assertSame($nodes, $visit($nodes, $iterable, $visit));
        $this->assertTrue($node->relations()->empty());
    }

    public function testDoesntParseIfNotASplObjectStorage()
    {
        $visit = new ParseIterable;
        $nodes = Map::of('object', Node::class);

        $this->assertSame($nodes, $visit($nodes, new \stdClass, $visit));
    }

    public function testParse()
    {
        $iterable = (static function($key, $value) {
            yield $key => $value;
        })($key = new \stdClass, $value = new \stdClass);
        $visit = new ParseIterable;
        $nodes = Map::of('object', Node::class);

        $newNodes = $visit($nodes, $iterable, new ExtractProperties);

        $this->assertNotSame($nodes, $newNodes);
        $node = $newNodes->get($iterable);
        $relations = unwrap($node->relations());
        $this->assertCount(2, $relations);
        $this->assertSame('key[0]', $relations[0]->property()->toString());
        $this->assertTrue($relations[0]->node()->comesFrom($key));
        $this->assertSame('value[0]', $relations[1]->property()->toString());
        $this->assertTrue($relations[1]->node()->comesFrom($value));
    }
}
