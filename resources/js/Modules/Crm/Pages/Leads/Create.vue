<script setup>
/**
 * Modules\Crm — the "new lead" page, rendered by LeadCreateController
 * via Inertia::render('Modules/Crm/Leads/Create'). Posts to
 * crm.leads.store; validation errors land on form.errors.
 */
import { useForm } from '@inertiajs/vue3';
import AdminLayout from '@/Core/Layouts/AdminLayout.vue';
import PageHeader from '@/Core/Components/PageHeader.vue';
import Card from '@/Core/Components/Card.vue';
import Button from '@/Core/Components/Button.vue';
import TextInput from '@/Core/Components/TextInput.vue';

const props = defineProps({
    statuses: Array, // suggested pipeline stages from the controller
});

const form = useForm({
    name: '',
    email: '',
    status: 'new',
});

const statusLabels = {
    new: 'جدید',
    contacted: 'تماس‌گرفته‌شده',
    qualified: 'واجد شرایط',
};
</script>

<template>
    <AdminLayout title="سرنخ جدید">
        <PageHeader title="سرنخ جدید">
            <template #actions>
                <Button variant="secondary" href="/admin/leads">بازگشت به فهرست</Button>
            </template>
        </PageHeader>

        <Card class="max-w-lg">
            <form class="space-y-4" @submit.prevent="form.post('/admin/leads')">
                <TextInput v-model="form.name" label="نام" required :error="form.errors.name" />
                <TextInput v-model="form.email" label="ایمیل (اختیاری)" type="email" :error="form.errors.email" />

                <div>
                    <label class="mb-1 block text-sm font-medium text-slate-700">وضعیت</label>
                    <select
                        v-model="form.status"
                        class="block w-full rounded-md border-0 px-3 py-2 text-slate-900 shadow-sm ring-1 ring-inset ring-slate-300 focus:ring-2 focus:ring-inset focus:ring-brand sm:text-sm"
                        :class="{ 'ring-red-500': form.errors.status }"
                    >
                        <option v-for="status in props.statuses" :key="status" :value="status">
                            {{ statusLabels[status] ?? status }}
                        </option>
                    </select>
                    <p v-if="form.errors.status" class="mt-1 text-sm text-red-600">{{ form.errors.status }}</p>
                </div>

                <Button type="submit" :disabled="form.processing">ثبت سرنخ</Button>
            </form>
        </Card>
    </AdminLayout>
</template>
