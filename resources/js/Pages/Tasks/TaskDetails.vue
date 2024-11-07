<script setup>
import { usePage, useForm } from '@inertiajs/vue3'
import { ref } from 'vue';

let isMemberModalOpen = ref(false);

const props = defineProps({
    task: Object,
    members: Object,
});

const form = useForm({
    assigned_member: [],
});

const openMemberModal = () => {
    isMemberModalOpen.value = !isMemberModalOpen.value;
}

/* const getPriorityClass = (priority) => {
  switch (priority) {
    case 'high': return 'bg-[#EF4444]';
    case 'medium': return 'bg-[#D97706]';
    case 'low': return 'bg-[#38A169]';
    default: return 'bg-gray';
  }
}; */

</script>

<template>
    <div class="bg-color-white rounded-lg shadow-lg p-6 w-full">
        <h3 class="text-lg text-navy-blue font-semibold mb-4">Task Details</h3>
        <div class="mb-4">
            <label for="projectName" class="block text-sm font-medium">Task Name</label>
            <input type="text" id="projectName" v-model="task.title" class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:ring focus:ring-sky-blue" required>
        </div>
        <div class="mb-4">
            <label for="projectDescription" class="block text-sm font-medium">Description</label>
            <textarea id="projectDescription" v-model="task.description" class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:ring focus:ring-sky-blue"></textarea>
        </div>
        <div class="block sm:flex w-full">
            <div class="mb-4 w-full sm:w-1/3 mr-1">
                <label for="dueDate" class="block text-sm font-medium">Due Date</label>
                <input
                    type="date"
                    id="dueDate"
                    v-model="task.due_date"
                    class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:ring focus:ring-sky-blue"
                />
            </div>
            <div class="mb-4 w-full sm:w-1/3 mx-1">
                <label for="status" class="block text-sm font-medium">Priority</label>
                <select
                    id="status"
                    v-model="task.priority"
                    class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:ring focus:ring-sky-blue"
                >
                    <option value="low">Low</option>
                    <option value="medium">Medium</option>
                    <option value="high">High</option>
                </select>
            </div>
            <div class="mb-4 w-full sm:w-1/3 ml-1">
                <label for="status" class="block text-sm font-medium">Status</label>
                <select
                    id="status"
                    v-model="task.status"
                    class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:ring focus:ring-sky-blue"
                >
                    <option value="To Do">To Do</option>
                    <option value="In Progress">In Progress</option>
                    <option value="Completed">Completed</option>
                </select>
            </div>
        </div>
        <div class="mb-4">
            <div class="flex py-2">
                <label for="assignedMembers" class="block text-sm font-medium mb-2">Assigned Members:</label>
                <div class="pl-2 cursor-pointer -mt-1 relative group" @click="openMemberModal">
                    <i class="fas fa-circle-plus text-xl text-sky-blue"></i>
                    <span class="absolute top-full mt-1 right-0 transform -translate-x-1 hidden group-hover:flex items-center px-4 py-3 text-sm font-semibold text-white bg-light-gray rounded-md shadow-lg z-10 whitespace-nowrap">
                        Add Member
                    </span>
                </div>
            </div>
            <div class="pb-3" v-if="isMemberModalOpen">
                <select
                    id="assignedMembers"
                    v-model="form.assigned_members"
                    multiple
                    class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:ring focus:ring-sky-blue"
                >
                    <option class="text-navy-blue" v-for="member in members" :key="member.id" :value="member.user_id">{{ member.user.name }}</option>
                </select>
            </div>
            <div class="flex">
                <div v-for="(user, index) in task.users" :key="index" class="flex items-center pl-2">
                    <img :src="'/' + user.profile_image" alt="User Avatar" class="w-6 h-6 rounded-full border-2 border-white" />
                    <span class="text-sm text-gray-700 pl-1">{{ user.name }}</span>
                </div>
            </div>
        </div>

         <!-- Tags -->
         <div class="flex space-x-2">
            <span v-for="(tag, index) in task.labels" :key="index" class="text-xs text-blue-600 bg-blue-100 px-2 py-1 rounded-full">
                {{ tag }}
            </span>
        </div>

        <!-- Progress Bar -->
        <div class="w-full h-2 bg-gray rounded-full">
            <div :style="{ width: 50 + '%' }" class="h-full bg-sky-blue rounded-full"></div>
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