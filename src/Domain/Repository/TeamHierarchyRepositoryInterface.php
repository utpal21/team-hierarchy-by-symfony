<?php
declare(strict_types=1);

namespace App\Domain\Repository;

use App\Domain\Model\Team;

interface TeamHierarchyRepositoryInterface
{
    /** @return iterable<Team> */
    public function loadFromCsv(string $csvPath): iterable;
}
