<?php

namespace KunicMarko\SimpleDI\Tests\Fixtures;

use KunicMarko\SimpleDI\Annotation\Service;

/**
 * @Service
 *
 * @author Marko Kunic <kunicmarko20@gmail.com>
 */
class Service3 implements Service3Interface
{
    public function talk()
    {
        return self::class;
    }
}
