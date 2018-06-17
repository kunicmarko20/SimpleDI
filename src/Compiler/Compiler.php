<?php

namespace KunicMarko\SimpleDI\Compiler;

use Doctrine\Common\Annotations\AnnotationReader;
use Doctrine\Common\Annotations\AnnotationRegistry;
use KunicMarko\SimpleDI\Annotation\Service;
use KunicMarko\SimpleDI\ContainerBuilder;
use KunicMarko\SimpleDI\ContainerException;
use KunicMarko\SimpleDI\ParameterBagBuilder;
use ReflectionClass;

/**
 * @author Marko Kunic <kunicmarko20@gmail.com>
 */
final class Compiler
{
    /**
     * @var AnnotationReader
     */
    private $annotationReader;

    /**
     * @var ContainerBuilder
     */
    private $containerBuilder;

    /**
     * @var ClassFinder
     */
    private $classFinder;

    /**
     * @var ServiceResolver
     */
    private $serviceResolver;

    public function __construct(
        ContainerBuilder $containerBuilder,
        ClassFinder $classFinder = null,
        ServiceResolver $serviceResolver = null
    ) {
        $this->containerBuilder = $containerBuilder;
        $this->classFinder = $classFinder ?? new ClassFinder();
        $this->serviceResolver = $serviceResolver ?? new ServiceResolver(
            $this->containerBuilder,
            $this->getAnnotationReader()
        );
    }

    private function getAnnotationReader(): AnnotationReader
    {
        if ($this->annotationReader === null) {
            AnnotationRegistry::registerLoader('class_exists');
            $this->annotationReader = new AnnotationReader();
        }

        return $this->annotationReader;
    }

    public function compile(): void
    {
        $classReflectors = [];

        foreach ($this->classFinder->findClassesInDirectory(
            $this->containerBuilder->getParameter(ParameterBagBuilder::SIMPLE_DI_SERVICE_SCAN_DIRECTORY)
        ) as $class) {
            if (!($this->hasServiceAnnotation($reflectionClass = new ReflectionClass($class)))) {
                continue;
            }

            if (!$reflectionClass->isInstantiable()) {
                throw ContainerException::notInstantiable($class);
            }

            $classReflectors[$class] = $reflectionClass;
        }

        $this->serviceResolver->setClassReflectors($classReflectors);

        foreach ($classReflectors as $reflectionClass) {
            $this->serviceResolver->resolve($reflectionClass);
        }
    }

    private function hasServiceAnnotation(ReflectionClass $reflectionClass): ?Service
    {
        return $this->getAnnotationReader()->getClassAnnotation(
            $reflectionClass,
            Service::class
        );
    }
}
