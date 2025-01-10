<script setup>
  import { ref, computed, defineEmits, watch } from 'vue';
  import { usePage, useForm } from '@inertiajs/vue3'
  import Dropdown from '@/Components/Dropdown.vue';
  import SubTasks from './SubTasks.vue';
  import Comments from './Comments.vue';
  import TaskDescription from './TaskDescription.vue';
  import TaskTimer from './TaskTimer.vue';
  import moment from 'moment';
  import Swal from 'sweetalert2';

  const currentTab = ref('Subtasks');
  const tabs = ['Subtasks', 'Comments', 'Task Description', 'Task History'];

const props = defineProps({
    task: Object,
    members: Object,
    tasks: Object,
    labels: Object,
    project: Object,
});

const emit = defineEmits();

const challenges = usePage().props.participantChallenges
const authUser = usePage().props.auth.user
const totalTrackedSeconds = ref(0);

const form = useForm({
    title: props.task.title,
    due_date: props.task.due_date,
    priority: props.task.priority,
    labels:  props.task.labels.map(label => label.id), //props.task.labels,
    points: props.task.points,
    challenge_ids: props.task.challenges.map(challenge => challenge.id),
    assigned_members: props.task.users.map(user => user.id), //[],
    removed_users: [],
    comment: '',
    comment_id: null,
    attachments: null,
    subtask: '',
    name: '', // subtasks name
    is_completed: null,
    dependency: null,
    depends_on_task_id: null,
    description: props.task.description,
    status: props.task.status.id,
});


  let timerInterval = null;
  const timeSpent = ref(0); // Time spent in seconds
  const isTimerRunning = ref(false);
  
  // Method to format time (HH:mm:ss)
  const formattedTime = computed(() => {
    const hours = String(Math.floor(timeSpent.value / 3600)).padStart(2, '0');
    const minutes = String(Math.floor((timeSpent.value % 3600) / 60)).padStart(2, '0');
    const seconds = String(timeSpent.value % 60).padStart(2, '0');
    return `${hours}:${minutes}:${seconds}`;
  });
  
  // Start or stop the timer
  const toggleTimer = () => {
    if (isTimerRunning.value) {
      clearInterval(timerInterval);
    } else {
      timerInterval = setInterval(() => {
        timeSpent.value++;
      }, 1000);
    }
    isTimerRunning.value = !isTimerRunning.value;
  };
  
    const isEditing = ref(false);

    const editTask = () => {
    isEditing.value = true;
    };

    const cancelEdit = () => {
    isEditing.value = false;
    };

// Computed property for formatted duration
const formattedDuration = computed(() => {
  const hours = Math.floor(totalTrackedSeconds.value / 3600);
  const minutes = Math.floor((totalTrackedSeconds.value % 3600) / 60);
  const seconds = totalTrackedSeconds.value % 60;
  return `${hours}h ${minutes}m ${seconds}s`;
});

// Update the tracked duration
const updateTaskDuration = (newTrackedSeconds) => {
  totalTrackedSeconds.value = newTrackedSeconds;
};

const updateTask = () => {

    form.put(`/tasks-update/${props.task.id}`, {
        data: form,
        preserveScroll: true,
        onSuccess: () => {
            isEditing.value = false;
            Swal.fire({
                text: "Task successfully updated!",
                position: 'bottom-end',
                backdrop: false,
                timer: 2000,
                showConfirmButton: false,
                toast:true,
                icon: 'success',
            });
        },
        onError: (error) => {
            console.error('Error updating tasks', error)           
        }
    })
}
  
  
  const closeModal = () => {
    emit('close')
  };

  const addDependency = () => {
    form.post(`/tasks/${props.task.id}/set-dependency`, {
        data: form,
        preserveScroll: true,
        onSuccess: (response) => {
            console.log('res', response.props.flash.success)           
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
            console.error('Error setting dependency', error)           
        }
    })
}

const removeDependency = (dependency, id) => {
    form.depends_on_task_id = dependency.depends_on_task_id
    form.post(`/tasks/${id}/remove-dependency`, {
        data: form,
        preserveScroll: true,
        onSuccess: () => {
            form.reset()
        },
        onError: (error) => {
            console.error('Error removing dependency', error)           
        }
    })

}

const removeUser = (id) => {
    form.removed_users.push(id)
    
    if(props.task.user_id !== authUser.id){
        Swal.fire({
            text: 'You cannot delete this user',
            position: 'bottom-end',
            backdrop: false,
            timer: 2000,
            showConfirmButton: false,
            toast:true,
            icon:'error',
        });
        return
    }

    form.post(`/remove-member/${props.task.id}`, {
        data: form,
        preserveScroll: true,
        onSuccess: () => {
            form.reset()
        },
        onError: (error) => {
            console.error('Error deleting users', error)
        }
    })
}

const isUserAssigned = (userId) => {
      return props.task.users.some(user => user.id === userId);
}

const assignMember = () => {
    form.post(`/assign-member/${props.task.id}`, {
        data: form,
        preserveScroll: true,
        onSuccess: (response) => {
            if(response.props.flash.success){
                form.reset()
            }else{
                Swal.fire({
                    text: response.props.flash.message,
                    position: 'bottom-end',
                    backdrop: false,
                    timer: 2000,
                    showConfirmButton: false,
                    toast:true,
                    icon: 'error',
                });
            }
        },
        onError: (error) => {
            console.error('Error assigning users', error)
        }
    })

}

const confirmDelete = (id) => {
    Swal.fire({
        title: 'Are you sure?',
        text: "Once deleted, this task cannot be recovered.",
        showCancelButton: true,
        confirmButtonColor: '#EF4444', // Red for delete
        cancelButtonColor: '#38A169', // Green for cancel
        confirmButtonText: 'Yes, delete it!'
    }).then((result) => {
        if (result.isConfirmed) {
            form.delete(`/task-remove/${id}`, {
                data: form,
                preserveScroll: true,
                onSuccess: (response) => {
                    console.log(response.props.flash.success)
                    if(response.props.flash.success){
                        form.reset()
                        Swal.fire({
                            icon: 'success',
                            title: 'Deleted!',
                            text: 'The task has been successfully deleted.',
                        });
                    }else{
                        Swal.fire({
                            icon: 'error',
                            //title: 'Deleted!',
                            text: response.props.flash.message,
                        });
                    }
                    
                },
                onError: (error) => {
                    console.error('Error deleting task', error)
                }
            })
        }
    });
}

const updateDesc = () => {
    form.description = newDesc.value
    form.put(`/task-description/${props.task.id}`, {
        data: form,
        preserveScroll: true,
        onSuccess: () => {
            form.reset()
            Swal.fire({
                text: "Description successfully saved!",
                position: 'bottom-end',
                backdrop: false,
                timer: 2000,
                showConfirmButton: false,
                toast:true,
                icon: 'success',
            });

        },
        onError: (error) => {
            console.error('Error removing dependency', error)           
        }
    })

}

const newDesc = ref(props.task.description);


// Watch for changes in task description (prop)
watch(
    () => props.task.description,
    (newValue) => {
        newDesc.value = newValue; // Keep the reactive description updated
    }
);

const formatDate = (date) => {
    return moment(date).format('ll')
}
</script>

<template>
    <div class="">
      <div class="bg-light-gray rounded-lg shadow-lg p-2">
        <!-- Modal Header -->
        <div class="flex justify-between items-center p-4 border-b border-dark-gray">
          <h2 class="text-navy-blue font-bold text-lg capitalize">{{ task.title }}</h2>
          <div v-if="task.user_id == $page.props.auth.user.id || task.users.some(user => user.id === $page.props.auth.user.id)">
            <button title="Edit" class="text-gray hover:text-sky-blue mr-2" @click="editTask"><i class="fa-solid fa-pen-to-square"></i></button>
            <button v-if="task.user_id == $page.props.auth.user.id" title="Delete" class="text-gray hover:text-red-warning" @click="confirmDelete(task.id)"><i class="fa-solid fa-trash"></i></button>
          </div>
        </div>
  
        <!-- Modal Body -->
        <div class="p-4 space-y-4">
            <div v-if="isEditing">
          <!-- Editable Task Form -->
              <div class="grid grid-cols-2 gap-4">
                <div>
                  <label class="text-sm text-gray">Task Name:</label>
                  <input v-model="form.title" type="text" class="input-field" />
                </div>
                <div>
                  <label class="text-sm text-gray">Status:</label>
                  <select v-model="form.status" class="input-field">
                    <option v-for="status in project.statuses" :key="status.id" :value="status.id">{{ status.name }}</option>
                  </select>
                </div>
                <div>
                  <label class="text-sm text-gray">Due Date:</label>
                  <input v-model="form.due_date" type="date" class="input-field" />
                </div>
                <div>
                  <label class="text-sm text-gray">Priority:</label>
                  <select v-model="form.priority" class="input-field">
                      <option value="low">Low</option>
                      <option value="medium">Medium</option>
                      <option value="high">High</option>
                  </select>
                </div>
                <div>
                  <label class="text-sm text-gray">Labels/Tags:</label>
                  <select
                      v-model="form.labels"
                      multiple
                      class="input-field"
                  >
                      <option class="text-navy-blue" v-for="label in labels" :key="label.id" :value="label.id">{{ label.name }}</option>
                  </select>
                </div>
                <div>
                  <label class="text-sm text-gray">Challenge Related:</label>
                  <select
                      v-model="form.challenge_ids"
                      multiple
                      class="input-field"
                  >
                      <option v-for="challenge in challenges" :key="challenge.id" :value="challenge.id">{{ challenge.name }}</option>
                  </select>
                </div>
                <div>
                  <label class="text-sm text-gray">Task Points:</label>
                  <input v-model="form.points" type="number" class="input-field" />
                </div>
                <div>
                  <div class="flex">
                    <label class="text-sm text-gray">Task Dependency:</label>
                  </div>
                  <div class="pb-3 flex">
                      <select
                          id="setDependencies"
                          v-model="form.dependency"
                          class="block w-full border-gray-300 rounded-md shadow-sm focus:ring focus:ring-sky-blue"
                      >
                          <option
                              class="text-navy-blue text-sm"
                              v-for="task in tasks"
                              :key="task.id"
                              :value="task.id"
                          >{{ task.title }}</option>
                      </select>
                      <button
                          :disabled="!form.dependency"
                          @click="addDependency"
                          class="font-semibold text-xs ml-2 bg-sky-blue text-linen py-1 px-3 rounded-lg inline-block hover:bg-crystal-blue hover:text-navy-blue hover:shadow-lg"
                          >
                          Save Dependency
                      </button>
                  </div>
                </div>
                <div>
                  <label class="text-sm text-gray">Assigned Members:</label>
                  <select
                      id="assignedMembers"
                      v-model="form.assigned_members"
                      multiple
                      class="input-field"
                  >
                      <option
                          class="text-navy-blue text-sm"
                          v-for="member in members"
                          :key="member.id"
                          :value="member.id"
                          :disabled="isUserAssigned(member.user_id)"
                      >{{ member.name }}</option>
                  </select>
                  <div class="flex py-1 justify-end">
                      <button
                          @click="assignMember"
                          class="font-semibold text-xs bg-sky-blue text-linen py-1 px-3 rounded-full inline-block hover:bg-crystal-blue hover:text-navy-blue hover:shadow-lg"
                          >
                          Save Member
                      </button>
                  </div>
                </div>



              </div>

              <div class="flex justify-end space-x-2 mt-4">
                <button @click="updateTask" class="bg-sky-blue text-color-white px-4 py-2 rounded-md">Save</button>
                <button @click="cancelEdit" class="bg-gray text-color-white px-4 py-2 rounded-md">Cancel</button>
              </div>
            </div>


          <div v-else class="grid grid-cols-2 gap-4">
            <div>
              <p class="text-sm text-gray">Status:</p>
              <p class="text-sm text-navy-blue font-semibold">{{ task.status.name }}</p>
            </div>
            <div>
              <p class="text-sm text-gray">Due Date:</p>
              <p class="text-sm text-navy-blue font-semibold">{{ task.due_date ? formatDate(task.due_date) : 'Not set' }}</p>
            </div>
            <div>
              <p class="text-sm text-gray">Priority:</p>
              <p class="text-sm text-navy-blue font-semibold">
                <span class="font-bold capitalize" :class="[
                      task.priority === 'low' ? 'text-[#38A169]' : 
                      task.priority === 'medium' ? 'text-[#D97706]' : 
                      task.priority === 'high' ? 'text-[#EF4444]' : ''
                  ]">{{ task.priority }}</span>
                </p>
            </div>
            <div>
              <p class="text-sm text-gray">Task Points:</p>
              <p class="text-sm text-navy-blue font-semibold">{{ task.points }}</p>
            </div>
            <div>
              <p class="text-sm text-gray">Challenge Related:</p>
              <p class="text-sm text-navy-blue font-semibold">
                <div v-if="task.challenges" v-for="(challenge, index) in task.challenges" :key="index" class="rounded-full">
                                        {{ challenge.name }}
                </div>
              </p>
            </div>
            <div>
              <p class="text-sm text-gray">Task Dependency:</p>
              <p class="text-sm text-navy-blue font-semibold">
                <div class="" v-for="dependency in task.dependencies" :key="dependency.id">
                    <div class="flex items-center">
                        <div class="text-sm rounded-full">{{ dependency.title }}</div>
                        <div v-if="task.user_id == $page.props.auth.user.id" class="text-sm cursor-pointer pl-3 pt-1">
                            <Dropdown align="right" width="48">
                                <template #trigger>
                                    <i class="fa-solid fa-ellipsis"></i>
                                </template>
                                <template #content>
                                    <div
                                        class="hover:bg-crystal-blue px-3 py-2"
                                        @click.stop="removeDependency(dependency.pivot, task.id)"
                                    >
                                        Remove
                                    </div>
                                </template>
                            </Dropdown>
                        </div>

                    </div>
                </div>
              </p>
            </div>
            <div>
              <p class="text-sm text-gray">Task Duration:</p>
              <p class="text-sm text-navy-blue font-semibold">{{ formattedDuration }}</p>
            </div>
            <div>
                <p class="text-sm text-gray">Time Tracking:</p>
                <TaskTimer :task="task" @update-task-duration="updateTaskDuration"/>
            </div>
          </div>
  
          <div v-if="!isEditing">
            <p class="text-sm text-gray mb-1">Labels/Tags:</p>
            <div class="flex flex-wrap gap-2">
              <span 
                v-if="task.labels"
                v-for="(tag, index) in task.labels"
                :key="index"
                :style="{ backgroundColor: tag.color }"
                class="text-sm px-2 py-1 rounded-full"
                >
                  {{ tag.name }}
              </span>
            </div>
          </div>
  
          <div v-if="!isEditing">
            <p class="text-sm text-gray mb-1">Assigned Members:</p>
            <div class="flex flex-wrap gap-2">
              <div v-for="(user, index) in task.users" :key="index" class="flex items-center pl-2">
                  <Dropdown align="right" width="40">
                      <template #trigger>
                          <div class="flex cursor-pointer">
                              <img :src="'/' + user.profile_image" alt="User Avatar" class="w-8 h-8 rounded-full object-cover border-dark-gray" />
                              <span class="text-sm flex items-center text-navy-blue pl-1">{{ user.name }}</span>
                          </div>
                      </template>
                      <template #content>
                          <div
                              class="hover:bg-crystal-blue text-sm px-3 py-2 cursor-pointer"
                              @click="removeUser(user.id)"
                          >
                              Remove
                          </div>
                      </template>
                  </Dropdown>

                </div>
            </div>
          </div>
        </div>
  
        <!-- Tabs Section -->
        <div class="border-t border-dark-gray">
          <div class="flex space-x-4 px-4 py-2">
            <button 
              v-for="tab in tabs" 
              :key="tab" 
              :class="['text-sm', 'font-semibold', currentTab === tab ? 'text-navy-blue' : 'text-gray']"
              @click="currentTab = tab"
            >
              {{ tab }}
            </button>
          </div>
          <div class="p-4">
            <div v-if="currentTab === 'Subtasks'">
              <!-- Subtasks content here -->
              <SubTasks :task="task"/>
            </div>
            <div v-if="currentTab === 'Comments'">
              <!-- Comments content here -->
              <Comments :task="task"/>
            </div>
            <div v-if="currentTab === 'Task Description'">
              <!-- Task Description content here -->
              <TaskDescription :task="{ description: newDesc }" @update:desc="(value) => (newDesc = value)"/>
              <div class="flex pb-2 justify-end mt-2">
                  <button
                      @click="updateDesc"
                      class="font-semibold text-xs bg-sky-blue text-linen py-1 px-3 rounded-full inline-block hover:bg-crystal-blue hover:text-navy-blue hover:shadow-lg"
                      >
                      Save
                  </button>
              </div>
            </div>
            <div v-if="currentTab === 'Task History'">
              <!-- Task History content here -->
               <div class="bg-color-white rounded-lg h-64 overflow-y-auto">
                  <div class="p-2" v-if="task.histories.length" v-for="history in task.histories" :key="history.id">
                      <p class="text-xs" v-if="history.attribute == 'status_id'">
                          <strong>{{ history.user.name }}</strong> changed <strong>Status</strong>
                          from <em class="font-bold">{{ history.old_status ? history.old_status.name : '' }}</em>
                          to <em class="font-bold">{{ history.new_status ? history.new_status.name : '' }}</em>
                          on {{ formatDate(history.created_at) }}.
                      </p>
                      <p class="text-xs" v-else>
                          <strong>{{ history.user.name }}</strong> changed <strong class="capitalize">{{ history.attribute }}</strong>
                          from <em class="font-bold" v-html="history.old_value ? history.old_value : 'null'"></em>
                          to <em class="font-bold" v-html="history.new_value ? history.new_value : 'null'"></em>
                          on {{ formatDate(history.created_at) }}.
                      </p>
                  </div>
                  <div v-else>
                      No History
                  </div>
              </div>
            </div>
          </div>
        </div>
  
        <!-- Modal Footer -->
        <div class="flex justify-between items-center p-4 border-t border-dark-gray">
          <div>
            <p class="text-sm text-gray">Task Progress:</p>
            <div class="w-full bg-dark-gray rounded-full h-2 mt-1">
              <div 
                :style="{ width: task.progress + '%' }" 
                class="bg-green h-2 rounded-full"
              ></div>
            </div>
          </div>
          <button class="bg-sky-blue text-color-white px-4 py-2 rounded-md" @click="closeModal">Close</button>
        </div>
      </div>
    </div>
  </template>
  
  
  
  <style scoped>
  button {
    transition: all 0.3s ease;
  }

  .input-field {
    padding: 0.5rem;
    border: 1px solid #ccc;
    border-radius: 0.375rem;
    width: 100%;
}
  </style>
  