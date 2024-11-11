<script setup>
import Dropdown from '@/Components/Dropdown.vue';
import DropdownLink from '@/Components/DropdownLink.vue';
import { VueDraggable } from 'vue-draggable-plus'
import { ref, computed, toRaw } from 'vue';
import NewTaskModal from './NewTaskModal.vue';
import axios from 'axios';
import Modal from '@/Components/Modal.vue';
import TaskCard from '../Tasks/TaskCard.vue';

const el = ref()
let isTaskModalOpen = ref(false);
let columnId = ref(null);

const props = defineProps({
    column: Object,
    auth_id: Number,
    project_id: Number,
    project: Object,
});

// Cloned version of tasks to avoid reactive proxies in VueDraggable
const clonedTasks = computed(() => props.column.tasks.map(task => toRaw(task)));

const onEnd = async () => {
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

const handleAddToColumn = () => {
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

const columnHeight = computed(() => {
    return 'calc(100vh - 295px)';
});

const updateTasks = async (id, data) => {
    try {
        const response = await axios.post(`/tasks-update/${id}`, data);
        console.log('Tasks updated successfully:', response.data.message);
    } catch (error) {
        console.error('Failed to update tasks:', error.response ? error.response.data : error.message);
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

</script>

<template>
    <div class="w-[18rem]">
        <div class="flex justify-between">
            <h2 class="text-lg text-navy-blue font-bold mb-4">{{ column.name }}</h2>
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
                <TaskCard v-for="item in clonedTasks" :key="item.id" :task="item" :members="project.members" />
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
      
    </div>
</template>