<script setup>
/**
 * Modules\Store — the one-page checkout (auth required): cart summary,
 * read-only account block (the order snapshots the account identity
 * server-side), and the shipping form. Totals shown here are
 * informational; the server recomputes everything on submit.
 */
import { router, useForm } from '@inertiajs/vue3';
import StorefrontLayout from '@/Modules/Store/Components/StorefrontLayout.vue';
import TextInput from '@/Core/Components/TextInput.vue';
import Button from '@/Core/Components/Button.vue';

defineProps({
    account: Object, // { name, email } — the authenticated user
    lines: Array, // [{ product_id, name, price, quantity, subtotal }]
    total: [Number, String],
    cartCount: Number,
});

const form = useForm({
    customer_phone: '',
    shipping_address: '',
    notes: '',
});

const formatPrice = (price) => Number(price ?? 0).toLocaleString('fa-IR');

function removeLine(line) {
    router.post('/store/cart/remove', { product_id: line.product_id }, { preserveScroll: true });
}
</script>

<template>
    <StorefrontLayout title="تسویه حساب" :cart-count="cartCount">
        <h1 class="mb-6 text-2xl font-bold text-slate-900">تسویه حساب</h1>

        <div class="grid gap-6 lg:grid-cols-5">
            <!-- Cart summary -->
            <div class="lg:col-span-2">
                <div class="rounded-lg border border-slate-200 bg-white p-4">
                    <h2 class="mb-3 text-sm font-semibold text-slate-700">سبد خرید</h2>

                    <ul class="divide-y divide-slate-100">
                        <li v-for="line in lines" :key="line.product_id" class="flex items-center justify-between gap-3 py-2.5">
                            <div>
                                <div class="text-sm font-medium text-slate-900">{{ line.name }}</div>
                                <div class="text-xs text-slate-400">
                                    {{ line.quantity }} × {{ formatPrice(line.price) }}
                                </div>
                            </div>

                            <div class="flex shrink-0 items-center gap-3">
                                <span class="text-sm text-slate-700">{{ formatPrice(line.subtotal) }}</span>
                                <button class="text-xs text-red-600 hover:underline" @click="removeLine(line)">حذف</button>
                            </div>
                        </li>
                    </ul>

                    <div class="mt-3 flex items-center justify-between border-t border-slate-200 pt-3">
                        <span class="text-sm font-semibold text-slate-700">جمع کل</span>
                        <span class="font-bold text-slate-900">{{ formatPrice(total) }} تومان</span>
                    </div>

                    <p class="mt-2 text-xs leading-relaxed text-slate-400">
                        در این نسخه مالیات و هزینهٔ ارسال محاسبه نمی‌شود؛ پرداخت پس از ثبت
                        سفارش هماهنگ خواهد شد.
                    </p>
                </div>
            </div>

            <!-- Customer form -->
            <div class="lg:col-span-3">
                <form
                    class="space-y-4 rounded-lg border border-slate-200 bg-white p-4"
                    @submit.prevent="form.post('/store/checkout')"
                >
                    <h2 class="text-sm font-semibold text-slate-700">مشخصات شما</h2>

                    <!-- Account identity: read-only — the order records
                         exactly this account (single source of truth). -->
                    <div class="rounded-md bg-slate-50 px-3 py-2.5">
                        <div class="text-sm font-medium text-slate-900">{{ account.name }}</div>
                        <div class="text-xs text-slate-500" dir="ltr">{{ account.email }}</div>
                        <div class="mt-1 text-xs text-slate-400">سفارش با همین حساب کاربری ثبت می‌شود.</div>
                    </div>

                    <TextInput v-model="form.customer_phone" label="شمارهٔ تماس (اختیاری)" :error="form.errors.customer_phone" />

                    <div>
                        <label class="mb-1 block text-sm font-medium text-slate-700">آدرس ارسال</label>
                        <textarea
                            v-model="form.shipping_address"
                            rows="3"
                            required
                            class="block w-full rounded-md border-0 px-3 py-2 text-slate-900 shadow-sm ring-1 ring-inset ring-slate-300 focus:ring-2 focus:ring-inset focus:ring-brand sm:text-sm"
                            :class="{ 'ring-red-500': form.errors.shipping_address }"
                        />
                        <p v-if="form.errors.shipping_address" class="mt-1 text-sm text-red-600">{{ form.errors.shipping_address }}</p>
                    </div>

                    <div>
                        <label class="mb-1 block text-sm font-medium text-slate-700">توضیحات سفارش (اختیاری)</label>
                        <textarea
                            v-model="form.notes"
                            rows="2"
                            class="block w-full rounded-md border-0 px-3 py-2 text-slate-900 shadow-sm ring-1 ring-inset ring-slate-300 focus:ring-2 focus:ring-inset focus:ring-brand sm:text-sm"
                            :class="{ 'ring-red-500': form.errors.notes }"
                        />
                        <p v-if="form.errors.notes" class="mt-1 text-sm text-red-600">{{ form.errors.notes }}</p>
                    </div>

                    <Button type="submit" class="w-full" :disabled="form.processing">ثبت سفارش</Button>
                </form>
            </div>
        </div>
    </StorefrontLayout>
</template>
