<script setup>
/**
 * Core\UI — standard text input with label and validation error slot.
 * Works with Inertia's useForm: <TextInput v-model="form.email" :error="form.errors.email" />
 */
defineProps({
    label: String,
    type: { type: String, default: 'text' },
    error: String,
    autocomplete: String,
    required: Boolean,
});

const model = defineModel({ type: [String, Number], default: '' });
</script>

<template>
    <div>
        <label v-if="label" class="mb-1 block text-sm font-medium text-slate-700">
            {{ label }}
        </label>

        <input
            v-model="model"
            :type="type"
            :autocomplete="autocomplete"
            :required="required"
            :dir="type === 'email' ? 'ltr' : undefined"
            class="block w-full rounded-md border-0 px-3 py-2 text-slate-900 shadow-sm ring-1 ring-inset ring-slate-300 focus:ring-2 focus:ring-inset focus:ring-brand sm:text-sm"
            :class="{ 'ring-red-500': error }"
        />

        <p v-if="error" class="mt-1 text-sm text-red-600">{{ error }}</p>
    </div>
</template>
