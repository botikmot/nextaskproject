<script setup>
import { usePage, useForm } from '@inertiajs/vue3'
import UserImage from '@/Components/UserImage.vue';
import axios from 'axios';
import { ref, defineEmits, computed } from 'vue';

const emit = defineEmits(['conversationStarted']);
const props = defineProps({
  type: String,
  projects: Array,
});
const page = usePage();
const sharedFriends = page.props.sharedFriends || [];
const authUserId = page.props.auth.user.id
const selectedProjectId = ref(null);
const selectAll = ref(false); 
const selectedParticipants = ref([]);
const searchQuery = ref('');
const groupName = ref('');

const startConversation = async (id) => {
    try {
        console.log('Friend ID', id);
        const data = {
            friend_id: id,
        };
        const response = await axios.post('/conversations/private', data);
        console.log('Conversation', response);

        // Emit the new conversation details to the parent
        if (response.data.success) {
            const conversation = response.data.conversation;
            // Emit the conversation to the parent
            emit('conversationStarted', conversation);
        }
    } catch (error) {
        console.error('Error starting conversation:', error);
    }
}

const startGroupConversation = async () => {
    try {
        const data = {
            name: groupName.value,
            participants: selectedParticipants.value
                .filter(participant => participant.id !== authUserId)
                .map(participant => participant.id)
        };
        console.log('data', data);
        const response = await axios.post('/conversations/group', data);
        console.log('Conversation', response);

        // Emit the new conversation details to the parent
        if (response.data.success) {
            const conversation = response.data.conversation;
            // Emit the conversation to the parent
            emit('conversationStarted', conversation);
        }
    } catch (error) {
        console.error('Error starting conversation:', error);
    }
}

const filteredUsers = computed(() => {
  const project = props.projects.find(p => p.id === selectedProjectId.value);
  if (!project || !searchQuery.value) return project ? project.users : [];

  return project.users.filter(
    user =>
      user.name.toLowerCase().includes(searchQuery.value.toLowerCase()) ||
      user.email.toLowerCase().includes(searchQuery.value.toLowerCase())
  );
});

const addParticipant = (user) => {
  if (!selectedParticipants.value.find((participant) => participant.id === user.id)) {
    selectedParticipants.value.push(user);
  }
};

const removeParticipant = (participant) => {
  selectedParticipants.value = selectedParticipants.value.filter(
    (user) => user.id !== participant.id
  );
};

const onProjectChange = () => {
  selectAll.value = false;
  selectedParticipants.value = [];
};

const onSelectAllUsers = () => {
  if (selectAll.value) {
    const project = props.projects.find(p => p.id === selectedProjectId.value);
    selectedParticipants.value = [...project.users];
  } else {
    selectedParticipants.value = [];
  }
};

const cancel = () => {
    emit('close');
}

</script>

<template>
    <div class="bg-linen rounded-lg shadow-lg p-6 w-full">
        <h3 class="text-lg text-navy-blue font-semibold mb-4">{{ type == 'private' ? 'Friends' : 'Group' }}</h3>
        <div
            v-if="type === 'private'"
            class="py-1 px-3 mb-2 border-b border-dark-gray hover:bg-light-gray"
            v-for="friend in sharedFriends"
            :key="friend.id"
        >
            <div>
                <!-- Profile Section -->
                <div class="flex justify-between">
                    <div class="flex items-center">
                        <UserImage class="h-8 w-8 rounded-full object-cover" :user="friend" />
                        <div class="pl-3">
                            <div class="text-navy-blue font-medium text-sm">{{ friend.name }}</div>
                            <div class="text-xs text-gray">Mutual Projects: {{ friend.mutual_projects }}</div>
                        </div>
                    </div>
                    <button @click="startConversation(friend.id)" class="px-3 text-sm text-navy-blue rounded-full hover:bg-sky-blue hover:text-color-white hover:font-bold">
                        Start Conversation
                    </button>
                </div>
            </div>
        </div>

        <div v-else>
            <div class="mb-4">
                <label for="groupName" class="block text-sm font-medium text-navy-blue">Group Name</label>
                <input type="text" id="groupName" v-model="groupName" class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:ring focus:ring-sky-blue" required>
            </div>
            <div class="mb-4">
                <label for="project" class="block text-sm font-medium text-navy-blue">Select Group</label>
                <select
                    v-model="selectedProjectId"
                    id="project"
                    class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:ring focus:ring-sky-blue"
                    @change="onProjectChange"
                >
                    <option v-for="project in projects" :key="project.id" :value="project.id">
                        {{ project.title }}
                    </option>
                </select>
            </div>

            <div class="mb-4">
                <label for="participants" class="block text-sm font-medium text-navy-blue">Group</label>
                <div class="mt-1">
                    <!-- Select All Users from Project -->
                    <label class="inline-flex items-center">
                        <input type="checkbox" v-model="selectAll" @change="onSelectAllUsers" />
                        <span class="ml-2">Select All Users from this Group</span>
                    </label>
                    
                    <!-- OR -->
                    <div class="mt-2">
                        <input
                            type="text"
                            v-model="searchQuery"
                            placeholder="Search by name or email"
                            class="block w-full border-gray-300 rounded-md shadow-sm focus:ring focus:ring-sky-blue"
                        />

                    <!-- Searchable Dropdown -->
                        <div class="mt-2 max-h-48 overflow-auto bg-white border border-gray-300 rounded-md shadow-lg">
                            <div
                                v-for="user in filteredUsers"
                                :key="user.id"
                                @click="addParticipant(user)"
                                class="cursor-pointer px-4 py-2 hover:bg-sky-blue text-sm text-gray-800"
                            >
                                {{ user.name }} ({{ user.email }})
                            </div>
                        </div>
                    </div>

                    <!-- Display Selected Participants -->
                    <div class="mt-2">
                        <div
                            v-for="participant in selectedParticipants"
                            :key="participant.id"
                            class="inline-flex items-center px-3 py-1 my-1 mr-2 text-sm bg-sky-blue text-color-white rounded-full"
                        >
                            {{ participant.name }} ({{ participant.email }})
                            <button
                                type="button"
                                @click="removeParticipant(participant)"
                                class="ml-2 text-white hover:text-gray-800"
                            >
                            &times;
                            </button>
                        </div>
                    </div>
                </div>
            </div>
            <div class="flex justify-end">
                <button type="button" class="mr-2 bg-gray-300 text-navy-blue py-2 px-4 rounded" @click="cancel">Cancel</button>
                <button type="submit" class="bg-sky-blue text-linen rounded-full py-2 px-4 hover:bg-crystal-blue hover:text-navy-blue hover:shadow-lg" @click="startGroupConversation">Start Conversation</button>
            </div>

        </div>


    </div>
</template>