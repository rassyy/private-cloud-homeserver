<script setup>
defineProps({
    message: { type: String, default: 'This folder is empty' },
    description: { type: String, default: 'Drag and drop files here, or click "New File" to get started.' },
});
</script>

<template>
    <div class="flex flex-col items-center justify-center py-24 text-center">
        <div class="w-20 h-20 bg-slate-100 rounded-2xl flex items-center justify-center mb-6">
            <span class="material-symbols-outlined text-4xl text-slate-300">folder_off</span>
        </div>
        <h3 class="font-geist text-lg tracking-tight font-semibold text-slate-900 mb-2">{{ message }}</h3>
        <p class="text-sm text-slate-500 max-w-sm">{{ description }}</p>
    </div>
</template>
