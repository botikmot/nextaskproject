<script setup>
import { ref, computed } from 'vue';
import Modal from '@/Components/Modal.vue';
import NewTaskModal from '@/Pages/Projects/NewTaskModal.vue';
import axios from 'axios';
import { usePage, useForm } from '@inertiajs/vue3'
import Swal from 'sweetalert2';
import UserImage from '@/Components/UserImage.vue';
import moment from 'moment';

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
const getAllEvents = page.props.getAllEvents || [];
const userChallenges = page.props.participantChallenges

console.log('userChallenges', userChallenges)

const todaysEvents = computed(() => {
    const now = new Date();

    // Get the start and end of today's date
    const startOfToday = new Date(now.getFullYear(), now.getMonth(), now.getDate());
    const endOfToday = new Date(now.getFullYear(), now.getMonth(), now.getDate() + 1);

    // Filter events to include only those starting within today's range
    return getAllEvents.filter(event => {
        const eventStart = new Date(event.start);
        return eventStart >= startOfToday && eventStart < endOfToday;
    });
});

console.log('todaysEvents', todaysEvents.value)

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

const formatDate = (date) => {
    return moment(date).format('lll');
}

</script>

<!-- <template>
    <div class="mb-6 text-lg text-navy-blue font-semibold">Quick Actions </div>
    <button @click="createTask" class="block w-full mb-4 py-2 text-linen hover:font-bold bg-gradient-to-r from-navy-blue to-sky-blue rounded-full hover:from-sky-blue hover:to-navy-blue hover:shadow-lg">
        Create New Task
    </button>
    
    <div v-if="receivedFriendRequests.length > 0" class="mt-6">
        <h3 class="font-bold text-navy-blue mb-4">You Have Friend Requests</h3>
        <div
            class="py-1 px-3 mb-2 border-b border-dark-gray hover:bg-light-gray"
            v-for="(request, index) in receivedFriendRequests"
            :key="request.id"
        >
            <div class="flex items-center justify-between">
                
                <div class="flex items-center space-x-3">
                    <UserImage class="h-8 w-8 rounded-full object-cover" :user="request.sender" />
                    <div>
                        <div class="text-navy-blue font-medium text-sm">{{ request.sender.name }}</div>
                        <div class="text-xs text-gray">Mutual Projects: {{ request.sender.mutual_projects }}</div>
                    </div>
                </div>
                
                <div class="relative group">
                    
                    <button
                        @click="acceptFriendRequest(request.id, index)"
                        class="w-8 h-8 rounded-full cursor-pointer hover:bg-crystal-blue text-color-white flex items-center justify-center transition duration-200 ease-in-out"
                        :disabled="loadingId === `accept-${request.id}`"
                    >
                   
                    <template v-if="loadingId === `accept-${request.id}`">
                        <div class="loader border-2 border-t-2 border-green rounded-full w-4 h-4 animate-spin"></div>
                    </template>
                    <template v-else>
                        <i class="fas fa-check text-green text-sm"></i>
                    </template>
                    </button>

                   
                    <button
                        @click="rejectFriendRequest(request.id, index)"
                        class="w-8 h-8 rounded-full cursor-pointer hover:bg-crystal-blue text-color-white flex items-center justify-center transition duration-200 ease-in-out"
                        :disabled="loadingId === `reject-${request.id}`"
                    >
                   
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
               
                <div class="flex items-center space-x-3">
                    <UserImage class="h-8 w-8 rounded-full object-cover" :user="suggested" />
                    <div>
                        <div class="text-navy-blue font-medium text-sm">{{ suggested.name }}</div>   
                        <div v-if="suggested.badges.length > 0" class="flex space-x-1">
                            <img v-for="badge in suggested.badges" :key="badge.id" :src="badge.icon" :alt="badge.name" class="h-4 w-4" :title="badge.name">
                        </div>
                        <div class="text-xs text-gray pl-1">Mutual Projects: {{ suggested.mutual_projects }}</div>
                    </div>
                </div>
               
                <div class="relative group">
                    <button
                        v-if="isAddFriendSuccess !== suggested.id"
                        @click="addFriend(suggested.id)"
                        class="w-8 h-8 rounded-full cursor-pointer hover:bg-crystal-blue text-color-white flex items-center justify-center transition duration-200 ease-in-out"
                        :disabled="loadingId === suggested.id"
                    >
                    
                    <i
                        v-if="loadingId !== suggested.id"
                        class="fas fa-user-plus text-sky-blue text-sm"
                    ></i>

                    
                    <div v-else role="status" class="pl-2">
                        <svg aria-hidden="true" class="w-6 h-6 me-2 text-gray animate-spin fill-sky-blue" viewBox="0 0 100 101" fill="none" xmlns="http://www.w3.org/2000/svg"><path d="M100 50.5908C100 78.2051 77.6142 100.591 50 100.591C22.3858 100.591 0 78.2051 0 50.5908C0 22.9766 22.3858 0.59082 50 0.59082C77.6142 0.59082 100 22.9766 100 50.5908ZM9.08144 50.5908C9.08144 73.1895 27.4013 91.5094 50 91.5094C72.5987 91.5094 90.9186 73.1895 90.9186 50.5908C90.9186 27.9921 72.5987 9.67226 50 9.67226C27.4013 9.67226 9.08144 27.9921 9.08144 50.5908Z" fill="currentColor"/><path d="M93.9676 39.0409C96.393 38.4038 97.8624 35.9116 97.0079 33.5539C95.2932 28.8227 92.871 24.3692 89.8167 20.348C85.8452 15.1192 80.8826 10.7238 75.2124 7.41289C69.5422 4.10194 63.2754 1.94025 56.7698 1.05124C51.7666 0.367541 46.6976 0.446843 41.7345 1.27873C39.2613 1.69328 37.813 4.19778 38.4501 6.62326C39.0873 9.04874 41.5694 10.4717 44.0505 10.1071C47.8511 9.54855 51.7191 9.52689 55.5402 10.0491C60.8642 10.7766 65.9928 12.5457 70.6331 15.2552C75.2735 17.9648 79.3347 21.5619 82.5849 25.841C84.9175 28.9121 86.7997 32.2913 88.1811 35.8758C89.083 38.2158 91.5421 39.6781 93.9676 39.0409Z" fill="currentFill"/></svg>
                        <span class="sr-only">Loading...</span>
                    </div>
                    </button>

                    <i v-else class="fa-solid fa-check text-green text-sm mr-3"></i>
                    
                    <div
                        class="absolute top-full mt-1 right-0 transform -translate-x-1 hidden group-hover:flex items-center px-4 py-3 text-sm font-semibold text-white bg-light-gray rounded-md shadow-lg z-10 whitespace-nowrap"
                    >
                        {{ isAddFriendSuccess !== suggested.id ? 'Add Friend' : 'Request Sent' }}
                    </div>
                </div>
            </div>
        </div>
    </div>
    <div v-if="todaysEvents.length > 0" class="mt-6">
        <h3 class="font-bold text-navy-blue mb-2">{{ todaysEvents.length > 1 ? "Today's Events" : "Today's Event" }}</h3>
        <ul class="mb-6 list-none">
            <li v-for="event in todaysEvents" :key="event.id" class="py-1 flex border-b border-dark-gray">
                <div>
                    <p class="font-semibold text-red-warning">{{ event.title }}</p>
                    <p class="text-sm text-navy-blue">{{ formatDate(event.start) }}</p>
                </div>
            </li>
        </ul>
    </div>

    <Modal :show="isTaskModalOpen" @close="isTaskModalOpen = false">
        <NewTaskModal @close="isTaskModalOpen = false" :projects="projects" :labels="labels"/>
    </Modal>

</template> -->

<template>
    <div class="right-sidebar">
      <!-- Header -->
      <div class="text-lg text-navy-blue font-semibold mb-6">Quick Actions</div>
      
      <!-- Create Task Button -->
      <button @click="createTask" class="w-full mb-4 hover:scale-105 py-2 text-linen bg-gradient-to-r from-navy-blue to-sky-blue rounded-full hover:from-sky-blue hover:to-navy-blue hover:shadow-lg transition">
        Create New Task
      </button>
  
      <!-- Task Time Tracking Section -->
      <div class="time-tracking-section mb-6">
        <h3 class="font-bold text-navy-blue mb-4">Time Tracking</h3>
        <div class="flex items-center justify-between py-2 px-3 border border-dark-gray rounded-md">
          <div>
            <p class="text-sm text-navy-blue">Active Task</p>
            <p class="text-xs text-gray">Tracking Time: {{ formatTime(activeTaskTime) }}</p>
          </div>
          <button @click="toggleTimeTracking" class="bg-crystal-blue text-navy-blue py-1 px-3 hover:text-color-white rounded-full text-sm hover:bg-sky-blue transition">Toggle Timer</button>
        </div>
      </div>
  
      <!-- Friend Requests Section -->
      <div v-if="receivedFriendRequests.length > 0" class="mb-6">
        <h3 class="font-bold text-navy-blue mb-4">Friend Requests</h3>
        <div v-for="(request, index) in receivedFriendRequests" :key="request.id" class="friend-request mb-4 py-2 px-3 border border-dark-gray rounded-md hover:bg-light-gray">
          <div class="flex items-center justify-between">
            <div class="flex items-center space-x-3">
              <UserImage class="h-8 w-8 rounded-full object-cover" :user="request.sender" />
              <div>
                <div class="text-navy-blue font-medium text-sm">{{ request.sender.name }}</div>
                <div class="text-xs text-gray">Mutual Projects: {{ request.sender.mutual_projects }}</div>
              </div>
            </div>
            <div class="flex items-center space-x-2">
              <button @click="acceptFriendRequest(request.id, index)" class="text-green hover:bg-dark-gray h-8 w-8 rounded-full">
                <i class="fas fa-check"></i>
              </button>
              <button @click="rejectFriendRequest(request.id, index)" class="text-red-warning hover:bg-dark-gray rounded-full h-8 w-8">
                <i class="fas fa-times"></i>
              </button>
            </div>
          </div>
        </div>
      </div>
  
      <!-- People You May Know Section -->
      <div v-if="suggestedFriends.length > 0" class="mb-6">
        <div class="flex items-center mb-4">
            <h3 class="font-bold text-navy-blue">People You May Know</h3>
        </div>
        <div v-for="suggested in suggestedFriends" :key="suggested.id" class="suggested-friend mb-3 py-2 px-3 border border-dark-gray rounded-md hover:bg-light-gray">
          <div class="flex items-center justify-between">
            <div class="flex items-center space-x-3">
              <UserImage class="h-8 w-8 rounded-full object-cover" :user="suggested" />
              <div>
                <div class="text-navy-blue font-medium text-sm">{{ suggested.name }}</div>
                <div class="text-xs text-gray pl-1">Mutual Projects: {{ suggested.mutual_projects }}</div>
              </div>
            </div>
            <button @click="addFriend(suggested.id)" class="bg-sky-blue hover:bg-navy-blue text-color-white rounded-full h-8 w-8">
              <i class="fas fa-user-plus text-sm"></i>
            </button>
          </div>
        </div>
      </div>
  
      <!-- Today's Events Section -->
      <div v-if="todaysEvents.length > 0" class="mb-6">
        <div class="flex items-center mb-4">
            <h3 class="font-bold text-navy-blue">{{ todaysEvents.length > 1 ? "Today's Events" : "Today's Event" }}</h3>
        </div>
        <ul class="list-none">
          <li v-for="event in todaysEvents" :key="event.id" class="py-2 flex border-b border-dark-gray">
            <div>
              <p class="font-semibold text-red-warning">{{ event.title }}</p>
              <p class="text-sm text-navy-blue">{{ formatDate(event.start) }}</p>
            </div>
          </li>
        </ul>
      </div>
  
      <!-- Challenges Section -->
      <div v-if="userChallenges.length > 0" class="mb-6">
        <div class="flex items-center mb-4">
            <h3 class="font-bold text-navy-blue">Challenges</h3>
        </div>
        <div v-for="challenge in userChallenges" :key="challenge.id" class="challenge-item mb-3 py-2 px-3 border border-dark-gray rounded-md hover:bg-light-gray">
          <div class="flex items-center justify-between">
            <div class="text-sm text-navy-blue">{{ challenge.name }}</div>
            <div class="text-xs text-gray text-right">Progress: <span class="text-sky-blue font-bold">{{ challenge.user_completion_percentage }}%</span></div>
          </div>
        </div>
      </div>
        <Modal :show="isTaskModalOpen" @close="isTaskModalOpen = false">
            <NewTaskModal @close="isTaskModalOpen = false" :projects="projects" :labels="labels"/>
        </Modal>
    </div>
  </template>
  
  <script>
  export default {
    data() {
      return {
        receivedFriendRequests: [], // Example data
        suggestedFriends: [], // Example data
        todaysEvents: [], // Example data
        userChallenges: [], // Example data
        activeTaskTime: 0, // Example data for time tracking
      };
    },
    methods: {
      createTask() {
        // Logic to create a new task
      },
      acceptFriendRequest(id, index) {
        // Logic to accept friend request
      },
      rejectFriendRequest(id, index) {
        // Logic to reject friend request
      },
      addFriend(id) {
        // Logic to add friend
      },
      toggleTimeTracking() {
        // Logic to toggle time tracking
      },
      formatTime(seconds) {
        // Format time in hh:mm:ss
        let hours = Math.floor(seconds / 3600);
        let minutes = Math.floor((seconds % 3600) / 60);
        let secs = seconds % 60;
        return `${hours}:${minutes}:${secs}`;
      },
      formatDate(date) {
        // Format date
        return new Date(date).toLocaleString();
      }
    },
  };
  </script>

  