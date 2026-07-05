<script setup>
/**
 * Modules\Demo — landing page, rendered by ShowDemoController via
 * Inertia::render('Modules/Demo/Index'). Uses the shared Core layout
 * and components exactly the way a real product module would.
 */
import AdminLayout from '@/Core/Layouts/AdminLayout.vue';
import PageHeader from '@/Core/Components/PageHeader.vue';
import Card from '@/Core/Components/Card.vue';
</script>

<template>
    <AdminLayout title="نمونه">
        <PageHeader title="ماژول نمونه" subtitle="نمایش قرارداد ماژول‌های Penova" />

        <Card title="این صفحه از کجا آمد؟">
            <p class="text-sm leading-relaxed text-slate-600">
                این صفحه، آیتم «نمونه» در منوی کنار، و دو ویجت روی داشبورد، همگی
                توسط <span dir="ltr">DemoServiceProvider</span> ثبت شده‌اند — بدون
                هیچ تغییری در Core. برای ساخت ماژول واقعی، پوشهٔ
                <span dir="ltr">app/Modules/Demo</span> را کپی کنید و از
                <span dir="ltr">app/Modules/README.md</span> شروع کنید.
            </p>
        </Card>
    </AdminLayout>
</template>
