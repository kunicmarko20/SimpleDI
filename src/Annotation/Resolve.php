<?php

namespace KunicMarko\SimpleDI\Annotation;

/**
 * @Annotation
 * @Target("METHOD")
 *
 * @author Marko Kunic <kunicmarko20@gmail.com>
 */
final class Resolve
{
    /**
     * @var string
     */
    public $interface;

    /**
     * @var string
     */
    public $implementation;

    public function getInterface(): string
    {
        if ($this->interface) {
            return $this->interface;
        }

        throw new \InvalidArgumentException(
            sprintf(
                'Argument "interface" is mandatory in "%s" annotation.',
                self::class
            )
        );
    }

    public function getImplementation(): string
    {
        if ($this->implementation) {
            return $this->implementation;
        }

        throw new \InvalidArgumentException(
            sprintf(
                'Argument "implementation" is mandatory in "%s" annotation.',
                self::class
            )
        );
    }
}
