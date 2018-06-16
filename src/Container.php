<?php

namespace KunicMarko\SimpleDI;

use Doctrine\Common\Annotations\AnnotationReader;
use Doctrine\Common\Annotations\AnnotationRegistry;
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

    public function set($id, \ReflectionClass $class)
    {
        return $this->services[$id] = $class;
    }

    public function compile(): ContainerInterface
    {
        foreach ($this->findFiles($this->parameterBag->get(ParameterBag::SIMPLE_DI_SERVICE_CAN_DIRECTORY)) as $file) {
            if (!($className = $this->getFullyQualifiedClassName($file))) {
                continue;
            }

            if (!($this->isService($reflectionClass = new \ReflectionClass($className)))) {
                continue;
            }

            //@TODO Remove this and add logic that supports resolving of Interface
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

        $dependencies = $this->getDependencies($constructor->getParameters());

        return $reflectionClass->newInstanceArgs($dependencies);
    }

    private function getDependencies(array $parameters): array
    {
        $dependencies = [];

        /** @var \ReflectionParameter $parameter */
        foreach ($parameters as $parameter) {
            if (!($dependency = $parameter->getClass())) {
                if ($parameter->isDefaultValueAvailable()) {
                    if (($value = $parameter->getDefaultValue()) && $this->parameterBag->has($value)) {
                        $dependencies[] = $this->parameterBag->get($value);
                        continue;
                    }

                    $dependencies[] = $value;

                    continue;
                }

                throw ContainerException::classDependencyUnresolvable($parameter->getName());
            }

            if (($service = $this->get($dependency->getName())) instanceof \ReflectionClass) {
                $this->services[$dependency->getName()] = $this->resolveService($service);
            }

            $dependencies[] = $this->get($dependency->getName());
        }

        return $dependencies;
    }
}
