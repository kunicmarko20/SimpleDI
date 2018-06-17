<?php

namespace KunicMarko\SimpleDI\Compiler;

use Doctrine\Common\Annotations\AnnotationReader;
use KunicMarko\SimpleDI\Annotation\Resolve;
use KunicMarko\SimpleDI\ContainerBuilder;
use KunicMarko\SimpleDI\ContainerException;
use ReflectionClass;
use ReflectionMethod;
use ReflectionParameter;

/**
 * @author Marko Kunic <kunicmarko20@gmail.com>
 */
final class ServiceResolver
{
    /**
     * @var ContainerBuilder
     */
    private $containerBuilder;

    /**
     * @var ReflectionClass[]
     */
    private $classReflectors;

    /**
     * @var AnnotationReader
     */
    private $annotationReader;

    /**
     * @var string[][]
     */
    private $resolvedParameters;

    public function __construct(
        ContainerBuilder $containerBuilder,
        AnnotationReader $annotationReader
    ) {
        $this->containerBuilder = $containerBuilder;
        $this->annotationReader = $annotationReader;
    }

    public function resolve(ReflectionClass $reflectionClass): void
    {
        if (!($constructor = $reflectionClass->getConstructor())) {
            $this->set($reflectionClass);
            return;
        }

        $this->loadResolvedParametersFromAnnotation($constructor);
        $this->resolveDependencies($constructor);
    }

    private function set(ReflectionClass $reflectionClass): void
    {
        $this->containerBuilder->set(
            $reflectionClass->getName(),
            $reflectionClass->newInstance()
        );
    }

    private function setWithArguments(ReflectionClass $reflectionClass, array $dependencies): void
    {
        $this->containerBuilder->set(
            $reflectionClass->getName(),
            $reflectionClass->newInstanceArgs($dependencies)
        );
    }

    private function loadResolvedParametersFromAnnotation(\ReflectionMethod $method): void
    {
        if (!($annotation = $this->annotationReader->getMethodAnnotation($method, Resolve::class))) {
            return;
        }

        $this->resolvedParameters[$method->getDeclaringClass()->getName()] = $annotation->values;
    }

    private function resolveDependencies(ReflectionMethod $method): void
    {
        $dependencies = [];

        /** @var \ReflectionParameter $parameter */
        foreach ($method->getParameters() as $parameter) {
            if (!($dependency = $parameter->getClass())) {
                $dependencies[] = $this->resolveParameter($parameter);
                continue;
            }

            $dependencyClassName = $dependency->getName();

            if ($dependency->isInterface()) {
                $dependencyClassName = $this->resolveInterface($method, $dependencyClassName);
            }

            if (isset($this->classReflectors[$dependencyClassName])
                && !$this->containerBuilder->has($dependencyClassName)
            ) {
                $this->resolve($this->classReflectors[$dependencyClassName]);
            }

            $dependencies[] = $this->containerBuilder->get($dependencyClassName);
        }

        $this->setWithArguments($method->getDeclaringClass(), $dependencies);
    }

    private function resolveParameter(ReflectionParameter $parameter)
    {
        if ($this->hasResolvedParameter($class = $parameter->getDeclaringClass(), $name = $parameter->getName())) {
            return $this->containerBuilder
                ->getParameter($this->getResolvedParameter($class, $name));
        }

        if (!$parameter->isDefaultValueAvailable()) {
            throw ContainerException::classDependencyUnresolvable($name);
        }

        return $parameter->getDefaultValue();
    }

    private function hasResolvedParameter(ReflectionClass $reflectionClass, string $parameter): bool
    {
        return isset($this->resolvedParameters[$reflectionClass->getName()][$parameter]);
    }

    private function getResolvedParameter(ReflectionClass $reflectionClass, string $parameter): string
    {
        return $this->resolvedParameters[$reflectionClass->getName()][$parameter];
    }

    private function resolveInterface(\ReflectionMethod $method, string $dependencyClassName): string
    {
        if ($this->hasResolvedParameter($class = $method->getDeclaringClass(), $dependencyClassName)) {
            return $this->getResolvedParameter($class, $dependencyClassName);
        }

        throw ContainerException::unableToAutowireInterface($dependencyClassName, $class);
    }

    public function setClassReflectors(array $classReflectors): void
    {
        $this->classReflectors = $classReflectors;
    }
}
