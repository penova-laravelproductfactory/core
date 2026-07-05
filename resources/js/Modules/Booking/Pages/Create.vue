<script setup>
/**
 * Modules\Booking — the "new booking" page, rendered by
 * BookingCreateController via Inertia::render('Modules/Booking/Create').
 * Posts to booking.store; validation errors land on form.errors.
 */
import { useForm } from '@inertiajs/vue3';
import AdminLayout from '@/Core/Layouts/AdminLayout.vue';
import PageHeader from '@/Core/Components/PageHeader.vue';
import Card from '@/Core/Components/Card.vue';
import Button from '@/Core/Components/Button.vue';
import BookingForm from '@/Modules/Booking/Components/BookingForm.vue';

const props = defineProps({
    statuses: Array, // BookingStatus::values() from the controller
});

const form = useForm({
    customer_name: '',
    starts_at: '',
    status: 'pending',
});
</script>

<template>
    <AdminLayout title="رزرو جدید">
        <PageHeader title="رزرو جدید">
            <template #actions>
                <Button variant="secondary" href="/admin/bookings">بازگشت به فهرست</Button>
            </template>
        </PageHeader>

        <Card class="max-w-lg">
            <BookingForm
                :form="form"
                :statuses="props.statuses"
                submit-label="ثبت رزرو"
                @submit="form.post('/admin/bookings')"
            />
        </Card>
    </AdminLayout>
</template>
