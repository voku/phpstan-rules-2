<?php

declare(strict_types=1);

namespace Symplify\PHPStanRules\ParentGuard;

use PhpParser\Node\Stmt\ClassMethod;
use PHPStan\Analyser\Scope;
use PHPStan\Reflection\Php\PhpMethodReflection;
use Symplify\PHPStanRules\ParentGuard\ParentElementResolver\ParentMethodResolver;

final class ParentClassMethodGuard
{
    /**
     * @var \Symplify\PHPStanRules\ParentGuard\ParentElementResolver\ParentMethodResolver
     */
    private $parentMethodResolver;
    public function __construct(ParentMethodResolver $parentMethodResolver)
    {
        $this->parentMethodResolver = $parentMethodResolver;
    }

    public function isClassMethodGuardedByParentClassMethod(ClassMethod $classMethod, Scope $scope): bool
    {
        $phpMethodReflection = $this->parentMethodResolver->resolve($scope, $classMethod->name->toString());
        return $phpMethodReflection instanceof PhpMethodReflection;
    }
}
