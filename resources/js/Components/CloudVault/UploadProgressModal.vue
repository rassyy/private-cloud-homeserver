<script setup>
import { computed } from 'vue';

const props = defineProps({
    visible: { type: Boolean, default: false },
    uploads: { type: Array, default: () => [] }, // [{ name, progress, status }]
});

const emit = defineEmits(['close', 'cancel']);

const totalProgress = computed(() => {
    if (props.uploads.length === 0) return 0;
    const sum = props.uploads.reduce((acc, u) => acc + (u.progress || 0), 0);
    return Math.round(sum / props.uploads.length);
});

const completedCount = computed(() => props.uploads.filter(u => u.status === 'done').length);
const isAllDone = computed(() => props.uploads.length > 0 && completedCount.value === props.uploads.length);
</script>

<template>
    <Transition
        enter-active-class="transition-all duration-300 ease-out"
        enter-from-class="translate-y-full opacity-0"
        enter-to-class="translate-y-0 opacity-100"
        leave-active-class="transition-all duration-300 ease-in"
        leave-from-class="translate-y-0 opacity-100"
        leave-to-class="translate-y-full opacity-0"
    >
        <div
            v-if="visible && uploads.length > 0"
            class="fixed bottom-6 right-6 w-[380px] bg-white/90 backdrop-blur-xl border border-slate-200/50 shadow-2xl rounded-2xl z-[100] overflow-hidden"
        >
            <!-- Header -->
            <div class="px-5 py-4 border-b border-slate-200/50 flex justify-between items-center">
                <div>
                    <h3 class="font-geist text-sm font-semibold text-slate-900">
                        {{ isAllDone ? 'Upload complete' : 'Uploading...' }}
                    </h3>
                    <p class="text-xs text-slate-500 mt-0.5">{{ completedCount }} of {{ uploads.length }} files</p>
                </div>
                <div class="flex items-center gap-2">
                    <span v-if="isAllDone" class="material-symbols-outlined text-green-500 text-[20px]" style="font-variation-settings: 'FILL' 1;">check_circle</span>
                    <button
                        class="p-1 rounded-lg hover:bg-slate-100 text-slate-400 hover:text-slate-700 transition-all"
                        @click="emit('close')"
                    >
                        <span class="material-symbols-outlined text-[18px]">close</span>
                    </button>
                </div>
            </div>

            <!-- Overall Progress -->
            <div class="px-5 py-2 bg-slate-50/50">
                <div class="w-full bg-slate-200 rounded-full h-1.5">
                    <div
                        class="h-1.5 rounded-full transition-all duration-500 ease-out"
                        :class="isAllDone ? 'bg-green-500' : 'bg-primary'"
                        :style="{ width: totalProgress + '%' }"
                    ></div>
                </div>
            </div>

            <!-- File List -->
            <div class="max-h-[200px] overflow-y-auto scrollbar-thin divide-y divide-slate-100">
                <div
                    v-for="(upload, index) in uploads"
                    :key="index"
                    class="px-5 py-3 flex items-center gap-3"
                >
                    <span
                        v-if="upload.status === 'done'"
                        class="material-symbols-outlined text-green-500 text-[18px]"
                        style="font-variation-settings: 'FILL' 1;"
                    >check_circle</span>
                    <span
                        v-else-if="upload.status === 'error'"
                        class="material-symbols-outlined text-red-500 text-[18px]"
                        style="font-variation-settings: 'FILL' 1;"
                    >error</span>
                    <div v-else class="w-[18px] h-[18px] border-2 border-primary border-t-transparent rounded-full animate-spin"></div>

                    <div class="flex-1 min-w-0">
                        <p class="text-xs font-medium text-slate-900 truncate">{{ upload.name }}</p>
                        <p v-if="upload.status === 'error'" class="text-[10px] text-red-500">Upload failed</p>
                        <div v-else-if="upload.status === 'uploading'" class="w-full bg-slate-200 rounded-full h-1 mt-1">
                            <div class="bg-primary h-1 rounded-full transition-all duration-300" :style="{ width: (upload.progress || 0) + '%' }"></div>
                        </div>
                    </div>

                    <div class="flex items-center gap-2">
                        <span class="text-[10px] font-mono text-slate-400">{{ upload.progress || 0 }}%</span>
                        <button
                            v-if="upload.status === 'uploading'"
                            class="text-slate-400 hover:text-red-500 transition-colors flex items-center"
                            @click="emit('cancel', upload.id || index)"
                            title="Cancel upload"
                        >
                            <span class="material-symbols-outlined text-[16px]">close</span>
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </Transition>
</template>
