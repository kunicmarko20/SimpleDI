<?php

namespace KunicMarko\SimpleDI;

use KunicMarko\SimpleDI\Compiler\Compiler;
use Psr\Container\ContainerInterface;

/**
 * @author Marko Kunic <kunicmarko20@gmail.com>
 */
final class ContainerBuilder implements ContainerInterface
{
    /**
     * @var array
     */
    private $services = [];

    /**
     * @var ParameterBagBuilder
     */
    private $parameterBagBuilder;

    /**
     * @var Compiler
     */
    private $compiler;

    /**
     * @var bool
     */
    private $built = false;

    public function __construct(
        ParameterBagBuilder $parameterBagBuilder = null,
        Compiler $serviceResolver = null
    ) {
        $this->parameterBagBuilder = $parameterBagBuilder ?? new ParameterBagBuilder();
        $this->compiler = $serviceResolver ?? new Compiler($this);
    }

    public function build(): ContainerInterface
    {
        $this->compiler->compile();

        $this->built = true;

        return new Container($this->parameterBagBuilder->all(), $this->services);
    }

    public function get($id)
    {
        if (!isset($this->services[$id])) {
            throw ContainerException::serviceNotFound($id);
        }

        if ($this->built) {
            throw ContainerException::built();
        }

        return $this->services[$id];
    }

    public function getParameterBag(): ParameterBagBuilder
    {
        return $this->parameterBagBuilder;
    }

    public function getParameter(string $id)
    {
        if ($this->built) {
            throw ContainerException::built();
        }

        return $this->parameterBagBuilder->get($id);
    }

    public function hasParameter(string $id): bool
    {
        return $this->parameterBagBuilder->has($id);
    }

    public function setParameter(string $id, $value): void
    {
        $this->parameterBagBuilder->set($id, $value);
    }

    public function has($id): bool
    {
        return isset($this->services[$id]);
    }

    public function set(string $id, $class): void
    {
        $this->services[$id] = $class;
    }

    public function remove(string $name): void
    {
        unset($this->services[$name]);
    }
}
