<?php
declare(strict_types = 1);

namespace Fixtures\Innmind\ObjectGraph;

final class Foo
{
    private $a;
    private $b;

    public function __construct(?self $a = null, ?self $b = null)
    {
        $this->a = $a;
        $this->b = $b;
    }
}
