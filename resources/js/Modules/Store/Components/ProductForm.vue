<script setup>
/**
 * Modules\Store — the shared product form (fields + submit), used by
 * both Pages/Products/Create.vue and Edit.vue so the two stay in sync.
 *
 * Receives the parent's Inertia useForm object and emits `submit`;
 * where the data goes (post vs put) stays with the page.
 *
 * Type-dependent fields: stock only for physical products,
 * download_url only for downloadable ones (mirrors the backend
 * required_if rule).
 */
import TextInput from '@/Core/Components/TextInput.vue';
import Button from '@/Core/Components/Button.vue';

defineProps({
    form: { type: Object, required: true }, // Inertia useForm object
    types: { type: Array, default: () => ['physical', 'virtual', 'downloadable'] },
    submitLabel: { type: String, required: true },
});

defineEmits(['submit']);

const typeLabels = {
    physical: 'فیزیکی',
    virtual: 'مجازی',
    downloadable: 'دانلودی',
};
</script>

<template>
    <form class="space-y-4" @submit.prevent="$emit('submit')">
        <TextInput v-model="form.name" label="نام محصول" required :error="form.errors.name" />

        <div>
            <label class="mb-1 block text-sm font-medium text-slate-700">
                شناسهٔ یکتا (slug)
            </label>
            <input
                v-model="form.slug"
                type="text"
                required
                dir="ltr"
                class="block w-full rounded-md border-0 px-3 py-2 text-slate-900 shadow-sm ring-1 ring-inset ring-slate-300 focus:ring-2 focus:ring-inset focus:ring-brand sm:text-sm"
                :class="{ 'ring-red-500': form.errors.slug }"
            />
            <p v-if="form.errors.slug" class="mt-1 text-sm text-red-600">{{ form.errors.slug }}</p>
        </div>

        <div>
            <label class="mb-1 block text-sm font-medium text-slate-700">نوع محصول</label>
            <select
                v-model="form.type"
                class="block w-full rounded-md border-0 px-3 py-2 text-slate-900 shadow-sm ring-1 ring-inset ring-slate-300 focus:ring-2 focus:ring-inset focus:ring-brand sm:text-sm"
                :class="{ 'ring-red-500': form.errors.type }"
            >
                <option v-for="type in types" :key="type" :value="type">
                    {{ typeLabels[type] ?? type }}
                </option>
            </select>
            <p v-if="form.errors.type" class="mt-1 text-sm text-red-600">{{ form.errors.type }}</p>
        </div>

        <div>
            <label class="mb-1 block text-sm font-medium text-slate-700">قیمت (تومان)</label>
            <input
                v-model="form.price"
                type="number"
                min="0"
                step="0.01"
                required
                dir="ltr"
                class="block w-full rounded-md border-0 px-3 py-2 text-slate-900 shadow-sm ring-1 ring-inset ring-slate-300 focus:ring-2 focus:ring-inset focus:ring-brand sm:text-sm"
                :class="{ 'ring-red-500': form.errors.price }"
            />
            <p v-if="form.errors.price" class="mt-1 text-sm text-red-600">{{ form.errors.price }}</p>
        </div>

        <TextInput v-model="form.sku" label="کد محصول (SKU — اختیاری)" :error="form.errors.sku" />

        <!-- Stock: physical products only -->
        <div v-if="form.type === 'physical'">
            <label class="mb-1 block text-sm font-medium text-slate-700">موجودی انبار</label>
            <input
                v-model="form.stock"
                type="number"
                min="0"
                dir="ltr"
                class="block w-full rounded-md border-0 px-3 py-2 text-slate-900 shadow-sm ring-1 ring-inset ring-slate-300 focus:ring-2 focus:ring-inset focus:ring-brand sm:text-sm"
                :class="{ 'ring-red-500': form.errors.stock }"
            />
            <p v-if="form.errors.stock" class="mt-1 text-sm text-red-600">{{ form.errors.stock }}</p>
        </div>

        <!-- Download URL: downloadable products only -->
        <div v-if="form.type === 'downloadable'">
            <label class="mb-1 block text-sm font-medium text-slate-700">لینک دانلود</label>
            <input
                v-model="form.download_url"
                type="url"
                required
                dir="ltr"
                placeholder="https://…"
                class="block w-full rounded-md border-0 px-3 py-2 text-slate-900 shadow-sm ring-1 ring-inset ring-slate-300 focus:ring-2 focus:ring-inset focus:ring-brand sm:text-sm"
                :class="{ 'ring-red-500': form.errors.download_url }"
            />
            <p v-if="form.errors.download_url" class="mt-1 text-sm text-red-600">{{ form.errors.download_url }}</p>
        </div>

        <div>
            <label class="mb-1 block text-sm font-medium text-slate-700">توضیحات (اختیاری)</label>
            <textarea
                v-model="form.description"
                rows="4"
                class="block w-full rounded-md border-0 px-3 py-2 text-slate-900 shadow-sm ring-1 ring-inset ring-slate-300 focus:ring-2 focus:ring-inset focus:ring-brand sm:text-sm"
                :class="{ 'ring-red-500': form.errors.description }"
            />
            <p v-if="form.errors.description" class="mt-1 text-sm text-red-600">{{ form.errors.description }}</p>
        </div>

        <label class="flex items-center gap-2 text-sm text-slate-700">
            <input v-model="form.is_active" type="checkbox" class="rounded border-slate-300" />
            محصول فعال است (در فروشگاه نمایش داده شود)
        </label>

        <Button type="submit" :disabled="form.processing">{{ submitLabel }}</Button>
    </form>
</template>
