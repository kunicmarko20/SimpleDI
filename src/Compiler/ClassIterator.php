<?php

namespace KunicMarko\SimpleDI\Compiler;

use Iterator;
use Symfony\Component\Finder\SplFileInfo;
use function count;

/**
 * @author Marko Kunic <kunicmarko20@gmail.com>
 */
final class ClassIterator implements Iterator
{
    /**
     * @var array
     */
    private $elements;

    /**
     * @var int
     */
    private $index = 0;

    public function __construct(Iterator $iterator)
    {
        $this->elements = iterator_to_array($iterator, false);
    }

    public function rewind(): void
    {
        reset($this->elements);
    }

    public function valid(): bool
    {
        return isset($this->elements[$this->index]) && (bool) $this->elements[$this->index];
    }

    public function key()
    {
        return $this->index;
    }

    public function current()
    {
        return $this->getFullyQualifiedClassName($this->elements[$this->index]);
    }

    public function next(): void
    {
        $this->index++;

        if ($this->index < count($this->elements) && !$this->current()) {
            $this->next();
        }
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
