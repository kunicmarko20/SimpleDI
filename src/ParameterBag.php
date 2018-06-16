<?php

namespace KunicMarko\SimpleDI;

/**
 * @author Marko Kunic <kunicmarko20@gmail.com>
 */
final class ParameterBag
{
    public const SIMPLE_DI_SERVICE_SCAN_DIRECTORY = 'simple_di.service_scan_directory';

    /**
     * @var array
     */
    private $parameters;

    public function __construct(array $parameters = [])
    {
        $this->parameters = $parameters;
    }

    public function get(string $id)
    {
        if (!array_key_exists($id, $this->parameters)) {
            throw ParameterException::parameterNotFound($id);
        }

        return $this->parameters[$id];
    }

    public function add(string $id, $value): void
    {
        $this->parameters[$id] = $value;
    }

    public function set(array $parameters): void
    {
        $this->parameters = $parameters;
    }

    public function all(): array
    {
        return $this->parameters;
    }

    public function has(string $id): bool
    {
        return array_key_exists($id, $this->parameters);
    }
}
