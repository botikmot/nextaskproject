<script setup>
import { ref } from 'vue';
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head } from '@inertiajs/vue3';

const tasks = [
    {
        id: 1,
        title: "Design Landing Page",
        status: "In Progress",
        deadline: "2024-11-10",
        priority: "High",
        assignedTo: "Alice Johnson",
        description: "Create a modern, responsive design for the homepage using Figma.",
    },
    {
        id: 2,
        title: "Set Up Database",
        status: "Pending",
        deadline: "2024-11-15",
        priority: "Medium",
        assignedTo: "Bob Smith",
        description: "Initialize the database schema for the new project and set up sample data.",
    },
    {
        id: 3,
        title: "Review Codebase",
        status: "Completed",
        deadline: "2024-11-01",
        priority: "Low",
        assignedTo: "Carol White",
        description: "Review and refactor the existing codebase for consistency and efficiency.",
    },
];
let expandedTask = ref(false);

const toggleTask = (index) => {
    expandedTask.value = expandedTask.value === index ? null : index;
};


</script>


<template>
    <Head title="Tasks" />

    <AuthenticatedLayout pageTitle="My Tasks">
        <div class="w-full">
            <div>
                <p>Track your progress, prioritize effectively, and stay on top of your responsibilities. Every task brings you one step closer to your goals.</p>
            </div>
            <div class="w-1/3 px-4 py-6 sm:px-6 lg:px-8">
                <!-- Tasks List Section -->
                <section class="space-y-4">
                    <div
                        v-for="(task, index) in tasks"
                        :key="task.id"
                        class="bg-white shadow rounded p-4 cursor-pointer"
                        @click="toggleTask(index)"
                    >
                        <div class="flex justify-between items-center">
                            <h2 class="text-lg font-semibold">{{ task.title }}</h2>
                            <span
                                :class="{
                                    'text-blue-500': task.status === 'In Progress',
                                    'text-green-500': task.status === 'Completed',
                                    'text-yellow-500': task.status === 'Pending',
                                }"
                            >
                                {{ task.status }}
                            </span>
                        </div>
                        <div v-if="expandedTask === index" class="mt-2 text-sm text-gray-600">
                            <p><strong>Deadline:</strong> {{ task.deadline }}</p>
                            <p><strong>Priority:</strong> {{ task.priority }}</p>
                            <p><strong>Assigned to:</strong> {{ task.assignedTo }}</p>
                            <p class="mt-2">{{ task.description }}</p>
                        </div>
                    </div>
                </section>
            </div>
        </div>
    </AuthenticatedLayout>
</template>

<style scoped>
.cursor-pointer {
    cursor: pointer;
}
</style>
