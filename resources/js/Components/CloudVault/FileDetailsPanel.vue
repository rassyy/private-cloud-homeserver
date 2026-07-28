<script setup>
import { ref, watch } from 'vue';
import axios from 'axios';

const props = defineProps({
    file: { type: Object, default: null },
    visible: { type: Boolean, default: false },
});

const emit = defineEmits(['close', 'download']);

const details = ref(null);
const loading = ref(false);

watch(() => props.file, async (newFile) => {
    if (newFile && newFile.type === 'file') {
        loading.value = true;
        try {
            const response = await axios.get(route('files.show', newFile.id));
            details.value = response.data;
        } catch (e) {
            details.value = null;
        }
        loading.value = false;
    } else {
        details.value = null;
    }
}, { immediate: true });
</script>

<template>
    <Transition
        enter-active-class="transition-all duration-300 ease-out"
        enter-from-class="translate-x-full opacity-0"
        enter-to-class="translate-x-0 opacity-100"
        leave-active-class="transition-all duration-300 ease-in"
        leave-from-class="translate-x-0 opacity-100"
        leave-to-class="translate-x-full opacity-0"
    >
        <aside
            v-if="visible && file"
            class="w-[320px] bg-white border border-slate-200/50 rounded-2xl shadow-sm flex flex-col h-full overflow-y-auto shrink-0 scrollbar-thin"
        >
            <!-- Header / Preview -->
            <div class="p-6 border-b border-slate-200/50 flex flex-col items-center text-center relative">
                <button
                    class="absolute top-4 right-4 p-1 rounded-lg hover:bg-slate-100 text-slate-400 hover:text-slate-700 transition-all"
                    @click="emit('close')"
                >
                    <span class="material-symbols-outlined text-[18px]">close</span>
                </button>

                <div
                    class="w-24 h-24 rounded-2xl flex items-center justify-center mb-4 border overflow-hidden relative"
                    :class="file.type === 'folder' ? 'bg-blue-50 border-blue-100' : 'bg-slate-50 border-slate-100'"
                >
                    <span
                        v-if="file.type === 'folder'"
                        class="material-symbols-outlined text-5xl text-blue-500"
                        style="font-variation-settings: 'FILL' 1;"
                    >folder</span>
                    <img
                        v-else-if="file.mime_type?.startsWith('image/')"
                        :src="route('files.preview', file.id)"
                        :alt="file.name"
                        class="w-full h-full object-cover"
                        loading="lazy"
                    />
                    <video
                        v-else-if="file.mime_type?.startsWith('video/')"
                        :src="route('files.preview', file.id) + '#t=0.1'"
                        class="w-full h-full object-cover"
                        preload="metadata"
                        muted
                        playsinline
                    ></video>
                    <span
                        v-else
                        class="material-symbols-outlined text-5xl"
                        :class="file.icon_color"
                        style="font-variation-settings: 'FILL' 1;"
                    >{{ file.icon }}</span>
                </div>
                <h3 class="font-geist text-base tracking-tight font-semibold text-slate-900 mb-1 break-all">{{ file.name }}</h3>
                <p class="text-xs font-medium text-slate-500 uppercase tracking-wider">
                    {{ file.type === 'folder' ? 'Folder' : (file.extension?.toUpperCase() + ' File') }}
                </p>
            </div>

            <!-- Metadata (files only) -->
            <div v-if="details && !loading" class="p-6 space-y-6 flex-1 text-sm">
                <!-- Information -->
                <div>
                    <h4 class="text-xs font-medium text-slate-500 uppercase tracking-wider mb-4 border-b border-slate-200/50 pb-2">Information</h4>
                    <dl class="space-y-4">
                        <div class="flex flex-col">
                            <dt class="text-xs text-slate-500 mb-1">Type</dt>
                            <dd class="text-slate-900 font-medium">{{ details.extension?.toUpperCase() }} File (.{{ details.extension }})</dd>
                        </div>
                        <div class="flex flex-col">
                            <dt class="text-xs text-slate-500 mb-1">Size</dt>
                            <dd class="text-slate-900 font-medium">{{ details.size }} ({{ details.size_bytes?.toLocaleString() }} bytes)</dd>
                        </div>
                        <div class="flex flex-col">
                            <dt class="text-xs text-slate-500 mb-1">Location</dt>
                            <dd class="text-slate-900 font-medium flex items-center gap-1">
                                <span class="material-symbols-outlined text-[16px] text-slate-400">folder</span>
                                {{ details.location }}
                            </dd>
                        </div>
                        <div class="flex flex-col">
                            <dt class="text-xs text-slate-500 mb-1">Owner</dt>
                            <dd class="text-slate-900 font-medium">{{ details.owner }}</dd>
                        </div>
                    </dl>
                </div>

                <!-- History -->
                <div>
                    <h4 class="text-xs font-medium text-slate-500 uppercase tracking-wider mb-4 border-b border-slate-200/50 pb-2">History</h4>
                    <dl class="space-y-4">
                        <div class="flex flex-col">
                            <dt class="text-xs text-slate-500 mb-1">Created</dt>
                            <dd class="text-slate-900 font-medium">{{ details.created_at }}</dd>
                        </div>
                        <div class="flex flex-col">
                            <dt class="text-xs text-slate-500 mb-1">Modified</dt>
                            <dd class="text-slate-900 font-medium">{{ details.updated_at }}</dd>
                        </div>
                        <div v-if="details.last_accessed_at" class="flex flex-col">
                            <dt class="text-xs text-slate-500 mb-1">Opened</dt>
                            <dd class="text-slate-900 font-medium">{{ details.last_accessed_at }}</dd>
                        </div>
                    </dl>
                </div>
            </div>

            <!-- Folder info (simplified) -->
            <div v-else-if="file.type === 'folder'" class="p-6 space-y-4 flex-1 text-sm">
                <div>
                    <h4 class="text-xs font-medium text-slate-500 uppercase tracking-wider mb-4 border-b border-slate-200/50 pb-2">Information</h4>
                    <dl class="space-y-4">
                        <div class="flex flex-col">
                            <dt class="text-xs text-slate-500 mb-1">Type</dt>
                            <dd class="text-slate-900 font-medium">Folder</dd>
                        </div>
                        <div class="flex flex-col">
                            <dt class="text-xs text-slate-500 mb-1">Items</dt>
                            <dd class="text-slate-900 font-medium">{{ file.items_count }} items</dd>
                        </div>
                    </dl>
                </div>
            </div>

            <!-- Loading -->
            <div v-else-if="loading" class="p-6 flex-1 flex items-center justify-center">
                <div class="animate-spin rounded-full h-6 w-6 border-2 border-slate-300 border-t-primary"></div>
            </div>

            <!-- Action Footer -->
            <div v-if="file.type === 'file'" class="p-4 border-t border-slate-200/50 bg-white flex gap-2 rounded-b-2xl">
                <button
                    class="flex-1 bg-slate-900 text-white py-2.5 px-4 rounded-xl text-sm font-medium hover:bg-slate-800 transition-all duration-300 ease-in-out shadow-sm hover:shadow-md hover:-translate-y-0.5 flex items-center justify-center gap-2"
                    @click="emit('download', file)"
                >
                    <span class="material-symbols-outlined text-[18px]">download</span> Download
                </button>
                <button class="p-2.5 border border-slate-200/80 text-slate-700 rounded-xl hover:bg-slate-50 transition-all duration-300 ease-in-out flex items-center justify-center shadow-sm">
                    <span class="material-symbols-outlined text-[18px]">share</span>
                </button>
            </div>
        </aside>
    </Transition>
</template>
