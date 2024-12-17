<script setup>
import { ref, computed } from 'vue';
import Modal from '@/Components/Modal.vue';
import NewTaskModal from '@/Pages/Projects/NewTaskModal.vue';
import axios from 'axios';
import { usePage, useForm } from '@inertiajs/vue3'
import Swal from 'sweetalert2';

const page = usePage();
let isTaskModalOpen = ref(false);
let projects = ref([])
let labels = ref(null)
const props = defineProps({
    projects: Object,
    labels: Object,
});

const form = useForm({
    receiver_id: null,
});

const suggestedFriends = page.props.suggestedFriends || [];
const receivedFriendRequests = page.props.receivedFriendRequests || [];

const createTask = async () => {
    const response = await axios.get('/tasks');
    if(response.data.success){
        console.log(response)
        projects.value = response.data.projects
        labels.value = response.data.labels
        isTaskModalOpen.value = true
    }
}

const addFriend = (friend_id) => {
    console.log('friend_id', friend_id)
    form.receiver_id = friend_id
    form.post('/friend-requests', {
        data: form,
        preserveScroll: true,
        onSuccess: (response) => {
            form.reset()
            Swal.fire({
                text: response.props.flash.message,
                position: 'bottom-end',
                backdrop: false,
                timer: 2000,
                showConfirmButton: false,
                toast:true,
                icon: response.props.flash.success ? 'success' : 'error',
            });
        },
        onError: (error) => {
            console.error('Error sending friend requests', error)
        }
    })
}

const acceptFriendRequest = (id) => {
    form.post(`/friend-requests/${id}/accept`, {
        data: form,
        preserveScroll: true,
        onSuccess: (response) => {
            form.reset()
            Swal.fire({
                text: response.props.flash.message,
                position: 'bottom-end',
                backdrop: false,
                timer: 2000,
                showConfirmButton: false,
                toast:true,
                icon: response.props.flash.success ? 'success' : 'error',
            });
        },
        onError: (error) => {
            console.error('Error accepting friend requests', error)
        }
    })
}

const rejectFriendRequest = (id) => {
    form.post(`/friend-requests/${id}/reject`, {
        data: form,
        preserveScroll: true,
        onSuccess: (response) => {
            form.reset()
            Swal.fire({
                text: response.props.flash.message,
                position: 'bottom-end',
                backdrop: false,
                timer: 2000,
                showConfirmButton: false,
                toast:true,
                icon: response.props.flash.success ? 'success' : 'error',
            });
        },
        onError: (error) => {
            console.error('Error rejecting friend requests', error)
        }
    })
}

console.log('receivedFriendRequests', receivedFriendRequests)
</script>

<template>
    <div class="mb-6 text-lg font-semibold text-gray-800 dark:text-gray-100">Quick Actions </div>
    <button @click="createTask" class="block w-full mb-4 py-2 text-linen hover:font-bold bg-sky-blue rounded-full hover:bg-crystal-blue hover:text-navy-blue hover:shadow-lg">Create New Task</button>
    <!-- <button class="block w-full mb-4 py-2 text-white bg-blue-500 rounded">Join Meeting</button> -->

    <div v-if="receivedFriendRequests.length > 0" class="mt-6">
        <h3 class="font-bold text-navy-blue mb-4">You Have Friend Requests</h3>
        <div
            class="py-1 px-3 mb-2 border-b border-dark-gray hover:bg-light-gray"
            v-for="request in receivedFriendRequests"
            :key="request.id"
        >
            <div class="flex items-center justify-between">
                <!-- Profile Section -->
                <div class="flex items-center space-x-3">
                    <img
                        :src="'/' + request.sender.profile_image"
                        alt="Profile"
                        class="w-8 h-8 rounded-full object-cover border-2 border-white"
                    />
                    <div>
                        <div class="text-navy-blue font-medium text-sm">{{ request.sender.name }}</div>
                        <div class="text-xs text-gray">Mutual Projects: {{ request.sender.mutual_projects }}</div>
                    </div>
                </div>
                <!-- Accept/Reject Button -->
                <div class="relative group">
                    <button
                        @click="acceptFriendRequest(request.id)"
                        class="w-8 h-8 rounded-full cursor-pointer hover:bg-crystal-blue text-color-white flex items-center justify-center transition duration-200 ease-in-out"
                    >
                        <i class="fas fa-check text-green text-sm"></i> <!-- Accept Icon -->
                    </button>
                    <button
                        @click="rejectFriendRequest(request.id)"
                        class="w-8 h-8 rounded-full cursor-pointer hover:bg-crystal-blue text-color-white flex items-center justify-center transition duration-200 ease-in-out"
                    >
                        <i class="fas fa-times text-red-warning text-sm"></i> <!-- Reject Icon -->
                    </button>
                </div>
            </div>
        </div>
    </div>




    <div class="mt-6">
        <h3 class="font-bold text-navy-blue mb-4">People You May Know</h3>
        <div
            class="py-1 px-3 mb-2 border-b border-dark-gray hover:bg-light-gray"
            v-for="suggested in suggestedFriends"
            :key="suggested.id"
        >
            <div class="flex items-center justify-between">
                <!-- Profile Section -->
                <div class="flex items-center space-x-3">
                    <img
                        :src="'/' + suggested.profile_image"
                        alt="Profile"
                        class="w-8 h-8 rounded-full object-cover border-2 border-white"
                    />
                    <div>
                        <div class="text-navy-blue font-medium text-sm">{{ suggested.name }}</div>
                        <div class="text-xs text-gray">Mutual Projects: {{ suggested.mutual_projects }}</div>
                    </div>
                </div>
                <!-- Add Friend Icon with Tooltip -->
                <div class="relative group">
                    <button
                        @click="addFriend(suggested.id)"
                        class="w-8 h-8 rounded-full cursor-pointer hover:bg-crystal-blue text-color-white flex items-center justify-center transition duration-200 ease-in-out"
                    >
                        <i class="fas fa-user-plus text-sky-blue text-sm"></i> <!-- Add Friend Icon -->
                    </button>
                    <div
                        class="absolute top-full mt-1 right-0 transform -translate-x-1 hidden group-hover:flex items-center px-4 py-3 text-sm font-semibold text-white bg-light-gray rounded-md shadow-lg z-10 whitespace-nowrap"
                    >
                        Add Friend
                    </div>
                </div>
            </div>
        </div>
    </div>


    <Modal :show="isTaskModalOpen" @close="isTaskModalOpen = false">
        <NewTaskModal @close="isTaskModalOpen = false" :projects="projects" :labels="labels"/>
    </Modal>

</template>