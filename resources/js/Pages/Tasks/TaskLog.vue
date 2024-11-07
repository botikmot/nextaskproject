<script setup>

const props = defineProps({
    task: Object,
});

const getPriorityClass = (priority) => {
  switch (priority) {
    case 'high': return 'bg-[#EF4444]';
    case 'medium': return 'bg-[#D97706]';
    case 'low': return 'bg-[#38A169]';
    default: return 'bg-gray';
  }
};

</script>

<template>
    <div class="bg-color-white p-4 mb-3 rounded shadow-lg border relative border-dark-gray">
        <!-- Task Title -->
        <h3 class="text-lg font-semibold text-gray-800 truncate">{{ task.title }}</h3>
        
        <!-- Task Assignee -->
        <div class="flex items-center space-x-2">
            <span class="text-sm text-gray-500">Assigned to:</span>
            <div v-for="(user, index) in task.users" :key="index" class="flex items-center space-x-1">
                <img :src="'/' + user.profile_image" alt="User Avatar" class="w-6 h-6 rounded-full border-2 border-white" />
                <span class="text-sm text-gray-700">{{ user.name }}</span>
            </div>
        </div>

        <!-- Task Priority -->
        <div class="text-sm">
            <span class="font-semibold">Priority:</span>
            <span :class="getPriorityClass(task.priority)" class="inline-block px-2 py-1 rounded text-white text-sm">
                {{ task.priority }}
            </span>
        </div>

        <!-- Due Date -->
        <div class="text-sm text-gray-600">
            <span class="font-semibold">Due Date:</span> {{ task.due_date }}
        </div>

        <!-- Task Description -->
        <p class="text-sm text-gray-700 line-clamp-3">{{ task.description }}</p>

        <!-- Task Status -->
        <div class="flex items-center space-x-2">
            <span class="font-semibold text-gray-600">Status:</span>
            <span class="text-sm px-3 py-1 rounded-full bg-blue-100 text-blue-600">{{ task.status }}</span>
        </div>

        <!-- Progress Bar -->
        <div class="w-full h-2 bg-gray-200 rounded-full">
            <div :style="{ width: 50 + '%' }" class="h-full bg-blue-500 rounded-full"></div>
        </div>

        <!-- Tags -->
        <div class="flex space-x-2">
            <span v-for="(tag, index) in task.labels" :key="index" class="text-xs text-blue-600 bg-blue-100 px-2 py-1 rounded-full">
                {{ tag }}
            </span>
        </div>

        <!-- Comments and Attachments -->
        <!-- <div class="flex items-center justify-between text-sm text-gray-500">
            <span>{{ task.comments }} comments</span>
            <div v-if="task.attachments > 0" class="flex items-center space-x-1">
                <span>{{ task.attachments }} attachments</span>
                <svg xmlns="http://www.w3.org/2000/svg" class="w-4 h-4 text-gray-500" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 10l5 5-5 5M13 10l5 5-5 5" />
                </svg>
            </div>
        </div> -->
    </div>
</template>