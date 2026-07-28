<script setup>
import { ref, computed } from 'vue';
import { router } from '@inertiajs/vue3';

const props = defineProps({
    visible: { type: Boolean, default: false },
    item: { type: Object, default: null },
    itemType: { type: String, default: null },
    folders: { type: Array, default: () => [] },
    allFolders: { type: Array, default: () => [] },
});

const emit = defineEmits(['close']);

const selectedFolderId = ref(null);

const availableFolders = computed(() => {
    const list = props.allFolders && props.allFolders.length > 0 ? props.allFolders : props.folders;
    // Filter out the item itself if we are moving a folder
    if (props.item && props.itemType === 'folder') {
        return list.filter(f => f.id !== props.item.id);
    }
    return list;
});

function submit() {
    if (!props.item) return;

    const routeName = props.itemType === 'folder' ? 'folders.move' : 'files.move';
    const data = props.itemType === 'folder'
        ? { parent_id: selectedFolderId.value }
        : { folder_id: selectedFolderId.value };

    router.post(route(routeName, props.item.id), data, {
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
                    <div v-if="visible" class="bg-white w-full max-w-[480px] rounded-2xl shadow-2xl border border-slate-200/30 overflow-hidden flex flex-col max-h-[500px]">
                        <div class="px-6 py-5 border-b border-slate-200/50 flex justify-between items-center shrink-0">
                            <div class="flex items-center gap-3">
                                <span class="material-symbols-outlined text-slate-400">drive_file_move</span>
                                <div>
                                    <h2 class="font-geist text-xl tracking-tight font-semibold text-slate-900">Move Item</h2>
                                    <p class="text-xs text-slate-500">Moving "{{ item?.name }}"</p>
                                </div>
                            </div>
                            <button class="text-slate-400 hover:text-slate-900 transition-colors" @click="emit('close')">
                                <span class="material-symbols-outlined">close</span>
                            </button>
                        </div>

                        <!-- Folder Tree -->
                        <div class="p-2 overflow-y-auto flex-grow bg-slate-50 scrollbar-thin">
                            <ul class="text-sm space-y-1 pb-4 p-2">
                                <!-- Root: My Files -->
                                <li
                                    class="flex items-center gap-2 px-4 py-2.5 rounded-lg cursor-pointer transition-colors"
                                    :class="selectedFolderId === null ? 'text-primary bg-primary/5 font-medium' : 'text-slate-500 hover:bg-slate-100'"
                                    @click="selectedFolderId = null"
                                >
                                    <span class="material-symbols-outlined text-sm">cloud</span>
                                    <span class="flex-grow">My Files (Root)</span>
                                    <span v-if="selectedFolderId === null" class="material-symbols-outlined text-sm text-primary">check</span>
                                </li>

                                <!-- Available Folders -->
                                <li
                                    v-for="folder in availableFolders"
                                    :key="folder.id"
                                    class="flex items-center gap-2 px-4 py-2.5 rounded-lg cursor-pointer ml-4 transition-colors"
                                    :class="[
                                        selectedFolderId === folder.id ? 'bg-blue-50 text-blue-700 border border-blue-200/50 font-medium' : 'text-slate-600 hover:bg-slate-100',
                                        item?.id === folder.id && itemType === 'folder' ? 'opacity-30 pointer-events-none' : ''
                                    ]"
                                    @click="selectedFolderId = folder.id"
                                >
                                    <span class="material-symbols-outlined text-sm" :class="selectedFolderId === folder.id ? 'text-primary' : 'text-slate-400'">folder</span>
                                    <span class="flex-grow">{{ folder.name }}</span>
                                    <span v-if="selectedFolderId === folder.id" class="material-symbols-outlined text-sm text-primary">check</span>
                                </li>
                            </ul>
                        </div>

                        <div class="px-6 py-4 bg-white border-t border-slate-200/50 flex justify-end gap-3 rounded-b-2xl shrink-0">
                            <button type="button" class="px-4 py-2 rounded-lg text-xs font-medium text-slate-500 hover:bg-slate-200/50 transition-colors" @click="emit('close')">Cancel</button>
                            <button type="button" class="px-6 py-2 bg-slate-900 text-white rounded-lg text-xs font-medium hover:bg-slate-800 transition-all shadow-sm" @click="submit">Move here</button>
                        </div>
                    </div>
                </Transition>
            </div>
        </Transition>
    </Teleport>
</template>
