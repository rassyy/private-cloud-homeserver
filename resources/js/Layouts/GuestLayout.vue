<script setup>
import { Head } from '@inertiajs/vue3';
</script>

<template>
    <div class="min-h-screen flex items-center justify-center bg-slate-50/50 antialiased">
        <Head title="Self Cloud" />
        <div class="flex flex-col items-center w-full max-w-md px-6">
            <!-- Logo -->
            <div class="flex items-center gap-3 mb-8">
                <div class="w-10 h-10 rounded-xl bg-white border border-slate-200 shadow-sm flex items-center justify-center">
                    <span class="material-symbols-outlined text-slate-900 text-[22px]" style="font-variation-settings: 'FILL' 1;">cloud</span>
                </div>
                <h1 class="font-geist text-2xl tracking-tight font-bold text-slate-900">Self Cloud</h1>
            </div>

            <!-- Card -->
            <div class="w-full bg-white border border-slate-200/50 rounded-2xl shadow-sm p-8">
                <slot />
            </div>
        </div>
    </div>
</template>
