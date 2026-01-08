<script setup>
import { ref, onMounted } from 'vue';

const tasks = ref([]);
const newTask = ref({
    title: '',
    description: '',
    status: 'pending',
    due_date: ''
});
const loading = ref(false);
const fetchTasks = async () => {
    loading.value = true;
    try {
        const response = await axios.get('/api/tasks');
        tasks.value = response.data.data;
    } catch (err) {
        console.error('Error fetching tasks', err);
        alert('Failed to load tasks.');
    } finally {
        loading.value = false;
    }
};

const formatError = (err) => {
    if (err.response && err.response.status === 422) {
        // Validation errors
        return Object.values(err.response.data.errors).flat().join('\n');
    }
    return err.response?.data?.message || 'An unexpected error occurred.';
};

const createTask = async () => {
    try {
        await axios.post('/api/tasks', newTask.value);
        newTask.value = { title: '', description: '', status: 'pending', due_date: '' };
        await fetchTasks();
    } catch (err) {
        alert(formatError(err));
    }
};

const updateStatus = async (task) => {
    try {
        await axios.put(`/api/tasks/${task.id}`, { ...task });
    } catch (err) {
        alert(formatError(err));
        await fetchTasks();
    }
};

const deleteTask = async (id) => {
    if (!confirm('Are you sure you want to delete this task?')) return;
    try {
        await axios.delete(`/api/tasks/${id}`);
        await fetchTasks();
    } catch (err) {
        alert(formatError(err));
    }
};

onMounted(() => {
    fetchTasks();
});
</script>

<template>
    <div class="max-w-5xl mx-auto p-6 bg-white shadow-lg rounded-lg mt-10">
        <h1 class="text-3xl font-bold mb-6 text-gray-800 border-b pb-4">Task Manager</h1>

        <!-- Error Message -->
        <!-- Create Task Form -->
        <div class="mb-8 p-6 bg-gray-50 border rounded-lg shadow-sm">
            <h2 class="text-xl font-semibold mb-4 text-gray-700">Add New Task</h2>
            <form @submit.prevent="createTask" class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-4">
                <div class="col-span-1 md:col-span-2 lg:col-span-1">
                    <label class="block text-sm text-gray-600 mb-1">Title</label>
                    <input v-model="newTask.title" type="text" required class="w-full border p-2 rounded focus:ring-2 focus:ring-blue-400" placeholder="Task Title" />
                </div>
                
                <div class="col-span-1 md:col-span-2 lg:col-span-2">
                    <label class="block text-sm text-gray-600 mb-1">Description</label>
                    <input v-model="newTask.description" type="text" class="w-full border p-2 rounded focus:ring-2 focus:ring-blue-400" placeholder="Description (optional)" />
                </div>

                <div>
                   <label class="block text-sm text-gray-600 mb-1">Status</label>
                   <select v-model="newTask.status" class="w-full border p-2 rounded focus:ring-2 focus:ring-blue-400">
                        <option value="pending">Pending</option>
                        <option value="in_progress">In Progress</option>
                        <option value="completed">Completed</option>
                    </select>
                </div>

                <div>
                    <label class="block text-sm text-gray-600 mb-1">Due Date</label>
                    <input v-model="newTask.due_date" type="date" class="w-full border p-2 rounded focus:ring-2 focus:ring-blue-400" />
                </div>

                <div class="col-span-1 md:col-span-2 lg:col-span-4 flex justify-end">
                    <button type="submit" class="bg-blue-600 text-white px-6 py-2 rounded hover:bg-blue-700 transition">
                        Add Task
                    </button>
                </div>
            </form>
        </div>

        <!-- Task List -->
        <div>
            <h2 class="text-xl font-semibold mb-4 text-gray-700">All Tasks</h2>
            
            <div v-if="loading" class="text-center py-10 text-gray-500">
                Loading tasks...
            </div>

            <div v-else-if="tasks.length === 0" class="text-center py-10 bg-gray-50 rounded text-gray-500 border border-dashed">
                No tasks found. Create one above!
            </div>

            <div v-else class="overflow-x-auto">
                <table class="w-full border-collapse border border-gray-200">
                    <thead class="bg-gray-100">
                        <tr>
                            <th class="border p-3 text-left font-semibold text-gray-600">Title & Description</th>
                            <th class="border p-3 text-left font-semibold text-gray-600 w-40">Status</th>
                            <th class="border p-3 text-left font-semibold text-gray-600 w-40">Due Date</th>
                            <th class="border p-3 text-center font-semibold text-gray-600 w-24">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr v-for="task in tasks" :key="task.id" class="hover:bg-gray-50 transition">
                            <td class="border p-3">
                                <div class="font-bold text-gray-800">{{ task.title }}</div>
                                <div class="text-sm text-gray-500 mt-1">{{ task.description }}</div>
                            </td>
                            <td class="border p-3">
                                <select 
                                    v-model="task.status" 
                                    @change="updateStatus(task)" 
                                    class="border p-1 rounded text-sm w-full"
                                    :class="{
                                        'bg-yellow-50 text-yellow-700 border-yellow-200': task.status === 'pending',
                                        'bg-blue-50 text-blue-700 border-blue-200': task.status === 'in_progress',
                                        'bg-green-50 text-green-700 border-green-200': task.status === 'completed'
                                    }"
                                >
                                    <option value="pending">Pending</option>
                                    <option value="in_progress">In Progress</option>
                                    <option value="completed">Completed</option>
                                </select>
                            </td>
                            <td class="border p-3 text-gray-700">{{ task.due_date }}</td>
                            <td class="border p-3 text-center">
                                <button @click="deleteTask(task.id)" class="text-red-500 hover:text-red-700 p-1" title="Delete Task">
                                    🗑️
                                </button>
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</template>
