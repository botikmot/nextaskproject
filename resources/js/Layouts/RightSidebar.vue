<script setup>
import { ref, computed, onMounted, onBeforeUnmount } from 'vue';
import Modal from '@/Components/Modal.vue';
import NewTaskModal from '@/Pages/Projects/NewTaskModal.vue';
import axios from 'axios';
import { usePage, useForm } from '@inertiajs/vue3'
import Swal from 'sweetalert2';
import UserImage from '@/Components/UserImage.vue';
import moment from 'moment';
import TaskTimer from '@/Pages/Tasks/TaskTimer.vue';

const page = usePage();
let isTaskModalOpen = ref(false);
let projects = ref([])
let labels = ref(null)
let loadingId = ref(null)
let isAddFriendSuccess = ref(false)
const activeTasks = ref([])
const trackingIntervals = ref({})
const isRunning = ref(false);

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

const fetchActiveTasks = async () => {
  const response = await axios.get('/tasks/active');
      activeTasks.value = response.data.tasks;
      console.log('active tasks', activeTasks.value)
      // Start intervals for all active tasks
      /* activeTasks.value.forEach(task => {
        if (!trackingIntervals.value[task.id]) {
          startTrackingInterval(task.id);
        }
      }); */
}

const startTrackingInterval = (taskId) => {
      trackingIntervals.value[taskId] = setInterval(() => {
        const task = activeTasks.value.find(task => task.id === taskId);
        if (task) {
          task.tracking_time++;
        }
      }, 1000);
}

const clearTrackingIntervals = () => {
      Object.keys(trackingIntervals.value).forEach(taskId => {
        clearInterval(trackingIntervals.value[taskId]);
      });
      trackingIntervals.value = {};
    }

const formatTime = (seconds) => {
      const hours = Math.floor(seconds / 3600);
      const minutes = Math.floor((seconds % 3600) / 60);
      const secs = Math.floor(seconds % 60);
      return `${hours}h ${minutes}m ${secs}s`;
}

const toggleTimeTracking = async (task, index) => {
  console.log('task id---', task)
    if(task.isRunning && !task.running && index !== task.running){
      await axios.post(`/tasks/${task.id}/stop-timer`);
      clearTrackingIntervals()
      task.running = index
    }else{
      await axios.post(`/tasks/${task.id}/start-timer`);
      startTrackingInterval(task.id);
      task.running = null
    }
    console.log('task id2---', task)
    //await axios.post(`/tasks/${task.id}/toggle-time-tracking`);
    //fetchActiveTasks()
}

onMounted(() => {
  fetchActiveTasks()
})

onBeforeUnmount(() => {
  clearTrackingIntervals()
})

</script>

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
        <div v-if="activeTasks.length > 0">
          <div v-for="(task, index) in activeTasks" :key="task.id" class="py-2 px-3 border border-dark-gray rounded-md mb-2">
            <div>
              <p class="text-sm text-navy-blue">{{ task.name }}</p>
              <!-- <p class="text-xs text-gray">{{ formatTime(task.tracking_time) }}</p> -->
            </div>
            <TaskTimer :task="task"/>
            <!-- <button @click="toggleTimeTracking(task, index)" class="bg-crystal-blue text-navy-blue py-1 px-3 hover:text-white rounded-full text-sm hover:bg-sky-blue transition">
              Toggle Timer
            </button> -->
          </div>
        </div>
        <p v-else class="text-sm text-gray">No active tasks.</p>
        <!-- <div class="flex items-center justify-between py-2 px-3 border border-dark-gray rounded-md">
          <div>
            <p class="text-sm text-navy-blue">{{ task.name }}</p>
            <p class="text-xs text-gray">Tracking Time: {{ formatTime(activeTaskTime) }}</p>
          </div>
          <button @click="toggleTimeTracking" class="bg-crystal-blue text-navy-blue py-1 px-3 hover:text-color-white rounded-full text-sm hover:bg-sky-blue transition">Toggle Timer</button>
        </div> -->
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
      formatDate(date) {
        // Format date
        return new Date(date).toLocaleString();
      }
    },
  };
  </script>

  