<script setup>
import GuestLayout from '@/Layouts/GuestLayout.vue';
import { Head, Link, useForm } from '@inertiajs/vue3';

const form = useForm({
    name: '',
    email: '',
    password: '',
    password_confirmation: '',
});

const submit = () => {
    form.post(route('register'), {
        onFinish: () => form.reset('password', 'password_confirmation'),
    });
};
</script>

<template>
    <GuestLayout>
        <Head title="Sign up" />

        <h2 class="font-geist text-xl tracking-tight font-semibold text-slate-900 mb-1">Create an account</h2>
        <p class="text-sm text-slate-500 mb-6">Get started with Self Cloud</p>

        <form @submit.prevent="submit" class="space-y-5">
            <div>
                <label class="block text-xs font-medium text-slate-500 mb-1.5" for="name">Full Name</label>
                <input
                    id="name"
                    v-model="form.name"
                    type="text"
                    required
                    autofocus
                    autocomplete="name"
                    class="w-full px-4 py-3 bg-slate-50 rounded-xl border border-slate-200 focus:border-slate-900 focus:ring-1 focus:ring-slate-900 outline-none transition-all text-sm text-slate-900 placeholder:text-slate-400"
                    placeholder="John Doe"
                />
                <p v-if="form.errors.name" class="text-xs text-red-500 mt-1.5">{{ form.errors.name }}</p>
            </div>

            <div>
                <label class="block text-xs font-medium text-slate-500 mb-1.5" for="email">Email</label>
                <input
                    id="email"
                    v-model="form.email"
                    type="email"
                    required
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
                    autocomplete="new-password"
                    class="w-full px-4 py-3 bg-slate-50 rounded-xl border border-slate-200 focus:border-slate-900 focus:ring-1 focus:ring-slate-900 outline-none transition-all text-sm text-slate-900"
                />
                <p v-if="form.errors.password" class="text-xs text-red-500 mt-1.5">{{ form.errors.password }}</p>
            </div>

            <div>
                <label class="block text-xs font-medium text-slate-500 mb-1.5" for="password_confirmation">Confirm Password</label>
                <input
                    id="password_confirmation"
                    v-model="form.password_confirmation"
                    type="password"
                    required
                    autocomplete="new-password"
                    class="w-full px-4 py-3 bg-slate-50 rounded-xl border border-slate-200 focus:border-slate-900 focus:ring-1 focus:ring-slate-900 outline-none transition-all text-sm text-slate-900"
                />
                <p v-if="form.errors.password_confirmation" class="text-xs text-red-500 mt-1.5">{{ form.errors.password_confirmation }}</p>
            </div>

            <button
                type="submit"
                class="w-full bg-slate-900 text-white py-3 px-4 rounded-xl text-sm font-medium hover:bg-slate-800 transition-all duration-300 ease-in-out shadow-sm hover:shadow-md disabled:opacity-50"
                :disabled="form.processing"
            >
                Create Account
            </button>

            <p class="text-center text-sm text-slate-500">
                Already have an account?
                <Link :href="route('login')" class="text-slate-900 font-medium hover:underline">Sign in</Link>
            </p>
        </form>
    </GuestLayout>
</template>
