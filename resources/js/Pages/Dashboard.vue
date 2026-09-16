<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head } from '@inertiajs/vue3';
</script>

<template>
    <Head title="Dashboard" />

    <AuthenticatedLayout>
        <template #header>
            <h2 class="text-xl font-semibold text-gray-800">
                Dashboard
            </h2>
        </template>

        <!-- Isi konten dashboard di sini -->
        <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg p-6">
            <p class="text-gray-600">Selamat datang di Gym Management System</p>
        </div>
    </AuthenticatedLayout>
</template>