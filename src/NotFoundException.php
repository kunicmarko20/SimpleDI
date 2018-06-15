<?php

namespace KunicMarko\SimpleDI;

use Psr\Container\NotFoundExceptionInterface;

/**
 * @author Marko Kunic <kunicmarko20@gmail.com>
 */
final class NotFoundException extends \RuntimeException implements NotFoundExceptionInterface
{
    public static function serviceNotFound(string $id)
    {
        return new self(sprintf(
            'No entry was found for "%s" identifier.',
            $id
        ));
    }
}
