<?php

namespace KunicMarko\SimpleDI;

use KunicMarko\SimpleDI\Annotation\Resolve;
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
            'Class dependency "%s" unresolvable.',
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

    public static function unableToAutowireInterface(string $interface, string $class)
    {
        return new self(sprintf(
            'Unable to Autowire "%s" interface in "%s" class, try adding "%s" annotation.',
            $interface,
            $class,
            Resolve::class
        ));
    }

    public static function built()
    {
        return new self("Container has already been built, you can't fetch anything from Builder.");
    }
}
