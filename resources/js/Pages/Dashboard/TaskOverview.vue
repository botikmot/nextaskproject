<script setup>
import { ref, computed, onMounted } from 'vue';

const props = defineProps({
    tasks: Object,
});
console.log('tasks', props.tasks)

// Get tasks created this week
const totalTasksThisWeek = computed(() => {
    const startOfWeek = getStartOfWeek();
    const endOfWeek = getEndOfWeek();
    console.log('startOfWeek', startOfWeek)
    console.log('endOfWeek', endOfWeek)
    return props.tasks.filter(task => {
      const taskDate = new Date(task.created_at);
      return taskDate >= startOfWeek && taskDate <= endOfWeek;
    }).length;
})

// Get completed tasks this week
const tasksCompletedThisWeek = computed(() => {
    const startOfWeek = getStartOfWeek();
    const endOfWeek = getEndOfWeek();
    return props.tasks.filter(task => {
        const taskDate = new Date(task.created_at);
        return (
            taskDate >= startOfWeek &&
            taskDate <= endOfWeek &&
            task.project.completed_status_id === task.status.id
        );
    }).length;
});

// Task completion rate
const taskCompletionRate = computed(() => {
    const total = totalTasksThisWeek.value;
    const completed = tasksCompletedThisWeek.value;

    console.log('total', total);
    console.log('completed', completed);

    return total > 0 ? Math.round((completed / total) * 100) : 0; // Avoid division by zero
});

// Computed property to get tasks due today, excluding completed tasks
const tasksDueToday = computed(() => {
    const today = getToday();
    return props.tasks.filter(task => {
        return task.due_date === today && task.project.completed_status_id !== task.status.id;
    }).length;
});

const getToday = () => {
    const today = new Date();
    return today.toISOString().split('T')[0];
}
console.log('Tasks due today:', tasksDueToday.value);
const getStartOfWeek = () => {
    const today = new Date();
    const day = today.getDay(); // 0 (Sunday) to 6 (Saturday)
    const diff = today.getDate() - day + (day === 0 ? -6 : 1); // Adjust when it's Sunday
    //return new Date(today.setDate(diff));
    const startOfWeek = new Date(today.setDate(diff)); // Set to the start of the week
    startOfWeek.setHours(0, 0, 0, 0); // Set time to 8:00 AM

    return startOfWeek;
}
const getEndOfWeek = () => {
    const startOfWeek = getStartOfWeek();
    const endOfWeek = new Date(startOfWeek); // Clone the startOfWeek date
    endOfWeek.setDate(startOfWeek.getDate() + 6); // Add 6 days to get the end of the week
    endOfWeek.setHours(23, 59, 59, 999); // Optionally, set to the end of the day
    return endOfWeek;
};
console.log('totalTasksThisWeek', totalTasksThisWeek.value)

</script>

<template>
    <div class="bg-color-white p-6 rounded-lg shadow-md">
        <h2 class="text-lg font-bold text-navy-blue border-b border-dark-gray pb-2">Task Overview</h2>
        <template v-if="totalTasksThisWeek === 0">
            <p class="mt-2 text-gray-500">No tasks have been created this week yet.</p>
            <p class="text-gray-500 mb-8">Start by creating your first task to manage your productivity!</p>
            <a
                class="mt-6 cursor-pointer px-6 py-3 bg-sky-blue text-color-white rounded-full hover:font-bold hover:bg-crystal-blue hover:text-navy-blue hover:shadow-lg"
                :href="route('my-tasks')"
            >
                View Tasks
            </a>
        </template>
        <template v-else-if="tasksDueToday === 0">
            <p class="mt-2">You have no tasks due today.</p>
            <div class="mt-4 mb-6">
                <div class="bg-crystal-blue rounded-full w-full h-2">
                    <div
                        class="bg-navy-blue h-2 rounded-full"
                        :style="{ width: taskCompletionRate + '%' }"
                    ></div>
                </div>
                <p class="text-sm mt-2">{{ taskCompletionRate }}% of tasks completed this week.</p>
            </div>
            <a
                class="mt-4 px-6 cursor-pointer py-3 bg-sky-blue text-color-white rounded-full hover:font-bold hover:bg-crystal-blue hover:text-navy-blue hover:shadow-lg"
                :href="route('my-tasks')"
            >
                View All Tasks
            </a>
        </template>
        <template v-else>
            <p class="mt-2">You have {{ tasksDueToday }} tasks due today.</p>
            <div class="mt-4 mb-6">
                <div class="bg-crystal-blue rounded-full h-2">
                    <div
                    class="bg-navy-blue h-2 rounded-full"
                    :style="{ width: taskCompletionRate + '%' }"
                    ></div>
                </div>
                <p class="text-sm mt-2">{{ taskCompletionRate }}% of tasks completed this week.</p>
            </div>
            <a
                class="mt-4 px-6 cursor-pointer py-2 bg-sky-blue text-color-white rounded-full hover:font-bold hover:bg-crystal-blue hover:text-navy-blue hover:shadow-lg"
                :href="route('my-tasks')"
            >
                View Tasks
            </a>
        </template>
    </div>
</template>