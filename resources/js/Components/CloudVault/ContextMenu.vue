<script setup>
import { onMounted, onUnmounted } from 'vue';

const props = defineProps({
    visible: { type: Boolean, required: true },
    position: { type: Object, default: () => ({ x: 0, y: 0 }) },
    item: { type: Object, default: null },
    itemType: { type: String, default: null },
    viewMode: { type: String, default: 'normal' },
});

const emit = defineEmits(['close', 'rename', 'download', 'move', 'star', 'details', 'delete', 'restore', 'force-delete', 'share']);

function handleClickOutside() {
    emit('close');
}

onMounted(() => {
    document.addEventListener('click', handleClickOutside);
    document.addEventListener('contextmenu', handleClickOutside);
});

onUnmounted(() => {
    document.removeEventListener('click', handleClickOutside);
    document.removeEventListener('contextmenu', handleClickOutside);
});
</script>

<template>
    <Teleport to="body">
        <Transition
            enter-active-class="transition duration-150 ease-out"
            enter-from-class="opacity-0 scale-95"
            enter-to-class="opacity-100 scale-100"
            leave-active-class="transition duration-100 ease-in"
            leave-from-class="opacity-100 scale-100"
            leave-to-class="opacity-0 scale-95"
        >
            <div
                v-if="visible && item"
                class="fixed w-48 bg-white/90 backdrop-blur-xl border border-slate-200/50 shadow-lg rounded-xl py-2 z-[100] flex flex-col text-sm"
                :style="{ left: position.x + 'px', top: position.y + 'px' }"
                @click.stop
            >
                <template v-if="viewMode === 'trash'">
                    <button
                        class="flex items-center gap-3 px-4 py-2 hover:bg-slate-50 text-slate-700 transition-colors w-full text-left font-medium"
                        @click="emit('restore')"
                    >
                        <span class="material-symbols-outlined text-[18px] text-primary">restore_from_trash</span>
                        Restore
                    </button>
                    <div class="h-px bg-slate-100 my-1 w-full"></div>
                    <button
                        class="flex items-center gap-3 px-4 py-2 hover:bg-red-50 hover:text-red-600 text-red-500 transition-colors w-full text-left font-medium"
                        @click="emit('force-delete')"
                    >
                        <span class="material-symbols-outlined text-[18px]">delete_forever</span>
                        Delete Permanently
                    </button>
                </template>

                <template v-else>
                    <button
                        class="flex items-center gap-3 px-4 py-2 hover:bg-slate-50 text-slate-700 transition-colors w-full text-left"
                        @click="emit('rename')"
                    >
                        <span class="material-symbols-outlined text-[18px]">edit</span>
                        Rename
                    </button>

                    <button
                        v-if="itemType === 'file'"
                        class="flex items-center gap-3 px-4 py-2 hover:bg-slate-50 text-slate-700 transition-colors w-full text-left"
                        @click="emit('download')"
                    >
                        <span class="material-symbols-outlined text-[18px]">download</span>
                        Download
                    </button>

                    <button
                        class="flex items-center gap-3 px-4 py-2 hover:bg-slate-50 text-slate-700 transition-colors w-full text-left"
                        @click="emit('move')"
                    >
                        <span class="material-symbols-outlined text-[18px]">drive_file_move</span>
                        Move
                    </button>

                    <button
                        class="flex items-center gap-3 px-4 py-2 hover:bg-slate-50 text-slate-700 transition-colors w-full text-left"
                        @click="emit('share')"
                    >
                        <span class="material-symbols-outlined text-[18px]">share</span>
                        Share
                    </button>

                    <button
                        class="flex items-center gap-3 px-4 py-2 hover:bg-slate-50 text-slate-700 transition-colors w-full text-left border-b border-slate-100 pb-3"
                        @click="emit('star')"
                    >
                        <span class="material-symbols-outlined text-[18px]">{{ item.is_starred ? 'star' : 'star_border' }}</span>
                        {{ item.is_starred ? 'Unstar' : 'Star' }}
                    </button>

                    <button
                        class="flex items-center gap-3 px-4 py-2 hover:bg-slate-50 text-slate-700 transition-colors w-full text-left pt-3"
                        @click="emit('details')"
                    >
                        <span class="material-symbols-outlined text-[18px]">info</span>
                        Details
                    </button>

                    <div class="h-px bg-slate-100 my-1 w-full"></div>

                    <button
                        class="flex items-center gap-3 px-4 py-2 hover:bg-red-50 hover:text-red-600 text-red-500 transition-colors w-full text-left"
                        @click="emit('delete')"
                    >
                        <span class="material-symbols-outlined text-[18px]">delete</span>
                        Delete
                    </button>
                </template>
            </div>
        </Transition>
    </Teleport>
</template>
