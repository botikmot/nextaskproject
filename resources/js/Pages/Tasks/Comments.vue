
<script setup>
import { usePage, useForm } from '@inertiajs/vue3'
import { ref, computed } from 'vue';
import Dropdown from '@/Components/Dropdown.vue';

const props = defineProps({
    task: Object,
});

const fileInput = ref(null);
const attachments = ref([]);
let isUpdateComment = ref(false);

const form = useForm({
    comment: '',
    comment_id: null,
    attachments: null,
});

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

</script>

<template>
    <div v-if="task.comments.length" class="">
        <div class="items-center border-b border-dark-gray py-1" v-for="comment in task.comments" :key="comment.id">
            <div class="flex relative items-center justify-between">
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
</template>