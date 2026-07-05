<script setup>
/**
 * Modules\Store — products list, rendered by ProductIndexController via
 * Inertia::render('Modules/Store/Products/Index'). Standard module CRUD
 * table (same pattern as Booking/Crm).
 */
import { Link, router } from '@inertiajs/vue3';
import AdminLayout from '@/Core/Layouts/AdminLayout.vue';
import PageHeader from '@/Core/Components/PageHeader.vue';
import Button from '@/Core/Components/Button.vue';
import Pagination from '@/Core/Components/Pagination.vue';

defineProps({
    products: Object, // Laravel LengthAwarePaginator JSON
});

const typeLabels = {
    physical: 'فیزیکی',
    virtual: 'مجازی',
    downloadable: 'دانلودی',
};

function destroy(product) {
    if (confirm(`محصول «${product.name}» حذف شود؟`)) {
        router.delete(`/admin/store/products/${product.id}`, { preserveScroll: true });
    }
}
</script>

<template>
    <AdminLayout title="فروشگاه — محصولات">
        <PageHeader title="محصولات" subtitle="مدیریت محصولات فروشگاه">
            <template #actions>
                <Button href="/admin/store/products/create">محصول جدید</Button>
            </template>
        </PageHeader>

        <div class="space-y-4">
            <div class="overflow-x-auto rounded-lg bg-white shadow">
                <table class="min-w-full divide-y divide-slate-200 text-sm">
                    <thead class="bg-slate-100 text-slate-700">
                        <tr>
                            <th class="px-4 py-3 text-start font-semibold">نام</th>
                            <th class="px-4 py-3 text-start font-semibold">نوع</th>
                            <th class="px-4 py-3 text-start font-semibold">قیمت</th>
                            <th class="px-4 py-3 text-start font-semibold">موجودی</th>
                            <th class="px-4 py-3 text-start font-semibold">وضعیت</th>
                            <th class="px-4 py-3" />
                        </tr>
                    </thead>

                    <tbody class="divide-y divide-slate-100 text-slate-900">
                        <tr v-for="product in products.data" :key="product.id" class="hover:bg-slate-50">
                            <td class="px-4 py-3">{{ product.name }}</td>
                            <td class="px-4 py-3">
                                <span class="rounded bg-slate-100 px-2 py-0.5 text-xs font-medium text-slate-600">
                                    {{ typeLabels[product.type] ?? product.type }}
                                </span>
                            </td>
                            <td class="px-4 py-3" dir="ltr">{{ product.price }}</td>
                            <td class="px-4 py-3">{{ product.type === 'physical' ? (product.stock ?? 0) : '—' }}</td>
                            <td class="px-4 py-3">
                                <span
                                    class="rounded px-2 py-0.5 text-xs font-medium"
                                    :class="product.is_active ? 'bg-green-100 text-green-700' : 'bg-slate-100 text-slate-500'"
                                >
                                    {{ product.is_active ? 'فعال' : 'غیرفعال' }}
                                </span>
                            </td>
                            <td class="px-4 py-3 text-end">
                                <Link
                                    :href="`/admin/store/products/${product.id}/edit`"
                                    class="me-3 text-brand hover:underline"
                                >
                                    ویرایش
                                </Link>
                                <button class="text-red-600 hover:underline" @click="destroy(product)">حذف</button>
                            </td>
                        </tr>

                        <tr v-if="products.data.length === 0">
                            <td colspan="6" class="px-4 py-8 text-center text-slate-400">
                                هنوز محصولی ثبت نشده است.
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>

            <Pagination :links="products.links" />
        </div>
    </AdminLayout>
</template>
