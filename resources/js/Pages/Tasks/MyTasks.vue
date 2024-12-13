<script setup>
import { ref } from 'vue';
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head } from '@inertiajs/vue3';
import TaskCard from './TaskCard.vue';

const props = defineProps({
  tasks: Object,
  userRole: String,
});

/* let expandedTask = ref(false);

const toggleTask = (index) => {
    expandedTask.value = expandedTask.value === index ? null : index;
}; */

console.log('tasks', props.tasks)


</script>


<template>
    <Head title="Tasks" />

    <AuthenticatedLayout pageTitle="My Tasks">
        <div class="w-full bg-linen p-4">
            <div class="flex justify-between">
                <p class="text-navy-blue text-lg">
                    Track your progress, prioritize effectively, and stay on top of your responsibilities. 
                    Every task brings you one step closer to your goals.
                </p>
            </div>
            <div class="w-full px-4 py-6 sm:px-6 lg:px-8">
                <!-- Tasks List Section -->
                <section 
                    class="grid grid-cols-1 sm:grid-cols-2 2xl:grid-cols-4 xl:grid-cols-3 gap-4 items-start"
                >
                    <TaskCard v-for="(item, index) in tasks" :key="item.id" :task="item" :tasks="item.project.tasks" :completedId="item.project.completed_status_id" :members="item.project.users" />
                </section>
            </div>
        </div>
    </AuthenticatedLayout>


</template>

<style scoped>
.cursor-pointer {
    cursor: pointer;
}

.expanded {
    transition: all 0.3s ease-in-out;
}
</style>
