<?php
declare(strict_types = 1);

namespace Tests\Innmind\ObjectGraph\Visitor;

use Innmind\ObjectGraph\{
    Visitor\RemoveDependenciesSubGraph,
    Node,
    Relation,
    Relation\Property,
};
use PHPUnit\Framework\TestCase;

class RemoveDependenciesSubGraphTest extends TestCase
{
    public function testInvokation()
    {
        // root
        //  |-> level10 <---------|
        //  |    |-> level2       |
        //  |         |-> level3  |
        //  |              |------|
        //  |-> level11
        //       |-> level4
        $root = Node::of(new class {
        });
        $level10 = Node::of(new class {
        });
        $level11 = Node::of(new class {
        });
        $level2 = Node::of(new class {
        });
        $level3 = Node::of(new class {
        });
        $level4 = Node::of(new class {
        });
        $root->relate($foo = Relation::of(
            new Property('level10'),
            $level10,
        ));
        $root->relate(Relation::of(
            new Property('level11'),
            $level11,
        ));
        $level10->relate(Relation::of(
            new Property('level2'),
            $level2,
        ));
        $level2->relate(Relation::of(
            new Property('level3'),
            $level3,
        ));
        $level3->relate(Relation::of(
            new Property('recursion'),
            $level10,
        ));
        $level11->flagAsDependency();
        $level11->relate(Relation::of(
            new Property('level4'),
            $level4,
        ));

        $removeDependenciesSubGraph = new RemoveDependenciesSubGraph;

        $this->assertCount(1, $level11->relations());
        $this->assertNull($removeDependenciesSubGraph($root));
        $this->assertCount(0, $level11->relations());
    }
}
