<?php

namespace KunicMarko\SimpleDI;

use Psr\Container\ContainerInterface;

/**
 * @author Marko Kunic <kunicmarko20@gmail.com>
 */
final class Container implements ContainerInterface
{
    /**
     * @var array
     */
    private $services;

    /**
     * @var ParameterBag
     */
    private $parameterBag;

    public function __construct(array $parameters = [], array $services = [])
    {
        $this->parameterBag = new ParameterBag($parameters);
        $this->services = $services;
    }

    public function get($id)
    {
        if (!isset($this->services[$id])) {
            throw ContainerException::serviceNotFound($id);
        }

        return $this->services[$id];
    }

    public function has($id): bool
    {
        return isset($this->services[$id]);
    }

    public function getParameter(string $id)
    {
        return $this->parameterBag->get($id);
    }

    public function hasParameter(string $id): bool
    {
        return $this->parameterBag->has($id);
    }
}
