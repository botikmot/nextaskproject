<script setup>
import { usePage, useForm } from '@inertiajs/vue3'
import Dropdown from '@/Components/Dropdown.vue';
import { ref, computed } from 'vue';
import TextInput from '@/Components/TextInput.vue';


let isMemberModalOpen = ref(false);
let isUpdateComment = ref(false);
let showSubtask = ref(false)
let isDependencyModalOpen = ref(false)

const props = defineProps({
    task: Object,
    members: Object,
    tasks: Object,
});

const fileInput = ref(null);
const attachments = ref([]);

const form = useForm({
    assigned_members: [],
    removed_users: [],
    comment: '',
    comment_id: null,
    attachments: null,
    subtask: '',
    name: '', // subtasks name
    is_completed: null,
    dependency: null,
});

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

const submitComment = async () => {
    console.log(form.comment)
    if(!isUpdateComment.value){

        if(attachments.value.length){
            form.comment = form.comment == '' ? 'attachments' : form.comment
            const formData = new FormData();
            formData.append('comment', form.comment);

            attachments.value.forEach((file, index) => {
                formData.append(`attachments[${index}]`, file); // Append each file to FormData
            });
            const response = await axios.post(`/task-comment/${props.task.id}`, formData, {
                headers: {
                    "Content-Type": "multipart/form-data"
                }
            });
            if (response.status === 200) {
                form.reset();
                attachments.value = []; // Clear attachments
                props.task.comments.push(response.data.comment);
            }
            
        }else{
            form.post(`/task-comment/${props.task.id}`, {
                data: form,
                preserveScroll: true,
                onSuccess: () => {
                    form.reset()
                    attachments.value = []
                },
                onError: (error) => {
                    console.error('Error saving comment', error)
                }
            })
        }
    }else{
        console.log('Update Comment', form.comment)
        form.put(`/task-comment/${form.comment_id}`, {
            data: form,
            preserveScroll: true,
            onSuccess: () => {
                form.reset()
                isUpdateComment.value = false
            },
            onError: (error) => {
                console.error('Error updating comment', error)
            }
        })
    }
}

const removeComment = (id) => {
    form.comment_id = id
    console.log(form.comment_id)
    form.delete(`/task-comment/${form.comment_id}`, {
        data: form,
        preserveScroll: true,
        onSuccess: () => {
            form.reset()
        },
        onError: (error) => {
            console.error('Error deleting comment', error)
        }
    })
}

const editComment = (id, comment) => {
    isUpdateComment.value = true
    form.comment_id = id
    form.comment = comment
    console.log(form.comment_id)
}

const cancelUpdate = () => {
    isUpdateComment.value = false
    form.comment = ''
}

const triggerFileInput = () => {
  fileInput.value.click(); // Programmatically click the file input
}

const handleFileUpload = (event) => {
    attachments.value = Array.from(event.target.files); // Store multiple files
    console.log('attachments:', attachments.value)
}

const isImage = (attachment) => {
    const imageExtensions = ['jpg', 'jpeg', 'png', 'gif', 'jfif'];
    const ext = getFileExtension(attachment.filename);
    return imageExtensions.includes(ext.toLowerCase());
}

const isVideo = (attachment) => {
    const videoExtensions = ['mp4', 'mov', 'avi'];
    const ext = getFileExtension(attachment.filename);
    return videoExtensions.includes(ext.toLowerCase());
}

const getFileExtension = (filename) => {
    return filename.split('.').pop();
}

const toggleSubtask = () => {
    showSubtask.value = !showSubtask.value
}

const submitSubtask = () => {
    console.log(form.subtask)

    form.post(`/task/${props.task.id}/subtask`, {
        data: form,
        preserveScroll: true,
        onSuccess: () => {
            form.reset()
            showSubtask.value = false
            form.subtask = ''
        },
        onError: (error) => {
            console.error('Error adding subtasks', error)
        }
    })

}

const updateSubtaskStatus = (subtask) => {
    console.log('subtask', subtask)
    subtask.is_completed = !subtask.is_completed;

    form.name = subtask.name
    form.is_completed = subtask.is_completed
    form.put(`/task/${subtask.id}/subtask`, {
        data: form,
        preserveScroll: true,
        onSuccess: () => {
            form.reset()
        },
        onError: (error) => {
            console.error('Error updating subtasks', error)           
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
        <h3 class="text-lg text-navy-blue font-semibold mb-4">Task Details</h3>
        <div class="mb-4">
            <label for="projectName" class="block text-sm font-medium">Task Name</label>
            <input type="text" id="projectName" v-model="task.title" class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:ring focus:ring-sky-blue" required>
        </div>
        
        <div>
            <div class="flex py-2">
                <label for="setDependency" class="block text-sm font-medium mb-2">Set Task Dependency:</label>
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
        </div>


        <div class="mb-4">
            <label for="projectDescription" class="block text-sm font-medium">Description</label>
            <textarea id="projectDescription" rows="1" v-model="task.description" class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:ring focus:ring-sky-blue"></textarea>
        </div>
        <div class="block sm:flex w-full">
            <div class="mb-4 w-full sm:w-1/3 mr-1">
                <label for="dueDate" class="block text-sm font-medium">Due Date</label>
                <input
                    type="date"
                    id="dueDate"
                    v-model="task.due_date"
                    class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:ring focus:ring-sky-blue"
                />
            </div>
            <div class="mb-4 w-full sm:w-1/3 mx-1">
                <label for="status" class="block text-sm font-medium">Priority</label>
                <select
                    id="status"
                    v-model="task.priority"
                    class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:ring focus:ring-sky-blue"
                >
                    <option value="low">Low</option>
                    <option value="medium">Medium</option>
                    <option value="high">High</option>
                </select>
            </div>
            <div class="mb-4 w-full sm:w-1/3 ml-1">
                <label for="status" class="block text-sm font-medium">Status</label>
                <select
                    id="status"
                    v-model="task.status"
                    class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:ring focus:ring-sky-blue"
                >
                    <option value="To Do">To Do</option>
                    <option value="In Progress">In Progress</option>
                    <option value="Completed">Completed</option>
                </select>
            </div>
        </div>
        <div class="mb-4">
            <div class="flex py-2">
                <label for="assignedMembers" class="block text-sm font-medium mb-2">Assigned Members:</label>
                <div class="pl-2 cursor-pointer -mt-1 relative group" @click="openMemberModal">
                    <i class="fas fa-circle-plus text-xl text-sky-blue"></i>
                    <span class="absolute top-full mt-1 right-0 transform -translate-x-1 hidden group-hover:flex items-center px-4 py-3 text-sm font-semibold text-white bg-light-gray rounded-md shadow-lg z-10 whitespace-nowrap">
                        Add Member
                    </span>
                </div>
            </div>
            <div class="pb-3" v-if="isMemberModalOpen">
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
                                <img :src="'/' + user.profile_image" alt="User Avatar" class="w-7 h-7 rounded-full border-2 border-color-white" />
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

                   <!--  <img :src="'/' + user.profile_image" alt="User Avatar" class="w-6 h-6 rounded-full border-2 border-white" />
                    <span class="text-sm text-gray-700 pl-1">{{ user.name }}</span> -->
                </div>
            </div>
        </div>

         <!-- Tags -->
         <div v-if="task.labels" class="space-x-2 pb-2">
            <label for="Labels" class="block text-sm font-medium pb-2">Labels/Tags:</label>
            <span v-for="(tag, index) in task.labels.split(', ')" :key="index" class="text-sm text-color-white bg-sky-blue px-2 py-1 rounded-full">
                {{ tag }}
            </span>
        </div>

        <hr class="text-dark-gray"/>
        
        <!------ Subtasks ----->
        <div class="py-4">
            <div class="flex py-2">
                <label for="subTasks" class="block text-sm font-medium mb-2">Subtasks:</label>
                <div @click="toggleSubtask" class="pl-2 cursor-pointer -mt-1 relative group">
                    <i class="fas fa-circle-plus text-xl text-sky-blue"></i>
                </div>
            </div>
            <div v-for="subtask in task.subtasks" :key="subtask.id" class="flex items-center space-x-2">
                <!-- Checkbox -->
                <input
                    type="checkbox"
                    :checked="subtask.is_completed"
                    @change="updateSubtaskStatus(subtask)"
                    class="form-checkbox h-4 w-4 text-blue-600"
                />
                <!-- Subtask Name -->
                <div class="text-sm" :class="{ 'line-through text-gray-500': subtask.is_completed }">
                    {{ subtask.name }}
                </div>
            </div>
            <TextInput
                placeholder="Enter subtask here..."
                v-model="form.subtask"
                v-if="showSubtask"
                class="mt-2 block w-full border-gray-300 rounded-md shadow-sm focus:ring focus:ring-sky-blue"
                @keyup.enter="submitSubtask"
            />
        </div>

        <hr class="text-dark-gray"/>

        <div v-if="task.comments.length" class="pt-3">
            <label for="Labels" class="block text-sm font-medium pb-1">Comments:</label>
            <div class="items-center border-b border-dark-gray py-1" v-for="comment in task.comments" :key="comment.id">
                <div class="pl-2 flex relative items-center justify-between">
                    <div class="flex">
                        <img :src="'/' + comment.user.profile_image" alt="Profile" class="w-7 h-7 rounded-full border-2 border-color-white" />
                        <div class="text-sm pl-1 pt-1" v-html="comment.content.replace(/\n/g, '<br>')"></div>
                    </div>
                    <div v-if="comment.user_id == $page.props.auth.user.id" class="text-sm cursor-pointer right-0 top-3">
                        <Dropdown align="right" width="48">
                            <template #trigger>
                                <i class="fa-solid fa-ellipsis"></i>
                            </template>
                            <template #content>
                                <div
                                    class="hover:bg-crystal-blue px-3 py-2"
                                    @click="removeComment(comment.id)"
                                >
                                    Remove
                                </div>
                                <div
                                    class="hover:bg-crystal-blue px-3 py-2"
                                    @click="editComment(comment.id, comment.content)"
                                >
                                    Edit
                                </div>
                            </template>
                        </Dropdown>
                    </div>
                </div>
                <div class="flex py-3 pl-10" v-if="comment.attachments.length">
                    <div class="pl-2" v-for="attachment in comment.attachments" :key="attachment.id">
                        <div v-if="isImage(attachment)" class="max-w-36">
                            <!-- Display image if attachment is an image -->
                            <img :src="'/storage/' + attachment.path" alt="Attachment Image" class="attachment-image" />
                        </div>
                        <div v-else-if="isVideo(attachment)">
                            <!-- Display video if attachment is a video -->
                            <!-- <video controls class="attachment-video">
                                <source :src="getAttachmentUrl(attachment)" type="video/mp4">
                                Your browser does not support the video tag.
                            </video> -->
                        </div>
                        <div v-else>
                            <!-- Display filename if attachment is not an image or video -->
                            <span class="attachment-filename">{{ attachment.filename }}</span>
                        </div>
                    </div>
                </div>
                
            </div>
        </div>

        <div class="mb-4 pt-3">
            <textarea id="taskComment" v-model="form.comment" placeholder="Type your comment" class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:ring focus:ring-sky-blue" required></textarea>
            <div class="flex py-1 justify-between">
                <div class="flex items-center">
                    <input ref="fileInput" class="hidden" type="file" multiple @change="handleFileUpload" />
                    <button
                        @click="triggerFileInput"
                        class="font-semibold text-sm bg-color-white text-navy-blue py-1 mt-2 px-3 mr-2 rounded-full inline-block hover:bg-gray hover:text-color-white"
                        >
                        Add Attachments
                    </button>
                    <div class="text-xs pt-2 italic" v-if="attachments.length">
                        added {{ attachments.length }} attachments
                    </div>
                </div>
                <div class="flex">
                    <button
                        v-if="isUpdateComment"
                        @click="cancelUpdate"
                        class="font-semibold text-sm bg-color-white text-navy-blue py-1 mt-2 px-3 mr-2 rounded-full inline-block hover:bg-gray hover:text-color-white"
                        >
                        Cancel Update
                    </button>

                    <button
                        @click="submitComment"
                        class="font-semibold text-sm bg-sky-blue text-linen py-1 mt-2 px-3 rounded-full inline-block hover:bg-crystal-blue hover:text-navy-blue hover:shadow-lg"
                        >
                        {{ isUpdateComment ? 'Update Comment' : 'Submit' }}
                    </button>
                </div>
            </div>
        </div>


        <!-- Progress Bar -->
        <div class="w-full h-2 bg-gray rounded-full my-4">
            <div :style="{ width: 50 + '%' }" class="h-full bg-sky-blue rounded-full"></div>
        </div>
    </div>
</template>