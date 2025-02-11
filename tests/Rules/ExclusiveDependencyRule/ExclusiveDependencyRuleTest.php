<?php

declare(strict_types=1);

namespace Symplify\PHPStanRules\Tests\Rules\ExclusiveDependencyRule;

use Doctrine\ORM\EntityManager;
use Iterator;
use PHPStan\Rules\Rule;
use PHPStan\Testing\RuleTestCase;
use Symplify\PHPStanRules\Rules\ExclusiveDependencyRule;
use Symplify\PHPStanRules\Tests\Rules\ExclusiveDependencyRule\Source\AllowedEventSubscriber;

/**
 * @extends RuleTestCase<ExclusiveDependencyRule>
 */
final class ExclusiveDependencyRuleTest extends RuleTestCase
{
    /**
     * @dataProvider provideData()
     * @param mixed[] $expectedErrorMessagesWithLines
     */
    public function testRule(string $filePath, array $expectedErrorMessagesWithLines): void
    {
        $this->analyse([$filePath], $expectedErrorMessagesWithLines);
    }

    /**
     * @return Iterator<mixed>
     */
    public function provideData(): Iterator
    {
        yield [__DIR__ . '/Fixture/SkipNotSpecified.php', []];
        yield [__DIR__ . '/Fixture/SkipSomeRepository.php', []];
        yield [__DIR__ . '/Fixture/SkipAllowedEventSubscriber.php', []];

        $errorMessage = sprintf(
            ExclusiveDependencyRule::ERROR_MESSAGE,
            EntityManager::class,
            implode('", "', [AllowedEventSubscriber::class, '*Repository'])
        );

        yield [__DIR__ . '/Fixture/SomeController.php', [[$errorMessage, 9]]];
        yield [__DIR__ . '/Fixture/WarnController.php', [[$errorMessage, 16]]];
    }

    /**
     * @return string[]
     */
    public static function getAdditionalConfigFiles(): array
    {
        return [__DIR__ . '/config/configured_rule.neon'];
    }

    protected function getRule(): Rule
    {
        return self::getContainer()->getByType(ExclusiveDependencyRule::class);
    }
}
