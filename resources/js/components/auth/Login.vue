<template>
    <div class="auth-container">
        <h2>Login</h2>
        <form @submit.prevent="login">
            <input type="email" v-model="email" placeholder="Email" required />
            <input type="password" v-model="password" placeholder="Password" required />
            <button type="submit" :disabled="loading">
                {{ loading ? 'Logging in...' : 'Login' }}
            </button>
        </form>
        <p v-if="error" class="error">{{ error }}</p>
        <p>Don't have an account? <router-link to="/register">Register</router-link></p>
    </div>
</template>

<script>
import axios from 'axios';
import { useRouter } from 'vue-router';
import { ref } from 'vue';

export default {
    setup() {
        const email = ref('');
        const password = ref('');
        const error = ref('');
        const loading = ref(false);
        const router = useRouter();

        const login = async () => {
            error.value = ''; 
            loading.value = true; 

            try {
               
                await axios.get('/sanctum/csrf-cookie');

                const response = await axios.post('/login', {
                    email: email.value,
                    password: password.value
                });

                const token = response.data.token;
                localStorage.setItem('auth_token', token);
                axios.defaults.headers.common['Authorization'] = `Bearer ${token}`;

                router.push('/dashboard');
            } catch (err) {
                // console.error('Login Error:', err);
                error.value = err.response?.data?.message || 'Invalid credentials';
            } finally {
                loading.value = false;
            }
        };

        return { email, password, login, error, loading };
    }
};
</script>

<style scoped>
.auth-container {
    max-width: 400px;
    margin: auto;
    text-align: center;
    padding: 20px;
    border-radius: 8px;
    box-shadow: 0 0 10px rgba(0, 0, 0, 0.1);
    background: #fff;
}
input {
    display: block;
    width: 100%;
    padding: 10px;
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
</style>
