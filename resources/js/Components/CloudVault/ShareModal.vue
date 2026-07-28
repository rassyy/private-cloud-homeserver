<script setup>
import { watch } from 'vue';
import { useForm } from '@inertiajs/vue3';

const props = defineProps({
    visible: { type: Boolean, default: false },
    item: { type: Object, default: null },
    itemType: { type: String, default: null },
});

const emit = defineEmits(['close']);

const form = useForm({
    email: '',
    permission: 'view',
});

watch(() => props.visible, (val) => {
    if (val) {
        form.reset();
        form.clearErrors();
    }
});

function submit() {
    if (!props.item) return;
    const routeName = props.itemType === 'folder' ? 'folders.share' : 'files.share';
    form.post(route(routeName, props.item.id), {
        preserveScroll: true,
        onSuccess: () => emit('close'),
    });
}
</script>

<template>
    <Teleport to="body">
        <Transition
            enter-active-class="transition-opacity duration-300"
            enter-from-class="opacity-0"
            enter-to-class="opacity-100"
            leave-active-class="transition-opacity duration-300"
            leave-from-class="opacity-100"
            leave-to-class="opacity-0"
        >
            <div
                v-if="visible"
                class="fixed inset-0 z-50 flex items-center justify-center glass-overlay"
                @click.self="emit('close')"
            >
                <Transition
                    enter-active-class="transition-all duration-300 ease-out"
                    enter-from-class="opacity-0 scale-95 translate-y-4"
                    enter-to-class="opacity-100 scale-100 translate-y-0"
                    leave-active-class="transition-all duration-200 ease-in"
                    leave-from-class="opacity-100 scale-100 translate-y-0"
                    leave-to-class="opacity-0 scale-95 translate-y-4"
                >
                    <div v-if="visible" class="bg-white w-full max-w-[420px] rounded-2xl shadow-2xl border border-slate-200/30 overflow-hidden">
                        <div class="px-6 py-5 border-b border-slate-200/50 flex justify-between items-center">
                            <div class="flex items-center gap-3">
                                <span class="material-symbols-outlined text-blue-500" style="font-variation-settings: 'FILL' 1;">share</span>
                                <h2 class="font-geist text-xl tracking-tight font-semibold text-slate-900">Share Item</h2>
                            </div>
                            <button class="text-slate-400 hover:text-slate-900 transition-colors" @click="emit('close')">
                                <span class="material-symbols-outlined">close</span>
                            </button>
                        </div>

                        <form @submit.prevent="submit">
                            <div class="p-6 space-y-4">
                                <div class="p-3 bg-slate-50 border border-slate-200/60 rounded-xl flex items-center gap-3">
                                    <span class="material-symbols-outlined text-slate-500">{{ itemType === 'folder' ? 'folder' : 'description' }}</span>
                                    <div class="truncate">
                                        <p class="text-xs font-medium text-slate-500 uppercase">Sharing</p>
                                        <p class="text-sm font-semibold text-slate-900 truncate">{{ item?.name }}</p>
                                    </div>
                                </div>

                                <div class="flex flex-col gap-2">
                                    <label class="text-xs font-medium text-slate-500" for="shareEmailInput">User Email</label>
                                    <input
                                        id="shareEmailInput"
                                        v-model="form.email"
                                        class="w-full px-4 py-3 bg-slate-50 rounded-xl border border-slate-200 focus:border-slate-900 focus:ring-1 focus:ring-slate-900 outline-none transition-all text-sm text-slate-900 placeholder:text-slate-400"
                                        type="email"
                                        placeholder="user@example.com"
                                        required
                                    />
                                    <p class="text-xs text-slate-400">Enter the registered email address of the person to share with.</p>
                                    <p v-if="form.errors.email" class="text-xs text-red-500 mt-1">{{ form.errors.email }}</p>
                                </div>

                                <div class="flex flex-col gap-2">
                                    <label class="text-xs font-medium text-slate-500" for="sharePermission">Permission</label>
                                    <select
                                        id="sharePermission"
                                        v-model="form.permission"
                                        class="w-full px-4 py-3 bg-slate-50 rounded-xl border border-slate-200 focus:border-slate-900 focus:ring-1 focus:ring-slate-900 outline-none transition-all text-sm text-slate-900"
                                    >
                                        <option value="view">Viewer (Can view and download)</option>
                                        <option value="edit">Editor (Can edit and manage)</option>
                                    </select>
                                    <p v-if="form.errors.permission" class="text-xs text-red-500 mt-1">{{ form.errors.permission }}</p>
                                </div>
                            </div>

                            <div class="px-6 py-4 bg-slate-50 border-t border-slate-200/50 flex justify-end gap-3 rounded-b-2xl">
                                <button type="button" class="px-4 py-2 rounded-lg text-xs font-medium text-slate-500 hover:bg-slate-200/50 transition-colors" @click="emit('close')">Cancel</button>
                                <button type="submit" class="px-6 py-2 bg-slate-900 text-white rounded-lg text-xs font-medium hover:bg-slate-800 transition-all shadow-sm" :disabled="form.processing">Share</button>
                            </div>
                        </form>
                    </div>
                </Transition>
            </div>
        </Transition>
    </Teleport>
</template>
