<script setup>
import { usePage } from '@inertiajs/vue3';
const user = usePage().props.auth.user
user.status = 'Working on new features for NexTask'
</script>

<template>
    <div>
        <img class="object-cover w-24 h-24 rounded-full mx-auto" :src="'/' + user.profile_image" alt="Profile" />
        <h2 class="text-xl text-navy-blue font-semibold mt-4">{{ user.name }}</h2>
        <p class="text-gray mt-2">{{ user.status }}</p>
        <button @click="updateStatus" class="bg-sky-blue text-color-white py-2 px-4 mt-4 rounded hover:bg-blue-600 transition">Update Status</button>
    </div>
</template>