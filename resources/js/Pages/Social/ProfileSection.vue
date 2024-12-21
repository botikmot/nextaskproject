<script setup>
import { usePage } from '@inertiajs/vue3';
import UserImage from '@/Components/UserImage.vue';
const user = usePage().props.auth.user
user.status = 'Working on new features for NexTask'
</script>

<template>
    <div>
        <UserImage class="h-24 w-24 rounded-full object-cover mx-auto" :user="user" />
        <h2 class="text-xl text-navy-blue font-semibold mt-4">{{ user.name }}</h2>
        <p class="text-gray mt-2">{{ user.status }}</p>
        <button @click="updateStatus" class="bg-sky-blue text-color-white py-2 px-4 mt-4 rounded hover:bg-blue-600 transition">Update Status</button>
    </div>
</template>