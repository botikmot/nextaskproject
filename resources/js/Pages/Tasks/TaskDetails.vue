<script setup>
import { usePage, useForm } from '@inertiajs/vue3'
import Dropdown from '@/Components/Dropdown.vue';
import { ref, computed, watch } from 'vue';
import TextInput from '@/Components/TextInput.vue';
import TaskDescription from './TaskDescription.vue';
import SubTasks from './SubTasks.vue';
import moment from 'moment';
import Comments from './Comments.vue';
import Swal from 'sweetalert2';

let isMemberModalOpen = ref(false);
let showSubtask = ref(false)
let isDependencyModalOpen = ref(false)
let isEdit = ref(false)

const props = defineProps({
    task: Object,
    members: Object,
    tasks: Object,
    labels: Object,
});

const activeTab = ref('subtask')

const form = useForm({
    title: props.task.title,
    due_date: props.task.due_date,
    priority: props.task.priority,
    labels:  props.task.labels.map(label => label.id), //props.task.labels,
    assigned_members: [],
    removed_users: [],
    comment: '',
    comment_id: null,
    attachments: null,
    subtask: '',
    name: '', // subtasks name
    is_completed: null,
    dependency: null,
    depends_on_task_id: null,
    description: props.task.description,
});

const formatDate = (date) => {
    return moment(date).format('lll')
}

const openMemberModal = () => {
    isMemberModalOpen.value = !isMemberModalOpen.value;
}

const isUserAssigned = (userId) => {
      return props.task.users.some(user => user.id === userId);
}

const assignMember = () => {
    isMemberModalOpen.value = false
    form.post(`/assign-member/${props.task.id}`, {
        data: form,
        preserveScroll: true,
        onSuccess: () => {
            form.reset()
        },
        onError: (error) => {
            console.error('Error assigning users', error)
        }
    })

}

const removeUser = (id) => {
    form.removed_users.push(id)
    console.log(form.removed_users)
    form.post(`/remove-member/${props.task.id}`, {
        data: form,
        preserveScroll: true,
        onSuccess: () => {
            form.reset()
        },
        onError: (error) => {
            console.error('Error deleting users', error)
        }
    })
}

const addDependency = () => {
    console.log('dependencies', form.dependency)
    console.log('task_id', props.task.id)
    form.post(`/tasks/${props.task.id}/set-dependency`, {
        data: form,
        preserveScroll: true,
        onSuccess: () => {
            form.reset()
        },
        onError: (error) => {
            console.error('Error setting dependency', error)           
        }
    })
}

const selectTab = (tabName) => {
  activeTab.value = tabName;
}

const updateTask = () => {

    form.put(`/tasks-update/${props.task.id}`, {
        data: form,
        preserveScroll: true,
        onSuccess: () => {
            form.reset()
            isEdit.value = false
            Swal.fire({
                text: "Task successfully updated!",
                position: 'bottom-end',
                backdrop: false,
                timer: 2000,
                showConfirmButton: false,
                toast:true,
                icon: 'success',
            });
        },
        onError: (error) => {
            console.error('Error updating tasks', error)           
        }
    })


}

const cancelUpdate = () => {
    isEdit.value = false
}

const removeDependency = (dependency, id) => {
    console.log(dependency.depends_on_task_id)
    console.log(id)
    form.depends_on_task_id = dependency.depends_on_task_id
    form.post(`/tasks/${id}/remove-dependency`, {
        data: form,
        preserveScroll: true,
        onSuccess: () => {
            form.reset()
        },
        onError: (error) => {
            console.error('Error removing dependency', error)           
        }
    })

}

const updateDesc = () => {
    form.description = newDesc.value
    form.put(`/task-description/${props.task.id}`, {
        data: form,
        preserveScroll: true,
        onSuccess: () => {
            form.reset()
            Swal.fire({
                text: "Description successfully saved!",
                position: 'bottom-end',
                backdrop: false,
                timer: 2000,
                showConfirmButton: false,
                toast:true,
                icon: 'success',
            });

        },
        onError: (error) => {
            console.error('Error removing dependency', error)           
        }
    })

}

const newDesc = ref(props.task.description);


// Watch for changes in task description (prop)
watch(
    () => props.task.description,
    (newValue) => {
        newDesc.value = newValue; // Keep the reactive description updated
    }
);


/* const getPriorityClass = (priority) => {
  switch (priority) {
    case 'high': return 'bg-[#EF4444]';
    case 'medium': return 'bg-[#D97706]';
    case 'low': return 'bg-[#38A169]';
    default: return 'bg-gray';
  }
}; */

</script>

<template>
    <div class="bg-color-white rounded-lg shadow-lg p-6 w-full">
        <div class="flex justify-between">
            <h3 v-if="!isEdit" class="text-2xl text-navy-blue font-semibold mb-4">{{ task.title }}</h3>
            <TextInput
                v-else
                placeholder="Enter title here..."
                v-model="form.title"
                class="block w-full border-gray-300 rounded-md shadow-sm focus:ring focus:ring-sky-blue"
            />
            <div v-if="task.user_id == $page.props.auth.user.id">
                <div @click="isEdit = true" class="px-3 cursor-pointer py-1 relative group">
                    <i class="fa-regular fa-pen-to-square text-lg text-gray"></i>
                    <span class="absolute top-full mt-1 right-0 transform -translate-x-1 hidden group-hover:flex items-center px-4 py-3 text-sm font-semibold text-white bg-light-gray rounded-md shadow-lg z-10 whitespace-nowrap">
                        Edit
                    </span>
                </div>
            </div>
        </div>
        

        <div class="pb-3 block sm:flex justify-between">
            <div class="">
                <table class="text-md">
                    <tr>
                        <td class="text-gray py-1 pr-3">Status:</td>
                        <td class="pl-4 py-1"><span class="text-xs py-1 px-3 rounded-full" :style="{ backgroundColor: task.status.color }">{{ task.status.name }}</span></td>
                    </tr>
                    <tr>
                        <td class="text-gray py-1 pr-3">Due Date:</td>
                        <td class="pl-4 py-1">
                            <div v-if="!isEdit">
                                {{ task.due_date ? formatDate(task.due_date) : 'Not set' }}
                            </div>
                            <div v-else>
                                <input
                                    type="date"
                                    id="dueDate"
                                    v-model="form.due_date"
                                    class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:ring focus:ring-sky-blue"
                                />
                            </div>
                        </td>
                    </tr>
                    <tr>
                        <td class="text-gray py-1 pr-3">Priority:</td>
                        <td class="pl-4 py-1 capitalize">
                            <div v-if="!isEdit">
                                <span class="font-bold" :class="[
                                    task.priority === 'low' ? 'text-[#38A169]' : 
                                    task.priority === 'medium' ? 'text-[#D97706]' : 
                                    task.priority === 'high' ? 'text-[#EF4444]' : ''
                                ]">{{ task.priority }}</span>
                            </div>
                            <div v-else>
                                <select
                                    id="priority"
                                    v-model="form.priority"
                                    class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:ring focus:ring-sky-blue"
                                >
                                    <option value="low">Low</option>
                                    <option value="medium">Medium</option>
                                    <option value="high">High</option>
                                </select>
                            </div>
                        </td>
                    </tr>
                    <tr>
                        <td class="text-gray py-1 pr-3">Labels/Tags:</td>
                        <td class="pl-4 py-1">
                            <div v-if="!isEdit">
                                <span v-if="task.labels" v-for="(tag, index) in task.labels" :key="index" :style="{ backgroundColor: tag.color }" class="text-xs px-2 py-1 ml-1 rounded-full">
                                    {{ tag.name }}
                                </span>
                            </div>
                            <div v-else>
                                <select
                                    id="assignedMembers"
                                    v-model="form.labels"
                                    multiple
                                    class="mt-1 block w-full border-gray-300 rounded-md focus:ring focus:ring-sky-blue"
                                >
                                    <option class="text-navy-blue" v-for="label in labels" :key="label.id" :value="label.id">{{ label.name }}</option>
                                </select>
                            </div>
                        </td>
                    </tr>
                </table>
            </div>
            <div v-if="task.subtasks.length" class="flex justify-center items-center w-full sm:w-1/2">
                <div>
                    <div class="text-center text-5xl font-bold text-sky-blue">{{ task.progress }} %</div>
                    <div class="text-center text-gray">Complete</div>
                </div>
            </div>
        </div>
        
        <hr class="text-dark-gray"/>

        <div>
            <div class="flex pt-2 justify-between">
                <label for="setDependency" class="block text-gray font-medium mb-2">Task Dependency:</label>
                <div class="pl-2 cursor-pointer -mt-1 relative group" @click="isDependencyModalOpen = !isDependencyModalOpen">
                    <i class="fas fa-circle-plus text-xl text-sky-blue"></i>
                </div>
            </div>
            <div class="pb-3" v-if="isDependencyModalOpen">
                <select
                    id="setDependencies"
                    v-model="form.dependency"
                    class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:ring focus:ring-sky-blue"
                >
                    <option
                        class="text-navy-blue text-sm"
                        v-for="task in tasks"
                        :key="task.id"
                        :value="task.id"
                    >{{ task.title }}</option>
                </select>
                <div class="flex py-1 justify-end">
                    <button
                        @click="addDependency"
                        class="font-semibold text-xs bg-sky-blue text-linen py-1 px-3 rounded-full inline-block hover:bg-crystal-blue hover:text-navy-blue hover:shadow-lg"
                        >
                        + Add Dependency
                    </button>
                </div>
            </div>

            <div class="pb-3">
                <div class="ml-4 pb-1" v-for="dependency in task.dependencies" :key="dependency.id">
                    <div class="flex items-center">
                        <div class="px-3 py-1 text-sm text-linen rounded-full bg-sky-blue">{{ dependency.title }}</div>
                        <div class="pl-3 text-sm flex items-center italic text-navy-blue">
                            <span class="font-bold">{{ dependency.status.name }}</span>
                        </div>
                        <div v-if="task.user_id == $page.props.auth.user.id" class="text-sm cursor-pointer pl-3 pt-1">
                            <Dropdown align="right" width="48">
                                <template #trigger>
                                    <i class="fa-solid fa-ellipsis"></i>
                                </template>
                                <template #content>
                                    <div
                                        class="hover:bg-crystal-blue px-3 py-2"
                                        @click.stop="removeDependency(dependency.pivot, task.id)"
                                    >
                                        Remove
                                    </div>
                                </template>
                            </Dropdown>
                        </div>

                    </div>
                </div>
            </div>


        </div>

        <hr class="text-dark-gray"/>

        <div class="mb-4">
            <div class="flex pt-2 justify-between">
                <label for="assignedMembers" class="block text-gray font-medium mb-2">Assigned Members:</label>
                <div class="pl-2 cursor-pointer -mt-1 relative group" @click="openMemberModal">
                    <i class="fas fa-circle-plus text-xl text-sky-blue"></i>
                    <span class="absolute top-full mt-1 right-0 transform -translate-x-1 hidden group-hover:flex items-center px-4 py-3 text-sm font-semibold text-white bg-light-gray rounded-md shadow-lg z-10 whitespace-nowrap">
                        Add Member
                    </span>
                </div>
            </div>
            <div class="" v-if="isMemberModalOpen">
                <select
                    id="assignedMembers"
                    v-model="form.assigned_members"
                    multiple
                    class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:ring focus:ring-sky-blue"
                >
                    <option
                        class="text-navy-blue text-sm"
                        v-for="member in members"
                        :key="member.id"
                        :value="member.id"
                        :disabled="isUserAssigned(member.user_id)"
                    >{{ member.name }}</option>
                </select>
                <div class="flex py-1 justify-end">
                    <button
                        @click="assignMember"
                        class="font-semibold text-xs bg-sky-blue text-linen py-1 px-3 rounded-full inline-block hover:bg-crystal-blue hover:text-navy-blue hover:shadow-lg"
                        >
                        + Add Member
                    </button>
                </div>
            </div>
            <div class="flex">
                <div v-for="(user, index) in task.users" :key="index" class="flex items-center pl-2">
                    <Dropdown align="right" width="40">
                        <template #trigger>
                            <div class="flex cursor-pointer">
                                <img :src="'/' + user.profile_image" alt="User Avatar" class="w-7 h-7 rounded-full border-2 object-cover border-color-white" />
                                <span class="text-sm flex items-center text-navy-blue pl-1">{{ user.name }}</span>
                            </div>
                        </template>
                        <template #content>
                            <div
                                class="hover:bg-crystal-blue text-sm px-3 py-2 cursor-pointer"
                                @click="removeUser(user.id)"
                            >
                                Remove
                            </div>
                        </template>
                    </Dropdown>

                </div>
            </div>
        </div>

        <hr class="text-dark-gray"/>

        <div>
            <div class="mb-4 border-b border-dark-gray">
            <ul
                class="flex flex-wrap -mb-[5px] text-sm font-medium text-center list-none"
                id="default-tab"
                role="tablist"
            >
                <li class="me-2" role="presentation">
                    <button
                        class="inline-block p-4 border-b-2 rounded-t-lg border-dark-gray text-gray"
                        id="subtask-tab"
                        type="button"
                        role="tab"
                        :class="{ 'border-sky-blue text-sky-blue': activeTab === 'subtask' }"
                        @click="selectTab('subtask')"
                    >
                        Subtask <span class="pl-1"> ({{ task.subtasks.length }})</span>
                    </button>
                </li>
                <li class="me-2" role="presentation">
                    <button
                        class="inline-block p-4 border-b-2 rounded-t-lg border-dark-gray text-gray hover:text-sky-blue hover:border-sky-blue"
                        id="comments-tab"
                        type="button"
                        role="tab"
                        :class="{ 'border-sky-blue text-sky-blue': activeTab === 'comments' }"
                        @click="selectTab('comments')"
                    >
                        Comments <span class="pl-1"> ({{ task.comments.length }})</span>
                    </button>
                </li>
                <li class="me-2" role="presentation">
                    <button
                        class="inline-block p-4 border-b-2 rounded-t-lg border-dark-gray text-gray hover:text-sky-blue hover:border-sky-blue"
                        id="description-tab"
                        type="button"
                        role="tab"
                        :class="{ 'border-sky-blue text-sky-blue': activeTab === 'description' }"
                        @click="selectTab('description')"
                    >
                        Description
                    </button>
                </li>
                <li class="me-2" role="presentation">
                    <button
                        class="inline-block p-4 border-b-2 rounded-t-lg border-dark-gray text-gray hover:text-sky-blue hover:border-sky-blue"
                        id="history-tab"
                        type="button"
                        role="tab"
                        :class="{ 'border-sky-blue text-sky-blue': activeTab === 'history' }"
                        @click="selectTab('history')"
                    >
                        History
                    </button>
                </li>
            </ul>
            </div>
            <div id="default-tab-content">
                <div
                    v-show="activeTab === 'subtask'"
                    class="rounded-lg"
                    id="subtask"
                    role="tabpanel"
                >
                    <div class="">
                        <SubTasks :task="task"/>
                        <!-- <TextInput
                            placeholder="Enter subtask here..."
                            v-model="form.subtask"
                            class="mt-4 block w-full border-gray-300 rounded-md shadow-sm focus:ring focus:ring-sky-blue"
                            @keyup.enter="submitSubtask"
                        /> -->
                    </div>

                </div>
                <div
                    v-show="activeTab === 'comments'"
                    class="p-4 rounded-lg bg-light-gray"
                    id="comments"
                    role="tabpanel"
                >
                    <Comments :task="task"/>
                </div>
                <div
                    v-show="activeTab === 'description'"
                    class="rounded-lg bg-light-gray"
                    id="description"
                    role="tabpanel"
                >
                    <div class="mb-4">
                        <TaskDescription :task="{ description: newDesc }" @update:desc="(value) => (newDesc = value)"/>
                        <div class="flex pb-2 justify-end pr-2">
                            <button
                                @click="updateDesc"
                                class="font-semibold text-xs bg-sky-blue text-linen py-1 px-3 rounded-full inline-block hover:bg-crystal-blue hover:text-navy-blue hover:shadow-lg"
                                >
                                Save
                            </button>
                        </div>

                    </div>
                </div>
                <div
                    v-show="activeTab === 'history'"
                    class="p-4 rounded-lg bg-light-gray"
                    id="history"
                    role="tabpanel"
                >
                    <div class="py-2" v-if="task.histories.length" v-for="history in task.histories" :key="history.id">
                        <p class="text-xs" v-if="history.attribute == 'status_id'">
                            <strong>{{ history.user.name }}</strong> changed <strong>Status</strong>
                            from <em class="font-bold">{{ history.old_status ? history.old_status.name : '' }}</em>
                            to <em class="font-bold">{{ history.new_status ? history.new_status.name : '' }}</em>
                            on {{ formatDate(history.created_at) }}.
                        </p>
                        <p class="text-xs" v-else>
                            <strong>{{ history.user.name }}</strong> changed <strong class="capitalize">{{ history.attribute }}</strong>
                            from <em class="font-bold" v-html="history.old_value ? history.old_value : 'null'"></em>
                            to <em class="font-bold" v-html="history.new_value ? history.new_value : 'null'"></em>
                            on {{ formatDate(history.created_at) }}.
                        </p>
                    </div>
                    <div v-else>
                        No History
                    </div>
                </div>
            </div>
        </div>

        <!-- <hr class="text-dark-gray"/> -->
        
        <!-- Progress Bar -->
        <div class="w-full h-2 bg-crystal-blue rounded-full my-4">
            <div :style="{ width: task.progress + '%' }" class="h-full bg-navy-blue rounded-full"></div>
        </div>

        <div v-if="isEdit" class="flex justify-end">
            <button type="button" class="mr-2 bg-gray-300 text-navy-blue py-2 px-4 rounded" @click="cancelUpdate">Cancel</button>
            <button type="submit" class="bg-sky-blue text-linen rounded-full py-2 px-4 hover:bg-crystal-blue hover:text-navy-blue hover:shadow-lg" @click="updateTask">Save</button>
        </div>

    </div>
</template>