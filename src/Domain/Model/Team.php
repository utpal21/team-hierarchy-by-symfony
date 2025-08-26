<?php
declare(strict_types=1);

namespace App\Domain\Model;

final class Team
{
    public function __construct(
        private readonly string $teamName,
        private readonly ?string $parentTeam,
        private readonly string $managerName,
        private readonly ?string $businessUnit
    ) {}

    public function teamName(): string { return $this->teamName; }
    public function parentTeam(): ?string { return $this->parentTeam; }
    public function managerName(): string { return $this->managerName; }
    public function businessUnit(): ?string { return $this->businessUnit; }
    public function isRoot(): bool { return $this->parentTeam === null || $this->parentTeam === ''; }
}
