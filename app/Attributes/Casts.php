<?php

namespace App\Attributes;

use Attribute;

#[Attribute(Attribute::TARGET_CLASS)]
class Casts
{
    /**
     * Create a new Casts attribute instance.
     *
     * @param  array<string, string>  $casts
     */
    public function __construct(public array $casts) {}
}
