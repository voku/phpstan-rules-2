<?php

declare(strict_types=1);

namespace Symplify\PHPStanRules\Rules\Enum;

use PhpParser\Node;
use PhpParser\Node\Stmt\Class_;
use PHPStan\Analyser\Scope;
use PHPStan\Node\InClassNode;
use PHPStan\Rules\Rule;
use Symplify\PHPStanRules\Enum\EnumConstantAnalyzer;
use Symplify\PHPStanRules\Matcher\SharedNamePrefixMatcher;
use Symplify\PHPStanRules\NodeAnalyzer\ClassAnalyzer;
use Symplify\RuleDocGenerator\Contract\ConfigurableRuleInterface;
use Symplify\RuleDocGenerator\Contract\DocumentedRuleInterface;
use Symplify\RuleDocGenerator\ValueObject\CodeSample\ConfiguredCodeSample;
use Symplify\RuleDocGenerator\ValueObject\RuleDefinition;

/**
 * @see \Symplify\PHPStanRules\Tests\Rules\Enum\EmbeddedEnumClassConstSpotterRule\EmbeddedEnumClassConstSpotterRuleTest
 */
final class EmbeddedEnumClassConstSpotterRule implements Rule
{
    /**
     * @var string
     */
    public const ERROR_MESSAGE = 'Constants "%s" should be extract to standalone enum class';
    /**
     * @var \Symplify\PHPStanRules\NodeAnalyzer\ClassAnalyzer
     */
    private $classAnalyzer;
    /**
     * @var \Symplify\PHPStanRules\Matcher\SharedNamePrefixMatcher
     */
    private $sharedNamePrefixMatcher;
    /**
     * @var \Symplify\PHPStanRules\Enum\EnumConstantAnalyzer
     */
    private $enumConstantAnalyzer;
    /**
     * @var array<class-string>
     */
    private $parentTypes;

    /**
     * @param array<class-string> $parentTypes
     */
    public function __construct(ClassAnalyzer $classAnalyzer, SharedNamePrefixMatcher $sharedNamePrefixMatcher, EnumConstantAnalyzer $enumConstantAnalyzer, array $parentTypes)
    {
        $this->classAnalyzer = $classAnalyzer;
        $this->sharedNamePrefixMatcher = $sharedNamePrefixMatcher;
        $this->enumConstantAnalyzer = $enumConstantAnalyzer;
        $this->parentTypes = $parentTypes;
    }

    /**
     * @return class-string<Node>
     */
    public function getNodeType(): string
    {
        return InClassNode::class;
    }

    /**
     * @param InClassNode $node
     * @return mixed[]
     */
    public function processNode(Node $node, Scope $scope): array
    {
        if ($this->shouldSkip($node)) {
            return [];
        }

        $classLike = $node->getOriginalNode();
        if (! $classLike instanceof Class_) {
            return [];
        }

        $constantNames = $this->classAnalyzer->resolveConstantNames($classLike);

        $groupedByPrefix = $this->sharedNamePrefixMatcher->match($constantNames);

        $enumConstantNamesGroup = [];

        foreach ($groupedByPrefix as $prefix => $constantNames) {
            if (\count($constantNames) < 1) {
                continue;
            }

            if ($this->enumConstantAnalyzer->isNonEnumConstantPrefix($prefix)) {
                continue;
            }

            $enumConstantNamesGroup[] = $constantNames;
        }

        return $this->createErrorMessages($enumConstantNamesGroup);
    }

    public function getRuleDefinition(): RuleDefinition
    {
        return new RuleDefinition(self::ERROR_MESSAGE, [new ConfiguredCodeSample(
            <<<'CODE_SAMPLE'
class SomeProduct extends AbstractObject
{
    public const STATUS_ENABLED = 1;

    public const STATUS_DISABLED = 0;
}
CODE_SAMPLE
            ,
            <<<'CODE_SAMPLE'
class SomeProduct extends AbstractObject
{
}

class SomeStatus
{
    public const ENABLED = 1;

    public const DISABLED = 0;
}
CODE_SAMPLE
            ,
            [
                'parentTypes' => ['AbstractObject'],
            ]
        )]);
    }

    private function shouldSkip(InClassNode $inClassNode): bool
    {
        $classReflection = $inClassNode->getClassReflection();

        // already enum
        if (strpos($classReflection->getName(), '\\Enum\\') !== false && strpos($classReflection->getName(), '\\Rules\\Enum\\') === false) {
            return true;
        }

        foreach ($this->parentTypes as $parentType) {
            if ($classReflection->isSubclassOf($parentType)) {
                return false;
            }
        }

        return true;
    }

    /**
     * @param string[][] $enumConstantNamesGroup
     * @return string[]
     */
    private function createErrorMessages(array $enumConstantNamesGroup): array
    {
        $errorMessages = [];

        foreach ($enumConstantNamesGroup as $enumConstantNameGroup) {
            $enumConstantNamesString = \implode('", "', $enumConstantNameGroup);
            $errorMessages[] = \sprintf(self::ERROR_MESSAGE, $enumConstantNamesString);
        }

        return $errorMessages;
    }
}
