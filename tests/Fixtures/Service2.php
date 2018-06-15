<?php

namespace KunicMarko\SimpleDI\Tests\Fixtures;

use KunicMarko\SimpleDI\Annotation\Service;

/**
 * @Service
 *
 * @author Marko Kunic <kunicmarko20@gmail.com>
 */
class Service2
{
    public function talk()
    {
        return 'bla';
    }
}
