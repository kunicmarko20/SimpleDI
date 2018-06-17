<?php

namespace KunicMarko\SimpleDI\Tests\Fixtures;

use KunicMarko\SimpleDI\Annotation\Resolve;
use KunicMarko\SimpleDI\Annotation\Service;

/**
 * @Service
 *
 * @author Marko Kunic <kunicmarko20@gmail.com>
 */
class Service2
{
    public const TEST_PARAMETER = 'service2.word';

    /**
     * @var string
     */
    private $word;

    /**
     * @Resolve({
     *     "word" : "service2.word"
     * })
     */
    public function __construct(string $word)
    {
        $this->word = $word;
    }

    public function talk()
    {
        return $this->word;
    }
}
