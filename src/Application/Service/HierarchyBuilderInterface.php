<?php
declare(strict_types=1);

namespace App\Application\Service;

use App\Application\Dto\TeamNode;
use App\Domain\Model\Team;

interface HierarchyBuilderInterface
{
    /** @param iterable<Team> $teams
     *  @return array{rootName: string, root: TeamNode}
     */
    public function build(iterable $teams): array;
}
