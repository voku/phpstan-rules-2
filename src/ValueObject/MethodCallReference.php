<?php

declare(strict_types=1);

namespace Symplify\PHPStanRules\ValueObject;

final class MethodCallReference
{
    /**
     * @var string
     */
    private $class;
    /**
     * @var string
     */
    private $method;
    public function __construct(string $class, string $method)
    {
        $this->class = $class;
        $this->method = $method;
    }

    public function getClass(): string
    {
        return $this->class;
    }

    public function getMethod(): string
    {
        return $this->method;
    }
}
