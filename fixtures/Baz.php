<?php
declare(strict_types = 1);

namespace Fixtures\Innmind\ObjectGraph;

final class Baz
{
    private $a;

    public function __construct(Bar $a)
    {
        $this->a = $a;
    }
}
