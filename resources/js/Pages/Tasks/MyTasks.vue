<script setup>
import { ref } from 'vue';
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head } from '@inertiajs/vue3';

const props = defineProps({
  tasks: Object,
  userRole: String,
});

let expandedTask = ref(false);

const toggleTask = (index) => {
    expandedTask.value = expandedTask.value === index ? null : index;
};

console.log('tasks', props.tasks)


</script>


<template>
    <Head title="Tasks" />

    <AuthenticatedLayout pageTitle="My Tasks">
        <div class="w-full bg-linen p-4">
            <div>
                <p class="text-navy-blue font-bold text-xl">Track your progress, prioritize effectively, and stay on top of your responsibilities. Every task brings you one step closer to your goals.</p>
            </div>
            <div class="w-1/4 px-4 py-6 sm:px-6 lg:px-8">
                <!-- Tasks List Section -->
                <section class="space-y-4">
                    <div
                        v-for="(task, index) in tasks"
                        :key="task.id"
                        class="bg-color-white shadow-lg rounded p-4 cursor-pointer"
                        @click="toggleTask(index)"
                    >
                        <div class="flex justify-between items-center">
                            <h2 class="text-lg font-semibold capitalize">{{ task.title }}</h2>
                            <span
                                :class="{
                                    'text-blue-500': task.status.name === 'In Progress',
                                    'text-green-500': task.status.name === 'Completed',
                                    'text-yellow-500': task.status.name === 'To Do',
                                }"
                            >
                                {{ task.status.name }}
                            </span>
                        </div>
                        <div v-if="expandedTask === index" class="mt-2 text-sm text-gray-600">
                            <p><strong>Deadline:</strong> {{ task.due_date }}</p>
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
