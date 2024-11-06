<script setup>
import { ref, defineEmits } from 'vue';
import axios from 'axios';
import { usePage, useForm } from '@inertiajs/vue3'
const emit = defineEmits();

const props = defineProps({
  project_id: Number,
});

let results = ref([]);
const search = ref('');
const selectedUsers = ref([]);

const form = useForm({
    members: [],
});

const cancel = () => {
    emit('close');
}

const fetchResults = async () => {
    if (search.value.trim().length < 3) {
        results.value = [];
        return;
    }
    
    try {
        const response = await axios.get('/search-members', { params: { query: search.value, project_id: props.project_id} });
        results.value = response.data || [];

    } catch (error) {
        console.error("Error fetching search results:", error);
    }
}

const addUser = (user) => {
    if (!form.members.some(members => members.id === user.id)) {
        form.members.push(user);
    }
    // Clear search and results
    search.value = '';
    results.value = [];
}

const removeUser = (user) => {
    form.members = form.members.filter(selectedUser => selectedUser.id !== user.id);
}

const submitSelectedUsers = async () => {
    const members = { members: form.members.map(user => user.id) };
    console.log('payload', members)
    
    form.post(`/project-members/${props.project_id}`, {
        data: members,
        preserveScroll: true,
        onSuccess: () => {
            form.reset()
            console.log('Successfully saved.')
            emit('close')
        },
        onError: (error) => {
            console.error('Error creating post', error)
        }
    })

}

</script>

<template>
    <div class="bg-linen rounded-lg shadow-lg p-6 w-full">
        <h3 class="text-lg text-navy-blue font-semibold mb-4">Add Member to Project</h3>
        
        <div class="mb-4 relative">
            <label for="projectName" class="block text-sm font-medium text-navy-blue">Search Name or Email</label>
            <input
                type="text"
                id="searchMember"
                v-model="search"
                @input="fetchResults"
                class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:ring focus:ring-sky-blue"
                required
            />
            <!-- Dropdown for search results -->
            <ul
                v-if="results.length > 0"
                class="absolute z-10 mt-1 w-full bg-white border border-gray-300 rounded-md shadow-lg max-h-60 overflow-y-auto"
            >
                <li
                    v-for="result in results"
                    :key="result.id"
                    @click="addUser(result)"
                    class="cursor-pointer bg-light-gray px-4 py-2 hover:bg-crystal-blue"
                >
                    {{ result.name }} ({{ result.email }})
                </li>
            </ul>

            <!-- Selected Users List -->
            <div v-if="form.members.length > 0" class="mt-4 space-y-2">
                <div v-for="user in form.members" :key="user.id" class="flex items-center space-x-2">
                    <span>{{ user.name }} ({{ user.email }})</span>
                    <button
                        @click="removeUser(user)"
                        class="text-red-500 hover:text-red-700"
                    >
                        Remove
                    </button>
                </div>
            </div>
                
        </div>
        <div class="flex justify-end">
            <button type="button" class="mr-2 bg-gray-300 text-navy-blue py-2 px-4 rounded" @click="cancel">Cancel</button>
            <button type="submit" class="bg-sky-blue text-linen rounded-full py-2 px-4 hover:bg-crystal-blue hover:text-navy-blue hover:shadow-lg" @click="submitSelectedUsers">Save</button>
        </div>
        
    </div>
</template>