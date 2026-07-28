<script setup>
defineProps({
    visible: { type: Boolean, default: false },
    folderName: { type: String, default: 'My Files' },
});
</script>

<template>
    <Transition
        enter-active-class="transition-all duration-300 ease-out"
        enter-from-class="opacity-0"
        enter-to-class="opacity-100"
        leave-active-class="transition-all duration-300 ease-in"
        leave-from-class="opacity-100"
        leave-to-class="opacity-0"
    >
        <div
            v-show="visible"
            class="absolute inset-0 z-50 bg-white/40 backdrop-blur-md border-2 border-dashed border-primary/50 rounded-2xl m-6 flex flex-col items-center justify-center transition-all duration-300 ease-in-out shadow-2xl"
        >
            <div class="bg-white p-6 rounded-2xl shadow-sm border border-slate-200/50 flex flex-col items-center">
                <span class="material-symbols-outlined text-primary text-5xl mb-4 animate-bounce">upload_file</span>
                <h2 class="font-geist text-2xl tracking-tight text-slate-900 font-semibold">Drop files here</h2>
                <p class="text-sm text-slate-500 mt-2">Upload to {{ folderName }}</p>
            </div>
        </div>
    </Transition>
</template>
