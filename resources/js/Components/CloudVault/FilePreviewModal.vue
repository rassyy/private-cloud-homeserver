<script setup>
import { computed } from 'vue';

const props = defineProps({
    visible: { type: Boolean, default: false },
    file: { type: Object, default: null },
});

const emit = defineEmits(['close']);

const isImage = computed(() => props.file?.mime_type?.startsWith('image/'));
const isVideo = computed(() => props.file?.mime_type?.startsWith('video/'));
const isPdf = computed(() => props.file?.mime_type === 'application/pdf');
const isPreviewable = computed(() => isImage.value || isVideo.value || isPdf.value);

const previewUrl = computed(() => {
    if (!props.file) return '';
    return route('files.preview', props.file.id);
});

function download() {
    if (!props.file) return;
    window.location.href = route('files.download', props.file.id);
}

function handleKeydown(event) {
    if (event.key === 'Escape') emit('close');
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
                v-if="visible && file"
                class="fixed inset-0 z-[200] flex flex-col bg-slate-900/80 backdrop-blur-2xl"
                @keydown="handleKeydown"
                tabindex="0"
            >
                <!-- Top Toolbar -->
                <div class="w-full h-16 flex items-center justify-between px-6 border-b border-white/10 shrink-0">
                    <!-- Left: File Info -->
                    <div class="flex items-center gap-4 text-white">
                        <span class="material-symbols-outlined" :class="file.icon_color">{{ file.icon }}</span>
                        <div>
                            <h2 class="font-geist text-lg tracking-tight font-semibold text-white">{{ file.name }}</h2>
                            <p class="text-sm text-white/70">{{ file.size }} • {{ file.updated_at }}</p>
                        </div>
                    </div>

                    <!-- Right: Actions -->
                    <div class="flex items-center gap-2">
                        <button
                            class="p-2 rounded-full hover:bg-white/10 text-white transition-colors duration-150 flex items-center justify-center"
                            @click="download"
                            title="Download"
                        >
                            <span class="material-symbols-outlined">download</span>
                        </button>
                        <div class="w-px h-6 bg-white/20 mx-2"></div>
                        <button
                            class="p-2 rounded-full hover:bg-white/10 text-white transition-colors duration-150 flex items-center justify-center"
                            @click="emit('close')"
                            title="Close"
                        >
                            <span class="material-symbols-outlined">close</span>
                        </button>
                    </div>
                </div>

                <!-- Main Preview Area -->
                <div class="flex-1 w-full h-full flex items-center justify-center p-6 overflow-hidden">
                    <div class="relative max-w-5xl w-full max-h-full flex items-center justify-center">
                        <!-- Image Preview -->
                        <img
                            v-if="isImage"
                            :src="previewUrl"
                            :alt="file.name"
                            class="max-w-full max-h-[calc(100vh-8rem)] object-contain rounded-lg shadow-[0_12px_32px_rgba(15,23,42,0.15)] ring-1 ring-white/20"
                        />

                        <!-- PDF Preview -->
                        <iframe
                            v-else-if="isPdf"
                            :src="previewUrl"
                            class="w-full h-[calc(100vh-8rem)] rounded-lg shadow-[0_12px_32px_rgba(15,23,42,0.15)] ring-1 ring-white/20 bg-white"
                        ></iframe>

                        <!-- Video Preview -->
                        <video
                            v-else-if="isVideo"
                            controls
                            class="max-w-full max-h-[calc(100vh-8rem)] rounded-lg shadow-[0_12px_32px_rgba(15,23,42,0.15)] ring-1 ring-white/20 bg-black"
                        >
                            <source :src="previewUrl" :type="file.mime_type">
                            Your browser does not support the video tag.
                        </video>

                        <!-- Non-previewable -->
                        <div v-else class="text-center text-white">
                            <span class="material-symbols-outlined text-7xl text-white/40 mb-6" style="font-variation-settings: 'FILL' 1;">{{ file.icon }}</span>
                            <h3 class="font-geist text-xl font-semibold mb-2">Preview not available</h3>
                            <p class="text-white/60 mb-6">This file type cannot be previewed.</p>
                            <button
                                class="px-6 py-3 bg-white text-slate-900 rounded-xl text-sm font-medium hover:bg-slate-100 transition-all shadow-lg"
                                @click="download"
                            >
                                <span class="material-symbols-outlined text-[18px] mr-2 align-middle">download</span>
                                Download File
                            </button>
                        </div>
                    </div>
                </div>
            </div>
        </Transition>
    </Teleport>
</template>
