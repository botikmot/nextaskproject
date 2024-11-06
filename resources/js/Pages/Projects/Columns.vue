<script setup>
import Dropdown from '@/Components/Dropdown.vue';
import DropdownLink from '@/Components/DropdownLink.vue';
import { VueDraggable } from 'vue-draggable-plus'
import { ref, computed } from 'vue';
import NewTaskModal from './NewTaskModal.vue';
import axios from 'axios';
import Modal from '@/Components/Modal.vue';
import TaskDetails from '../Tasks/TaskDetails.vue';

const el = ref()
let isTaskModalOpen = ref(false);
let columnId = ref(null);

const props = defineProps({
    column: Object,
    auth_id: Number,
    project_id: Number,
    project: Object,
});

const onEnd = async (event) => {
    const updatedTasks = props.column.tasks.map((task, idx) => ({
        id: task.id,
        index: idx,
        status_id: props.column.id,
    }));
    
    const data = {
        status_id: props.column.id,
        tasks: updatedTasks
    }
    updateTasks(props.column.id, data)    
}

const handleAddToColumn = (event) => {
    const updatedTasks = props.column.tasks.map((task, idx) => ({
        id: task.id,
        index: idx,
        status_id: props.column.id,
    }));

    const data = {
        status_id: props.column.id,
        tasks: updatedTasks
    }
    updateTasks(props.column.id, data)
}

const updateTasks = async (id, data) => {
    try {
        const response = await axios.post(route('task.update', id), data);
        console.log('Tasks updated successfully:', response.data.message);
    } catch (error) {
        console.error('Failed to update tasks:', error.response ? error.response.data : error.message);
    }
}

const openTaskModal = (id) => {
    isTaskModalOpen.value = true;
    columnId.value = id
}

</script>

<template>
    <div class="w-[20rem]">
        <div class="flex justify-between">
            <h2 class="text-lg text-sky-blue font-bold mb-4">{{ column.name }}</h2>
            <div class="cursor-pointer relative" v-if="column.user_id == auth_id">
                <Dropdown align="right" width="48">
                    <template #trigger>
                        <i class="fa-solid fa-ellipsis"></i>
                    </template>
                    <template #content>
                        <DropdownLink
                            as="button"
                            class="hover:bg-crystal-blue"
                            :href="route('status.remove', column.id)"
                        >
                            Remove
                        </DropdownLink>
                    </template>
                </Dropdown>
                
            </div>
        </div>
        <!-- Task List -->
        <VueDraggable ref="el" v-model="column.tasks" group="tasks" @add="handleAddToColumn" @end="onEnd">
            <TaskDetails v-for="item in column.tasks" :key="item.id" :task="item" />
        </VueDraggable>
        

        <!-- Add Task Button -->
        <button @click="openTaskModal(column.id)"
                class="w-full mt-2 bg-sky-blue text-linen py-1 rounded hover:shadow-xl hover:bg-navy-blue">
            + Add Task
        </button>

        <!-- Task Modal -->
        <Modal :show="isTaskModalOpen" @close="isTaskModalOpen = false">
            <NewTaskModal @close="isTaskModalOpen = false" :project_id="project_id" :column_id="columnId" :project="project"/>
        </Modal>
      
    </div>
</template>