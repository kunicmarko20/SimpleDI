<?php

namespace KunicMarko\SimpleDI;

use Psr\Container\ContainerExceptionInterface;
use Psr\Container\NotFoundExceptionInterface;

/**
 * @author Marko Kunic <kunicmarko20@gmail.com>
 */
final class ContainerException extends \LogicException implements ContainerExceptionInterface, NotFoundExceptionInterface
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

    public static function serviceNotFound(string $id)
    {
        return new self(sprintf(
            'No entry was found for "%s" identifier.',
            $id
        ));
    }
}
