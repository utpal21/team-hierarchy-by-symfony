<?php
declare(strict_types=1);

namespace App\Domain\Servces;


use App\Domain\Exception\DomainValidationException;
use App\Domain\Model\Team;

final class HierarchyValidator
{
/**
 * @param iterable<Team> $teams
 */
    public function validate(array $teams): void
    {
        $errors = [];

        // 1. Exactly one root node (no parent_team)
        $rootTeams = array_filter($teams, fn(Team $t) => $t->isRoot());
        if (count($rootTeams) !== 1) {
            $errors['hierarchy'][] = 'There must be exactly one root team (team without parent).';
        }

        // 2. Every non-root must have a valid parent
        $teamNames = array_map(fn(Team $t) => $t->teamName(), $teams);
        foreach ($teams as $team) {
            if ($team->isRoot()) {
                continue;
            }
            if (!in_array($team->parentTeam(), $teamNames, true)) {
                $errors['parent_team'][] = "Parent team '{$team->parentTeam()}' for '{$team->teamName()}' does not exist.";
            }
        }

        // 3. Manager name must always be populated
        foreach ($teams as $team) {
            if (trim($team->managerName()) === '') {
                $errors['manager_name'][] = "Team '{$team->teamName()}' must have a manager.";
            }
        }

        // 4. business_unit is optional → no validation needed

        if (!empty($errors)) {
            throw new DomainValidationException($errors);
        }
    }
}
