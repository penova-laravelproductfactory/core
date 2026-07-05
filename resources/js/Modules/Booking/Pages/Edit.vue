<script setup>
/**
 * Modules\Booking — the "edit booking" page, rendered by
 * BookingEditController via Inertia::render('Modules/Booking/Edit').
 * Same shared form as Create; submits with PUT to booking.update.
 */
import { useForm } from '@inertiajs/vue3';
import AdminLayout from '@/Core/Layouts/AdminLayout.vue';
import PageHeader from '@/Core/Components/PageHeader.vue';
import Card from '@/Core/Components/Card.vue';
import Button from '@/Core/Components/Button.vue';
import BookingForm from '@/Modules/Booking/Components/BookingForm.vue';

const props = defineProps({
    booking: Object, // { id, customer_name, starts_at (ISO), status }
    statuses: Array, // BookingStatus::values() from the controller
});

const form = useForm({
    customer_name: props.booking.customer_name,
    // ISO from Laravel → the "YYYY-MM-DDTHH:mm" a datetime-local expects.
    starts_at: (props.booking.starts_at ?? '').slice(0, 16),
    status: props.booking.status,
});
</script>

<template>
    <AdminLayout :title="`ویرایش رزرو — ${booking.customer_name}`">
        <PageHeader title="ویرایش رزرو" :subtitle="booking.customer_name">
            <template #actions>
                <Button variant="secondary" href="/admin/bookings">بازگشت به فهرست</Button>
            </template>
        </PageHeader>

        <Card class="max-w-lg">
            <BookingForm
                :form="form"
                :statuses="props.statuses"
                submit-label="ذخیره تغییرات"
                @submit="form.put(`/admin/bookings/${props.booking.id}`)"
            />
        </Card>
    </AdminLayout>
</template>
