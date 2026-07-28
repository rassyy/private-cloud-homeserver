<script setup>
import { Link } from '@inertiajs/vue3';

const props = defineProps({
    folders: { type: Array, default: () => [] },
    files: { type: Array, default: () => [] },
});

const emit = defineEmits(['context-menu', 'select', 'open-folder', 'preview-file']);
</script>

<template>
    <div class="grid grid-cols-2 md:grid-cols-3 lg:grid-cols-4 xl:grid-cols-5 gap-5">
        <!-- Folder Cards -->
        <div
            v-for="folder in folders"
            :key="'folder-' + folder.id"
            class="group bg-white border border-slate-200/50 rounded-2xl p-5 shadow-sm hover:shadow-lg hover:-translate-y-1 hover:border-slate-300/50 transition-all duration-300 ease-in-out cursor-pointer flex flex-col gap-4 relative"
            @click="emit('open-folder', folder)"
            @contextmenu.prevent="emit('context-menu', $event, folder, 'folder')"
        >
            <div class="flex justify-between items-start">
                <div class="p-2.5 bg-blue-50 text-blue-500 rounded-xl flex items-center justify-center">
                    <span class="material-symbols-outlined text-2xl" style="font-variation-settings: 'FILL' 1;">folder</span>
                </div>
                <div class="flex items-center gap-1">
                    <span
                        v-if="folder.is_starred"
                        class="material-symbols-outlined text-[18px] text-amber-400"
                        style="font-variation-settings: 'FILL' 1;"
                        title="Starred"
                    >star</span>
                    <button
                        class="opacity-0 group-hover:opacity-100 p-1.5 rounded-lg hover:bg-slate-100 text-slate-400 hover:text-slate-700 transition-all duration-300 ease-in-out"
                        @click.stop="emit('context-menu', $event, folder, 'folder')"
                    >
                        <span class="material-symbols-outlined text-[20px]">more_horiz</span>
                    </button>
                </div>
            </div>
            <div>
                <h3 class="font-geist text-sm tracking-tight font-semibold text-slate-900 truncate">{{ folder.name }}</h3>
                <p class="text-xs text-slate-500 mt-1 font-medium">{{ folder.items_count }} items</p>
            </div>
        </div>

        <!-- File Cards -->
        <div
            v-for="file in files"
            :key="'file-' + file.id"
            class="group bg-white border border-slate-200/50 rounded-2xl p-2 pb-4 shadow-sm hover:shadow-lg hover:-translate-y-1 hover:border-slate-300/50 transition-all duration-300 ease-in-out cursor-pointer flex flex-col gap-3 relative overflow-hidden"
            @click="emit('preview-file', file)"
            @contextmenu.prevent="emit('context-menu', $event, file, 'file')"
        >
            <!-- Thumbnail -->
            <div class="h-32 rounded-xl bg-slate-50 border border-slate-100 flex items-center justify-center overflow-hidden relative">
                <!-- Image Thumbnail -->
                <img
                    v-if="file.mime_type?.startsWith('image/')"
                    :src="route('files.preview', file.id)"
                    :alt="file.name"
                    class="w-full h-full object-cover transition-transform duration-300 group-hover:scale-105"
                    loading="lazy"
                />
                <!-- Video Thumbnail -->
                <video
                    v-else-if="file.mime_type?.startsWith('video/')"
                    :src="route('files.preview', file.id) + '#t=0.1'"
                    class="w-full h-full object-cover transition-transform duration-300 group-hover:scale-105"
                    preload="metadata"
                    muted
                    playsinline
                ></video>
                <!-- Fallback Generic Icon -->
                <div v-else class="text-slate-300">
                    <span class="material-symbols-outlined text-4xl" :class="file.icon_color" style="font-variation-settings: 'FILL' 1;">{{ file.icon }}</span>
                </div>
                <span
                    v-if="file.is_starred"
                    class="absolute top-2 left-2 material-symbols-outlined text-[18px] text-amber-400 drop-shadow-sm z-10"
                    style="font-variation-settings: 'FILL' 1;"
                    title="Starred"
                >star</span>
                <!-- File Action Menu Overlay -->
                <div class="absolute top-2 right-2 opacity-0 group-hover:opacity-100 transition-all duration-300 ease-in-out z-10">
                    <button
                        class="p-1.5 rounded-lg bg-white/90 backdrop-blur-sm shadow-sm hover:bg-white text-slate-600 transition-all duration-300 ease-in-out"
                        @click.stop="emit('context-menu', $event, file, 'file')"
                    >
                        <span class="material-symbols-outlined text-[18px]">more_horiz</span>
                    </button>
                </div>
            </div>
            <div class="px-3">
                <h3 class="font-geist text-sm tracking-tight font-semibold text-slate-900 truncate">{{ file.name }}</h3>
                <div class="flex justify-between items-center mt-1.5">
                    <p class="text-xs text-slate-500 font-medium">{{ file.size }}</p>
                    <span
                        v-if="file.is_starred"
                        class="material-symbols-outlined text-[14px] text-amber-400"
                        style="font-variation-settings: 'FILL' 1;"
                    >star</span>
                </div>
            </div>
        </div>
    </div>
</template>
