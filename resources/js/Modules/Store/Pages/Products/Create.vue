<script setup>
/**
 * Modules\Store — the "new product" page, rendered by
 * ProductCreateController via Inertia::render('Modules/Store/Products/Create').
 * Posts to store.products.store; validation errors land on form.errors.
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
    types: Array, // ProductType::values() from the controller
});

const form = useForm({
    name: '',
    slug: '',
    type: 'physical',
    price: '',
    sku: '',
    stock: null,
    is_active: true,
    description: '',
    download_url: '',
});
</script>

<template>
    <AdminLayout title="محصول جدید">
        <PageHeader title="محصول جدید">
            <template #actions>
                <Button variant="secondary" :href="ws('/store/products')">بازگشت به فهرست</Button>
            </template>
        </PageHeader>

        <Card class="max-w-lg">
            <ProductForm
                :form="form"
                :types="props.types"
                mode="create"
                @submit="form.post(ws('/store/products'))"
            />
        </Card>
    </AdminLayout>
</template>
