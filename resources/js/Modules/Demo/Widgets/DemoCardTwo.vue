<script setup>
/**
 * Modules\Demo — placeholder dashboard widget #2: same pattern as #1,
 * proving several widgets from one module coexist on the grid.
 */
import Card from '@/Core/Components/Card.vue';

defineProps({
    widget: Object,
});
</script>

<template>
    <Card :title="widget.title">
        <p class="text-sm leading-relaxed text-slate-600">
            ویجت دوم ماژول Demo — با <span dir="ltr">order</span> بزرگ‌تر، پس
            بعد از ویجت اول در grid می‌نشیند.
        </p>
    </Card>
</template>
