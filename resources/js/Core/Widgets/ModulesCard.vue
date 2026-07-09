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
import { useI18n } from '@/Core/composables/i18n';

defineProps({
    widget: Object,
});

const { t } = useI18n();
</script>

<template>
    <Card :title="widget.title">
        <!-- Body carries one inline <strong>; the catalog value is
             developer-authored (not user input), so v-html is safe here. -->
        <p class="text-sm leading-relaxed text-slate-600" v-html="t('widgets.modules_body')" />
    </Card>
</template>
