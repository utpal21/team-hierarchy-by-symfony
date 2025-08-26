<?php
declare(strict_types=1);

namespace App\Application\UseCase;

use App\Domain\Servces\HierarchyValidator;
use App\Application\Service\HierarchyFilter;
use App\Application\Service\HierarchyBuilderInterface;
use App\Domain\Repository\TeamHierarchyRepositoryInterface;

final class BuildTeamHierarchy
{
    /**
     * Constructor.
     *
     * @param TeamHierarchyRepositoryInterface $repo The repository to load team data from CSV.
     * @param HierarchyBuilderInterface $builder The service to build the team hierarchy.
     * @param HierarchyFilter $filter The service to filter the hierarchy by team name.
     * @param HierarchyValidator $validator The service to validate the team hierarchy.
     */
    public function __construct(
        private readonly TeamHierarchyRepositoryInterface $repo,
        private readonly HierarchyBuilderInterface $builder,
        private readonly HierarchyFilter $filter,
        private readonly HierarchyValidator $validator
    ) {}


    /**
     * Executes the use case to build and optionally filter the team hierarchy.
     *
     * @param string $csvPath The path to the CSV file containing team data.
     * @param string|null $q Optional team name to filter the hierarchy.
     * @return array The resulting team hierarchy as an associative array.
     */
    public function execute(string $csvPath, ?string $q): array
    {
        $teams = $this->repo->loadFromCsv($csvPath);
        // Convert the iterable to an array before passing it to the validator
        $teamsArray = iterator_to_array($teams, false);
        // Validate business rules
        $this->validator->validate($teamsArray);
        // Build hierarchy
        $result = $this->builder->build($teamsArray);
        $root = $result['root'];
        // Optionally filter by team name
        if ($q !== null && trim($q) !== '') {
            $root = $this->filter->filterByTeam($root, trim($q));
            $result['rootName'] = $root->teamName;
        }

        return [$result['rootName'] => $root->toArray()];
    }
}
