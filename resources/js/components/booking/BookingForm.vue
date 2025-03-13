<template>
    <div class="booking-form">
        <h2>Book a Meeting Room</h2>
        <form @submit.prevent="bookRoom">
            <div>
                <label>Meeting Name:</label>
                <input type="text" v-model="meetingName" required />
            </div>
            <div>
                <label>Date and Time:</label>
                <input type="datetime-local" v-model="startTime" required />
            </div>
            <div>
                <label>Duration:</label>
                <select v-model="duration" required>
                    <option value="30">30 mins</option>
                    <option value="60">60 mins</option>
                    <option value="90">90 mins</option>
                </select>
            </div>
            <div>
                <label>Members:</label>
                <input type="number" v-model="members" min="1" required />
            </div>
            <div>
                <label>Meeting Room:</label>
                <select v-model="selectedRoom" required>
                    <option v-for="room in availableRooms" :key="room.id" :value="room.id">
                        {{ room.name }} (Capacity: {{ room.capacity }})
                    </option>
                </select>
            </div>
            <button type="submit" :disabled="loading">
                {{ loading ? 'Booking...' : 'Book Room' }}
            </button>
            <p v-if="errorMessage" class="error">{{ errorMessage }}</p>
            <p v-if="successMessage" class="success">{{ successMessage }}</p>
        </form>
    </div>
</template>

<script>
import axios from 'axios';
import { ref, watch, onMounted } from 'vue';

export default {
    setup() {
        const meetingName = ref('');
        const startTime = ref('');
        const duration = ref('');
        const members = ref(1);
        const availableRooms = ref([]);
        const selectedRoom = ref(null);
        const errorMessage = ref('');
        const successMessage = ref('');
        const loading = ref(false);

        watch([startTime, duration, members], async ([newStartTime, newDuration, newMembers]) => {
            if (!newStartTime || !newDuration || !newMembers) return;
            await fetchAvailableRooms();
        });

        const fetchAvailableRooms = async () => {
            errorMessage.value = '';

            try {
                const token = localStorage.getItem('auth_token');
                if (!token) {
                    errorMessage.value = 'You must be logged in!';
                    return;
                }

                if (!startTime.value) {
                    errorMessage.value = "Please select date and time.";
                    return;
                }

                const response = await axios.get('/available-rooms', {
                    params: {
                        start_time: startTime.value,
                        duration: duration.value,
                        members: members.value
                    },
                    headers: { Authorization: `Bearer ${token}` }
                });

                // console.log("Available Rooms Response:", response.data); 

                if (Array.isArray(response.data)) {
                    availableRooms.value = response.data;
                } else {
                    errorMessage.value = 'Unexpected response format!';
                }
            } catch (error) {
                // console.error("API Error:", error.response?.data || error);
                errorMessage.value = error.response?.data?.message || 'Error fetching meeting rooms.';
            }
        };

        onMounted(() => {
            console.log("Component Mounted: Waiting for user input before fetching rooms.");
        });

        const bookRoom = async () => {
            errorMessage.value = '';
            successMessage.value = '';
            loading.value = true;

            try {
                const token = localStorage.getItem('auth_token');
                if (!token) {
                    errorMessage.value = 'You must be logged in!';
                    loading.value = false;
                    return;
                }

                await axios.post(
                    '/book-room',
                    {
                        meeting_room_id: selectedRoom.value,
                        meeting_name: meetingName.value,
                        start_time: startTime.value,
                        duration: duration.value,
                        members: members.value
                    },
                    { headers: { Authorization: `Bearer ${token}` } }
                );

                successMessage.value = 'Meeting Room Booked Successfully!';
                setTimeout(() => {
                    successMessage.value = '';
                    window.location.href = '/my-bookings';
                }, 1500);
            } catch (error) {
                // console.error("Booking Error:", error);
                errorMessage.value = error.response?.data?.message || 'Booking failed!';
            } finally {
                loading.value = false;
            }
        };

        return {
            meetingName,
            startTime,
            duration,
            members,
            availableRooms,
            selectedRoom,
            bookRoom,
            errorMessage,
            successMessage,
            loading
        };
    }
};
</script>

<style scoped>
.booking-form {
    max-width: 400px;
    margin: auto;
    padding: 20px;
    border-radius: 8px;
    box-shadow: 0 0 10px rgba(0, 0, 0, 0.1);
    background: #fff;
}
input, select {
    width: 100%;
    padding: 8px;
    margin-bottom: 10px;
    border: 1px solid #ccc;
    border-radius: 5px;
}
button {
    width: 100%;
    padding: 10px;
    background-color: #007bff;
    color: white;
    border: none;
    border-radius: 5px;
    cursor: pointer;
}
button:disabled {
    background-color: #ccc;
    cursor: not-allowed;
}
.error {
    color: red;
    margin-top: 10px;
}
.success {
    color: green;
    margin-top: 10px;
}
</style>
