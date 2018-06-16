<?php

namespace KunicMarko\SimpleDI\Tests\Fixtures;

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

    public function __construct(string $word = self::TEST_PARAMETER)
    {
        $this->word = $word;
    }

    public function talk()
    {
        return $this->word;
    }
}
