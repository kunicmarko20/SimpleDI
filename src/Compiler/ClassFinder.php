<?php

namespace KunicMarko\SimpleDI\Compiler;

use Symfony\Component\Finder\Finder;
use Symfony\Component\Finder\SplFileInfo;

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

    public function findClassesInDirectories(array $directories): array
    {
        $classes = [];

        foreach ($directories as $directory) {
            $classes = array_merge($classes, $this->findClassesInDirectory($directory));
        }

        return array_unique($classes);
    }

    public function findClassesInDirectory(string $directory): array
    {
        $classes = [];

        foreach ($this->findFilesInDirectory($directory) as $file) {
            if (!($class = $this->getFullyQualifiedClassName($file))) {
                continue;
            }

            $classes[] = $class;
        }

        return array_unique($classes);
    }

    private function findFilesInDirectory(string $directory): \IteratorAggregate
    {
        return $this->finder
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
}
