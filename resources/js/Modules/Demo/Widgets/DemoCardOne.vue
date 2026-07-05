<script setup>
/**
 * Modules\Demo — placeholder dashboard widget #1. Registered by
 * DemoServiceProvider::widgets(); shows the minimum a widget needs:
 * the descriptor arrives as the `widget` prop.
 */
import Card from '@/Core/Components/Card.vue';

defineProps({
    widget: Object,
});
</script>

<template>
    <Card :title="widget.title">
        <p class="text-sm leading-relaxed text-slate-600">
            این کارت از ماژول Demo آمده است. یک ماژول واقعی این‌جا دادهٔ خودش
            (آخرین رزروها، سرنخ‌های باز، …) را نشان می‌دهد.
        </p>
    </Card>
</template>
