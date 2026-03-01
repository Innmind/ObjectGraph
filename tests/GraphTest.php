<?php
declare(strict_types = 1);

namespace Tests\Innmind\ObjectGraph;

use Innmind\ObjectGraph\{
    Graph,
    Node,
    Node\Reference,
    Relation,
    Relation\Property,
};
use Innmind\Immutable\Set;
use Innmind\BlackBox\PHPUnit\Framework\TestCase;

class GraphTest extends TestCase
{
    public function testRemoveDependenciesSubGraph()
    {
        // root
        //  |-> level10 <---------|
        //  |    |-> level2       |
        //  |         |-> level3  |
        //  |              |------|
        //  |-> level11
        //       |-> level4
        $root = new \stdClass;
        $level10 = new \stdClass;
        $level11 = new \stdClass;
        $level2 = new \stdClass;
        $level3 = new \stdClass;
        $level4 = new \stdClass;
        $graph = Graph::of(
            Node::of(
                $root,
                Set::of(
                    Relation::of(
                        Property::of('level10'),
                        Reference::of($level10),
                    ),
                    Relation::of(
                        Property::of('level11'),
                        Reference::of($level11),
                    ),
                ),
            ),
            Set::of(
                Node::of(
                    $level10,
                    Set::of(Relation::of(
                        Property::of('level2'),
                        Reference::of($level2),
                    )),
                ),
                Node::of(
                    $level11,
                    Set::of(Relation::of(
                        Property::of('level4'),
                        Reference::of($level4),
                    )),
                )->flagAsDependency(),
                Node::of(
                    $level2,
                    Set::of(Relation::of(
                        Property::of('level3'),
                        Reference::of($level3),
                    )),
                ),
                Node::of(
                    $level3,
                    Set::of(Relation::of(
                        Property::of('level10'),
                        Reference::of($level10),
                    )),
                ),
                Node::of($level4),
            ),
        );

        $graph = $graph->removeDependenciesSubGraph();

        $this->assertSame(5, $graph->nodes()->size());
        $this->assertNull(
            $graph
                ->nodes()
                ->find(static fn($node) => $node->comesFrom($level4))
                ->match(
                    static fn($node) => $node,
                    static fn() => null,
                ),
        );

        // root
        //  |-> level10 <---------|
        //  |    |-> level2       |
        //  |         |-> level3  |
        //  |              |------|
        //  |-> level11
        //       |-> level4
        $root = new \stdClass;
        $level10 = new \stdClass;
        $level11 = new \stdClass;
        $level2 = new \stdClass;
        $level3 = new \stdClass;
        $level4 = new \stdClass;
        $graph = Graph::of(
            Node::of(
                $root,
                Set::of(
                    Relation::of(
                        Property::of('level10'),
                        Reference::of($level10),
                    ),
                    Relation::of(
                        Property::of('level11'),
                        Reference::of($level11),
                    ),
                ),
            ),
            Set::of(
                Node::of(
                    $level10,
                    Set::of(Relation::of(
                        Property::of('level2'),
                        Reference::of($level2),
                    )),
                )->flagAsDependency(),
                Node::of(
                    $level11,
                    Set::of(Relation::of(
                        Property::of('level4'),
                        Reference::of($level4),
                    )),
                ),
                Node::of(
                    $level2,
                    Set::of(Relation::of(
                        Property::of('level3'),
                        Reference::of($level3),
                    )),
                ),
                Node::of(
                    $level3,
                    Set::of(Relation::of(
                        Property::of('level10'),
                        Reference::of($level10),
                    )),
                ),
                Node::of($level4),
            ),
        );

        $graph = $graph->removeDependenciesSubGraph();

        $this->assertSame(4, $graph->nodes()->size());
        $this->assertNull(
            $graph
                ->nodes()
                ->find(static fn($node) => $node->comesFrom($level2))
                ->match(
                    static fn($node) => $node,
                    static fn() => null,
                ),
        );
        $this->assertNull(
            $graph
                ->nodes()
                ->find(static fn($node) => $node->comesFrom($level3))
                ->match(
                    static fn($node) => $node,
                    static fn() => null,
                ),
        );
        $this->assertNotNull(
            $graph
                ->nodes()
                ->find(static fn($node) => $node->comesFrom($level10))
                ->match(
                    static fn($node) => $node,
                    static fn() => null,
                ),
        );
    }

    public function testRemoveRootSubGraph()
    {
        // root
        //  |-> level10 <---------|
        //  |    |-> level2       |
        //  |         |-> level3  |
        //  |              |------|
        //  |-> level11
        //       |-> level4
        $root = new \stdClass;
        $level10 = new \stdClass;
        $level11 = new \stdClass;
        $level2 = new \stdClass;
        $level3 = new \stdClass;
        $level4 = new \stdClass;
        $graph = Graph::of(
            Node::of(
                $root,
                Set::of(
                    Relation::of(
                        Property::of('level10'),
                        Reference::of($level10),
                    ),
                    Relation::of(
                        Property::of('level11'),
                        Reference::of($level11),
                    ),
                ),
            )->flagAsDependency(),
            Set::of(
                Node::of(
                    $level10,
                    Set::of(Relation::of(
                        Property::of('level2'),
                        Reference::of($level2),
                    )),
                ),
                Node::of(
                    $level11,
                    Set::of(Relation::of(
                        Property::of('level4'),
                        Reference::of($level4),
                    )),
                ),
                Node::of(
                    $level2,
                    Set::of(Relation::of(
                        Property::of('level3'),
                        Reference::of($level3),
                    )),
                ),
                Node::of(
                    $level3,
                    Set::of(Relation::of(
                        Property::of('level10'),
                        Reference::of($level10),
                    )),
                ),
                Node::of($level4),
            ),
        );

        $graph = $graph->removeDependenciesSubGraph();

        $this->assertSame(1, $graph->nodes()->size());
        $this->assertTrue($graph->nodes()->contains($graph->root()));
        $this->assertSame(0, $graph->root()->relations()->size());
    }
}
