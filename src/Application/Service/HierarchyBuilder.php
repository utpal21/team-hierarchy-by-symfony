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

        foreach ($teams as $t) {
            $this->nodes[$t->teamName()] = new TeamNode(
                $t->teamName(),
                $t->parentTeam() ?? '',
                $t->managerName(),
                $t->businessUnit()
            );
            if (!$t->isRoot()) {
                $this->parents[$t->teamName()] = $t->parentTeam() ?? '';
            }
        }

        foreach ($this->parents as $child => $parent) {
            if (!isset($this->nodes[$parent])) {
                throw new InvalidHierarchy(sprintf('Parent "%s" not found for "%s"', $parent, $child));
            }
        }

        $roots = array_filter($this->nodes, fn(TeamNode $n) => $n->parentTeam === '');
        if (count($roots) !== 1) {
            throw new InvalidHierarchy('Hierarchy must have exactly one root node.');
        }

        foreach ($this->parents as $child => $parent) {
            $this->nodes[$parent]->teams[$child] = $this->nodes[$child];
        }

        /** @var TeamNode $root */
        $root = array_values($roots)[0];
        return ['rootName' => $root->teamName, 'root' => $root];
    }
}
