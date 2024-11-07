<script setup>
import Dropdown from '@/Components/Dropdown.vue';
import DropdownLink from '@/Components/DropdownLink.vue';
import moment from 'moment';
import Swal from 'sweetalert2';
import { usePage, useForm } from '@inertiajs/vue3'
import TaskLog from './TaskLog.vue';
import Modal from '@/Components/Modal.vue';
import { ref } from 'vue';


const props = defineProps({
    task: Object,
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
    console.log('open modal')
    isTaskLogOpen.value = true;
    //columnId.value = id
}

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
        <p class="text-sm text-gray">{{ task.description }}</p>
        <p class="text-sm text-gray">Due date: {{ task.due_date }}</p>
        <p class="text-sm text-gray text-xs">Created {{ formatDate(task.created_at) }}</p>
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
            <TaskLog @close="isTaskLogOpen = false" :task="task"/>
        </Modal>
    </div>
</template>