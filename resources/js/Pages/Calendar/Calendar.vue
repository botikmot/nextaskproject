<script setup>
import { ref, onMounted, reactive} from 'vue';
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head } from '@inertiajs/vue3';

import FullCalendar from '@fullcalendar/vue3';
import dayGridPlugin from '@fullcalendar/daygrid';
import timeGridPlugin from '@fullcalendar/timegrid';
import interactionPlugin from '@fullcalendar/interaction';


const handleEventClick = (info) => {
    console.log('info event', info)
  alert(`Event clicked: ${info.event.end}`);
};

const handleDateClick = (info) => {
    console.log('info', info)
  alert(`Date clicked: ${info.dateStr}`);
};

// Calendar options using reactive for reactivity
const calendarOptions = reactive({
    plugins: [dayGridPlugin, timeGridPlugin, interactionPlugin],
    initialView: 'dayGridMonth',
    dateClick: handleDateClick,
    eventClick: handleEventClick,
    events: [
        { title: 'event 1', start: '2024-12-19', end: '2024-12-21', allDay: true },
        { title: 'event 2', date: '2024-12-25', allDay: true }
    ],
    headerToolbar: 
        {
            left: 'prev,next today',
            center: 'title',
            right: 'dayGridMonth,timeGridWeek,timeGridDay'
        },
    editable: true,
    selectable: true,
    contentHeight: 500,
    backgroundColor: 'white',
});


</script>

<template>
    <Head title="Calendar" />

    <AuthenticatedLayout pageTitle="Calendar">
        <div class="flex bg-linen h-screen w-full space-x-4">
            <!-- Upcoming Events Sidebar -->
            <aside class="w-1/4 bg-gray-100 p-4 rounded-lg shadow">
                <h2 class="text-lg font-semibold mb-4">Upcoming Events</h2>

                <!-- Sample Upcoming Events List -->
                <ul class="space-y-2">
                    <li class="flex justify-between bg-white p-2 rounded-lg shadow">
                        <div>
                            <h3 class="font-semibold">Team Meeting</h3>
                            <p class="text-sm text-gray-500">Tomorrow, 10:00 AM</p>
                        </div>
                        <span class="text-xs text-blue-500">Edit</span>
                    </li>
                    <li class="flex justify-between bg-white p-2 rounded-lg shadow">
                        <div>
                            <h3 class="font-semibold">Project Deadline</h3>
                            <p class="text-sm text-gray-500">November 10, 5:00 PM</p>
                        </div>
                        <span class="text-xs text-blue-500">Edit</span>
                    </li>
                    <li class="flex justify-between bg-white p-2 rounded-lg shadow">
                        <div>
                            <h3 class="font-semibold">Lunch with Client</h3>
                            <p class="text-sm text-gray-500">November 15, 12:30 PM</p>
                        </div>
                        <span class="text-xs text-blue-500">Edit</span>
                    </li>
                </ul>
            </aside>

            <!-- Calendar View -->
            <section class="flex-1 bg-white p-4 rounded-lg shadow">
                <h2 class="text-lg font-semibold mb-4">Calendar</h2>

                <!-- Calendar Component -->
                <FullCalendar ref="fullCalendar" :options="calendarOptions" />
                <!-- <div class="calendar">
                    <div class="grid grid-cols-7 gap-2">
                        <div v-for="day in days" :key="day" class="text-center font-bold">{{ day }}</div>
                        <div v-for="day in calendarDays" :key="day.date" class="border p-2 text-center">
                            <span>{{ day.getDate() }}</span>
                        </div>
                    </div>
                </div> -->

                <!-- New Event Form -->
                <!-- <div class="mt-6">
                    <h3 class="text-lg font-semibold">Create New Event</h3>
                    <form @submit.prevent="createEvent" class="flex flex-col space-y-2">
                        <input
                            type="text"
                            v-model="newEvent.title"
                            placeholder="Event Title"
                            class="border p-2 rounded-md"
                            required
                        />
                        <input
                            type="datetime-local"
                            v-model="newEvent.date"
                            class="border p-2 rounded-md"
                            required
                        />
                        <button class="bg-blue-500 text-white py-2 px-4 rounded shadow hover:bg-blue-600">Add Event</button>
                    </form>
                </div> -->
            </section>
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
</style>
