<script setup>
import { ref, defineEmits, computed } from 'vue';
const emit = defineEmits();

const selectedProjectId = ref(null);
const selectAll = ref(false);
const searchQuery = ref('');
const selectedParticipants = ref([]);

const props = defineProps({
  projects: Object,
  participants: Array,
});

const filteredUsers = computed(() => {
    const project = props.projects.find(p => p.id === selectedProjectId.value);
    if (!project) return [];

    // Filter project users to exclude those in participants
    let users = project.users.filter(
        user => !props.participants.some(participant => participant.id === user.id)
    );
    // Further filter based on the search query
    if (searchQuery.value) {
        users = users.filter(
            user =>
                user.name.toLowerCase().includes(searchQuery.value.toLowerCase()) ||
                user.email.toLowerCase().includes(searchQuery.value.toLowerCase())
        );
    }
    return users;

});

// Add participant
const addParticipant = (user) => {
  if (!selectedParticipants.value.find((participant) => participant.id === user.id)) {
    selectedParticipants.value.push(user);
  }
};

// Remove participant
const removeParticipant = (participant) => {
  selectedParticipants.value = selectedParticipants.value.filter(
    (user) => user.id !== participant.id
  );
};

const onProjectChange = () => {
  selectAll.value = false;
  selectedParticipants.value = [];
};

// Handle selecting all users from the project
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

const insertParticipant = () => {
    emit('newParticipants', selectedParticipants.value)
}

</script>
<template>
    <div class="bg-linen rounded-lg shadow-lg p-6">
        <h3 class="text-lg text-navy-blue font-semibold mb-4">Add Participant</h3>
        <div class="mb-4">
            <label for="project" class="block text-sm font-medium text-navy-blue">Select Project</label>
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
            <label for="participants" class="block text-sm font-medium text-navy-blue">Participants</label>
            <div class="mt-1">
                <!-- Select All Users from Project -->
                <label class="inline-flex items-center">
                    <input type="checkbox" v-model="selectAll" @change="onSelectAllUsers" />
                    <span class="ml-2">Select All Users from this Project</span>
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
            <button type="submit" class="bg-sky-blue text-linen rounded-full py-2 px-4 hover:bg-crystal-blue hover:text-navy-blue hover:shadow-lg" @click="insertParticipant">Add Participant</button>
        </div>

    </div>
</template>