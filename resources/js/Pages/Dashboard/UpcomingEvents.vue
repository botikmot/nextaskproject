<script setup>
import { ref, computed, reactive, onMounted} from 'vue';
import moment from 'moment';
import { usePage, useForm } from '@inertiajs/vue3'

const page = usePage();

const getAllEvents = page.props.getAllEvents || [];

const props = defineProps({
    page: Boolean,
});

const upcomingEvents = computed(() => {
    const now = new Date();

    // Filter events to include only those starting in the future
    const filteredEvents = getAllEvents?.filter(event => new Date(event.start) > now);

    // Sort events by their start date in ascending order (nearest first)
    const sortedEvents = filteredEvents.sort((a, b) => new Date(a.start) - new Date(b.start));

    // Return only the first 2 events
    return sortedEvents.slice(0, 2);
});

const todaysEvents = computed(() => {
    const now = new Date();

    // Get the start and end of today's date
    const startOfToday = new Date(now.getFullYear(), now.getMonth(), now.getDate());
    const endOfToday = new Date(now.getFullYear(), now.getMonth(), now.getDate() + 1);

    // Filter events to include only those starting within today's range
    return getAllEvents?.filter(event => {
        const eventStart = new Date(event.start);
        return eventStart >= startOfToday && eventStart < endOfToday;
    });
});

const formatDate = (date) => {
    return moment(date).format('lll');
}

</script>

<template>
    <div class="">

        <div v-if="todaysEvents.length > 0">
            <h2 class="text-lg font-bold border-b border-dark-gray text-navy-blue pb-2">{{ todaysEvents.length > 1 ? "Today's Events" : "Today's Event" }}</h2>
            <ul class="mt-2 mb-6 list-none">
                <li v-for="event in todaysEvents" :key="event.id" class="py-1 block xl:flex border-b border-dark-gray">
                    <div>
                        <p class="font-semibold text-red-warning">{{ event.title }}</p>
                        <p class="text-sm text-navy-blue">{{ formatDate(event.start) }}</p>
                    </div>
                    <div class="flex items-center pl-3">
                        <div v-for="(member, index) in event.participants.slice(0, 5)" :key="member.id" class="relative -mr-3">
                            <img :src="'/' + member.profile_image" alt="Profile" class="w-8 h-8 rounded-full object-cover border-2 border-color-white" />
                        </div>
                        
                        <div v-if="event.participants.length > 5" class="flex items-center text-navy-blue ml-3">
                            <span class="text-lg font-bold">+{{ event.participants.length - 5 }}</span>
                        </div>
                    </div>
                </li>
            </ul>
        </div>

        <div v-else>
            <h2 class="text-lg font-bold border-b border-dark-gray text-navy-blue pb-2">Upcoming Events</h2>
            <ul class="mt-2 mb-6 list-none">
                <template v-if="upcomingEvents.length > 0">
                    <li v-for="event in upcomingEvents" :key="event.id" class="py-1 block 2xl:flex border-b border-dark-gray">
                        <div>
                            <p class="font-semibold text-sky-blue">{{ event.title }}</p>
                            <p class="text-sm text-navy-blue">{{ formatDate(event.start) }}</p>
                        </div>
                        <div class="flex items-center pl-0 2xl:pl-3">
                            <div v-for="(member, index) in event.participants.slice(0, 5)" :key="member.id" class="relative -mr-3">
                                <img :src="'/' + member.profile_image" alt="Profile" class="w-8 h-8 rounded-full object-cover border-2 border-color-white" />
                            </div>
                            
                            <div v-if="event.participants.length > 5" class="flex items-center text-navy-blue ml-3">
                                <span class="text-lg font-bold">+{{ event.participants.length - 5 }}</span>
                            </div>
                        </div>
                    </li>
                </template>
            </ul>
        </div>

        <template v-if="upcomingEvents.length === 0 && todaysEvents.length === 0">
            <div class="py-4 mt-2 mb-6 text-gray-500 text-center">
                No upcoming events. Add a new event to get started!
            </div>
        </template>

        <a
            v-if="route().current() !== 'social'"
            class="absolute bottom-5 right-5 cursor-pointer px-6 py-3 bg-gradient-to-r from-navy-blue to-sky-blue text-color-white rounded-full hover:from-sky-blue hover:to-navy-blue hover:shadow-lg"
            :href="route('calendar')"
        >
           View Calendar
        </a>
    </div>
</template>
