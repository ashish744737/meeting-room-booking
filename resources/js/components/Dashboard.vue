<template>
    <div class="dashboard">
        <h2>Welcome to Meeting Room Booking</h2>
        <hr>
        <!-- Navigation Menu -->
        <nav>
            <router-link to="/dashboard">Home</router-link>
            <router-link to="/my-bookings">My Bookings</router-link>
            <router-link to="/book-room">Book a Room</router-link>
            <router-link to="/meeting-rooms">Meeting Rooms</router-link>
            <router-link to="/subscription-plans">Subscription Plans</router-link>
            <button @click="logout" :disabled="loading">
                {{ loading ? "Logging out..." : "Logout" }}
            </button>

        </nav>
    </div>

</template>

<script>
import axios from 'axios';
import { ref, onMounted } from 'vue';
import { useRouter } from 'vue-router';

export default {
    setup() {
        const router = useRouter();
        const loading = ref(false);

        const checkAuth = () => {
            const token = localStorage.getItem("auth_token");
            if (!token) {
                router.push("/login");
            }
        };

        const logout = async () => {
            loading.value = true;
            try {
                const token = localStorage.getItem("auth_token");
                if (!token) {
                    router.push("/login");
                    return;
                }

                await axios.post("/logout", {}, {
                    headers: { Authorization: `Bearer ${token}` }
                });

                localStorage.removeItem("auth_token");
                router.push("/login");
            } catch (error) {
                console.error("Logout Error:", error.response?.data || error);
            } finally {
                loading.value = false;
            }
        };

        onMounted(checkAuth);

        return { logout, loading };
    }
};
</script>

<style scoped>
.dashboard {
    text-align: center;
    margin-top: 50px;
}

/* Navigation Styles */
nav {
    margin-top: 20px;
}
nav a, button {
    display: inline-block;
    margin: 10px;
    padding: 10px 15px;
    text-decoration: none;
    color: white;
    background-color: #007bff;
    border-radius: 5px;
    transition: 0.3s;
}
nav a:hover, button:hover {
    background-color: #0056b3;
}
button {
    border: none;
    cursor: pointer;
}
button:disabled {
    background-color: gray;
    cursor: not-allowed;
}
</style>
