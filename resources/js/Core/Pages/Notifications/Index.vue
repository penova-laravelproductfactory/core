<script setup>
/**
 * Core\Notifications — the user's notification feed. Any module's
 * database notifications appear here; "data.message" is the display
 * convention every notification class should follow.
 */
import { router } from '@inertiajs/vue3';
import WorkspaceLayout from '@/Core/Layouts/WorkspaceLayout.vue';
import PageHeader from '@/Core/Components/PageHeader.vue';
import Pagination from '@/Core/Components/Pagination.vue';
import Button from '@/Core/Components/Button.vue';
import { useWorkspacePath } from '@/Core/composables/workspacePath';

defineProps({ notifications: Object });

const ws = useWorkspacePath();
const markRead = (id) => router.put(ws(`/notifications/${id}/read`), {}, { preserveScroll: true });
</script>

<template>
    <WorkspaceLayout title="اعلان‌ها">
        <PageHeader title="اعلان‌ها" subtitle="اعلان‌های حساب کاربری شما">
            <template #actions>
                <Button variant="secondary" @click="markRead('all')">علامت‌گذاری همه به‌عنوان خوانده‌شده</Button>
            </template>
        </PageHeader>

        <div class="space-y-2">
            <div
                v-for="notification in notifications.data"
                :key="notification.id"
                class="flex items-center justify-between rounded-lg bg-white p-4 shadow-sm"
                :class="{ 'opacity-60': notification.read_at }"
            >
                <div class="space-y-1">
                    <div class="text-sm leading-relaxed text-slate-900">
                        {{ notification.data.message ?? notification.type }}
                    </div>
                    <div class="text-xs text-slate-400">{{ notification.created_at }}</div>
                </div>

                <button
                    v-if="!notification.read_at"
                    class="shrink-0 text-sm text-brand hover:underline"
                    @click="markRead(notification.id)"
                >
                    خوانده شد
                </button>
            </div>

            <p v-if="notifications.data.length === 0" class="py-8 text-center text-slate-400">
                فعلاً اعلانی ندارید.
            </p>
        </div>

        <div class="mt-4">
            <Pagination :links="notifications.links" />
        </div>
    </WorkspaceLayout>
</template>
