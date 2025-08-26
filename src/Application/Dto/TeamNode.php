<?php
declare(strict_types=1);

namespace App\Application\Dto;

final class TeamNode
{
    /** @var array<string, TeamNode> */
    public array $teams = [];

    public function __construct(
        public readonly string $teamName,
        public readonly string $parentTeam,
        public readonly string $managerName,
        public readonly ?string $businessUnit
    ) {}

    /**
     * Converts the TeamNode and its children to an associative array.
     *
     * @return array
     */
    public function toArray(): array
    {
        $kids = [];
        foreach ($this->teams as $k => $v) { $kids[$k] = $v->toArray(); }

        return [
            'teamName'     => $this->teamName,
            'parentTeam'   => $this->parentTeam,
            'managerName'  => $this->managerName,
            'businessUnit' => $this->businessUnit ?? '',
            'teams'        => $kids,
        ];
    }
}
