<script setup>
import { ref, computed, reactive} from 'vue';
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head } from '@inertiajs/vue3';
import CreateEventModal from './CreateEventModal.vue';
import EventDetails from './EventDetails.vue';
import Modal from '@/Components/Modal.vue';
import moment from 'moment';
import FullCalendar from '@fullcalendar/vue3';
import dayGridPlugin from '@fullcalendar/daygrid';
import timeGridPlugin from '@fullcalendar/timegrid';
import interactionPlugin from '@fullcalendar/interaction';

let isEventModalOpen = ref(false);
let isEventDetailsModalOpen = ref(false);
let event = ref({});

const props = defineProps({
  projects: Object,
  events: Array,
});

const upcomingEvents = computed(() => {
    const now = new Date();

    // Filter events to include only those starting in the future
    const filteredEvents = props.events.filter(event => new Date(event.start) > now);

    // Sort events by their start date in ascending order (nearest first)
    return filteredEvents.sort((a, b) => new Date(a.start) - new Date(b.start));
});

const handleEventClick = (info) => {
    console.log('info event', info.event)
};

const handleDateClick = (info) => {
    console.log('info', info)
  //alert(`Date clicked: ${info.dateStr}`);
};

// Calendar options using reactive for reactivity
const calendarOptions = reactive({
    plugins: [dayGridPlugin, timeGridPlugin, interactionPlugin],
    initialView: 'dayGridMonth',
    dateClick: handleDateClick,
    eventClick: handleEventClick,
    events: props.events,
    headerToolbar: 
        {
            left: 'prev,next today',
            center: 'title',
            right: window.innerWidth > 768 ? 'dayGridMonth,timeGridWeek,timeGridDay' : 'timeGridDay' //'dayGridMonth,timeGridWeek,timeGridDay'
        },
    editable: true,
    selectable: true,
    contentHeight: 500,
    backgroundColor: 'white',
});

const formatDate = (date) => {
    return moment(date).format('lll');
}

const viewEventDetails = (details) => {
    console.log('event', details)
    event.value = details
    isEventDetailsModalOpen.value = true
}

</script>

<template>
    <Head title="Calendar" />

    <AuthenticatedLayout pageTitle="Calendar">
        <div class="block lg:flex bg-linen h-full w-full space-x-4">
            <!-- Upcoming Events Sidebar -->
            <aside class="w-full lg:w-1/4 bg-gray-100 p-4 rounded-lg shadow">
                <div class="flex items-center justify-between">
                    <h2 class="text-lg pl-3 text-navy-blue font-semibold mb-4">{{ events.length > 0 ? 'Upcoming Events' : 'Events' }}</h2>
                    <div>
                        <button @click="isEventModalOpen = true" class="block w-full mb-4 px-6 py-2 text-linen hover:font-bold bg-sky-blue rounded-full hover:bg-crystal-blue hover:text-navy-blue hover:shadow-lg">
                            Create Event
                        </button>
                    </div>
                </div>
                

                <!-- Upcoming Events List -->
                <ul class="space-y-2">
                    <!-- Loop through events and display only upcoming ones -->
                    <template v-if="events.length > 0">
                        <li
                            v-for="event in upcomingEvents"
                            :key="event.id"
                            class="flex items-center justify-between bg-color-white cursor-pointer p-3 rounded-lg shadow"
                            @click="viewEventDetails(event)"
                        >
                            <div>
                                <h3 class="font-semibold text-sky-blue">{{ event.title }}</h3>
                                <p class="text-sm text-navy-blue">{{ formatDate(event.start) }}</p>
                            </div>
                            <span class="text-gray px-3 text-center py-1 border rounded-full hidden 2xl:flex">View details</span>
                        </li>
                    </template>

                    <!-- Placeholder if no upcoming events -->
                    <template v-else>
                        <li class="flex justify-between bg-color-white p-2 rounded-lg shadow">
                            <div class="text-center text-gray-500 w-full">
                                No upcoming events
                            </div>
                        </li>
                    </template>
                </ul>
            </aside>

            <!-- Calendar View -->
            <section class="flex-1 p-4 rounded-lg">
                <!-- Calendar Component -->
                <FullCalendar ref="fullCalendar" :options="calendarOptions" />
            </section>

            <Modal :show="isEventModalOpen" @close="isEventModalOpen = false">
                <CreateEventModal @close="isEventModalOpen = false" :projects="projects"/>
            </Modal>

            <Modal :show="isEventDetailsModalOpen" @close="isEventDetailsModalOpen = false">
                <EventDetails @close="isEventDetailsModalOpen = false" :event="event" :projects="projects"/>
            </Modal>

        </div>
    </AuthenticatedLayout>
</template>

<style>
.calendar-page {
  padding: 20px;
  background-color: #f9fafb;
}
.fc-scrollgrid-sync-table, .fc-col-header, .fc-daygrid-body {
    width: 100% !important;
}

.fc-view {
    background-color: white !important;
}

.fc-toolbar-title {
    color: #40a2e3 !important;
    font-weight: 600;
}
.fc-col-header-cell-cushion {
    color: #16325B !important;
}

@media screen and (max-width: 768px) {
    .fc-toolbar {
        flex-wrap: wrap;
    }
    .fc-toolbar-title {
        font-size: 1rem; /* Smaller title */
    }
    .fc-header-toolbar {
        font-size: 0.8rem; /* Smaller buttons */
    }
    .fc-view {
        overflow-x: auto; /* Ensure calendar doesn't overflow on smaller screens */
    }
    .fc-daygrid-day-number {
        font-size: 0.8rem;
    }
    .fc {
        font-size: 0.8rem; /* Adjust overall font size */
    }
}
</style>
