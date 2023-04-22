<?php
declare(strict_types = 1);

namespace Tests\Innmind\ObjectGraph;

use Innmind\ObjectGraph\{
    NamespacePattern,
    Exception\DomainException,
};
use PHPUnit\Framework\TestCase;

class NamespacePatternTest extends TestCase
{
    public function testInterface()
    {
        $namespace = NamespacePattern::of('Foo\Bar');

        $this->assertSame('Foo\\Bar', $namespace->toString());
    }

    public function testThrowWhenNamespaceStartsWithANumber()
    {
        $this->expectException(DomainException::class);
        $this->expectExceptionMessage('Foo\\42Bar');

        NamespacePattern::of('Foo\\42Bar');
    }

    public function testThrowWhenEmptyNamespace()
    {
        $this->expectException(DomainException::class);
        $this->expectExceptionMessage('');

        NamespacePattern::of('');
    }
}
