<script setup>
/**
 * Core\UI — multiline text input with label and validation error, mirroring
 * TextInput. Works with Inertia's useForm:
 * <TextArea v-model="form.notes" :error="form.errors.notes" :rows="3" />
 */
defineProps({
    label: String,
    rows: { type: [String, Number], default: 3 },
    error: String,
    required: Boolean,
});

const model = defineModel({ type: String, default: '' });
</script>

<template>
    <div>
        <label v-if="label" class="mb-1 block text-sm font-medium text-slate-700">
            {{ label }}
        </label>

        <textarea
            v-model="model"
            :rows="rows"
            :required="required"
            class="block w-full rounded-md border-0 px-3 py-2 text-slate-900 shadow-sm ring-1 ring-inset ring-slate-300 focus:ring-2 focus:ring-inset focus:ring-brand sm:text-sm"
            :class="{ 'ring-red-500': error }"
        />

        <p v-if="error" class="mt-1 text-sm text-red-600">{{ error }}</p>
    </div>
</template>
