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
        $nodes = Map::of()
            ($structure, $node = new Node($structure));

        $this->assertTrue($node->relations()->empty());
        $this->assertSame($nodes, $visit($nodes, $structure, $visit));
        $this->assertTrue($node->relations()->empty());
    }

    public function testDoesntParseIfNotAnExpectedStructure()
    {
        $visit = new ParseSetAndSequence;
        $nodes = Map::of();

        $this->assertSame($nodes, $visit($nodes, new \stdClass, $visit));
    }

    /**
     * @dataProvider structures
     */
    public function testParse($structure, $object)
    {
        $visit = new ParseSetAndSequence;
        $nodes = Map::of();

        $newNodes = $visit($nodes, $structure, new ExtractProperties);

        $this->assertNotSame($nodes, $newNodes);
        $node = $newNodes->get($structure)->match(
            static fn($node) => $node,
            static fn() => null,
        );
        $relations = $node->relations()->toList();
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
