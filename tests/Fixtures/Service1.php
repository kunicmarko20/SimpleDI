<?php

namespace KunicMarko\SimpleDI\Tests\Fixtures;

use KunicMarko\SimpleDI\Annotation\Service;

/**
 * @Service
 *
 * @author Marko Kunic <kunicmarko20@gmail.com>
 */
class Service1
{
    /**
     * @var Service2
     */
    private $service2;

    public function __construct(Service2 $service2)
    {
        $this->service2 = $service2;
    }

    public function talk()
    {
        return $this->service2->talk();
    }
}
