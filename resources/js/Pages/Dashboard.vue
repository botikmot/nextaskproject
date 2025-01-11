<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head, usePage } from '@inertiajs/vue3';
import TaskOverview from './Dashboard/TaskOverview.vue';
import ProjectsWidget from './Dashboard/ProjectsWidget.vue';
import UpcomingEvents from './Dashboard/UpcomingEvents.vue';
import SocialHighlights from './Dashboard/SocialHighlights.vue';
import RecentChats from './Dashboard/RecentChats.vue';
import Notifications from './Dashboard/Notifications.vue';
import { ref, computed, reactive, onMounted} from 'vue';

const props = defineProps({
    userName: String,
    userRole: String,
    tasks: Object,
    projects: Array,
    events: Array,
});
const page = usePage();

const level = page.props.userLevel
const badges = page.props.badges
console.log('--user level--', level)
const currentDate = new Date().toLocaleDateString()

const isNewUser = page.props.auth.user.is_new

</script>

<template>
  <Head title="Dashboard" />

  <AuthenticatedLayout pageTitle="Dashboard" :notif="notif" v-slot="{ notif }">
    <div class="dashboard w-full bg-light-gray p-6">
      <!-- Welcome Banner -->
      <div class="bg-gradient-to-r from-sky-blue to-navy-blue text-color-white p-8 rounded-lg shadow-lg flex justify-between items-center">
        <div>
          <h1 class="text-3xl font-bold">
            {{ isNewUser ? `Welcome to Nextask, ${$page.props.auth.user.name}!` : `Welcome back, ${$page.props.auth.user.name}!` }}
          </h1>
          <p class="mt-2 text-lg">
            {{ isNewUser ? "We're excited to have you here. Let's get started on your journey!" : "Stay productive and achieve your goals today." }}
          </p>
          <p class="text-sm text-light-crystal">{{ currentDate }}</p>
        </div>
        <div class="block sm:flex items-center space-x-4">
          <div class="text-right">
            <div class="text-4xl font-bold">{{ level.name }}</div>
            <div class="text-sm text-honey-gold">Your Current Level</div>
            <div class="flex space-x-2 mt-2">
              <img
                v-for="badge in badges"
                :key="badge.id"
                :src="badge.icon"
                :alt="badge.name"
                class="h-10 w-10"
                :title="badge.name"
              />
            </div>
          </div>
          <img :src="level.icon_svg" alt="Level Icon" class="h-20 w-20 mt-2 sm:mt-0" />
        </div>
      </div>

      <!-- Main Dashboard Content -->
      <div class="grid grid-cols-1 lg:grid-cols-3 gap-6 mt-8">
        <!-- Task Overview -->
        <div class="bg-color-white relative p-6 relative rounded-lg pb-14 shadow-md border border-dark-gray">
          <TaskOverview :tasks="tasks" />
        </div>

        <!-- Project Highlights -->
        <div class="bg-color-white p-6 rounded-lg relative shadow-md pb-14 border border-dark-gray">
          <ProjectsWidget :projects="projects" />
        </div>

        <!-- Calendar Preview -->
        <div class="bg-color-white p-6 rounded-lg relative pb-14 shadow-md border border-dark-gray">
          <UpcomingEvents :events="events" :page="false"/>
        </div>
      </div>

      <!-- Additional Widgets -->
      <div class="grid grid-cols-1 lg:grid-cols-3 gap-6 mt-8">
        <!-- Social Highlights -->
        <div class="bg-color-white p-6 rounded-lg pb-14 relative shadow-md border border-dark-gray">
          <SocialHighlights />
        </div>

        <!-- Notifications -->
        <div class="bg-color-white p-6 rounded-lg shadow-md border border-dark-gray">
          <Notifications :notif="notif" />
        </div>

        <!-- Recent Chats -->
        <div class="bg-color-white p-6 pb-14 rounded-lg shadow-md border relative border-dark-gray">
          <RecentChats />
        </div>
      </div>
    </div>
  </AuthenticatedLayout>
</template>