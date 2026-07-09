<script setup>
/**
 * Core\DataTable (frontend half) — pairs with App\Core\DataTable\DataTableBuilder.
 *
 * Receives a Laravel paginator and a column definition, emits the
 * builder's query-string contract (?search, ?sort, ?direction, ?page)
 * via Inertia partial visits. Core pages and Module pages configure it
 * with props only — table behaviour lives here, once.
 *
 * columns: [{ key: 'name', label: 'نام', sortable: true }, ...]
 * Cell rendering can be customized per column with a #cell-<key> slot.
 */
import { computed, ref, watch } from 'vue';
import { router } from '@inertiajs/vue3';
import Pagination from '@/Core/Components/Pagination.vue';
import { useI18n } from '@/Core/composables/i18n';

const props = defineProps({
    paginator: { type: Object, required: true }, // Laravel LengthAwarePaginator JSON
    columns: { type: Array, required: true },
    // Extra query parameters the page wants to keep alongside the
    // table's own contract — e.g. filter selects ({ type: 'physical' }).
    // Changing a value triggers a reload; empty values are dropped from
    // the URL. DataTableBuilder-side, apply them with ->when() clauses.
    params: { type: Object, default: () => ({}) },
    // Tell users WHAT the search matches (e.g. "Search name or SKU…") — a
    // page passing its own value keeps it; omitted, it falls back to the
    // generic catalog placeholder below.
    searchPlaceholder: { type: String, default: '' },
});

const { t } = useI18n();

const placeholder = computed(() => props.searchPlaceholder || t('table.search_placeholder'));

const search = ref(new URLSearchParams(window.location.search).get('search') ?? '');
const sort = ref(new URLSearchParams(window.location.search).get('sort') ?? '');
const direction = ref(new URLSearchParams(window.location.search).get('direction') ?? 'desc');

function reload() {
    const extra = Object.fromEntries(
        Object.entries(props.params).filter(([, value]) => value !== null && value !== undefined && value !== ''),
    );

    router.get(
        window.location.pathname,
        { search: search.value || undefined, sort: sort.value || undefined, direction: direction.value, ...extra },
        { preserveState: true, preserveScroll: true, replace: true },
    );
}

let debounce;
watch(search, () => {
    clearTimeout(debounce);
    debounce = setTimeout(reload, 300);
});

// Value-compare (not identity): parents may recreate the object each
// render; reload only when a filter actually changed.
watch(() => JSON.stringify(props.params), () => reload());

function toggleSort(column) {
    if (!column.sortable) return;
    direction.value = sort.value === column.key && direction.value === 'asc' ? 'desc' : 'asc';
    sort.value = column.key;
    reload();
}
</script>

<template>
    <div class="space-y-4">
        <input
            v-model="search"
            type="search"
            :placeholder="placeholder"
            class="block w-64 rounded-md border-0 px-3 py-2 text-sm shadow-sm ring-1 ring-inset ring-slate-300 focus:ring-2 focus:ring-brand"
        />

        <div class="overflow-x-auto rounded-lg bg-white shadow">
            <table class="min-w-full divide-y divide-slate-200 text-sm">
                <thead class="bg-slate-100 text-start text-slate-700">
                    <tr>
                        <th
                            v-for="column in columns"
                            :key="column.key"
                            class="px-4 py-3 text-start font-semibold"
                            :class="{ 'cursor-pointer select-none': column.sortable }"
                            @click="toggleSort(column)"
                        >
                            {{ column.label }}
                            <span v-if="sort === column.key" class="text-brand">{{ direction === 'asc' ? '▲' : '▼' }}</span>
                        </th>
                        <th v-if="$slots.actions" class="px-4 py-3" />
                    </tr>
                </thead>

                <tbody class="divide-y divide-slate-100 text-slate-900">
                    <tr v-for="row in paginator.data" :key="row.id" class="hover:bg-slate-50">
                        <td v-for="column in columns" :key="column.key" class="px-4 py-3">
                            <slot :name="`cell-${column.key}`" :row="row">
                                {{ row[column.key] }}
                            </slot>
                        </td>
                        <td v-if="$slots.actions" class="px-4 py-3 text-end">
                            <slot name="actions" :row="row" />
                        </td>
                    </tr>

                    <tr v-if="paginator.data.length === 0">
                        <td :colspan="columns.length + 1" class="px-4 py-8 text-center text-slate-400">
                            {{ t('table.no_results') }}
                        </td>
                    </tr>
                </tbody>
            </table>
        </div>

        <Pagination :links="paginator.links" />
    </div>
</template>
