<?php

namespace KunicMarko\SimpleDI;

use Psr\Container\ContainerExceptionInterface;

/**
 * @author Marko Kunic <kunicmarko20@gmail.com>
 */
final class ContainerException extends \LogicException implements ContainerExceptionInterface
{
    public static function notInstantiable(string $class)
    {
        return new self(sprintf(
            'Class "%s" is not instantiable.',
            $class
        ));
    }

    public static function classDependencyUnresolvable(string $dependency)
    {
        return new self(sprintf(
            'Can not resolve class dependency "%s".',
            $dependency
        ));
    }
}
