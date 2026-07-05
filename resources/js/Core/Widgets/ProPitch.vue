<script setup>
/**
 * Core widget — the Modules / Pro pitch card (key "core-pro-pitch",
 * order 900 so it stays at the bottom under module widgets).
 */
import Card from '@/Core/Components/Card.vue';
import Button from '@/Core/Components/Button.vue';

defineProps({
    widget: Object,
});
</script>

<template>
    <Card :title="widget.title">
        <p class="text-sm leading-relaxed text-slate-600">
            این صفحه در نسخه Pro و ماژول‌های Penova (مثل رزرو، CRM، CMS و …) با
            ویجت‌های بیشتری پر می‌شود. هر ماژول ویجت‌هایش را با یک descriptor
            ساده از service provider خودش ثبت می‌کند و در همین grid ظاهر
            می‌شود — بدون دست‌زدن به Core.
        </p>

        <div class="mt-4">
            <!-- Placeholder CTA: پس از آماده‌شدن صفحه/سایت Penova Pro،
                 این لینک با آدرس واقعی جایگزین می‌شود. -->
            <Button href="/admin/settings">دیدن امکانات Penova Pro</Button>
        </div>
    </Card>
</template>
