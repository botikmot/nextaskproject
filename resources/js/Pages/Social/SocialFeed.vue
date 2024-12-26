<script setup>
import { ref } from 'vue';
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head } from '@inertiajs/vue3';
import ProfileSection from './ProfileSection.vue';
import FeedSection from './FeedSection.vue';
import SidebarSection from './SidebarSection.vue';
import UpcomingEvents from '../Dashboard/UpcomingEvents.vue';

const props = defineProps({
    posts: Object,
    suggestedFriends: Array,
    events: Array,
});

console.log('events', props.events)
</script>

<template>
    <Head title="Social" />

    <AuthenticatedLayout pageTitle="Social" :userChangedStatus="userChangedStatus" v-slot="{ userChangedStatus }">
        <div class="social-page flex flex-col lg:flex-row gap-6 p-6 w-full bg-linen">
            <!-- Profile Section (Fixed) -->
            <div class="profile-section lg:w-1/4 hidden lg:block lg:sticky lg:top-6 h-fit">
                <ProfileSection class="text-center p-6 bg-crystal-blue shadow rounded-lg" :events="events"/>
                <UpcomingEvents v-if="events.length > 0" class="mt-6" :events="events"/>
            </div>

            <!-- Feed Section (Scrollable) -->
            <!-- <div class="feed-section w-full lg:w-2/4 overflow-y-auto h-screen pr-2"> -->
                <FeedSection :posts="posts" />
            <!-- </div> -->

            <!-- Sidebar Section (Fixed) -->
            <div class="sidebar bg-gray-100 p-6 rounded-lg lg:w-1/4 hidden lg:block lg:sticky lg:top-6 h-fit">
                <SidebarSection :userChangedStatus="userChangedStatus"/>
            </div>
        </div>
    </AuthenticatedLayout>
</template>

<style scoped>
/* Scrollable feed section styling */
.feed-section {
    /* Full height with scrolling enabled */
    height: calc(100vh - 124px); /* Adjust for padding or headers */
    overflow-y: auto;
}

/* Hide scrollbars (optional, for better design) */
.feed-section::-webkit-scrollbar {
    width: 8px;
}

.feed-section::-webkit-scrollbar-thumb {
    background-color: #40a2e3; /* Customize scrollbar color */
    border-radius: 4px;
}

.feed-section::-webkit-scrollbar-thumb:hover {
    background-color: #a1a1aa;
}
</style>
