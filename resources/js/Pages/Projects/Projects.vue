<script setup>
import { ref } from 'vue';
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head, Link, usePage } from '@inertiajs/vue3';
import CreateProjectModal from './CreateProjectModal.vue';

let isModalOpen = ref(false);

const props = defineProps({
  projects: Object,
});

console.log(props.projects);

const openModal = () => {
    isModalOpen.value = true;
}

</script>

<template>
    <Head title="Projects" />

    <AuthenticatedLayout pageTitle="Projects">

        <div v-if="props.projects.length" class="flex flex-col space-y-6 w-full">
            <section class="flex justify-between">
                <div>
                    <!-- <h1 class="text-2xl font-semibold">My Projects</h1> -->
                    <p class="text-navy-blue text-xl">
                        Organize and manage your projects, track progress, and collaborate with your team.
                    </p>
                </div>
                <!-- Quick Actions Section -->
                <div class="block"></div>
            </section>

            <!-- Projects Section -->
            <section class="w-full">
                <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-4">
                    <a v-for="project in projects" :key="project.id" :href="route('project.show', project.id)" class="bg-white p-4 shadow rounded cursor-pointer hover:bg-linen">
                        <h2 class="text-lg text-sky-blue font-semibold mb-2">{{ project.title }}</h2>
                        <div class="space-y-1">
                            <h3 class="text-md text-navy-blue font-medium">{{ project.description }}</h3>
                            <p class="text-sm text-blue-600">Created: {{ project.created_at }}</p>
                            <p class="text-sm text-blue-600">Members: {{  project.members.length }}</p>
                        </div>
                    </a>
                    <!-- Add New Project Button -->
                    <button
                        @click="openModal"
                        class="flex text-navy-blue flex-col items-center justify-center bg-gray-100 p-4 shadow rounded cursor-pointer hover:text-linen hover:bg-sky-blue"
                    >
                        <span class="text-4xl text-blue-500">+</span>
                        <span class="text-sm font-semibold mt-2">Add New Project</span>
                    </button>
                </div>
                <!-- <div class="flex-1 bg-white p-4 shadow rounded">
                    <h2 class="text-lg font-semibold mb-2">Active Projects</h2>
                    <ul class="space-y-3">
                        
                        <li class="border-b pb-3">
                            <h3 class="text-md font-medium">Project Alpha</h3>
                            <p class="text-sm text-gray-500">Deadline: Dec 15, 2024</p>
                            <p class="text-sm">Description: A website redesign project aimed at improving user experience.</p>
                            <p class="text-sm text-blue-600">Status: In Progress</p>
                        </li>
                        <li class="border-b pb-3">
                            <h3 class="text-md font-medium">Project Beta</h3>
                            <p class="text-sm text-gray-500">Deadline: Jan 10, 2025</p>
                            <p class="text-sm">Description: Development of a mobile application for task management.</p>
                            <p class="text-sm text-green-600">Status: On Track</p>
                        </li>
                    </ul>
                </div> 
                <div class="flex-1 bg-white p-4 shadow rounded">
                    <h2 class="text-lg font-semibold mb-2">Completed Projects</h2>
                    <ul class="space-y-3">
                        
                        <li class="border-b pb-3">
                            <h3 class="text-md font-medium">Project Gamma</h3>
                            <p class="text-sm text-gray-500">Completed on: Oct 01, 2024</p>
                            <p class="text-sm">Description: Implementation of social features in NexTask platform.</p>
                        </li>
                    </ul>
                </div> -->
            </section>
        </div>

        <div v-else class="flex flex-col space-y-6 w-full">
            <section>
                <!-- <h1 class="text-2xl text-sky-blue font-semibold">My Projects</h1> -->
                <p class="text-navy-blue text-xl mt-3">
                    Welcome to <span class="font-bold">NexTask!</span> Let’s get started with managing your projects.
                </p>
                <p class="text-navy-blue mt-3">
                    To begin, you can create your first project by clicking the button below. 
                    Organize your tasks, set deadlines, and collaborate with your team to achieve your goals!
                </p>
            </section>

            <!-- No Projects Section for Newly Registered User -->
            <section class="flex-1 bg-white p-4 shadow rounded">
                <h2 class="text-lg font-semibold mb-2 text-navy-blue">You currently have no projects</h2>
                <p class="text-navy-blue mb-4">
                    Start your journey by creating a new project!
                </p>
                <button
                    @click="openModal"
                    class="font-semibold text-lg bg-sky-blue text-linen py-2 px-6 rounded-full inline-block hover:bg-crystal-blue hover:text-navy-blue hover:shadow-lg"
                    >
                    + Create Your First Project
                </button>
            </section>
        </div>

        <!-- Modal -->
        <div v-if="isModalOpen" class="fixed inset-0 flex items-center justify-center z-50 bg-black bg-opacity-50">
            <CreateProjectModal @close="isModalOpen = false"/>
        </div>

    </AuthenticatedLayout>
</template>

<style scoped>
/* Add any additional styles here if needed */
</style>
