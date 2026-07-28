<script setup>
import Checkbox from '@/Components/Checkbox.vue';
import GuestLayout from '@/Layouts/GuestLayout.vue';
import { Head, Link, useForm } from '@inertiajs/vue3';

defineProps({
    canResetPassword: { type: Boolean },
    status: { type: String },
});

const form = useForm({
    email: '',
    password: '',
    remember: false,
});

const submit = () => {
    form.post(route('login'), {
        onFinish: () => form.reset('password'),
    });
};
</script>

<template>
    <GuestLayout>
        <Head title="Sign in" />

        <h2 class="font-geist text-xl tracking-tight font-semibold text-slate-900 mb-1">Welcome back</h2>
        <p class="text-sm text-slate-500 mb-6">Sign in to your Self Cloud account</p>

        <div v-if="status" class="mb-4 text-sm font-medium text-green-600 bg-green-50 p-3 rounded-xl">
            {{ status }}
        </div>

        <form @submit.prevent="submit" class="space-y-5">
            <div>
                <label class="block text-xs font-medium text-slate-500 mb-1.5" for="email">Email</label>
                <input
                    id="email"
                    v-model="form.email"
                    type="email"
                    required
                    autofocus
                    autocomplete="username"
                    class="w-full px-4 py-3 bg-slate-50 rounded-xl border border-slate-200 focus:border-slate-900 focus:ring-1 focus:ring-slate-900 outline-none transition-all text-sm text-slate-900 placeholder:text-slate-400"
                    placeholder="name@company.com"
                />
                <p v-if="form.errors.email" class="text-xs text-red-500 mt-1.5">{{ form.errors.email }}</p>
            </div>

            <div>
                <label class="block text-xs font-medium text-slate-500 mb-1.5" for="password">Password</label>
                <input
                    id="password"
                    v-model="form.password"
                    type="password"
                    required
                    autocomplete="current-password"
                    class="w-full px-4 py-3 bg-slate-50 rounded-xl border border-slate-200 focus:border-slate-900 focus:ring-1 focus:ring-slate-900 outline-none transition-all text-sm text-slate-900"
                />
                <p v-if="form.errors.password" class="text-xs text-red-500 mt-1.5">{{ form.errors.password }}</p>
            </div>

            <div class="flex items-center justify-between">
                <label class="flex items-center gap-2 cursor-pointer">
                    <Checkbox
                        name="remember"
                        v-model:checked="form.remember"
                        class="w-4 h-4 rounded border-slate-300 text-slate-900 focus:ring-slate-900 focus:ring-offset-0"
                    />
                    <span class="text-sm text-slate-600">Remember me</span>
                </label>
                <Link
                    v-if="canResetPassword"
                    :href="route('password.request')"
                    class="text-sm text-slate-500 hover:text-slate-900 transition-colors"
                >Forgot password?</Link>
            </div>

            <button
                type="submit"
                class="w-full bg-slate-900 text-white py-3 px-4 rounded-xl text-sm font-medium hover:bg-slate-800 transition-all duration-300 ease-in-out shadow-sm hover:shadow-md disabled:opacity-50"
                :disabled="form.processing"
            >
                Sign in
            </button>

            <p class="text-center text-sm text-slate-500">
                Don't have an account?
                <Link :href="route('register')" class="text-slate-900 font-medium hover:underline">Sign up</Link>
            </p>
        </form>
    </GuestLayout>
</template>
