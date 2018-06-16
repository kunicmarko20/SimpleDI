<?php

namespace KunicMarko\SimpleDI;

/**
 * @author Marko Kunic <kunicmarko20@gmail.com>
 */
final class ParameterException extends \LogicException
{
    public static function parameterNotFound(string $id)
    {
        return new self(sprintf(
            'No entry was found for "%s" identifier.',
            $id
        ));
    }
}
