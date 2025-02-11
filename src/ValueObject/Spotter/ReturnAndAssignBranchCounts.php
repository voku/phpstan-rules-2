<?php

declare(strict_types=1);

namespace Symplify\PHPStanRules\ValueObject\Spotter;

final class ReturnAndAssignBranchCounts
{
    /**
     * @var int
     */
    private $returnTypeCount;
    /**
     * @var int
     */
    private $assignTypeCount;
    public function __construct(int $returnTypeCount, int $assignTypeCount)
    {
        $this->returnTypeCount = $returnTypeCount;
        $this->assignTypeCount = $assignTypeCount;
    }

    public function getReturnTypeCount(): int
    {
        return $this->returnTypeCount;
    }

    public function getAssignTypeCount(): int
    {
        return $this->assignTypeCount;
    }
}
