<?php

namespace KunicMarko\SimpleDI\Compiler;

use Symfony\Component\Finder\Finder;
use Iterator;

/**
 * @author Marko Kunic <kunicmarko20@gmail.com>
 */
final class ClassFinder
{
    /**
     * @var Finder
     */
    private $finder;

    public function __construct(Finder $finder = null)
    {
        $this->finder = $finder ?? new Finder();
    }

    /**
     * @param string|array $directories
     */
    public function findClassesInDirectories($directories): ClassIterator
    {
        return new ClassIterator($this->findFilesInDirectory($directories));
    }

    /**
     * @param string|array $directories
     */
    private function findFilesInDirectory($directories): Iterator
    {
        return $this->finder
            ->in($directories)
            ->files()
            ->name('*.php')
            ->getIterator();
    }
}
