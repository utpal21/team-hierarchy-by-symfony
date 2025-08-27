<?php
declare(strict_types=1);

namespace App\Application\Service;

use App\Application\Dto\TeamNode;
use App\Domain\Exception\InvalidHierarchy;
use App\Domain\Model\Team;

final class HierarchyBuilder implements HierarchyBuilderInterface
{
    /** @var array<string, TeamNode> */
    private array $nodes = [];
    /** @var array<string,string> */
    private array $parents = [];

    /**
     * Builds a team hierarchy from a list of teams.
     *
     * @param iterable $teams
     * @return array
     */
    public function build(iterable $teams): array
    {
        $this->nodes = [];
        $this->parents = [];

        foreach ($teams as $team) {
            $this->nodes[$team->teamName()] = new TeamNode(
                $team->teamName(),
                $team->parentTeam() ?? '',
                $team->managerName(),
                $team->businessUnit()
            );
            if (!$team->isRoot()) {
                $this->parents[$team->teamName()] = $team->parentTeam() ?? '';
            }
        }
        // ensure single root
        $roots = array_filter($this->nodes, fn(TeamNode $n) => $n->parentTeam === '');

        foreach ($this->parents as $child => $parent) {
            $this->nodes[$parent]->teams[$child] = $this->nodes[$child];
        }

        /** @var TeamNode $root */
        $root = array_values($roots)[0];
        return ['rootName' => $root->teamName, 'root' => $root];
    }
}
