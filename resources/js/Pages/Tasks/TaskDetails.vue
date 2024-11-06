<script setup>
const props = defineProps({
    task: Object,
});

</script>


<template>
    <div class="bg-white p-3 mb-3 rounded shadow-lg border border-dark-gray cursor-pointer">
        <div class="flex relative">
            <i :class="[
                    'fa-solid fa-flag text-xs pr-1 flex items-center',
                    task.priority === 'low' ? 'text-[#38A169]' : 
                    task.priority === 'medium' ? 'text-[#D97706]' : 
                    task.priority === 'high' ? 'text-[#EF4444]' : ''
                ]" :title="[
                    task.priority === 'low' ? 'Low Priority' : 
                    task.priority === 'medium' ? 'Medium Priority' : 
                    task.priority === 'high' ? 'High Priority' : ''
                ]">
            </i>
            <h3 class="text-md text-navy-blue font-semibold">{{ task.title }}</h3>
            <div v-if="task.user_id == $page.props.auth.user.id" class="absolute text-sm right-0 top-0">
                <i class="fa-solid fa-ellipsis"></i>
            </div>
        </div>
        <p class="text-sm text-gray">{{ task.description }}</p>
        <p class="text-sm text-gray">Due Date: {{ task.due_date }}</p>
        <p class="text-sm text-gray text-xs">{{ task.created_at }}</p>
    </div>
</template>