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
        $root = new Node(new class {
        });
        $level10 = new Node(new class {
        });
        $level11 = new Node(new class {
        });
        $level2 = new Node(new class {
        });
        $level3 = new Node(new class {
        });
        $level4 = new Node(new class {
        });
        $root->relate($foo = new Relation(
            new Property('level10'),
            $level10,
        ));
        $root->relate(new Relation(
            new Property('level11'),
            $level11,
        ));
        $level10->relate(new Relation(
            new Property('level2'),
            $level2,
        ));
        $level2->relate(new Relation(
            new Property('level3'),
            $level3,
        ));
        $level3->relate(new Relation(
            new Property('recursion'),
            $level10,
        ));
        $level11->flagAsDependency();
        $level11->relate(new Relation(
            new Property('level4'),
            $level4,
        ));

        $removeDependenciesSubGraph = new RemoveDependenciesSubGraph;

        $this->assertCount(1, $level11->relations());
        $this->assertNull($removeDependenciesSubGraph($root));
        $this->assertCount(0, $level11->relations());
    }
}
