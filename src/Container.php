<?php

namespace KunicMarko\SimpleDI;

use Doctrine\Common\Annotations\AnnotationReader;
use Doctrine\Common\Annotations\AnnotationRegistry;
use KunicMarko\SimpleDI\Annotation\Resolve;
use KunicMarko\SimpleDI\Annotation\Service;
use Psr\Container\ContainerInterface;
use Symfony\Component\Finder\Finder;
use Symfony\Component\Finder\SplFileInfo;

/**
 * @author Marko Kunic <kunicmarko20@gmail.com>
 */
final class Container implements ContainerInterface
{
    /**
     * @var array
     */
    private $services = [];

    /**
     * @var AnnotationReader
     */
    private $annotationReader;

    /**
     * @var ParameterBag
     */
    private $parameterBag;

    /**
     * @var string[][]
     */
    private $interfaceImplementations = [];

    public function __construct(ParameterBag $parameterBag = null)
    {
        $this->parameterBag = $parameterBag ?? new ParameterBag();
    }

    public function get($id)
    {
        if (!isset($this->services[$id])) {
            throw ContainerException::serviceNotFound($id);
        }

        return $this->services[$id];
    }

    public function getParameterBag(): ParameterBag
    {
        return $this->parameterBag;
    }

    public function has($id): bool
    {
        return isset($this->services[$id]);
    }

    public function set(string $id, \ReflectionClass $class)
    {
        return $this->services[$id] = $class;
    }

    public function remove(string $name): void
    {
        unset($this->services[$name]);
    }

    public function compile(): ContainerInterface
    {
        foreach ($this->findFiles($this->parameterBag->get(ParameterBag::SIMPLE_DI_SERVICE_SCAN_DIRECTORY)) as $file) {
            if (!($className = $this->getFullyQualifiedClassName($file))) {
                continue;
            }

            if (!($this->isService($reflectionClass = new \ReflectionClass($className)))) {
                continue;
            }

            if (!$reflectionClass->isInstantiable()) {
                throw ContainerException::notInstantiable($className);
            }

            $this->set($className, $reflectionClass);
        }

        /** @var  \ReflectionClass $reflectionClass */
        foreach ($this->services as $id => $reflectionClass) {
            $this->services[$id] = $this->resolveService($reflectionClass);
        }

        return $this;
    }

    private function findFiles(string $directory): \IteratorAggregate
    {
        return Finder::create()
            ->in($directory)
            ->files()
            ->name('*.php');
    }

    private function getFullyQualifiedClassName(SplFileInfo $file): ?string
    {
        if (!($namespace = $this->getNamespace($file->getPathname()))) {
            return null;
        }

        return $namespace . '\\' . $this->getClassName($file->getFilename());
    }

    private function getNamespace(string $filePath): ?string
    {
        $namespaceLine = preg_grep('/^namespace /', file($filePath));

        if (!$namespaceLine) {
            return null;
        }

        preg_match('/namespace (.*);$/', reset($namespaceLine), $match);

        return array_pop($match);
    }

    private function getClassName(string $fileName): string
    {
        return str_replace('.php', '', $fileName);
    }

    private function isService(\ReflectionClass $reflectionClass): ?Service
    {
        return $this->getAnnotationReader()->getClassAnnotation(
            $reflectionClass,
            Service::class
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

    private function resolveService(\ReflectionClass $reflectionClass)
    {
        if (!($constructor = $reflectionClass->getConstructor())) {
            return $reflectionClass->newInstance();
        }

        $dependencies = $this->getDependencies($constructor);

        return $reflectionClass->newInstanceArgs($dependencies);
    }

    private function getDependencies(\ReflectionMethod $method): array
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

            if (($service = $this->get($dependencyClassName)) instanceof \ReflectionClass) {
                $this->services[$dependencyClassName] = $this->resolveService($service);
            }

            $dependencies[] = $this->get($dependencyClassName);
        }

        return $dependencies;
    }

    private function resolveParameter(\ReflectionParameter $parameter)
    {
        if (!$parameter->isDefaultValueAvailable()) {
            throw ContainerException::classDependencyUnresolvable($parameter->getName());
        }

        //@TODO think about replacing this logic with Annotation
        if (($value = $parameter->getDefaultValue()) && $this->parameterBag->has($value)) {
            return $this->parameterBag->get($value);
        }

        return $value;
    }

    private function resolveInterface(\ReflectionMethod $method, string $dependencyClassName): string
    {
        if (isset($this->interfaceImplementations[$class = $method->getDeclaringClass()->getName()])) {
            return $this->getImplementation($class, $dependencyClassName);
        }

        foreach ($this->getAnnotationReader()->getMethodAnnotations($method) as $annotation) {
            if (!$annotation instanceof Resolve) {
                continue;
            }

            $this->interfaceImplementations[$class][$annotation->getInterface()] = $annotation->getImplementation();
        }

        return $this->getImplementation($class, $dependencyClassName);
    }

    private function getImplementation(string $class, string $dependencyClassName): string
    {
        if (!isset($this->interfaceImplementations[$class][$dependencyClassName])) {
            throw ContainerException::unableToAutowireInterface($dependencyClassName, $class);
        }

        return $this->interfaceImplementations[$class][$dependencyClassName];
    }
}
