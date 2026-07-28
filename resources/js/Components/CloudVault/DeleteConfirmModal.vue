<script setup>
import { router } from '@inertiajs/vue3';
import { computed } from 'vue';

const props = defineProps({
    visible: { type: Boolean, default: false },
    item: { type: Object, default: null },
    itemType: { type: String, default: null },
});

const emit = defineEmits(['close']);

const isPermanent = computed(() => props.itemType === 'force-delete-folder' || props.itemType === 'force-delete-file' || props.itemType === 'empty-trash');

function confirmDelete() {
    if (props.itemType === 'empty-trash') {
        router.post(route('folders.emptyTrash'), {}, {
            preserveScroll: true,
            onSuccess: () => emit('close'),
        });
        return;
    }

    if (!props.item) return;

    if (props.itemType === 'force-delete-folder') {
        router.delete(route('folders.forceDelete', props.item.id), {
            preserveScroll: true,
            onSuccess: () => emit('close'),
        });
        return;
    }

    if (props.itemType === 'force-delete-file') {
        router.delete(route('files.forceDelete', props.item.id), {
            preserveScroll: true,
            onSuccess: () => emit('close'),
        });
        return;
    }

    const routeName = props.itemType === 'folder' ? 'folders.destroy' : 'files.destroy';
    router.delete(route(routeName, props.item.id), {
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
                    <div v-if="visible" class="bg-white w-full max-w-[400px] rounded-2xl shadow-2xl border border-slate-200/30 overflow-hidden">
                        <div class="px-6 py-5 border-b border-slate-200/50 flex justify-between items-center">
                            <div class="flex items-center gap-3">
                                <span class="material-symbols-outlined text-red-500">warning</span>
                                <h2 class="font-geist text-xl tracking-tight font-semibold text-slate-900">
                                    {{ itemType === 'empty-trash' ? 'Empty Trash' : isPermanent ? 'Delete Permanently' : 'Delete ' + (itemType === 'folder' ? 'Folder' : 'File') }}
                                </h2>
                            </div>
                            <button class="text-slate-400 hover:text-slate-900 transition-colors" @click="emit('close')">
                                <span class="material-symbols-outlined">close</span>
                            </button>
                        </div>

                        <div class="p-6">
                            <p v-if="itemType === 'empty-trash'" class="text-sm text-slate-600">
                                Are you sure you want to permanently delete all items in the Trash? This action cannot be undone.
                            </p>
                            <p v-else-if="isPermanent" class="text-sm text-slate-600">
                                Are you sure you want to permanently delete <span class="font-semibold text-slate-900">"{{ item?.name }}"</span>? This action cannot be undone.
                            </p>
                            <p v-else class="text-sm text-slate-600">
                                Are you sure you want to move
                                <span class="font-semibold text-slate-900">"{{ item?.name }}"</span>
                                to Trash?
                            </p>
                            <p v-if="itemType === 'folder' || itemType === 'force-delete-folder'" class="text-xs text-slate-400 mt-2">
                                All files and subfolders inside this folder will also be {{ itemType === 'force-delete-folder' ? 'permanently deleted' : 'moved to Trash' }}.
                            </p>
                        </div>

                        <div class="px-6 py-4 bg-slate-50 border-t border-slate-200/50 flex justify-end gap-3 rounded-b-2xl">
                            <button
                                type="button"
                                class="px-4 py-2 rounded-lg text-xs font-medium text-slate-500 hover:bg-slate-200/50 transition-colors"
                                @click="emit('close')"
                            >Cancel</button>
                            <button
                                type="button"
                                class="px-6 py-2 bg-red-600 text-white rounded-lg text-xs font-medium hover:bg-red-700 transition-all shadow-sm"
                                @click="confirmDelete"
                            >{{ isPermanent ? 'Delete Permanently' : 'Move to Trash' }}</button>
                        </div>
                    </div>
                </Transition>
            </div>
        </Transition>
    </Teleport>
</template>
