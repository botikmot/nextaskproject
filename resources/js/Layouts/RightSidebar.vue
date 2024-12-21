<script setup>
import { ref, computed } from 'vue';
import Modal from '@/Components/Modal.vue';
import NewTaskModal from '@/Pages/Projects/NewTaskModal.vue';
import axios from 'axios';
import { usePage, useForm } from '@inertiajs/vue3'
import Swal from 'sweetalert2';
import UserImage from '@/Components/UserImage.vue';

const page = usePage();
let isTaskModalOpen = ref(false);
let projects = ref([])
let labels = ref(null)
let loadingId = ref(null)
let isAddFriendSuccess = ref(false)
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
    const response = await axios.get('/tasks-project');
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
    loadingId.value = friend_id
    form.post('/friend-requests', {
        data: form,
        preserveScroll: true,
        onSuccess: (response) => {
            form.reset()
            loadingId.value = null
            if(response.props.flash.success){
                isAddFriendSuccess.value = friend_id
            }

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

const acceptFriendRequest = (id, index) => {
    loadingId.value = `accept-${id}`
    form.post(`/friend-requests/${id}/accept`, {
        data: form,
        preserveScroll: true,
        onSuccess: (response) => {
            form.reset()
            loadingId.value = null
            Swal.fire({
                text: response.props.flash.message,
                position: 'bottom-end',
                backdrop: false,
                timer: 2000,
                showConfirmButton: false,
                toast:true,
                icon: response.props.flash.success ? 'success' : 'error',
            });
            receivedFriendRequests.splice(index, 1)
        },
        onError: (error) => {
            console.error('Error accepting friend requests', error)
        }
    })
}

const rejectFriendRequest = (id, index) => {

    loadingId.value = `reject-${id}`

    form.post(`/friend-requests/${id}/reject`, {
        data: form,
        preserveScroll: true,
        onSuccess: (response) => {
            form.reset()
            loadingId.value = null
            Swal.fire({
                text: response.props.flash.message,
                position: 'bottom-end',
                backdrop: false,
                timer: 2000,
                showConfirmButton: false,
                toast:true,
                icon: response.props.flash.success ? 'success' : 'error',
            });
            receivedFriendRequests.splice(index, 1)
        },
        onError: (error) => {
            console.error('Error rejecting friend requests', error)
        }
    })
}

console.log('receivedFriendRequests', receivedFriendRequests)
</script>

<template>
    <div class="mb-6 text-lg text-navy-blue font-semibold">Quick Actions </div>
    <button @click="createTask" class="block w-full mb-4 py-2 text-linen hover:font-bold bg-sky-blue rounded-full hover:bg-crystal-blue hover:text-navy-blue hover:shadow-lg">Create New Task</button>
    <!-- <button class="block w-full mb-4 py-2 text-white bg-blue-500 rounded">Join Meeting</button> -->

    <div v-if="receivedFriendRequests.length > 0" class="mt-6">
        <h3 class="font-bold text-navy-blue mb-4">You Have Friend Requests</h3>
        <div
            class="py-1 px-3 mb-2 border-b border-dark-gray hover:bg-light-gray"
            v-for="(request, index) in receivedFriendRequests"
            :key="request.id"
        >
            <div class="flex items-center justify-between">
                <!-- Profile Section -->
                <div class="flex items-center space-x-3">
                    <!-- <img
                        :src="'/' + request.sender.profile_image"
                        alt="Profile"
                        class="w-8 h-8 rounded-full object-cover border-2 border-white"
                    /> -->
                    <UserImage class="h-8 w-8 rounded-full object-cover" :user="request.sender" />
                    <div>
                        <div class="text-navy-blue font-medium text-sm">{{ request.sender.name }}</div>
                        <div class="text-xs text-gray">Mutual Projects: {{ request.sender.mutual_projects }}</div>
                    </div>
                </div>
                <!-- Accept/Reject Button -->
                <div class="relative group">
                    <!-- Accept Button -->
                    <button
                        @click="acceptFriendRequest(request.id, index)"
                        class="w-8 h-8 rounded-full cursor-pointer hover:bg-crystal-blue text-color-white flex items-center justify-center transition duration-200 ease-in-out"
                        :disabled="loadingId === `accept-${request.id}`"
                    >
                    <!-- Show icon or loader -->
                    <template v-if="loadingId === `accept-${request.id}`">
                        <div class="loader border-2 border-t-2 border-green rounded-full w-4 h-4 animate-spin"></div>
                    </template>
                    <template v-else>
                        <i class="fas fa-check text-green text-sm"></i>
                    </template>
                    </button>

                    <!-- Reject Button -->
                    <button
                        @click="rejectFriendRequest(request.id, index)"
                        class="w-8 h-8 rounded-full cursor-pointer hover:bg-crystal-blue text-color-white flex items-center justify-center transition duration-200 ease-in-out"
                        :disabled="loadingId === `reject-${request.id}`"
                    >
                    <!-- Show icon or loader -->
                    <template v-if="loadingId === `reject-${request.id}`">
                        <div class="loader border-2 border-t-2 border-red-warning rounded-full w-4 h-4 animate-spin"></div>
                    </template>
                    <template v-else>
                        <i class="fas fa-times text-red-warning text-sm"></i>
                    </template>
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
                    <UserImage class="h-8 w-8 rounded-full object-cover" :user="suggested" />
                    <div>
                        <div class="text-navy-blue font-medium text-sm">{{ suggested.name }}</div>
                        <div class="text-xs text-gray">Mutual Projects: {{ suggested.mutual_projects }}</div>
                    </div>
                </div>
                <!-- Add Friend Icon with Tooltip -->
                <div class="relative group">
                    <button
                        v-if="isAddFriendSuccess !== suggested.id"
                        @click="addFriend(suggested.id)"
                        class="w-8 h-8 rounded-full cursor-pointer hover:bg-crystal-blue text-color-white flex items-center justify-center transition duration-200 ease-in-out"
                        :disabled="loadingId === suggested.id"
                    >
                    <!-- Icon when not loading -->
                    <i
                        v-if="loadingId !== suggested.id"
                        class="fas fa-user-plus text-sky-blue text-sm"
                    ></i>

                    <!-- Spinner when loading -->
                    <div v-else role="status" class="pl-2">
                        <svg aria-hidden="true" class="w-6 h-6 me-2 text-gray animate-spin fill-sky-blue" viewBox="0 0 100 101" fill="none" xmlns="http://www.w3.org/2000/svg"><path d="M100 50.5908C100 78.2051 77.6142 100.591 50 100.591C22.3858 100.591 0 78.2051 0 50.5908C0 22.9766 22.3858 0.59082 50 0.59082C77.6142 0.59082 100 22.9766 100 50.5908ZM9.08144 50.5908C9.08144 73.1895 27.4013 91.5094 50 91.5094C72.5987 91.5094 90.9186 73.1895 90.9186 50.5908C90.9186 27.9921 72.5987 9.67226 50 9.67226C27.4013 9.67226 9.08144 27.9921 9.08144 50.5908Z" fill="currentColor"/><path d="M93.9676 39.0409C96.393 38.4038 97.8624 35.9116 97.0079 33.5539C95.2932 28.8227 92.871 24.3692 89.8167 20.348C85.8452 15.1192 80.8826 10.7238 75.2124 7.41289C69.5422 4.10194 63.2754 1.94025 56.7698 1.05124C51.7666 0.367541 46.6976 0.446843 41.7345 1.27873C39.2613 1.69328 37.813 4.19778 38.4501 6.62326C39.0873 9.04874 41.5694 10.4717 44.0505 10.1071C47.8511 9.54855 51.7191 9.52689 55.5402 10.0491C60.8642 10.7766 65.9928 12.5457 70.6331 15.2552C75.2735 17.9648 79.3347 21.5619 82.5849 25.841C84.9175 28.9121 86.7997 32.2913 88.1811 35.8758C89.083 38.2158 91.5421 39.6781 93.9676 39.0409Z" fill="currentFill"/></svg>
                        <span class="sr-only">Loading...</span>
                    </div>
                    </button>

                    <i v-else class="fa-solid fa-check text-green text-sm mr-3"></i>
                    <!-- Tooltip -->
                    <div
                        class="absolute top-full mt-1 right-0 transform -translate-x-1 hidden group-hover:flex items-center px-4 py-3 text-sm font-semibold text-white bg-light-gray rounded-md shadow-lg z-10 whitespace-nowrap"
                    >
                        {{ isAddFriendSuccess !== suggested.id ? 'Add Friend' : 'Request Sent' }}
                    </div>
                </div>
            </div>
        </div>
    </div>


    <Modal :show="isTaskModalOpen" @close="isTaskModalOpen = false">
        <NewTaskModal @close="isTaskModalOpen = false" :projects="projects" :labels="labels"/>
    </Modal>

</template>