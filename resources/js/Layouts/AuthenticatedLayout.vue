<script setup>
import { ref } from 'vue';
import ApplicationLogo from '@/Components/ApplicationLogo.vue';
import Dropdown from '@/Components/Dropdown.vue';
import DropdownLink from '@/Components/DropdownLink.vue';
import { Link } from '@inertiajs/vue3';

const showingSidebar = ref(false);

const navigation = [
    { name: 'Dashboard', href: route('dashboard'), current: route().current('dashboard'), icon: 'M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6' },
    {
        name: 'Members',
        href: route('members.index'),
        current: route().current('members.*'),
        icon: 'M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z'
    },
    {
        name: 'Paket Membership',
        href: route('membership-plans.index'),
        current: route().current('membership-plans.*'),
        icon: 'M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 012-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10'
    },
    {
        name: 'Check-in',
        href: route('check-in.index'),
        current: route().current('check-in.*'),
        icon: 'M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-6 9l2 2 4-4'
    },
    // Nanti kita tambah menu lain di sini (Members, Plans, dll)
];
</script>

<template>
    <div class="min-h-screen bg-gray-50 flex">
        <!-- Sidebar Mobile Overlay -->
        <div v-if="showingSidebar" class="fixed inset-0 z-40 bg-gray-900/50 lg:hidden" @click="showingSidebar = false">
        </div>

        <!-- Sidebar -->
        <aside :class="[
            'fixed inset-y-0 left-0 z-50 w-64 bg-blue-800 text-white transform transition-transform duration-200 ease-in-out lg:translate-x-0 lg:static lg:inset-0',
            showingSidebar ? 'translate-x-0' : '-translate-x-full'
        ]">
            <!-- Logo -->
            <div class="flex items-center h-16 px-6 border-b border-blue-700">
                <Link :href="route('dashboard')" class="flex items-center gap-3">
                    <ApplicationLogo class="block h-8 w-auto fill-current text-white" />
                    <span class="text-xl font-bold tracking-tight">Enegma</span>
                </Link>
            </div>

            <!-- Navigation -->
            <nav class="mt-6 px-3 space-y-1">
                <Link v-for="item in navigation" :key="item.name" :href="item.href" :class="[
                    item.current
                        ? 'bg-blue-900 text-white'
                        : 'text-blue-100 hover:bg-blue-700 hover:text-white',
                    'group flex items-center px-3 py-2.5 text-sm font-medium rounded-lg transition-colors'
                ]">
                    <svg class="mr-3 h-5 w-5 flex-shrink-0" xmlns="http://www.w3.org/2000/svg" fill="none"
                        viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                        <path stroke-linecap="round" stroke-linejoin="round" :d="item.icon" />
                    </svg>
                    {{ item.name }}
                </Link>
            </nav>

            <!-- User Info di bawah sidebar (opsional) -->
            <div class="absolute bottom-0 w-full p-4 border-t border-blue-700">
                <div class="flex items-center gap-3">
                    <div
                        class="h-9 w-9 rounded-full bg-blue-600 flex items-center justify-center text-sm font-semibold">
                        {{ $page.props.auth.user.name.charAt(0).toUpperCase() }}
                    </div>
                    <div class="flex-1 min-w-0">
                        <p class="text-sm font-medium text-white truncate">
                            {{ $page.props.auth.user.name }}
                        </p>
                        <p class="text-xs text-blue-200 truncate">
                            {{ $page.props.auth.user.email }}
                        </p>
                    </div>
                </div>
            </div>
        </aside>

        <!-- Main Content -->
        <div class="flex-1 flex flex-col min-w-0">
            <!-- Header -->
            <header class="bg-white shadow-sm border-b border-gray-200 sticky top-0 z-30">
                <div class="flex items-center justify-between h-16 px-4 sm:px-6 lg:px-8">
                    <!-- Mobile menu button -->
                    <button @click="showingSidebar = true"
                        class="lg:hidden p-2 rounded-md text-gray-500 hover:text-gray-700 hover:bg-gray-100 focus:outline-none">
                        <svg class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M4 6h16M4 12h16M4 18h16" />
                        </svg>
                    </button>

                    <!-- Page Title (opsional) -->
                    <div class="flex-1">
                        <slot name="header" />
                    </div>

                    <!-- Right side: User Dropdown -->
                    <div class="flex items-center gap-4">
                        <Dropdown align="right" width="48">
                            <template #trigger>
                                <button
                                    class="flex items-center gap-2 text-sm font-medium text-gray-700 hover:text-gray-900 focus:outline-none">
                                    <span class="hidden sm:inline">{{ $page.props.auth.user.name }}</span>
                                    <div
                                        class="h-8 w-8 rounded-full bg-blue-600 flex items-center justify-center text-white text-sm font-semibold">
                                        {{ $page.props.auth.user.name.charAt(0).toUpperCase() }}
                                    </div>
                                    <svg class="h-4 w-4 text-gray-400" fill="currentColor" viewBox="0 0 20 20">
                                        <path fill-rule="evenodd"
                                            d="M5.293 7.293a1 1 0 011.414 0L10 10.586l3.293-3.293a1 1 0 111.414 1.414l-4 4a1 1 0 01-1.414 0l-4-4a1 1 0 010-1.414z"
                                            clip-rule="evenodd" />
                                    </svg>
                                </button>
                            </template>

                            <template #content>
                                <DropdownLink :href="route('profile.edit')">
                                    Profile
                                </DropdownLink>
                                <DropdownLink :href="route('logout')" method="post" as="button">
                                    Log Out
                                </DropdownLink>
                            </template>
                        </Dropdown>
                    </div>
                </div>
            </header>

            <!-- Page Content -->
            <main class="flex-1 p-4 sm:p-6 lg:p-8">
                <div class="mb-5 bg-white shadow-inner p-5 border-b-4 border-blue-600 rounded-lg">
                    <slot name="headnav" />
                </div>
                <slot />
            </main>
        </div>
    </div>
</template>