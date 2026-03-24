<?php

namespace App\Support;

use App\Models\Material;
use App\Models\UserAccount;
use Illuminate\Support\Arr;
use Illuminate\Support\Collection;

class MainMenuViewDataFactory
{
    public function make(?UserAccount $authUser): array
    {
        $now = now();
        $catalogSections = $this->buildCatalogSections($now->toDateString());
        $categoryFilters = array_map(
            fn (array $section) => [
                'name' => $section['name'],
                'count' => $section['count'],
            ],
            $catalogSections
        );
        $privilegedMenuCount = $this->privilegedMenuCount($authUser);

        return [
            'authUser' => $authUser,
            'isPrivileged' => $this->isPrivileged($authUser),
            'roleLabel' => $this->roleLabel($authUser),
            'privilegedMenuCount' => $privilegedMenuCount,
            'managementMenuCount' => max($privilegedMenuCount - 2, 0),
            'catalogSections' => $catalogSections,
            'categoryFilters' => $categoryFilters,
            'totalCategories' => count($catalogSections),
            'totalMaterials' => array_sum(array_column($categoryFilters, 'count')),
            'todayLabel' => $now->format('d/m/Y'),
            'updatedAtLabel' => $now->format('d/m/Y H:i'),
        ];
    }

    private function buildCatalogSections(string $date): array
    {
        return Material::query()
            ->select('material.*')
            ->selectRaw('current_price.price as current_price')
            ->with('category')
            ->joinCurrentPriceAt($date)
            ->get()
            ->map(function (Material $material) {
                return [
                    'category_name' => $material->category?->category_name ?? 'ไม่ระบุหมวด',
                    'name' => $material->material_name,
                    'unit' => $material->unit,
                    'priceDisplay' => $material->current_price !== null
                        ? number_format((float) $material->current_price, 2)
                        : null,
                ];
            })
            ->sortBy(fn (array $item) => $item['category_name'].'|'.$item['name'])
            ->groupBy('category_name')
            ->map(function (Collection $items, string $categoryName) {
                return [
                    'name' => $categoryName,
                    'count' => $items->count(),
                    'items' => $items
                        ->map(fn (array $item) => Arr::except($item, 'category_name'))
                        ->values()
                        ->all(),
                ];
            })
            ->values()
            ->all();
    }

    private function isPrivileged(?UserAccount $authUser): bool
    {
        return $authUser !== null && in_array($authUser->role, ['admin', 'staff'], true);
    }

    private function privilegedMenuCount(?UserAccount $authUser): int
    {
        return match ($authUser?->role) {
            'admin' => 8,
            'staff' => 6,
            default => 0,
        };
    }

    private function roleLabel(?UserAccount $authUser): string
    {
        return match ($authUser?->role) {
            'admin' => 'ผู้ดูแลระบบ',
            'staff' => 'เจ้าหน้าที่',
            default => 'สมาชิก',
        };
    }
}
