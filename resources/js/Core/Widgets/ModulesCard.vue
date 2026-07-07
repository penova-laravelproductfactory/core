<script setup>
/**
 * Core widget — the Penova Modules card (key "core-modules-card",
 * order 900 so it stays at the bottom under module widgets).
 *
 * Points builders at the Module ecosystem — the layer where business
 * capability lives. Core stays free and complete; capability is added at
 * the edges as Modules, never inside Core.
 */
import Card from '@/Core/Components/Card.vue';

defineProps({
    widget: Object,
});
</script>

<template>
    <Card :title="widget.title">
        <p class="text-sm leading-relaxed text-slate-600">
            قابلیت‌های تجاری Penova به‌صورت <strong>ماژول</strong> اضافه
            می‌شوند (مثل فروشگاه، رزرو، CRM، CMS و …). هر ماژول ویجت‌هایش را با
            یک descriptor ساده از service provider خودش ثبت می‌کند و در همین
            grid ظاهر می‌شود — بدون دست‌زدن به Core.
        </p>
    </Card>
</template>
