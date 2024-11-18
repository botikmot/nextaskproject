<script setup>
import { usePage, useForm } from '@inertiajs/vue3';
import Dropdown from '@/Components/Dropdown.vue';
import DropdownLink from '@/Components/DropdownLink.vue';
import { VueDraggable } from 'vue-draggable-plus'
import { ref, computed, toRaw } from 'vue';
import NewTaskModal from './NewTaskModal.vue';
import axios from 'axios';
import Modal from '@/Components/Modal.vue';
import TaskCard from '../Tasks/TaskCard.vue';
import Swal from 'sweetalert2';
import UpdateColumnModal from './UpdateColumnModal.vue';

const el = ref()
let isTaskModalOpen = ref(false);
let columnId = ref(null);
let isColumnEdit = ref(false)

const props = defineProps({
    column: Object,
    auth_id: String,
    project_id: String,
    project: Object,
});

const form = useForm({
    name: '',
});

let newColumnName = ref(props.column.name)
let prevTasks = ref(props.column.tasks);
// Cloned version of tasks to avoid reactive proxies in VueDraggable
const clonedTasks = computed(() => props.column.tasks.map(task => toRaw(task)));

const onEnd = async (event) => {
    const updatedTasks = props.column.tasks.map((task, idx) => ({
        id: task.id,
        index: idx,
        status_id: props.column.id,
    }));
    
    const data = {
        status_id: props.column.id,
        project_id: props.project_id,
        tasks: updatedTasks,
        is_add: false,
        task_id: event.data.id,
    }
    
    const success = await updateTasks(props.column.id, data) 
    if (!success) {
        // Revert to previous state if update fails
        props.column.tasks = prevTasks.value;
    }else{
        prevTasks.value = props.column.tasks
    }   
}

const handleAddToColumn = async (event) => {
    const updatedTasks = props.column.tasks.map((task, idx) => ({
        id: task.id,
        index: idx,
        status_id: props.column.id,
    }));

    const data = {
        status_id: props.column.id,
        project_id: props.project_id,
        tasks: updatedTasks,
        is_add: true,
        task_id: event.data.id,
    }
    
    const success = await updateTasks(props.column.id, data)
    if (!success) {
        // Revert to previous state if update fails
        props.column.tasks = prevTasks.value;
    }else{
        prevTasks.value = props.column.tasks
    }   
}

const columnHeight = computed(() => {
    return 'calc(100vh - 295px)';
});

const updateTasks = async (id, data) => {
    try {
        const response = await axios.post(`/tasks-update/${id}`, data);
        if(!response.data.success){
            Swal.fire({
                icon: "error",
                html: response.data.message,
            });
            return false
        }else{
            console.log('Tasks updated successfully:', response.data.message);
            return true
        }
    } catch (error) {
        console.error('Failed to update tasks:', error.response ? error.response.data : error.message);
        Swal.fire({
            icon: "error",
            text: 'You do not have permission to update task in this project.',
        });
        return false
    }
}

const openTaskModal = (id) => {
    isTaskModalOpen.value = true;
    columnId.value = id
}

const updateTasksOrder = (newTasks) => {
  // Safely update the order of tasks in the column
  props.column.tasks = newTasks.map((task, index) => ({ ...task, index }));
};

const updateColumn = () => {
    if (newColumnName.value !== props.column.name && newColumnName.value != '') {
        // Perform API call to save changes if the name has changed
        console.log('update columsn', newColumnName.value)
        form.name = newColumnName.value
        form.put(`/status-update/${props.column.id}`, {
            data: form,
            preserveScroll: true,
            onSuccess: () => {
                form.reset()
                console.log('Successfully updated.')
            },
            onError: (error) => {
                console.error('Error updating column', error)
            }
        })

    }
    isColumnEdit.value = false; // Exit edit mode
}

const cancelEdit = () => {
    newColumnName.value = props.column.name
    isColumnEdit.value = false
}

const removeColumn = () => {
    console.log('removed')
    Swal.fire({
        title: 'Are you sure?',
        text: "Once deleted, this Column cannot be recovered.",
        showCancelButton: true,
        confirmButtonColor: '#EF4444', // Red for delete
        cancelButtonColor: '#38A169', // Green for cancel
        confirmButtonText: 'Yes, delete it!'
    }).then((result) => {
        if (result.isConfirmed) {
            form.get(`/status-remove/${props.column.id}`, {
                data: form,
                preserveScroll: true,
                onSuccess: () => {
                    form.reset()
                    console.log('Successfully deleted.', usePage().props.flash.message)
                    Swal.fire({
                        icon: usePage().props.flash.success ? "success" : "error",
                        text: usePage().props.flash.message
                    });
                },
                onError: (error) => {
                    console.error('Error deleting column', error)
                }
            })
        }
    });

    
}

</script>

<template>
    <div class="w-[18rem]">
        <div class="flex justify-between">
            <h2 class="text-lg text-navy-blue font-bold mb-4">{{ column.name }}</h2>
            <!-- <input
                v-else
                v-model="newColumnName"
                type="text"
                class="text-lg text-navy-blue font-bold mb-4 border border-gray-300 p-2 rounded"
                @keydown.enter="updateColumn"
                @blur="cancelEdit"
            /> -->
            <div class="cursor-pointer relative" v-if="column.user_id == auth_id">
                <Dropdown align="right" width="48">
                    <template #trigger>
                        <i class="fa-solid fa-ellipsis"></i>
                    </template>
                    <template #content>
                        <div @click="removeColumn" class="px-4 text-sm py-2 hover:bg-crystal-blue">
                            Remove
                        </div>
                        <div @click="isColumnEdit = true" class="px-4 text-sm py-2 hover:bg-crystal-blue">
                            Edit
                        </div>
                    </template>
                </Dropdown>
                
            </div>
        </div>
        <div class="w-full overflow-y-auto" :style="{ height: columnHeight }">
            <!-- Task List -->
            <VueDraggable
                :style="{ height: columnHeight }"
                ref="el"
                :model-value="clonedTasks"
                group="tasks"
                @add="handleAddToColumn"
                @end="onEnd"
                @update:model-value="updateTasksOrder"
            >
                <TaskCard v-for="item in clonedTasks" :key="item.id" :task="item" :members="project.users" :completedId="project.completed_status_id" :tasks="project.tasks" />
            </VueDraggable>
        
        </div>

        <!-- Add Task Button -->
        <button @click="openTaskModal(column.id)"
                class="w-full mt-2 bg-sky-blue text-linen py-1 rounded hover:shadow-xl hover:bg-navy-blue">
            + Add Task
        </button>

        <!-- Task Modal -->
        <Modal :show="isTaskModalOpen" @close="isTaskModalOpen = false">
            <NewTaskModal @close="isTaskModalOpen = false" :project_id="project_id" :column_id="columnId" :project="project" :index="column.tasks.length"/>
        </Modal>
        
        <!-- Edit Column Modal -->
        <Modal :show="isColumnEdit" @close="isColumnEdit = false">
            <UpdateColumnModal @close="isColumnEdit = false" :column="column" />
        </Modal>

    </div>
</template>