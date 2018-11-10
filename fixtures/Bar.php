<?php
declare(strict_types = 1);

namespace Fixtures\Innmind\ObjectGraph;

final class Bar
{
    private $a;

    public function __construct(Foo $a)
    {
        $this->a = $a;
    }
}
