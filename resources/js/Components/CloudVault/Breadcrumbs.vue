<script setup>
import { Link } from '@inertiajs/vue3';

defineProps({
    ancestors: { type: Array, default: () => [] },
    currentFolder: { type: Object, default: null },
});
</script>

<template>
    <nav class="flex items-center text-sm font-medium text-slate-500">
        <Link
            :href="route('folders.index')"
            class="hover:text-slate-900 transition-all duration-300 ease-in-out px-2 py-1 rounded-md hover:bg-slate-200/50 flex items-center gap-1"
        >
            <span class="material-symbols-outlined text-[16px]">home</span>
            My Files
        </Link>

        <template v-for="ancestor in ancestors" :key="ancestor.id">
            <span class="material-symbols-outlined text-[16px] mx-0.5 text-slate-300">chevron_right</span>
            <Link
                :href="route('folders.show', ancestor.id)"
                class="hover:text-slate-900 transition-all duration-300 ease-in-out px-2 py-1 rounded-md hover:bg-slate-200/50"
            >
                {{ ancestor.name }}
            </Link>
        </template>

        <template v-if="currentFolder">
            <span class="material-symbols-outlined text-[16px] mx-0.5 text-slate-300">chevron_right</span>
            <span class="text-slate-900 px-2 py-1 font-semibold tracking-tight">
                {{ currentFolder.name }}
            </span>
        </template>
    </nav>
</template>
