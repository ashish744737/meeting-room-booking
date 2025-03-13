<template>
    <div>
        <h2>My Bookings</h2>

        <!-- Filters: Past and Upcoming Bookings -->
        <div>
            <button @click="setFilter('upcoming')" :class="{ active: filterType === 'upcoming' }">
                Upcoming Bookings
            </button>
            <button @click="setFilter('past')" :class="{ active: filterType === 'past' }">
                Past Bookings
            </button>
        </div>

        <div v-if="loading">Loading...</div>
        <div v-else-if="bookings.length === 0">No bookings found.</div>

        <ul v-else>
            <li v-for="booking in bookings" :key="booking.id">
                <strong>{{ booking.meeting_name }}</strong>
                <br>
                Room: {{ booking.meeting_room_id }}
                <br>
                Date: {{ formatDate(booking.start_time) }}
                <br>
                Duration: {{ booking.duration }} minutes
                <br>
                <button @click="cancelBooking(booking.id)">Cancel</button>
            </li>
        </ul>

        <!-- Pagination Controls -->
        <div v-if="pagination.total > pagination.per_page">
            <button @click="fetchBookings(pagination.prev_page_url)" :disabled="!pagination.prev_page_url">
                &laquo; Previous
            </button>
            <span> Page {{ pagination.current_page }} of {{ pagination.last_page }} </span>
            <button @click="fetchBookings(pagination.next_page_url)" :disabled="!pagination.next_page_url">
                Next &raquo;
            </button>
        </div>

        <p v-if="errorMessage" class="error">{{ errorMessage }}</p>
    </div>
</template>

<script>
import axios from 'axios';
import { ref, onMounted } from 'vue';

export default {
    setup() {
        const bookings = ref([]);
        const loading = ref(true);
        const errorMessage = ref('');
        const pagination = ref({});
        const filterType = ref('upcoming');


        const fetchBookings = async (url = `/my-bookings?filter=${filterType.value}`) => {
            loading.value = true;
            errorMessage.value = '';

            try {
                const token = localStorage.getItem('auth_token');
                if (!token) {
                    errorMessage.value = "You must be logged in!";
                    return;
                }

                const response = await axios.get(url, {
                    headers: { Authorization: `Bearer ${token}` }
                });

                if (response.data && Array.isArray(response.data.data)) {
                    bookings.value = response.data.data;
                    pagination.value = {
                        current_page: response.data.current_page,
                        last_page: response.data.last_page,
                        total: response.data.total,
                        per_page: response.data.per_page,
                        prev_page_url: response.data.prev_page_url ? response.data.prev_page_url + `&filter=${filterType.value}` : null,
                        next_page_url: response.data.next_page_url ? response.data.next_page_url + `&filter=${filterType.value}` : null,
                    };
                } else {
                    bookings.value = [];
                    errorMessage.value = "Unexpected API response format.";
                }

            } catch (error) {
                // console.error("API Error:", error.response?.data || error);
                errorMessage.value = error.response?.data?.message || "Error fetching bookings.";
            } finally {
                loading.value = false;
            }
        };

     
        const setFilter = (type) => {
            filterType.value = type;
            fetchBookings(); 
        };

 
        const cancelBooking = async (bookingId) => {
            try {
                const token = localStorage.getItem('auth_token');
                if (!token) {
                    errorMessage.value = "You must be logged in!";
                    return;
                }

                await axios.delete(`/bookings/${bookingId}`, {
                    headers: { Authorization: `Bearer ${token}` }
                });

                bookings.value = bookings.value.filter(b => b.id !== bookingId);
            } catch (error) {
                console.error("Cancel Booking Error:", error);
                errorMessage.value = "Error canceling booking.";
            }
        };

        const formatDate = (dateTime) => {
            return new Date(dateTime).toLocaleString();
        };

        onMounted(() => fetchBookings());

        return {
            bookings,
            loading,
            errorMessage,
            pagination,
            fetchBookings,
            cancelBooking,
            formatDate,
            filterType,
            setFilter
        };
    }
};
</script>

<style scoped>
ul {
    list-style: none;
    padding: 0;
}
li {
    padding: 10px;
    border-bottom: 1px solid #ccc;
}
button {
    margin: 5px;
    padding: 8px 12px;
    background-color: #007bff;
    color: white;
    border: none;
    cursor: pointer;
    border-radius: 5px;
    transition: 0.3s;
}
button:hover {
    background-color: #0056b3;
}
button:disabled {
    background-color: #ccc;
    cursor: not-allowed;
}
.error {
    color: red;
}
.active {
    background-color: green !important;
}
</style>
