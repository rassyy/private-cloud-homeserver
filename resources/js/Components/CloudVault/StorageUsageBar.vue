<script setup>
import { computed } from 'vue';
import { usePage } from '@inertiajs/vue3';

const page = usePage();
const storage = computed(() => page.props.storage_usage || {
    percentage: 0,
    formatted_used: '0 B',
    formatted_total: '0 B'
});

const progressColorClass = computed(() => {
    if (storage.value.percentage >= 90) return 'bg-red-500';
    if (storage.value.percentage >= 70) return 'bg-yellow-400';
    return 'bg-green-500';
});
</script>

<template>
    <div class="p-4 bg-white/70 backdrop-blur-md border border-slate-200/50 rounded-2xl flex flex-col gap-2.5 shadow-[0_2px_8px_rgba(0,0,0,0.02)] transition-all duration-300 hover:shadow-sm">
        <div class="flex items-center justify-between text-xs font-semibold text-slate-800">
            <div class="flex items-center gap-2">
                <div class="w-6 h-6 rounded-lg bg-slate-100/80 flex items-center justify-center text-slate-600">
                    <span class="material-symbols-outlined text-[16px]">cloud_queue</span>
                </div>
                <span class="font-geist tracking-tight">Storage usage</span>
            </div>
            <span class="font-mono text-[11px] font-medium text-slate-500">{{ storage.percentage }}%</span>
        </div>
        
        <!-- Progress Bar -->
        <div class="w-full bg-slate-100 rounded-full h-1.5 overflow-hidden">
            <div
                class="h-full rounded-full transition-all duration-700 ease-out"
                :class="progressColorClass"
                :style="{ width: `${Math.max(storage.percentage, 2)}%` }"
            ></div>
        </div>

        <div class="flex items-center justify-between text-[11px] text-slate-500 pt-0.5">
            <span><strong class="font-medium text-slate-700">{{ storage.formatted_used }}</strong> used</span>
            <span>{{ storage.formatted_total }}</span>
        </div>
    </div>
</template>
