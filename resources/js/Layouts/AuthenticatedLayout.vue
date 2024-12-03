<script setup>
import { ref, computed, onMounted, onUnmounted } from 'vue';
import ApplicationLogo from '@/Components/ApplicationLogo.vue';
import Dropdown from '@/Components/Dropdown.vue';
import DropdownLink from '@/Components/DropdownLink.vue';
import { Link, usePage } from '@inertiajs/vue3';
import AuthImage from '@/Components/AuthImage.vue'
import ApplicationLogoAlt from '@/Components/ApplicationLogoAlt.vue';
import { eventBus } from '@/Pages/eventBus';

let showingNavigationDropdown = ref(false);
const isLargeScreen = ref(false);

defineProps({
    pageTitle: {
        type: String,
        default: 'Dashboard',
    },
});
const page = usePage();

const isCollapsed = ref(false);

const toggleLeftSidebar = () => {
    eventBus.toggleCollapsed();
    isCollapsed.value = !isCollapsed.value;
    //localStorage.setItem('collapsed', isCollapsed.value);
};

const checkScreenSize = () => {
    isLargeScreen.value = window.matchMedia('(min-width: 1024px)').matches;
};

const sidebarClass = computed(() => {
    if (!isLargeScreen.value) {
        return 'w-full'; // Full width on large screens
    }
    return isCollapsed.value ? 'w-16' : 'w-64'; // Adjust the width based on collapse state
});


const toggleSidebar = () => {
    showingNavigationDropdown.value = !showingNavigationDropdown.value;
    const sidebar = document.querySelector("aside");
    sidebar.classList.toggle("hidden");
}

const hideSidebar = computed(() => route().current('project.show'));

onMounted(() => {
    const storedCollapsed = localStorage.getItem('collapsed');
    isCollapsed.value = storedCollapsed === 'true';

    checkScreenSize();
    window.addEventListener('resize', checkScreenSize);
});

onUnmounted(() => {
    window.removeEventListener('resize', checkScreenSize);
});


</script>

<template>
    <div class="min-h-screen flex flex-col lg:flex-row bg-gray-100 dark:bg-gray-900">
        <!-- Left Sidebar -->
        <aside :class="sidebarClass" class="bg-navy-blue text-white hidden lg:block">
            <div class="mb-3 pl-4 py-4 flex justify-between items-center">
                <Link :href="route('dashboard')">
                    <ApplicationLogoAlt
                        v-if="!isCollapsed"
                        class="block h-8 w-auto fill-current text-gray-800 dark:text-gray-200"
                    />
                    <ApplicationLogo
                        v-else
                        class="block h-7 w-40 fill-current mt-1 text-gray-800 dark:text-gray-200"
                    />
                </Link>
                <button v-if="!isCollapsed" @click="toggleLeftSidebar" class="text-linen mt-3 mr-3 focus:outline-none hidden lg:block">
                    <i :class="isCollapsed ? 'fas fa-chevron-right' : 'fas fa-chevron-left'"></i>
                </button>
            </div>
            <nav class="text-linen">
                <ul class="list-none pl-0">
                    <li class="py-3 pl-6 hover:bg-dark-navy relative group" :class="{'bg-dark-navy text-sky-blue': route().current('dashboard')}">
                        <a :href="route('dashboard')" class="block">
                            <i class="fas fa-home mr-2"></i><span v-if="!isCollapsed">Dashboard</span>
                        </a>
                        <span v-if="isCollapsed" class="absolute top-full mt-1 left-10 text-navy-blue transform -translate-x-1 hidden group-hover:flex items-center px-4 py-3 text-sm font-semibold text-white bg-light-gray rounded-md shadow-lg z-10 whitespace-nowrap">
                            Dashboard
                        </span>
                    </li>
                    <li class="py-3 pl-6 hover:bg-dark-navy relative group" title="Tasks" :class="{'bg-dark-navy text-sky-blue': route().current('my-tasks')}">
                        <a :href="route('my-tasks')" class="block">
                            <i class="fas fa-tasks mr-2"></i><span v-if="!isCollapsed">My Tasks</span>
                        </a>
                        <span v-if="isCollapsed" class="absolute top-full mt-1 left-10 text-navy-blue transform -translate-x-1 hidden group-hover:flex items-center px-4 py-3 text-sm font-semibold text-white bg-light-gray rounded-md shadow-lg z-10 whitespace-nowrap">
                            Tasks
                        </span>
                    </li>
                    <li class="py-3 pl-6 hover:bg-dark-navy relative group" title="Projects" :class="{'bg-dark-navy text-sky-blue': route().current('projects')}">
                        <a :href="route('projects')" class="block">
                            <i class="fas fa-project-diagram mr-2"></i><span v-if="!isCollapsed">Projects</span>
                        </a>
                        <span v-if="isCollapsed" class="absolute top-full mt-1 left-10 text-navy-blue transform -translate-x-1 hidden group-hover:flex items-center px-4 py-3 text-sm font-semibold text-white bg-light-gray rounded-md shadow-lg z-10 whitespace-nowrap">
                            Projects
                        </span>
                    </li>
                    <li class="py-3 pl-6 hover:bg-dark-navy relative group" title="Social Network" :class="{'bg-dark-navy text-sky-blue': route().current('social')}">
                        <a :href="route('social')" class="block">
                            <i class="fas fa-users mr-2"></i><span v-if="!isCollapsed">Social</span>
                        </a>
                        <span v-if="isCollapsed" class="absolute top-full mt-1 left-10 text-navy-blue transform -translate-x-1 hidden group-hover:flex items-center px-4 py-3 text-sm font-semibold text-white bg-light-gray rounded-md shadow-lg z-10 whitespace-nowrap">
                            Social
                        </span>
                    </li>
                    <li class="py-3 pl-6 hover:bg-dark-navy relative group" title="Chat" :class="{'bg-dark-navy text-sky-blue': route().current('messages')}">
                        <a :href="route('messages')" class="block">
                            <i class="fas fa-comments mr-2"></i><span v-if="!isCollapsed">Chat</span>
                        </a>
                        <span v-if="isCollapsed" class="absolute top-full mt-1 left-10 text-navy-blue transform -translate-x-1 hidden group-hover:flex items-center px-4 py-3 text-sm font-semibold text-white bg-light-gray rounded-md shadow-lg z-10 whitespace-nowrap">
                            Chat
                        </span>
                    </li>
                    <li class="py-3 pl-6 hover:bg-dark-navy relative group" title="Calendar" :class="{'bg-dark-navy text-sky-blue': route().current('calendar')}">
                        <a :href="route('calendar')" class="block">
                            <i class="fas fa-calendar-alt mr-2"></i><span v-if="!isCollapsed">Calendar</span>
                        </a>
                        <span v-if="isCollapsed" class="absolute top-full mt-1 left-10 text-navy-blue transform -translate-x-1 hidden group-hover:flex items-center px-4 py-3 text-sm font-semibold text-white bg-light-gray rounded-md shadow-lg z-10 whitespace-nowrap">
                            Calendar
                        </span>
                    </li>
                </ul>
                <div v-if="isCollapsed" class="flex justify-center">
                    <button @click="toggleLeftSidebar" class="text-linen mt-3 mr-1 focus:outline-none hidden lg:block">
                        <i class="fas fa-chevron-right"></i>
                    </button>
                </div>
            </nav>
        </aside>

        <!-- Main Content Area -->
        <div class="flex-1 flex flex-col">
            <!-- Header -->
            <header class="flex flex-col lg:flex-row items-center justify-between bg-navy-blue p-4 border-b border-gray-200 dark:border-gray-700">
                <div class="flex items-center justify-between w-full lg:w-auto">
                    <div class="text-xl font-semibold text-color-white mr-8">{{ pageTitle }}</div>
                    <!-- <button class="lg:hidden text-white" @click="toggleSidebar">☰</button> -->
                    <button
                        @click="toggleSidebar"
                        class="lg:hidden inline-flex items-center justify-center rounded-md p-2 text-gray-400 transition duration-150 ease-in-out hover:bg-gray-100 hover:text-gray-500 focus:bg-gray-100 focus:text-gray-500 focus:outline-none dark:text-gray-500 dark:hover:bg-gray-900 dark:hover:text-gray-400 dark:focus:bg-gray-900 dark:focus:text-gray-400"
                    >
                        <svg
                            class="h-6 w-6 text-crystal-blue"
                            stroke="currentColor"
                            fill="none"
                            viewBox="0 0 24 24"
                        >
                            <path
                                :class="{
                                    hidden: showingNavigationDropdown,
                                    'inline-flex':
                                        !showingNavigationDropdown,
                                }"
                                stroke-linecap="round"
                                stroke-linejoin="round"
                                stroke-width="2"
                                d="M4 6h16M4 12h16M4 18h16"
                            />
                            <path
                                :class="{
                                    hidden: !showingNavigationDropdown,
                                    'inline-flex':
                                        showingNavigationDropdown,
                                }"
                                stroke-linecap="round"
                                stroke-linejoin="round"
                                stroke-width="2"
                                d="M6 18L18 6M6 6l12 12"
                            />
                        </svg>
                    </button>
                </div>

                <div class="flex items-center mt-4 lg:mt-0 space-x-4 w-full justify-between lg:w-auto">
                    <div class="relative w-56 xl:w-80">
                        <span class="absolute left-3 top-1/2 transform -translate-y-1/2 text-navy-blue text-xl">
                            <i class="fas fa-search"></i>
                        </span>
                        <input type="text" placeholder="Search..." class="w-full pl-10 p-2 rounded-full bg-[#bbe2ec] text-navy-blue"/>
                    </div>
                    <div class="flex">
                        <div class="flex justify-center items-center">
                            <i class="fas fa-bell text-linen text-xl"></i>
                        </div>
                        
                        
                        <div class="sm:ms-6 sm:flex sm:items-center">
                            <!-- Settings Dropdown -->
                            <div class="relative ms-3">
                                <Dropdown align="right" width="48">
                                    <template #trigger>
                                        <span class="inline-flex rounded-md">
                                            <button
                                                type="button"
                                                class="inline-flex items-center rounded-full bg-sky-blue px-1 py-1 text-sm font-medium leading-4 text-gray-500 transition duration-150 ease-in-out hover:text-gray-700 focus:outline-none dark:bg-gray-800 dark:text-gray-400 dark:hover:text-gray-300"
                                            >
                                                <AuthImage class="h-8 w-8 rounded-full object-cover" :user="page.props.auth.user" />
                                                <svg
                                                    class="-me-0.5 ms-2 h-4 w-4"
                                                    xmlns="http://www.w3.org/2000/svg"
                                                    viewBox="0 0 20 20"
                                                    fill="currentColor"
                                                >
                                                    <path
                                                        fill-rule="evenodd"
                                                        d="M5.293 7.293a1 1 0 011.414 0L10 10.586l3.293-3.293a1 1 0 111.414 1.414l-4 4a1 1 0 01-1.414 0l-4-4a1 1 0 010-1.414z"
                                                        clip-rule="evenodd"
                                                    />
                                                </svg>
                                            </button>
                                        </span>
                                    </template>

                                    <template #content>
                                        <DropdownLink
                                            class="hover:bg-crystal-blue"
                                            :href="route('profile.edit')"
                                        >
                                            Profile
                                        </DropdownLink>
                                        <DropdownLink
                                            class="hover:bg-crystal-blue"
                                            :href="route('logout')"
                                            method="post"
                                            as="button"
                                        >
                                            Log Out
                                        </DropdownLink>
                                    </template>
                                </Dropdown>
                            </div>
                        </div>
                        

                    </div>
                </div>
            </header>

            <!-- Page Content -->
            <main class="flex-grow space-y-4 lg:space-y-0 lg:space-x-4 lg:flex">
                <!-- Slot for main content -->
                <slot />
            </main>
        </div>

        <!-- Right Sidebar (Hidden on Mobile) -->
        <aside v-if="!hideSidebar" class="w-full lg:w-64 bg-color-white p-4 dark:bg-gray-800 hidden xl:block">
            <div class="mb-6 text-lg font-semibold text-gray-800 dark:text-gray-100">Quick Actions </div>
            <button class="block w-full mb-4 py-2 text-linen bg-sky-blue rounded-full">Create New Task</button>
            <!-- <button class="block w-full mb-4 py-2 text-white bg-blue-500 rounded">Join Meeting</button> -->
            <div class="mt-6">
                <h3 class="font-semibold text-gray-800 dark:text-gray-100 mb-2">People You May Know</h3>
                <!-- Placeholder for connection suggestions -->
            </div>
        </aside>
    </div>
</template>

