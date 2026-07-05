<script setup>
/**
 * Modules\Store — products list on Core's DataTable: global search,
 * column sorting, pagination, PLUS two module filters (type / status)
 * passed through DataTable's `params` prop so every reload and
 * pagination link keeps them (?search&sort&direction&type&status).
 *
 * Backend contract: ProductIndexController (DataTableBuilder + when()
 * filters) sends `products` (paginator) and `filters` (echo of the
 * active query) — the selects initialise from it on shared links.
 */
import { ref } from 'vue';
import { Link, router } from '@inertiajs/vue3';
import AdminLayout from '@/Core/Layouts/AdminLayout.vue';
import PageHeader from '@/Core/Components/PageHeader.vue';
import DataTable from '@/Core/Components/DataTable.vue';
import Button from '@/Core/Components/Button.vue';
import Modal from '@/Core/Components/Modal.vue';

const props = defineProps({
    products: Object, // Laravel LengthAwarePaginator JSON
    filters: Object, // { search, type, status } echoed by the controller
});

const columns = [
    { key: 'name', label: 'نام', sortable: true },
    { key: 'type', label: 'نوع', sortable: true },
    { key: 'price', label: 'قیمت', sortable: true },
    { key: 'stock', label: 'موجودی' },
    { key: 'status', label: 'وضعیت' },
    // key = the real DB column (sort whitelist); the cell slot renders
    // the human-formatted value the backend ships alongside it.
    { key: 'updated_at', label: 'آخرین ویرایش', sortable: true },
];

// Filter selects — reactive object handed to DataTable's params prop;
// changing a value triggers the table reload with the new query string.
const filterType = ref(props.filters?.type ?? '');
const filterStatus = ref(props.filters?.status ?? '');

const typeLabels = {
    physical: 'فیزیکی',
    virtual: 'مجازی',
    downloadable: 'دانلودی',
};

const typeClasses = {
    physical: 'bg-sky-100 text-sky-700',
    virtual: 'bg-purple-100 text-purple-700',
    downloadable: 'bg-green-100 text-green-700',
};

const formatPrice = (price) => Number(price ?? 0).toLocaleString('fa-IR');

// Delete flow: Core Modal confirmation instead of the browser confirm().
const deleting = ref(null);

function confirmDelete() {
    router.delete(`/admin/store/products/${deleting.value.id}`, {
        preserveScroll: true,
        onFinish: () => (deleting.value = null),
    });
}
</script>

<template>
    <AdminLayout title="فروشگاه — محصولات">
        <PageHeader title="محصولات" subtitle="مدیریت محصولات فروشگاه">
            <template #actions>
                <Button href="/admin/store/products/create">محصول جدید</Button>
            </template>
        </PageHeader>

        <!-- Filter bar — travels with the DataTable query via :params. -->
        <div class="mb-4 flex flex-wrap items-center gap-3">
            <select
                v-model="filterType"
                class="rounded-md border-0 px-3 py-2 text-sm shadow-sm ring-1 ring-inset ring-slate-300 focus:ring-2 focus:ring-brand"
            >
                <option value="">همهٔ انواع</option>
                <option value="physical">فیزیکی</option>
                <option value="virtual">مجازی</option>
                <option value="downloadable">دانلودی</option>
            </select>

            <select
                v-model="filterStatus"
                class="rounded-md border-0 px-3 py-2 text-sm shadow-sm ring-1 ring-inset ring-slate-300 focus:ring-2 focus:ring-brand"
            >
                <option value="">همهٔ وضعیت‌ها</option>
                <option value="active">فعال</option>
                <option value="inactive">غیرفعال</option>
            </select>
        </div>

        <DataTable :paginator="products" :columns="columns" :params="{ type: filterType, status: filterStatus }">
            <!-- Name links straight to the edit page (fast path for admins). -->
            <template #cell-name="{ row }">
                <Link :href="`/admin/store/products/${row.id}/edit`" class="font-medium text-slate-900 hover:text-brand hover:underline">
                    {{ row.name }}
                </Link>
            </template>

            <template #cell-type="{ row }">
                <span class="rounded px-2 py-0.5 text-xs font-medium" :class="typeClasses[row.type] ?? 'bg-slate-100 text-slate-600'">
                    {{ typeLabels[row.type] ?? row.type }}
                </span>
            </template>

            <template #cell-price="{ row }">
                {{ formatPrice(row.price) }}
            </template>

            <template #cell-stock="{ row }">
                {{ row.type === 'physical' ? (row.stock ?? 0) : '—' }}
            </template>

            <template #cell-updated_at="{ row }">
                <span dir="ltr" class="text-slate-500">{{ row.updated_at_human }}</span>
            </template>

            <template #cell-status="{ row }">
                <span
                    class="rounded px-2 py-0.5 text-xs font-medium"
                    :class="row.is_active ? 'bg-green-100 text-green-700' : 'bg-slate-100 text-slate-500'"
                >
                    {{ row.is_active ? 'فعال' : 'غیرفعال' }}
                </span>
            </template>

            <template #actions="{ row }">
                <Link :href="`/admin/store/products/${row.id}/edit`" class="me-3 text-brand hover:underline">ویرایش</Link>
                <button class="text-red-600 hover:underline" @click="deleting = row">حذف</button>
            </template>
        </DataTable>

        <Modal :show="deleting !== null" title="حذف محصول" @close="deleting = null">
            <p class="text-sm leading-relaxed text-slate-600">
                محصول «{{ deleting?.name }}» برای همیشه حذف می‌شود. مطمئن هستید؟
            </p>

            <div class="mt-4 flex justify-end gap-2">
                <Button variant="secondary" @click="deleting = null">انصراف</Button>
                <Button variant="danger" @click="confirmDelete">حذف محصول</Button>
            </div>
        </Modal>
    </AdminLayout>
</template>
