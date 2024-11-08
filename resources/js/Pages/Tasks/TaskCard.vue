<script setup>
import Dropdown from '@/Components/Dropdown.vue';
import DropdownLink from '@/Components/DropdownLink.vue';
import moment from 'moment';
import Swal from 'sweetalert2';
import { usePage, useForm } from '@inertiajs/vue3'
import TaskDetails from './TaskDetails.vue';
import Modal from '@/Components/Modal.vue';
import { ref, computed } from 'vue';


const props = defineProps({
    task: Object,
    members: Object,
});

let isTaskLogOpen = ref(false);

const formatDate = (date) => {
    return moment(date).fromNow()
}

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
                onSuccess: () => {
                    form.reset()
                    console.log('Successfully deleted.')
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
    return props.task.comments.reduce((sum, comment) => {
        return sum + (comment.attachments ? comment.attachments.length : 0);
    }, 0);
});

</script>


<template>
    <div @click="openTaskModal" class="bg-color-white p-3 mb-3 rounded shadow-lg border relative border-dark-gray cursor-pointer">
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
            <div v-if="task.user_id == $page.props.auth.user.id" class="absolute text-sm right-0 top-0" @click.stop>
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
        <p class="text-xs text-gray">{{ task.description }}</p>
        <p class="text-sm" v-if="task.due_date">Due date: <span class="text-[#D97706]">{{ remainingDays(task.due_date) }}</span></p>
        <p class="text-sm text-gray text-xs">Created {{ formatDate(task.created_at) }}</p>
        <div class="flex pt-1">
            <div v-if="task.comments.length" class="flex items-center">
                <i class="fa-solid fa-comment text-gray text-sm"></i>
                <span class="text-gray pl-1 text-sm">{{ task.comments.length }}</span>
            </div>
            <div v-if="totalAttachments > 0" class="flex items-center pl-3">
                <i class="fa-solid fa-paperclip text-gray text-sm"></i>
                <span class="text-gray pl-1 text-sm">{{ totalAttachments }}</span>
            </div>
        </div>
        <div class="absolute flex items-center bottom-2 right-4">
            <div v-for="(member, index) in task.users.slice(0, 4)" :key="member.id" class="relative -mr-3">
                <img :src="'/' + member.profile_image" alt="Profile" class="w-6 h-6 rounded-full border-2 border-color-white" />
            </div>
            <div v-if="task.users.length > 4" class="flex items-center text-navy-blue ml-3">
                <span class="text-sm font-bold">+{{ task.users.length - 4 }}</span>
            </div>
        </div>
        <!-- Task Modal -->
        <Modal :show="isTaskLogOpen" @close="isTaskLogOpen = false">
            <TaskDetails @close="isTaskLogOpen = false" :task="task" :members="members"/>
        </Modal>
    </div>
</template>