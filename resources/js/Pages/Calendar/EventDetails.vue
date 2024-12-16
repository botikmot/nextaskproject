<script setup>
import { usePage, useForm } from '@inertiajs/vue3'
import { ref, defineEmits, computed } from 'vue';
import Modal from '@/Components/Modal.vue';
import AddParticipants from './AddParticipants.vue';
import Swal from 'sweetalert2';

const emit = defineEmits();

let isEdit = ref(false)
let isAddParticipantModal = ref(false);

const props = defineProps({
  event: Object,
  projects: Object,
});

let participants = ref(props.event.participants);

// Convert to ISO 8601 format
const formatToISO8601 = (datetime) => {
    console.log('datetime--->', datetime)
    if (datetime.includes('T')) {
        return datetime
    }else {
        const [datePart, timePart] = datetime.split(' ');
        return `${datePart}T${timePart.slice(0, 5)}`;
    }
};

const form = useForm({
    title: props.event.title,
    description: props.event.extendedProps.description,
    start: props.event.start,
    end: props.event.end,
    all_day: props.event.allDay == 0 ? false : true,
    location: props.event.location,
    background_color: props.event.backgroundColor,
    participants: [],
});


const cancelUpdate = () => {
    isEdit.value = false
}

const addNewParticipants = (data) => {
    participants.value.push(...data);
    isAddParticipantModal.value = false
}



const updateEvent = () => {
    form.participants = participants.value.map(participant => participant.id);
    form.start = formatToISO8601(form.start)
    form.end = form.end ? formatToISO8601(form.end) : null
    console.log('event--->', form)
    form.post(`/events/${props.event.id}`, {
        data: form,
        preserveScroll: true,
        onSuccess: (response) => {
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
        },
        onError: (error) => {
            console.error('Error creating event', error)
        }
    })
}

// Remove participant
const removeParticipant = (participant) => {
    participants.value = participants.value.filter(
        (user) => user.id !== participant.id
    );
};

</script>

<template>
    <div class="bg-linen rounded-lg shadow-lg p-6">
        <div class="flex justify-between">
            <h3 class="text-lg text-navy-blue font-semibold mb-4">Event Details</h3>
            <div v-if="event.extendedProps.creator_id == $page.props.auth.user.id">
                <div @click="isEdit = !isEdit" class="px-3 cursor-pointer py-1 relative group">
                    <i class="fa-regular fa-pen-to-square text-lg text-gray"></i>
                    <span class="absolute top-full mt-1 right-0 transform -translate-x-1 hidden group-hover:flex items-center px-4 py-3 text-sm font-semibold text-white bg-light-gray rounded-md shadow-lg z-10 whitespace-nowrap">
                        Edit
                    </span>
                </div>
            </div>
        </div>
        <div class="mb-4">
            <label for="eventName" class="block text-sm font-medium text-navy-blue">Event Title</label>
            <input :disabled="isEdit==false" type="text" id="eventName" v-model="form.title" class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:ring focus:ring-sky-blue" required>
        </div>
        <div class="mb-4">
            <label for="eventDescription" class="block text-sm font-medium text-navy-blue">Description</label>
            <textarea :disabled="isEdit==false" id="eventDescription" v-model="form.description" class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:ring focus:ring-sky-blue"></textarea>
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
                    :disabled="isEdit==false"
                />
            </div>
            <div class="w-1/3 px-1">
                <label for="eventEnd" class="block text-sm font-medium text-navy-blue">End Date & Time</label>
                <input
                    type="datetime-local"
                    id="eventEnd"
                    v-model="form.end"
                    class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:ring focus:ring-sky-blue"
                    :disabled="isEdit==false"
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
                        :disabled="isEdit==false"
                    > 
                    <input :disabled="isEdit==false" type="text" id="projectColor" v-model="form.background_color" class="mt-1 w-3/4 border-gray-300 rounded-md shadow-sm focus:ring focus:ring-sky-blue">
                </div>
            </div>
        </div>
        <div class="mb-4">
            <label for="eventLocation" class="block text-sm font-medium text-navy-blue">Location</label>
            <input :disabled="isEdit==false" type="text" id="eventLocation" v-model="form.location" class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:ring focus:ring-sky-blue">
        </div>
        <div class="flex justify-center pl-6">
            <label class="block flex items-center font-medium text-navy-blue">
                <input 
                    type="checkbox" 
                    id="allDay" 
                    v-model="form.all_day"
                    class="mr-2 rounded border-gray-300 text-sky-blue focus:ring focus:ring-sky-blue"
                    :disabled="isEdit==false"
                />
                All Day Event
            </label>
        </div>
        <div class="">
            <label for="participants" class="block font-bold text-navy-blue">Participants</label>
        </div>
        <div class="mt-2">
            <div
                v-for="participant in participants"
                :key="participant.id"
                class="inline-flex items-center pl-1 pr-2 py-1 my-1 mr-2 text-sm bg-crystal-blue text-navy-blue rounded-full"
            >
                <img :src="'/' + participant.profile_image" alt="Profile" class="w-6 h-6 object-cover rounded-full border mr-1 border-color-white" />
                {{ participant.name }} ({{ participant.email }})
                
                <button
                    type="button"
                    @click="removeParticipant(participant)"
                    class="ml-2 text-white hover:text-gray-800"
                    v-if="isEdit"
                >
                    &times;
                </button>

            </div>
        </div>
        <div v-if="isEdit" class="my-4">
            <button @click="isAddParticipantModal = true" class="bg-sky-blue text-sm text-linen rounded-full py-1 px-4 hover:bg-crystal-blue hover:text-navy-blue hover:shadow-lg">+ Add Participant</button>
        </div>
        <div v-if="isEdit" class="flex pt-6 justify-end">
            <button type="button" class="mr-2 bg-gray-300 text-navy-blue py-2 px-4 rounded" @click="cancelUpdate">Cancel</button>
            <button type="submit" class="bg-sky-blue text-linen rounded-full py-2 px-4 hover:bg-crystal-blue hover:text-navy-blue hover:shadow-lg" @click="updateEvent">Update</button>
        </div>

        <Modal :show="isAddParticipantModal" @close="isAddParticipantModal = false">
            <AddParticipants @close="isAddParticipantModal = false" @newParticipants="addNewParticipants" :participants="event.participants" :projects="projects"/>
        </Modal>

    </div>
</template>