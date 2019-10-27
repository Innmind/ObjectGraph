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
        $root = new Node(new class {});
        $level1 = new Node(new class {});
        $level2 = new Node(new class {});
        $level3 = new Node($dependency = new class {});
        $root->relate(new Relation(
            new Property('level1'),
            $level1
        ));
        $level1->relate(new Relation(
            new Property('level2'),
            $level2
        ));
        $level1->relate(new Relation(
            new Property('level3'),
            $level3
        ));
        $level2->relate(new Relation(
            new Property('level3'),
            $level3
        ));
        $flagDependents = new FlagDependents($dependency);

        $this->assertNull($flagDependents($root));
        $this->assertFalse($root->isDependent());
        $this->assertTrue($level1->isDependent());
        $this->assertTrue($level2->isDependent());
        $this->assertFalse($level3->isDependent());
    }
}
