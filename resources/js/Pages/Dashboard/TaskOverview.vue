<script setup>
import { ref, computed, onMounted } from 'vue';
import { usePage } from '@inertiajs/vue3';

const props = defineProps({
    tasks: Object,
});
console.log('tasks', props.tasks)
const authUserId = usePage().props.auth.user.id
const data = ref({})
let totalTasksThisWeek = ref(0)
let tasksDueToday = ref(0)
let taskCompletionRate = ref(0)

const getTasksCompletionRate = async () => {
    const response = await axios.get('/tasks/tasks-completion-rate');
    console.log('total task this week-->' ,response.data)
    if(response.data.success){
        totalTasksThisWeek.value = response.data.totalTasksThisWeek
        taskCompletionRate.value = response.data.taskCompletionRate
        tasksDueToday.value = response.data.tasksDueToday
    }
}

onMounted(() => {    
    const savedProjects = JSON.parse(localStorage.getItem('selectedProjects')) || [];
    const savedStatuses = JSON.parse(localStorage.getItem('selectedStatuses')) || [];
    data.value.selectedProjects = savedProjects;
    data.value.selectedStatuses = savedStatuses;
    
    const savedSortBy = localStorage.getItem('SortBy') || 'title';
    const savedOrderBy = localStorage.getItem('OrderBy') || 'asc';

    data.value.sortBy = savedSortBy;
    data.value.sortOrder = savedOrderBy;
    getTasksCompletionRate()
});


</script>

<template>
    <div class="">
        <h2 class="text-lg font-bold text-navy-blue border-b border-dark-gray pb-2">Task Overview</h2>
        <template v-if="totalTasksThisWeek === 0">
            <p class="mt-2 text-gray-500">No tasks have been created/assigned to you this week yet.</p>
            <p class="text-gray-500 mb-8">Start by creating your first task to manage your productivity!</p>
            <a
                class="absolute bottom-5 right-5 cursor-pointer px-6 py-3 bg-gradient-to-r from-navy-blue to-sky-blue text-color-white rounded-full hover:from-sky-blue hover:to-navy-blue hover:shadow-lg"
                :href="route('my-tasks', {
                                selectedProjects: data.selectedProjects,
                                selectedStatuses: data.selectedStatuses,
                                sortBy: data.sortBy,
                                sortOrder: data.sortOrder
                            })"
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
                class="absolute bottom-5 right-5 px-6 cursor-pointer py-3 bg-gradient-to-r from-navy-blue to-sky-blue text-color-white rounded-full hover:from-sky-blue hover:to-navy-blue hover:shadow-lg"
                :href="route('my-tasks', {
                                selectedProjects: data.selectedProjects,
                                selectedStatuses: data.selectedStatuses,
                                sortBy: data.sortBy,
                                sortOrder: data.sortOrder
                            })"
            >
                View All Tasks
            </a>
        </template>
        <template v-else>
            <p class="mt-2 text-red-warning">You have {{ tasksDueToday }} tasks due today.</p>
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
                class="absolute bottom-5 right-5 px-6 cursor-pointer py-3 bg-gradient-to-r from-navy-blue to-sky-blue text-color-white rounded-full hover:from-sky-blue hover:to-navy-blue hover:shadow-lg"
                :href="route('my-tasks', {
                                selectedProjects: data.selectedProjects,
                                selectedStatuses: data.selectedStatuses,
                                sortBy: data.sortBy,
                                sortOrder: data.sortOrder
                            })"
            >
                View Tasks
            </a>
        </template>
    </div>
</template>