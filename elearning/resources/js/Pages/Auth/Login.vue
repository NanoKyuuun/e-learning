<script setup>
import { Head, useForm } from '@inertiajs/vue3';
import TextInput from '@/Components/forms/input/TextInput.vue';

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
    <Head title="Log in" />

    <div class="min-h-screen flex flex-col sm:justify-center items-center pt-6 sm:pt-0 bg-base-200 text-base-content">
        <div class="w-full sm:max-w-md mt-6 px-6 py-8 bg-base-100 shadow-xl rounded-2xl">
            <div class="mb-10 text-center">
                <h1 class="text-4xl font-black text-primary tracking-tight">E-LEARNING</h1>
                <p class="text-base-content/60 mt-2">Selamat datang kembali, silakan login.</p>
            </div>

            <form @submit.prevent="submit" class="space-y-4">
                <TextInput
                    label="Email"
                    v-model="form.email"
                    type="email"
                    :error="form.errors.email"
                    placeholder="nama@sekolah.sch.id"
                    required
                    autofocus
                />

                <TextInput
                    label="Password"
                    v-model="form.password"
                    type="password"
                    :error="form.errors.password"
                    required
                />

                <div class="flex items-center justify-between mt-4">
                    <label class="label cursor-pointer justify-start gap-2">
                        <input type="checkbox" class="checkbox checkbox-primary checkbox-sm" v-model="form.remember" />
                        <span class="label-text">Ingat saya</span>
                    </label>
                </div>

                <div class="mt-6">
                    <button class="btn btn-primary btn-block text-lg" :disabled="form.processing">
                        Masuk Sekarang
                    </button>
                </div>
            </form>
        </div>
    </div>
</template>
