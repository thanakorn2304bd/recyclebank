<?php

namespace App\Support\Reports;

class ReportFilters
{
    public function __construct(
        public readonly ?string $from,
        public readonly ?string $to,
        public readonly ?string $communityId,
        public readonly ?string $householdStatus,
        public readonly ?int $categoryId,
    ) {}

    public static function fromValidated(array $validated, bool $isPrivileged): self
    {
        return new self(
            from: $validated['from'] ?? null,
            to: $validated['to'] ?? null,
            communityId: $isPrivileged ? ($validated['community_id'] ?? null) : null,
            householdStatus: $isPrivileged ? ($validated['household_status'] ?? null) : null,
            categoryId: isset($validated['category_id']) ? (int) $validated['category_id'] : null,
        );
    }
}
