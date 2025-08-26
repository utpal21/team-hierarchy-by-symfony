<?php
declare(strict_types=1);

namespace App\Application\Service;

use App\Application\Dto\TeamNode;
use App\Domain\Exception\TeamNotFound;

final class HierarchyFilter
{
    /**
     * Filter hierarchy to include only the specified team and its subtree
     *
     * @param TeamNode $root The root of the team hierarchy
     * @param string $teamName The name of the team to filter by
     * @return TeamNode The filtered team hierarchy
     * @throws TeamNotFound If the specified team is not found in the hierarchy
     */
    public function filterByTeam(TeamNode $root, string $teamName): TeamNode
    {
        $path = $this->findPath($root, $teamName);
        if ($path === []) { throw new TeamNotFound(sprintf('Team "%s" not found', $teamName)); }

        // copy chain root→…→target
        $pruned = $this->cloneWithoutChildren($path[0]); $cursor = $pruned;
        for ($i = 1; $i < count($path); $i++) {
            $child = $this->cloneWithoutChildren($path[$i]);
            $cursor->teams[$child->teamName] = $child;
            $cursor = $child;
        }
        // attach target subtree
        $target = $path[array_key_last($path)];
        foreach ($target->teams as $k => $v) { $cursor->teams[$k] = $v; }

        return $pruned;
    }

    /**
     * Find path from node to target team name, or empty if not found
     *
     * @param TeamNode $node
     * @param string $target
     * @return array
     */
    private function findPath(TeamNode $node, string $target): array
    {
        if ($node->teamName === $target) { return [$node]; }
        foreach ($node->teams as $child) {
            $path = $this->findPath($child, $target);
            if ($path !== []) { array_unshift($path, $node); return $path; }
        }
        return [];
    }

    /**
     * Clone a TeamNode without its children
     *
     * @param TeamNode $n
     * @return TeamNode
     */
    private function cloneWithoutChildren(TeamNode $n): TeamNode
    {
        $c = new TeamNode($n->teamName, $n->parentTeam, $n->managerName, $n->businessUnit);
        $c->teams = [];
        return $c;
    }
}
