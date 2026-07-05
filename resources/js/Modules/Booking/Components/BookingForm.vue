<script setup>
/**
 * Modules\Booking — the shared booking form (fields + submit), used by
 * both Pages/Create.vue and Pages/Edit.vue so the two stay in sync.
 *
 * Receives the parent's Inertia useForm object and emits `submit`;
 * where the data goes (post vs put) stays with the page.
 *
 * starts_at is a plain datetime-local input for now — it will be
 * replaced by the Persian (Jalali) datepicker module later.
 */
import TextInput from '@/Core/Components/TextInput.vue';
import Button from '@/Core/Components/Button.vue';

defineProps({
    form: { type: Object, required: true }, // Inertia useForm object
    statuses: { type: Array, default: () => ['pending', 'confirmed', 'cancelled'] },
    submitLabel: { type: String, required: true },
});

defineEmits(['submit']);

// Persian labels for the raw status values the backend sends
// (BookingStatus::values()); unknown values fall back to the raw string.
const statusLabels = {
    pending: 'در انتظار',
    confirmed: 'تأییدشده',
    cancelled: 'لغوشده',
};
</script>

<template>
    <form class="space-y-4" @submit.prevent="$emit('submit')">
        <TextInput v-model="form.customer_name" label="نام مشتری" required :error="form.errors.customer_name" />

        <div>
            <label class="mb-1 block text-sm font-medium text-slate-700">زمان شروع رزرو</label>
            <input
                v-model="form.starts_at"
                type="datetime-local"
                required
                dir="ltr"
                class="block w-full rounded-md border-0 px-3 py-2 text-slate-900 shadow-sm ring-1 ring-inset ring-slate-300 focus:ring-2 focus:ring-inset focus:ring-brand sm:text-sm"
                :class="{ 'ring-red-500': form.errors.starts_at }"
            />
            <p v-if="form.errors.starts_at" class="mt-1 text-sm text-red-600">{{ form.errors.starts_at }}</p>
        </div>

        <div>
            <label class="mb-1 block text-sm font-medium text-slate-700">وضعیت</label>
            <select
                v-model="form.status"
                class="block w-full rounded-md border-0 px-3 py-2 text-slate-900 shadow-sm ring-1 ring-inset ring-slate-300 focus:ring-2 focus:ring-inset focus:ring-brand sm:text-sm"
                :class="{ 'ring-red-500': form.errors.status }"
            >
                <option v-for="status in statuses" :key="status" :value="status">
                    {{ statusLabels[status] ?? status }}
                </option>
            </select>
            <p v-if="form.errors.status" class="mt-1 text-sm text-red-600">{{ form.errors.status }}</p>
        </div>

        <Button type="submit" :disabled="form.processing">{{ submitLabel }}</Button>
    </form>
</template>
