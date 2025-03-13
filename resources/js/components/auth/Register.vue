<template>
    <div class="auth-container">
        <h2>Register</h2>
        <form @submit.prevent="register">
            <input type="text" v-model="name" placeholder="Full Name" required />
            <input type="email" v-model="email" placeholder="Email" required />
            <input type="password" v-model="password" placeholder="Password" required />
            <button type="submit">Register</button>
        </form>
        <p v-if="error" class="error">{{ error }}</p>
        <p>Already have an account? <router-link to="/login">Login</router-link></p>
    </div>
</template>

<script>
import axios from 'axios';
import { useRouter } from 'vue-router';
import { ref } from 'vue';

export default {
    setup() {
        const name = ref('');
        const email = ref('');
        const password = ref('');
        const error = ref('');
        const router = useRouter();

        const register = async () => {
            try {
                await axios.post('/register', {
                    name: name.value,
                    email: email.value,
                    password: password.value
                });
                router.push('/login');
            } catch (err) {
                error.value = err.response?.data?.message || 'Registration failed';
            }
        };

        return { name, email, password, register, error };
    }
};
</script>

<style scoped>
.auth-container {
    max-width: 400px;
    margin: auto;
    text-align: center;
}
input {
    display: block;
    width: 100%;
    padding: 8px;
    margin-bottom: 10px;
}
button {
    width: 100%;
    padding: 8px;
    background-color: green;
    color: white;
    border: none;
}
.error {
    color: red;
}
</style>
