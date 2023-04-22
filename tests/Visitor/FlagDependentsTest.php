<?php
declare(strict_types = 1);

namespace Tests\Innmind\ObjectGraph\Visitor;

use Innmind\ObjectGraph\{
    Visitor\FlagDependents,
    Node,
    Relation,
    Relation\Property,
};
use PHPUnit\Framework\TestCase;

class FlagDependentsTest extends TestCase
{
    public function testInvokation()
    {
        // root
        //  |-> level1 ------------|
        //       |-> level2        |
        //            |-> level3 <-|
        $dependency = new class {
        };
        $root = Node::of(new class {
        });
        $level1 = Node::of(new class {
        });
        $level2 = Node::of(new class {
        });
        $level3 = Node::of($dependency);
        $root->relate(Relation::of(
            new Property('level1'),
            $level1,
        ));
        $level1->relate(Relation::of(
            new Property('level2'),
            $level2,
        ));
        $level1->relate(Relation::of(
            new Property('level3'),
            $level3,
        ));
        $level2->relate(Relation::of(
            new Property('level3'),
            $level3,
        ));
        $flagDependents = new FlagDependents($dependency);

        $this->assertNull($flagDependents($root));
        $this->assertFalse($root->isDependent());
        $this->assertTrue($level1->isDependent());
        $this->assertTrue($level2->isDependent());
        $this->assertFalse($level3->isDependent());
    }
}
