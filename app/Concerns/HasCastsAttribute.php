<?php

namespace App\Concerns;

use App\Attributes\Casts;
use ReflectionClass;

trait HasCastsAttribute
{
    /**
     * The cached casts from attributes.
     *
     * @var array<string, array<string, string>>
     */
    protected static array $attributeCastsCache = [];

    /**
     * Get the casts array.
     *
     * @return array<string, string>
     */
    public function getCasts(): array
    {
        return array_merge(parent::getCasts(), $this->resolveAttributeCasts());
    }

    /**
     * Resolve the casts from attributes.
     *
     * @return array<string, string>
     */
    protected function resolveAttributeCasts(): array
    {
        $class = static::class;

        if (isset(static::$attributeCastsCache[$class])) {
            return static::$attributeCastsCache[$class];
        }

        $casts = [];
        $reflection = new ReflectionClass($this);

        $attributes = $reflection->getAttributes(Casts::class);

        if (! empty($attributes)) {
            $casts = $attributes[0]->newInstance()->casts;
        }

        return static::$attributeCastsCache[$class] = $casts;
    }
}
