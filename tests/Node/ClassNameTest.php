<?php
declare(strict_types = 1);

namespace Tests\Innmind\ObjectGraph\Node;

use Innmind\ObjectGraph\Node\ClassName;
use Innmind\BlackBox\PHPUnit\Framework\TestCase;

class ClassNameTest extends TestCase
{
    public function testInterface()
    {
        $class = ClassName::of(new \stdClass);

        $this->assertSame('stdClass', $class->toString());
    }
}
