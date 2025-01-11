<script setup>
  import { ref } from 'vue';
  import moment from 'moment';
  import Modal from '@/Components/Modal.vue';
  import TaskDetailsNew from './TaskDetailsNew.vue';
  
  const props = defineProps({
    task: Object,
    members: Object,
    tasks: Object,
    completedId: String,
    labels: Object,
    project: Object,
});

let isTaskLogOpen = ref(false);

const formatDate = (date) => {
    return moment(date).fromNow()
}

const remainingDays = (due_date) => {
    const dueDate = moment(due_date);
    const now = moment();
    const duration = moment.duration(dueDate.diff(now));
    const daysRemaining = duration.days();
    const hoursRemaining = duration.hours();
    
    if (daysRemaining > 0) {
        return `${daysRemaining} ${daysRemaining > 1 ? 'days' : 'day'} remaining`;
    } else if (daysRemaining === 0 && hoursRemaining > 0) {
        return `${hoursRemaining} ${hoursRemaining > 1 ? 'hours' : 'hour'} remaining`;
    } else if (daysRemaining < 0) {
        return daysRemaining < -1 
            ? `Overdue by ${Math.abs(daysRemaining)} days` 
            : `Overdue by ${Math.abs(hoursRemaining)} hours`;
    } else {
        return `Due soon!`;
    }
}
  
const openTaskModal = () => {
    isTaskLogOpen.value = true;
}
</script>

<template>
    <div class="w-full">
      <!-- Task Card -->
      <div 
        @click="openTaskModal" 
        class="border border-dark-gray rounded-lg shadow-lg p-5 cursor-pointer hover:shadow-xl transition-transform transform hover:scale-105"
        :class="`${ completedId == task.status_id ? 'bg-dark-gray' : 'bg-color-white shadow-lg' }`"
      >
        <!-- Task Title -->
      <div class="flex justify-between items-start mb-3">
        <h3 class="text-navy-blue font-bold text-md truncate leading-tight">{{ task.title }}</h3>
        <span 
          class="text-dark-navy text-xs px-2 py-1 rounded-full font-medium uppercase tracking-wide"
          style="white-space: nowrap;"
          :style="{ backgroundColor: task.status.color }"
        >
          {{ task.status.name }}
        </span>
      </div>

      <!-- Project Name -->
      <div class="flex justify-between items-center mb-2">
        <p class="text-sm text-gray leading-tight">Project:</p>
        <span class="text-teal font-medium">{{ task.project.title }}</span>
      </div>
      <!-- Due Date -->
      <div class="flex justify-between items-center mb-2">
        <p class="text-sm text-gray leading-tight">Due:</p>
        <p class="text-red-warning font-semibold leading-tight">
            <span class="text-[#D97706]">{{ completedId == task.status_id ? formatDate(task.due_date) : remainingDays(task.due_date) }}</span>
        </p>
      </div>

      <!-- Labels/Tags -->
      <div class="flex justify-between items-center mb-2">
        <p class="text-sm text-gray leading-tight">Labels/Tags:</p>
        <div class="flex flex-wrap gap-1">
            <span v-if="task.priority == 'high'" class="text-xs px-3 py-1 mr-1 bg-red-warning text-color-white rounded-full">Priority</span>
            <span v-if="task.labels" v-for="(tag, index) in task.labels" :key="index" :style="{ backgroundColor: tag.color }" class="text-navy-blue text-xs px-3 py-1 rounded-full font-medium leading-tight">
                {{ tag.name }}
            </span>
        </div>
      </div>
      <!-- Assigned Users -->
      <div class="flex justify-between mb-2">
        <p class="text-sm text-gray leading-tight">Assigned Users:</p>
        <div class="flex items-center space-x-2">
            <div v-for="(member, index) in task.users.slice(0, 4)" :key="member.id" class="relative">
                <img :src="'/' + member.profile_image" alt="Profile" class="w-9 h-9 rounded-full object-cover border-2 border-color-white hover:opacity-80" />
            </div>
            <div v-if="task.users.length > 4" class="flex items-center text-navy-blue ml-3">
                <span class="text-sm font-bold">+{{ task.users.length - 4 }}</span>
            </div>
        </div>
      </div>
      <!-- Task Progress -->
      <div v-if="task.subtasks && task.subtasks.length" class="mb-2">
        <p class="text-sm text-gray leading-tight mb-1">Progress:</p>
        <div class="w-full bg-crystal-blue rounded-full h-2">
          <div 
            class="bg-navy-blue h-2 rounded-full" 
            :style="{ width: `${task.progress}%` }"
          ></div>
        </div>
        <p class="text-xs text-gray leading-tight mt-1">{{ task.progress }}% complete</p>
      </div>

      <!-- Comments and Attachments -->
      <div class="flex items-center justify-start space-x-4">
        <div v-if="task.comments && task.comments.length" class="flex items-center space-x-1">
          <i class="fas fa-comments text-gray"></i>
          <span class="text-sm text-gray leading-tight">{{ task.comments.length }}</span>
        </div>

        <div v-if="task.attachments && task.attachments.length" class="flex items-center space-x-1">
          <i class="fas fa-paperclip text-gray"></i>
          <span class="text-sm text-gray leading-tight">{{ task.attachments.length }}</span>
        </div>
      </div>

      <!-- Time Tracking -->
      <!-- <div class="flex items-center justify-between mt-3">
        <p class="text-sm text-gray leading-tight">Time Spent:</p>
        <p class="text-teal font-semibold leading-tight">{{ task.timeSpent }}</p>
      </div> -->


      </div>
    </div>

    <!-- Task Modal -->
    <Modal :show="isTaskLogOpen" @close="isTaskLogOpen = false">
        <TaskDetailsNew @close="isTaskLogOpen = false" :task="task" :members="members" :tasks="tasks" :project="project" :labels="labels" />
    </Modal>


  </template>
  
  <style scoped>
  .card:hover {
    box-shadow: 0 8px 12px rgba(0, 0, 0, 0.2);
  }
  </style>
  