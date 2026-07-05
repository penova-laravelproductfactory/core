<?php

namespace App\Core\DataTable;

use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\Request;

/**
 * Core\DataTable — shared server-side table infrastructure.
 *
 * Pairs with resources/js/Core/Components/DataTable.vue. The Vue
 * component sends the query-string contract below; this builder applies
 * it to any Eloquent query. Core modules AND product Modules use the
 * same class — table behaviour is solved once, here.
 *
 * Query-string contract:
 *   ?search=foo&sort=name&direction=desc&per_page=25&page=2
 *
 * Usage:
 *   DataTableBuilder::for(User::query())
 *       ->searchable(['name', 'email'])
 *       ->sortable(['name', 'created_at'])
 *       ->paginate($request);
 */
class DataTableBuilder
{
    /** @param list<string> $searchable  @param list<string> $sortable */
    private function __construct(
        private Builder $query,
        private array $searchable = [],
        private array $sortable = [],
        private string $defaultSort = 'created_at',
        private string $defaultDirection = 'desc',
    ) {}

    public static function for(Builder $query): self
    {
        return new self($query);
    }

    /** Columns matched (LIKE) against the "search" parameter. */
    public function searchable(array $columns): self
    {
        $this->searchable = $columns;

        return $this;
    }

    /** Whitelist of columns the client may sort by — never trust raw input. */
    public function sortable(array $columns): self
    {
        $this->sortable = $columns;

        return $this;
    }

    public function defaultSort(string $column, string $direction = 'desc'): self
    {
        $this->defaultSort = $column;
        $this->defaultDirection = $direction;

        return $this;
    }

    /** Apply search + sort + pagination from the request and execute. */
    public function paginate(Request $request): LengthAwarePaginator
    {
        $this->applySearch($request->string('search')->toString());
        $this->applySort(
            $request->string('sort')->toString(),
            $request->string('direction')->toString(),
        );

        $perPage = min(
            (int) $request->input('per_page', config('penova.datatable.per_page')),
            (int) config('penova.datatable.max_per_page'),
        );

        return $this->query
            ->paginate($perPage)
            ->withQueryString();
    }

    private function applySearch(string $term): void
    {
        if ($term === '' || $this->searchable === []) {
            return;
        }

        $this->query->where(function (Builder $query) use ($term) {
            foreach ($this->searchable as $column) {
                $query->orWhere($column, 'like', "%{$term}%");
            }
        });
    }

    private function applySort(string $column, string $direction): void
    {
        if (! in_array($column, $this->sortable, true)) {
            [$column, $direction] = [$this->defaultSort, $this->defaultDirection];
        }

        $this->query->orderBy(
            $column,
            $direction === 'asc' ? 'asc' : 'desc',
        );
    }
}
