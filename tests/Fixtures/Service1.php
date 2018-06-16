<?php

namespace KunicMarko\SimpleDI\Tests\Fixtures;

use KunicMarko\SimpleDI\Annotation\Resolve;
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

    /**
     * @var Service3Interface
     */
    private $service3;

    /**
     * @Resolve(interface=Service3Interface::class, implementation=Service3::class)
     */
    public function __construct(Service2 $service2, Service3Interface $service3)
    {
        $this->service2 = $service2;
        $this->service3 = $service3;
    }

    public function talk()
    {
        return $this->service2->talk();
    }
}
