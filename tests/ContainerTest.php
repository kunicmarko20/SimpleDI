<?php

namespace KunicMarko\SimpleDI\Tests;

use KunicMarko\SimpleDI\Container;
use KunicMarko\SimpleDI\ContainerException;
use KunicMarko\SimpleDI\ParameterBag;
use KunicMarko\SimpleDI\ParameterException;
use KunicMarko\SimpleDI\Tests\Fixtures\Service1;
use KunicMarko\SimpleDI\Tests\Fixtures\Service2;
use KunicMarko\SimpleDI\Tests\Fixtures\Service3;
use PHPUnit\Framework\TestCase;

class ContainerTest extends TestCase
{
    /**
     * @var Container
     */
    private $container;

    public function setUp()
    {
        $this->container = new Container();
    }

    public function testCompile()
    {
        $this->container->getParameterBag()->replace([
            ParameterBag::SIMPLE_DI_SERVICE_SCAN_DIRECTORY => __DIR__ . '/Fixtures',
            Service2::TEST_PARAMETER => 'test word',
        ]);

        $this->container->compile();

        $this->assertInstanceOf(
            Service1::class,
            $service1 = $this->container->get(Service1::class)
        );

        $this->assertInstanceOf(
            $service3class = Service3::class,
            $service3 = $this->container->get(Service3::class)
        );

        $this->assertSame('test word', $service1->talk());
        $this->assertSame($service3class, $service3->talk());
    }

    public function testMissingDirectoryParameter()
    {
        $this->expectException(ParameterException::class);

        $this->container->compile();
    }

    public function testMissingParameterInContainer()
    {
        $this->container->getParameterBag()->set(
            ParameterBag::SIMPLE_DI_SERVICE_SCAN_DIRECTORY,
            __DIR__ . '/Fixtures'
        );

        $this->container->compile();

        $service1 = $this->container->get(Service1::class);

        $this->assertSame('service2.word', $service1->talk());
    }
}
