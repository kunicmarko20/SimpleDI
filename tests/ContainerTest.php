<?php

namespace KunicMarko\SimpleDI\Tests;

use KunicMarko\SimpleDI\ContainerBuilder;
use KunicMarko\SimpleDI\ParameterBagBuilder;
use KunicMarko\SimpleDI\ParameterException;
use KunicMarko\SimpleDI\Tests\Fixtures\Service1;
use KunicMarko\SimpleDI\Tests\Fixtures\Service2;
use KunicMarko\SimpleDI\Tests\Fixtures\Service3;
use PHPUnit\Framework\TestCase;

class ContainerTest extends TestCase
{
    /**
     * @var ContainerBuilder
     */
    private $container;

    public function setUp()
    {
        $this->container = new ContainerBuilder();
    }

    public function testCompile()
    {
        $this->container->getParameterBag()->replace([
            ParameterBagBuilder::SIMPLE_DI_SERVICE_SCAN_DIRECTORY => __DIR__ . '/Fixtures',
            Service2::TEST_PARAMETER => 'test word',
        ]);

        $container = $this->container->build();

        $this->assertInstanceOf(
            Service1::class,
            $service1 = $container->get(Service1::class)
        );

        $this->assertInstanceOf(
            $service3class = Service3::class,
            $service3 = $container->get(Service3::class)
        );

        $this->assertSame('test word', $service1->talk());
        $this->assertSame($service3class, $service3->talk());
    }

    public function testMissingDirectoryParameter()
    {
        $this->expectException(ParameterException::class);

        $this->container->build();
    }

    public function testMissingParameterInContainer()
    {
        $this->container->getParameterBag()->set(
            ParameterBagBuilder::SIMPLE_DI_SERVICE_SCAN_DIRECTORY,
            __DIR__ . '/Fixtures'
        );

        $this->expectException(ParameterException::class);

        $container = $this->container->build();

        $service1 = $container->get(Service1::class);

        $this->assertSame('service2.word', $service1->talk());
    }
}
