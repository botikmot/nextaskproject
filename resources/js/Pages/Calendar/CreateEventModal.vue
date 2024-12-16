<script setup>
import { usePage, useForm } from '@inertiajs/vue3'
import { ref, defineEmits, computed } from 'vue';
import Swal from 'sweetalert2';

const emit = defineEmits();
const selectedProjectId = ref(null); // The currently selected project ID
const selectAll = ref(false); // Whether to select all users from the project
const searchQuery = ref(''); // Search query for filtering users
const selectedParticipants = ref([]); // Array of selected participants

const props = defineProps({
  projects: Object,
});

const form = useForm({
    title: '',
    description: '',
    start: new Date().toISOString().slice(0, 16),
    end: null,
    all_day: false,
    location: '',
    background_color: '#40a2e3',
    participants: [],
});

// Computed property to filter users based on the search query
const filteredUsers = computed(() => {
  const project = props.projects.find(p => p.id === selectedProjectId.value);
  if (!project || !searchQuery.value) return project ? project.users : [];

  return project.users.filter(
    user =>
      user.name.toLowerCase().includes(searchQuery.value.toLowerCase()) ||
      user.email.toLowerCase().includes(searchQuery.value.toLowerCase())
  );
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

// Handle project change and reset selected users
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

const submitEvent = () => {
    form.participants = selectedParticipants.value.map(participant => participant.id);
    console.log('data-', form)
    form.post('/events', {
        data: form,
        preserveScroll: true,
        onSuccess: (response) => {
            form.reset()
            console.log('Successfully created.')
            Swal.fire({
                text: response.props.flash.message,
                position: 'bottom-end',
                backdrop: false,
                timer: 2000,
                showConfirmButton: false,
                toast:true,
                icon: response.props.flash.success ? 'success' : 'error',
            });
            emit('close')
        },
        onError: (error) => {
            console.error('Error creating event', error)
        }
    })

}


</script>

<template>
    <div class="bg-linen rounded-lg shadow-lg p-6">
        <h3 class="text-lg text-navy-blue font-semibold mb-4">Create Event</h3>
        <div>
            <div class="mb-4">
                <label for="eventName" class="block text-sm font-medium text-navy-blue">Event Title</label>
                <input type="text" id="eventName" v-model="form.title" class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:ring focus:ring-sky-blue" required>
            </div>
            <div class="mb-4">
                <label for="eventDescription" class="block text-sm font-medium text-navy-blue">Description</label>
                <textarea id="eventDescription" v-model="form.description" class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:ring focus:ring-sky-blue"></textarea>
            </div>
            <div class="mb-4 flex w-full items-center">
                <div class="w-1/3 px-1">
                    <label for="eventStart" class="block text-sm font-medium text-navy-blue">Start Date & Time</label>
                    <input
                        type="datetime-local"
                        id="eventStart"
                        v-model="form.start"
                        class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:ring focus:ring-sky-blue"
                        required
                    />
                </div>
                <div class="w-1/3 px-1">
                    <label for="eventEnd" class="block text-sm font-medium text-navy-blue">End Date & Time</label>
                    <input
                        type="datetime-local"
                        id="eventEnd"
                        v-model="form.end"
                        class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:ring focus:ring-sky-blue"
                    />
                </div>
                <div class="w-1/3 px-1">
                    <label for="columnColor" class="block text-sm font-medium text-navy-blue">Color</label>
                    <div class="flex w-full items-center">
                        <input 
                            type="color" 
                            id="columnColor" 
                            v-model="form.background_color" 
                            class="mt-1 w-1/4 block border-gray-300 rounded-md h-12 shadow-sm focus:ring focus:ring-sky-blue"
                        > 
                        <input type="text" id="projectColor" v-model="form.background_color" class="mt-1 w-3/4 border-gray-300 rounded-md shadow-sm focus:ring focus:ring-sky-blue">
                    </div>
                </div>
            </div>
            <div class="mb-4">
                <label for="eventLocation" class="block text-sm font-medium text-navy-blue">Location</label>
                <input type="text" id="eventLocation" v-model="form.location" class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:ring focus:ring-sky-blue">
            </div>
            <div class="flex justify-center pl-6">
                <label class="block flex items-center font-medium text-navy-blue">
                    <input 
                        type="checkbox" 
                        id="allDay" 
                        v-model="form.all_day"
                        class="mr-2 rounded border-gray-300 text-sky-blue focus:ring focus:ring-sky-blue"
                    />
                    All Day Event
                </label>
            </div>

            <div class="py-3 font-bold">Participants</div>
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

                <!-- Select All Users or Search Users -->
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
                <button type="submit" class="bg-sky-blue text-linen rounded-full py-2 px-4 hover:bg-crystal-blue hover:text-navy-blue hover:shadow-lg" @click="submitEvent">Save Event</button>
            </div>

        </div>
    </div>
</template>