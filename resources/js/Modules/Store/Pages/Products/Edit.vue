<script setup>
/**
 * Modules\Store — the "edit product" page, rendered by
 * ProductEditController via Inertia::render('Modules/Store/Products/Edit').
 * Same shared form as Create; submits with PUT to store.products.update.
 */
import { useForm } from '@inertiajs/vue3';
import AdminLayout from '@/Core/Layouts/AdminLayout.vue';
import PageHeader from '@/Core/Components/PageHeader.vue';
import Card from '@/Core/Components/Card.vue';
import Button from '@/Core/Components/Button.vue';
import ProductForm from '@/Modules/Store/Components/ProductForm.vue';
import { useWorkspacePath } from '@/Core/composables/workspacePath';

const ws = useWorkspacePath();

const props = defineProps({
    product: Object, // { id, name, slug, type, price, sku, stock, is_active, description, download_url }
    types: Array, // ProductType::values() from the controller
});

const form = useForm({
    name: props.product.name,
    slug: props.product.slug,
    type: props.product.type,
    price: props.product.price,
    sku: props.product.sku ?? '',
    stock: props.product.stock,
    is_active: Boolean(props.product.is_active),
    description: props.product.description ?? '',
    download_url: props.product.download_url ?? '',
});
</script>

<template>
    <AdminLayout :title="`ویرایش محصول — ${product.name}`">
        <PageHeader title="ویرایش محصول" :subtitle="product.name">
            <template #actions>
                <Button variant="secondary" :href="ws('/store/products')">بازگشت به فهرست</Button>
            </template>
        </PageHeader>

        <Card class="max-w-lg">
            <ProductForm
                :form="form"
                :types="props.types"
                mode="edit"
                @submit="form.put(ws(`/store/products/${props.product.id}`))"
            />
        </Card>
    </AdminLayout>
</template>
