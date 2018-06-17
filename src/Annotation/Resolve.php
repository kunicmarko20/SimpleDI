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
     * @var array
     */
    public $values = [];
}
