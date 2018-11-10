<?php
declare(strict_types = 1);

namespace Fixtures\Innmind\ObjectGraph;

final class Free
{
    private $a;
    private $b;

    public function __construct($a = null, $b = null)
    {
        $this->a = $a;
        $this->b = $b;
    }
}
