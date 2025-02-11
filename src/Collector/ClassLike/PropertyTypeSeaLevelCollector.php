<?php

declare(strict_types=1);

namespace Symplify\PHPStanRules\Collector\ClassLike;

use PhpParser\Comment\Doc;
use PhpParser\Node;
use PhpParser\Node\Stmt\ClassLike;
use PhpParser\PrettyPrinter\Standard;
use PHPStan\Analyser\Scope;
use PHPStan\Collectors\Collector;

/**
 * @implements Collector<ClassLike, array{int, int, string}>>
 *
 * @see \Symplify\PHPStanRules\Rules\Explicit\PropertyTypeDeclarationSeaLevelRule
 */
final class PropertyTypeSeaLevelCollector implements Collector
{
    /**
     * @readonly
     * @var \PhpParser\PrettyPrinter\Standard
     */
    private $printerStandard;
    public function __construct(Standard $printerStandard)
    {
        $this->printerStandard = $printerStandard;
    }

    public function getNodeType(): string
    {
        return ClassLike::class;
    }

    /**
     * @param ClassLike $node
     * @return array{int, int, string}
     */
    public function processNode(Node $node, Scope $scope): ?array
    {
        $printedProperties = '';

        // return typed properties/all properties
        $propertyCount = count($node->getProperties());

        $typedPropertyCount = 0;

        foreach ($node->getProperties() as $property) {
            if ($property->type instanceof Node) {
                ++$typedPropertyCount;
                continue;
            }

            $docComment = $property->getDocComment();
            if ($docComment instanceof Doc) {
                $docCommentText = $docComment->getText();

                // skip as unable to type
                if (strpos($docCommentText, 'callable') !== false || strpos($docCommentText, 'resource') !== false) {
                    ++$typedPropertyCount;
                    continue;
                }
            }

            // give useful context
            $printedProperties .= PHP_EOL . PHP_EOL . $this->printerStandard->prettyPrint([$property]);
        }

        return [$typedPropertyCount, $propertyCount, $printedProperties];
    }
}
