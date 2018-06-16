<?php

namespace KunicMarko\SimpleDI\Tests;

use KunicMarko\SimpleDI\Container;
use KunicMarko\SimpleDI\ParameterBag;
use KunicMarko\SimpleDI\Tests\Fixtures\Service1;
use KunicMarko\SimpleDI\Tests\Fixtures\Service2;
use PHPUnit\Framework\TestCase;

class ContainerTest extends TestCase
{
    /**
     * @var Container
     */
    private $container;

    public function setUp()
    {
        $this->container = new Container(
            new ParameterBag([
                ParameterBag::SIMPLE_DI_SERVICE_CAN_DIRECTORY => __DIR__ . '/Fixtures',
                Service2::TEST_PARAMETER => 'test word'
            ])
        );
    }

    public function testCompile()
    {
        $this->container->compile();

        $this->assertInstanceOf(
            Service1::class,
            $service1 = $this->container->get(Service1::class)
        );

        $this->assertSame('test word', $service1->talk());
    }
}
