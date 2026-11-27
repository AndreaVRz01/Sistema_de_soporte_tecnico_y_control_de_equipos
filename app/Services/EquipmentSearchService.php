<?php

namespace App\Services;

use App\Models\Equipment;
use Illuminate\Support\Collection;
use Illuminate\Support\Str;

class EquipmentSearchService
{
    private const IGNORED_QUERY_TOKENS = ['a', 'al', 'con', 'de', 'del', 'el', 'en', 'la', 'las', 'los', 'para', 'por', 'un', 'una', 'y'];

    public function similar(string $term, bool $availableOnly = false, ?int $categoryId = null, int $limit = 8): Collection
    {
        $queryTokens = array_values(array_filter(
            $this->tokens($term),
            fn (string $token) => !in_array($token, self::IGNORED_QUERY_TOKENS, true)
        ));

        if ($queryTokens === [] || collect($queryTokens)->every(fn (string $token) => strlen($token) < 3)) {
            return collect();
        }

        $candidates = Equipment::query()
            ->with('category')
            ->when($availableOnly, fn ($query) => $query->available())
            ->when($categoryId, fn ($query) => $query->where('category_id', $categoryId))
            ->get([
                'id',
                'category_id',
                'inventory_code',
                'serial_number',
                'name',
                'brand',
                'model',
                'specifications',
            ]);

        return $candidates
            ->map(function (Equipment $equipment) use ($queryTokens): array {
                $fields = [
                    $equipment->name,
                    $equipment->brand,
                    $equipment->model,
                    $equipment->inventory_code,
                    $equipment->serial_number,
                    $equipment->specifications,
                    $equipment->category?->name,
                    $equipment->category?->code,
                ];
                $candidateTokens = collect($fields)
                    ->filter()
                    ->flatMap(fn ($field) => $this->tokens((string) $field))
                    ->unique()
                    ->values();
                $score = 0;

                foreach ($queryTokens as $queryToken) {
                    $allowedDistance = strlen($queryToken) < 3
                        ? 0
                        : (int) floor(strlen($queryToken) * 0.35);
                    $bestDistance = $candidateTokens
                        ->map(fn (string $candidateToken) => $this->distance($queryToken, $candidateToken))
                        ->min();

                    if ($bestDistance === null || $bestDistance > $allowedDistance) {
                        return ['equipment' => $equipment, 'score' => null];
                    }

                    $score += $bestDistance;
                }

                return ['equipment' => $equipment, 'score' => $score];
            })
            ->filter(fn (array $match) => $match['score'] !== null)
            ->sortBy('score')
            ->take($limit)
            ->pluck('equipment')
            ->values();
    }

    private function tokens(string $value): array
    {
        $normalized = Str::ascii(mb_strtolower($value));
        $tokens = preg_split('/[^a-z0-9]+/', $normalized, -1, PREG_SPLIT_NO_EMPTY);

        return $tokens === false ? [] : $tokens;
    }

    private function distance(string $left, string $right): int
    {
        $distance = levenshtein($left, $right);

        if (strlen($left) !== strlen($right) || strlen($left) > 32 || $distance < 2) {
            return $distance;
        }

        $characters = str_split($left);
        for ($index = 0; $index < count($characters) - 1; $index++) {
            $swapped = $characters;
            [$swapped[$index], $swapped[$index + 1]] = [$swapped[$index + 1], $swapped[$index]];
            $distance = min($distance, 1 + levenshtein(implode('', $swapped), $right));
        }

        return $distance;
    }
}
