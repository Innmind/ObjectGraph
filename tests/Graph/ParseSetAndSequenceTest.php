<?php
declare(strict_types = 1);

namespace Tests\Innmind\ObjectGraph\Graph;

use Innmind\ObjectGraph\{
    Graph\ParseSetAndSequence,
    Graph\Visit,
    Graph\ExtractProperties,
    Node,
};
use Innmind\Immutable\{
    Map,
    Set,
    Sequence,
};
use function Innmind\Immutable\unwrap;
use PHPUnit\Framework\TestCase;

class ParseSetAndSequenceTest extends TestCase
{
    public function testInterface()
    {
        $this->assertInstanceOf(Visit::class, new ParseSetAndSequence);
    }

    /**
     * @dataProvider structures
     */
    public function testDoesntParseIfAlreadyParsed($structure)
    {
        $visit = new ParseSetAndSequence;
        $nodes = Map::of('object', Node::class)
            ($structure, $node = new Node($structure));

        $this->assertTrue($node->relations()->empty());
        $this->assertSame($nodes, $visit($nodes, $structure, $visit));
        $this->assertTrue($node->relations()->empty());
    }

    public function testDoesntParseIfNotAnExpectedStructure()
    {
        $visit = new ParseSetAndSequence;
        $nodes = Map::of('object', Node::class);

        $this->assertSame($nodes, $visit($nodes, new \stdClass, $visit));
    }

    /**
     * @dataProvider structures
     */
    public function testParse($structure, $object)
    {
        $visit = new ParseSetAndSequence;
        $nodes = Map::of('object', Node::class);

        $newNodes = $visit($nodes, $structure, new ExtractProperties);

        $this->assertNotSame($nodes, $newNodes);
        $node = $newNodes->get($structure);
        $relations = unwrap($node->relations());
        $this->assertCount(1, $relations);
        $this->assertSame('1', $relations[0]->property()->toString());
        $this->assertTrue($relations[0]->node()->comesFrom($object));
    }

    public function structures(): array
    {
        return [
            [Set::mixed(24, $object = new \stdClass, 66), $object],
            [Sequence::mixed(24, $object = new \stdClass, 66), $object],
        ];
    }
}
