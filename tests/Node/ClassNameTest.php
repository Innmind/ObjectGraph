<?php
declare(strict_types = 1);

namespace Tests\Innmind\ObjectGraph\Node;

use Innmind\ObjectGraph\Node\ClassName;
use PHPUnit\Framework\TestCase;

class ClassNameTest extends TestCase
{
    public function testInterface()
    {
        $class = new ClassName(new \stdClass);

        $this->assertSame('stdClass', (string) $class);
    }
}
