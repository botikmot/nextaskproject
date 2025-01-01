<script setup>
import { ref, defineEmits, computed, onMounted } from 'vue';

const props = defineProps({
    progress: Array,
    challenge: Object,
});

const totalPoints = computed(() => {
    return props.progress
                .filter(task => task.project.completed_status_id === task.status.id)
                .reduce((sum, task) => sum + task.points, 0);
});

</script>

<template>
    <div class="w-full mx-auto p-6 bg-white">
        <h1 class="text-2xl font-bold text-navy-blue">{{ challenge.name }}</h1>
        <hr class="text-dark-gray mt-3" />
        <div class="my-2">
            <div class="text-navy-blue"><strong>Total Points Needed:</strong> {{ challenge.points }}</div>
            <div class="text-navy-blue font-bold">
                Total Points Earned:<span class="text-red-warning pl-2">{{ totalPoints }}</span>
            </div>
            <div class="text-navy-blue"><strong>Progress:</strong> {{ (totalPoints / challenge.points) * 100 | round }}%</div>
            <div class="w-full bg-crystal-blue rounded-full h-3 mt-1">
                <div 
                    class="bg-sky-blue h-3 rounded-full" 
                    :style="{ width: `${(totalPoints / challenge.points) * 100}%` }"
                ></div>
            </div>
        </div>
        <div class="py-3 w-full">
            <h5 class="font-bold text-lg text-navy-blue mb-3">Related Tasks</h5>
            <table class="w-full border border-dark-gray border-collapse">
                <thead class="text-navy-blue border-b border-dark-gray">
                    <tr>
                        <th class="border-r border-dark-gray px-4 py-1">Tasks</th>
                        <th class="border-r border-dark-gray px-4 py-1">Points</th>
                        <th class="px-4 py-1">Status</th>
                    </tr>
                </thead>
                <tbody>
                    <tr 
                        v-for="task in progress" 
                        :key="task.id" 
                        :class="`border-b border-dark-gray ${ task.project.completed_status_id == task.status.id ? 'text-sky-blue' : 'text-navy-blue' }`"
                    >
                        <td class="capitalize border-r border-dark-gray px-4 py-1">{{ task.title }}</td>
                        <td class="text-center border-r border-dark-gray px-4 py-1">{{ task.points }}</td>
                        <td class="px-4 py-1">{{ task.status.name }}</td>
                    </tr>
                    <tr class="border-b border-dark-gray">
                        <td class="capitalize text-sky-blue border-r font-bold border-dark-gray px-4 py-1">Total completed</td>
                        <td class="text-center border-r border-dark-gray px-4 py-1 text-sky-blue font-bold">{{ totalPoints }}</td>
                        <td class="text-xs pl-4 py-1"></td>
                    </tr>
                </tbody>
            </table>
        </div>
    </div>
</template>