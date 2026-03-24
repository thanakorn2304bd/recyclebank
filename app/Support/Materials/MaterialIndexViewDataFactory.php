<?php

namespace App\Support\Materials;

use Illuminate\Contracts\Pagination\LengthAwarePaginator;

class MaterialIndexViewDataFactory
{
    public function metrics(LengthAwarePaginator $materials): array
    {
        $pageMaterials = $materials->getCollection();

        return [
            'activeCount' => $pageMaterials->where('is_active', true)->count(),
            'inactiveCount' => $pageMaterials->where('is_active', false)->count(),
        ];
    }

    public function sortColumns(array $query, string $sort, string $dir): array
    {
        return collect(['id', 'name', 'category', 'unit', 'price', 'status'])
            ->mapWithKeys(fn (string $column) => [
                $column => $this->sortColumn($query, $sort, $dir, $column),
            ])
            ->all();
    }

    private function sortColumn(array $query, string $sort, string $dir, string $column): array
    {
        $isActive = $sort === $column;

        return [
            'active' => $isActive,
            'url' => route('materials.index', array_merge($query, [
                'sort' => $column,
                'dir' => $isActive && $dir === 'asc' ? 'desc' : 'asc',
            ])),
            'indicator' => ! $isActive ? '↕' : ($dir === 'asc' ? '▲' : '▼'),
            'aria' => ! $isActive ? 'none' : ($dir === 'asc' ? 'ascending' : 'descending'),
        ];
    }
}
