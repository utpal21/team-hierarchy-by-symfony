<?php
declare(strict_types=1);

namespace App\Infrastructure\Csv;

use App\Domain\Exception\InvalidCsvHeaders;
use App\Domain\Model\Team;
use App\Domain\Repository\TeamHierarchyRepositoryInterface;
use SplFileObject;

final class CsvTeamHierarchyRepository implements TeamHierarchyRepositoryInterface
{
    /**
     * Load teams from a CSV file.
     *
     * @param string $csvPath Path to the CSV file.
     * @return iterable<Team> An iterable collection of Team objects.
     * @throws InvalidCsvHeaders If the CSV headers are invalid or missing required fields.
     */
    public function loadFromCsv(string $csvPath): iterable
    {
        $f = new SplFileObject($csvPath, 'r');
        $f->setFlags(SplFileObject::READ_CSV | SplFileObject::SKIP_EMPTY);

        $headers = null;
        foreach ($f as $row) {
            if ($row === [null] || $row === false) { continue; }

            if ($headers === null) {
                $headers = array_map(fn($h) => strtolower(trim((string)$h)), $row);

                foreach (['team','parent_team','manager_name'] as $must) {
                    if (!in_array($must, $headers, true)) {
                        throw new InvalidCsvHeaders(sprintf('Missing required header "%s"', $must));
                    }
                }
                continue;
            }

            $assoc = [];
            foreach ($headers as $i => $h) { 
                $assoc[$h] = $row[$i] ?? null; 
            }

            $teamName     = trim((string)($assoc['team'] ?? ''));
            $parentTeam   = trim((string)($assoc['parent_team'] ?? ''));
            $managerName  = trim((string)($assoc['manager_name'] ?? ''));
            $businessUnit = isset($assoc['business_unit']) ? trim((string)$assoc['business_unit']) : null;

            yield new Team(
                $teamName,
                $parentTeam === '' ? null : $parentTeam,
                $managerName,
                $businessUnit === '' ? null : $businessUnit
            );
        }
    }
}
