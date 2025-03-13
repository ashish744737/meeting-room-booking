<template>
    <div>
        <h1>Payment Details</h1>

        <div v-if="successMessage" class="success">{{ successMessage }}</div>
        <div v-if="errorMessage" class="error">{{ errorMessage }}</div>

        <form @submit.prevent="submitPayment">
            <label>Transaction No:</label>
            <input v-model="payment.transaction_no" required />

            <label>Payment Date:</label>
            <input type="date" v-model="payment.payment_date" required />

            <label>Amount:</label>
            <input type="number" v-model="payment.amount" required />

            <label>Payment Mode:</label>
            <select v-model="payment.payment_mode" required>
                <option value="Credit Card">Credit Card</option>
                <option value="UPI">UPI</option>
                <option value="Bank Transfer">Bank Transfer</option>
            </select>

            <label>Net Amount:</label>
            <input type="number" v-model="payment.net_amount" required />

            <button type="submit" :disabled="submitting">
                {{ submitting ? "Processing..." : "Submit Payment" }}
            </button>
        </form>
    </div>
</template>

<script>
import axios from 'axios';

export default {
    data() {
        return {
            payment: {
                transaction_no: '',
                payment_date: '',
                amount: '',
                payment_mode: '',
                net_amount: '',
            },
            submitting: false,
            successMessage: '',
            errorMessage: '',
        };
    },
    methods: {
        async submitPayment() {
            this.submitting = true;
            this.successMessage = '';
            this.errorMessage = '';

            try {
                const token = localStorage.getItem('auth_token');
                if (!token) {
                    this.errorMessage = "You must be logged in!";
                    return;
                }

                const response = await axios.post('/payment', this.payment, {
                    headers: { Authorization: `Bearer ${token}` },
                });

                this.successMessage = response.data.message;
                this.payment = {};
            } catch (error) {
                this.errorMessage = error.response?.data?.message || "Payment failed.";
            } finally {
                this.submitting = false;
            }
        }
    }
};
</script>

<style scoped>
h1 {
    text-align: center;
}
form {
    max-width: 400px;
    margin: 0 auto;
}
label {
    display: block;
    margin-top: 10px;
}
input, select {
    width: 100%;
    padding: 8px;
    margin-top: 5px;
}
button {
    margin-top: 15px;
    padding: 10px;
    width: 100%;
    background-color: #007bff;
    color: white;
    border: none;
    cursor: pointer;
}
button:hover {
    background-color: #0056b3;
}
.success {
    color: green;
    text-align: center;
}
.error {
    color: red;
    text-align: center;
}
</style>
