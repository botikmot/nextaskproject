<script setup>
import Dropdown from '@/Components/Dropdown.vue';
import DropdownLink from '@/Components/DropdownLink.vue';
import moment from 'moment';
import Swal from 'sweetalert2';
import { usePage, useForm } from '@inertiajs/vue3'
import TaskDetails from './TaskDetails.vue';
import Modal from '@/Components/Modal.vue';
import { ref, computed, onMounted } from 'vue';
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

const isTaskPage = ref(false);

const form = useForm({
    id: props.task.id,
});

const confirmDelete = (id) => {
    Swal.fire({
        title: 'Are you sure?',
        text: "Once deleted, this task cannot be recovered.",
        showCancelButton: true,
        confirmButtonColor: '#EF4444', // Red for delete
        cancelButtonColor: '#38A169', // Green for cancel
        confirmButtonText: 'Yes, delete it!'
    }).then((result) => {
        if (result.isConfirmed) {
            form.delete(`/task-remove/${form.id}`, {
                data: form,
                preserveScroll: true,
                onSuccess: (response) => {
                    console.log(response.props.flash.success)
                    if(response.props.flash.success){
                        form.reset()
                        Swal.fire({
                            icon: 'success',
                            title: 'Deleted!',
                            text: 'The task has been successfully deleted.',
                        });
                    }else{
                        Swal.fire({
                            icon: 'error',
                            //title: 'Deleted!',
                            text: response.props.flash.message,
                        });
                    }
                    
                },
                onError: (error) => {
                    console.error('Error deleting task', error)
                }
            })
        }
    });
}

const openTaskModal = () => {
    isTaskLogOpen.value = true;
}

const remainingDays = (due_date) => {
    const dueDate = moment(due_date);
    const now = moment();
    const duration = moment.duration(dueDate.diff(now));
    const daysRemaining = duration.days();
    const hoursRemaining = duration.hours();
    
    if (daysRemaining > 0) {
        return `${daysRemaining} days remaining`;
    } else if (daysRemaining === 0 && hoursRemaining > 0) {
        return `${hoursRemaining} hours remaining`;
    } else if (daysRemaining < 0) {
        return daysRemaining < -1 
            ? `Overdue by ${Math.abs(daysRemaining)} days` 
            : `Overdue by ${Math.abs(hoursRemaining)} hours`;
    } else {
        return `Due soon!`;
    }
}

const totalAttachments = computed(() => {
    if(props.task.comments){
    return props.task.comments.reduce((sum, comment) => {
        return sum + (comment.attachments ? comment.attachments.length : 0);
    }, 0);
    }else {
        return 0
    }
});

onMounted(() => {
  // Add a slight delay to ensure Inertia updates the title
  setTimeout(() => {
    isTaskPage.value = document.title.includes('Tasks');
  }, 100); // 100ms delay
});

</script>


<template>
    <div @click="openTaskModal" :class="`${ completedId == task.status_id ? 'bg-dark-gray' : 'bg-color-white shadow-lg' } ${ isTaskPage ? 'px-3 pt-3 pb-7' : 'p-3'} mb-3 rounded border relative border-dark-gray cursor-pointer`">
        <div class="flex justify-between">
            <div class="flex">
                <!-- <i :class="[
                        'fa-solid fa-flag text-xs pr-1 flex items-center',
                        task.priority === 'low' ? 'text-[#38A169]' : 
                        task.priority === 'medium' ? 'text-[#D97706]' : 
                        task.priority === 'high' ? 'text-[#EF4444]' : ''
                    ]" :title="[
                        task.priority === 'low' ? 'Low Priority' : 
                        task.priority === 'medium' ? 'Medium Priority' : 
                        task.priority === 'high' ? 'High Priority' : ''
                    ]">
                </i> -->
                <!-- <h3 :class="`${ completedId == task.status.id ? 'line-through' : '' } text-md text-navy-blue font-semibold`">{{ task.title }}</h3> -->
                <h3 class="text-md text-navy-blue font-semibold">{{ task.title }}</h3>
            </div>
            <div v-if="task.user_id == $page.props.auth.user.id" class="text-sm " @click.stop>
                <Dropdown align="right" width="48">
                    <template #trigger>
                        <i class="fa-solid fa-ellipsis"></i>
                    </template>
                    <template #content>
                        <div
                            class="hover:bg-crystal-blue px-3 py-2"
                            @click.stop="confirmDelete(task.id)"
                        >
                            Remove
                        </div>
                    </template>
                </Dropdown>
            </div>
        </div>
        <!-- <p class="text-xs text-gray">{{ task.description }}</p> -->
        <p class="text-xs" v-if="task.due_date">Due date: <span class="text-[#D97706]">{{ completedId == task.status_id ? formatDate(task.due_date) : remainingDays(task.due_date) }}</span></p>
        <p class="text-sm text-gray text-xs">Created {{ formatDate(task.created_at) }}</p>
        <div class="flex pt-1">
            <div v-if="task.subtasks && task.subtasks.length" class="flex items-center pr-3">
                <span class="text-red-warning text-xs font-bold">Progress: {{ task.progress }}%</span>
            </div>
            <div v-if="task.comments && task.comments.length" class="flex items-center pr-3">
                <i class="fa-solid fa-comment text-gray text-sm"></i>
                <span class="text-gray pl-1 text-xs">{{ task.comments.length }}</span>
            </div>
            <div v-if="totalAttachments > 0" class="flex items-center pr-3">
                <i class="fa-solid fa-paperclip text-gray text-sm"></i>
                <span class="text-gray pl-1 text-xs">{{ totalAttachments }}</span>
            </div>
            <div v-if="task.subtasks && task.subtasks.length > 0" class="flex items-center pr-3">
                <i class="fa-solid fa-list-check text-gray text-sm"></i>
                <span class="text-gray pl-1 text-xs">
                    {{ task.subtasks.filter(subtask => subtask.is_completed).length }}/{{ task.subtasks.length }}
                </span>
            </div>
        </div>
        <div v-if="task.users" :class="`absolute flex items-center ${ isTaskPage ? 'bottom-6' : 'bottom-2'} right-4`">
            <div v-for="(member, index) in task.users.slice(0, 4)" :key="member.id" class="relative -mr-3">
                <img :src="'/' + member.profile_image" alt="Profile" class="w-6 h-6 object-cover rounded-full border-2 border-color-white" />
            </div>
            <div v-if="task.users.length > 4" class="flex items-center text-navy-blue ml-3">
                <span class="text-sm font-bold">+{{ task.users.length - 4 }}</span>
            </div>
        </div>
        <!-- Progress Bar -->
        <div v-if="task.subtasks && task.subtasks.length" class="w-full h-2 bg-crystal-blue absolute rounded-tr rounded-tl -top-1 left-0 my-1">
            <div :style="{ width: task.progress + '%' }" :class="`h-full bg-sky-blue rounded-tl ${ task.progress == 100 ? 'rounded-tr' : ''}`"></div>
        </div>

        <div class="pt-2 flex items-center" v-if="isTaskPage">
            <span v-if="task.priority == 'high'" class="text-xs px-3 mr-1 py-1 bg-red-warning text-color-white rounded-full">Priority</span>
            <span class="text-xs px-3 py-1 rounded-full border border-dark-gray" :style="{ backgroundColor: task.status.color }">{{ task.status.name }}</span>
            <span v-if="task.labels" v-for="(tag, index) in task.labels" :key="index" :style="{ backgroundColor: tag.color }" class="text-xs px-2 py-1 ml-1 rounded-full">
                {{ tag.name }}
            </span>
        </div>
        <div v-else>
            <span v-if="task.priority == 'high'" class="text-xs px-3 py-1 mr-1 bg-red-warning text-color-white rounded-full">Priority</span>
            <span v-if="task.labels" v-for="(tag, index) in task.labels" :key="index" :style="{ backgroundColor: tag.color }" class="text-xs px-2 py-1 mr-1 rounded-full">
                {{ tag.name }}
            </span>
        </div>
        
        <div v-if="isTaskPage" class="w-full text-sm rounded-br rounded-bl flex justify-center left-0 bottom-0 text-dark-gray absolute" :style="{ backgroundColor: task.project.color }">
            {{ task.project.title }}
        </div>

        <!-- Task Modal -->
        <Modal :show="isTaskLogOpen" @close="isTaskLogOpen = false">
           <!--  <TaskDetails @close="isTaskLogOpen = false" :task="task" :members="members" :tasks="tasks" :project="project" :labels="labels"/> -->
           <TaskDetailsNew @close="isTaskLogOpen = false" :task="task" :members="members" :tasks="tasks" :project="project" :labels="labels" />
        </Modal>
    </div>
</template>