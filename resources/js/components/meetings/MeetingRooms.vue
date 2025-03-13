<template>
    <div>
        <h2>Available Meeting Rooms</h2>
        <ul>
            <li v-for="room in meetingRooms" :key="room.id">
                {{ room.name }} - Capacity: {{ room.capacity }}
            </li>
        </ul>
    </div>
</template>

<script>
import axios from 'axios';

export default {
    data() {
        return {
            meetingRooms: []
        };
    },
    async created() {
        try {
            const response = await axios.get('/meeting-rooms');
            this.meetingRooms = response.data;
        } catch (error) {
            // console.error("Error fetching meeting rooms", error);
        }
    }
};
</script>
