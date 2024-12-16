<script setup>
import { ref, computed, reactive} from 'vue';
import moment from 'moment';

const props = defineProps({
    events: Array,
});


const upcomingEvents = computed(() => {
    const now = new Date();

    // Filter events to include only those starting in the future
    const filteredEvents = props.events.filter(event => new Date(event.start) > now);

    // Sort events by their start date in ascending order (nearest first)
    const sortedEvents = filteredEvents.sort((a, b) => new Date(a.start) - new Date(b.start));

    // Return only the first 2 events
    return sortedEvents.slice(0, 2);
});

const formatDate = (date) => {
    return moment(date).format('lll');
}

</script>

<template>
    <div class="bg-color-white p-6 rounded-lg shadow-md">
        <h2 class="text-lg font-bold border-b border-dark-gray text-navy-blue pb-2">Upcoming Events</h2>
        
        <ul class="mt-2 mb-6 list-none">
            <template v-if="upcomingEvents.length > 0">
                <li v-for="event in upcomingEvents" :key="event.id" class="py-1 flex border-b border-dark-gray">
                    <div>
                        <p class="font-semibold text-sky-blue">{{ event.title }}</p>
                        <p class="text-sm text-navy-blue">{{ formatDate(event.start) }}</p>
                    </div>
                    <div class="flex items-center pl-3">
                        <div v-for="(member, index) in event.participants.slice(0, 5)" :key="member.id" class="relative -mr-3">
                            <img :src="'/' + member.profile_image" alt="Profile" class="w-8 h-8 rounded-full object-cover border-2 border-color-white" />
                        </div>
                        
                        <div v-if="event.participants.length > 5" class="flex items-center text-navy-blue ml-6">
                            <span class="text-lg font-bold">+{{ event.participants.length - 5 }}</span>
                        </div>
                    </div>
                </li>
            </template>
            <template v-else>
                <li class="py-4 text-gray-500 text-center">
                    No upcoming events. Add a new event to get started!
                </li>
            </template>
        </ul>

        <a
            class="mt-4 cursor-pointer px-6 py-3 bg-sky-blue text-color-white rounded-full hover:font-bold hover:bg-crystal-blue hover:text-navy-blue hover:shadow-lg"
            :href="route('calendar')"
        >
           View Calendar
        </a>
    </div>
</template>
