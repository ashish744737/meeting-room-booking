<template>
    <div>
        <h1>Subscription Plans</h1>

        <div v-if="loading">Loading...</div>
        <div v-else-if="errorMessage" class="error">{{ errorMessage }}</div>

        <!-- Show Current Subscription -->
        <div v-if="currentSubscription">
            <h2>Current Plan: {{ currentSubscription.plan.name }}</h2>
            <p>Expires on: {{ formatDate(currentSubscription.expires_at) }}</p>
            <p><strong>Bookings per day:</strong> {{ currentSubscription.plan.bookings_per_day }}</p>
        </div>

        <!-- Subscription Plans List -->
        <ul>
            <li v-for="plan in formattedPlans" :key="plan.id">
                <h3>{{ plan.name }}</h3>
                <p>{{ plan.description }}</p>
                <p><strong>Bookings per day:</strong> {{ plan.bookings_per_day }}</p>
                <strong>Price: ${{ plan.price }}</strong>
                <br />
                <button 
                    @click="handleSubscription(plan)" 
                    :disabled="isCurrentPlan(plan.id)"
                >
                    {{ isCurrentPlan(plan.id) ? "Subscribed" : "Subscribe" }}
                </button>
            </li>
        </ul>

        <p v-if="successMessage" class="success">{{ successMessage }}</p>

        <!-- Payment Form Modal -->
        <div v-if="showPaymentForm" class="modal">
            <div class="modal-content">
                <h2>Payment for {{ selectedPlan.name }}</h2>

                <form @submit.prevent="submitPayment">
                    <label>Transaction No:</label>
                    <input v-model="payment.transaction_no" required />

                    <label>Payment Date:</label>
                    <input type="date" v-model="payment.payment_date" required />

                    <label>Amount:</label>
                    <input type="number" v-model="payment.amount" required disabled />

                    <label>Payment Mode:</label>
                    <select v-model="payment.payment_mode" required>
                        <option value="Credit Card">Credit Card</option>
                        <option value="UPI">UPI</option>
                        <option value="Bank Transfer">Bank Transfer</option>
                    </select>

                    <label>Net Amount:</label>
                    <input type="number" v-model="payment.net_amount" required disabled />

                    <button type="submit" :disabled="submitting">
                        {{ submitting ? "Processing..." : "Confirm Payment" }}
                    </button>
                </form>

                <button @click="showPaymentForm = false" class="close-button">Cancel</button>
            </div>
        </div>
    </div>
</template>

<script>
import axios from 'axios';

export default {
    data() {
        return {
            plans: [],
            loading: true,
            subscribing: false,
            currentSubscription: null,
            errorMessage: '',
            successMessage: '',
            showPaymentForm: false,
            selectedPlan: null,
            submitting: false,
            payment: {
                transaction_no: '',
                payment_date: new Date().toISOString().split('T')[0],
                amount: 0,
                payment_mode: '',
                net_amount: 0,
            }
        };
    },
    computed: {
        formattedPlans() {
            return this.plans.map(plan => ({
                ...plan,
                name: plan.name.charAt(0).toUpperCase() + plan.name.slice(1)
            }));
        }
    },
    created() {
        this.fetchPlans();
        this.fetchCurrentSubscription();
    },
    methods: {
        async fetchPlans() {
            try {
                const response = await axios.get('/subscription-plans');
                this.plans = response.data;
            } catch (error) {
                // console.error('Error fetching subscription plans:', error);
                this.errorMessage = 'Failed to load subscription plans.';
            } finally {
                this.loading = false;
            }
        },

        async fetchCurrentSubscription() {
            try {
                const token = localStorage.getItem('auth_token');
                if (!token) return;

                const response = await axios.get('/current-subscription', {
                    headers: { Authorization: `Bearer ${token}` }
                });

                // console.log("Fetched Subscription Data:", response.data);
                this.currentSubscription = response.data.subscription || null;
                this.$forceUpdate();
            } catch (error) {
                // console.error("Error fetching current subscription:", error);
                this.currentSubscription = null;
            }
        },

        handleSubscription(plan) {
            if (this.isCurrentPlan(plan.id)) {
                alert("You already have an active subscription.");
                return;
            }

            if (this.currentSubscription) {
                alert("You already have an active subscription. Please wait until it expires before purchasing another plan.");
                return;
            }

            this.openPaymentForm(plan);
        },

        openPaymentForm(plan) {
            this.selectedPlan = plan;
            this.payment = {
                transaction_no: 'TXN' + Date.now(),
                payment_date: new Date().toISOString().split('T')[0],
                amount: plan.price,
                payment_mode: '',
                net_amount: plan.price,
            };
            this.showPaymentForm = true;
        },

        async submitPayment() {
            this.submitting = true;
            this.successMessage = '';
            this.errorMessage = '';

            try {
                const token = localStorage.getItem('auth_token');
                if (!token) {
                    this.errorMessage = 'You must be logged in!';
                    return;
                }

                console.log("Submitting Payment...");

                const paymentResponse = await axios.post('/payment', 
                    {
                        subscription_plan_id: this.selectedPlan.id,
                        transaction_no: this.payment.transaction_no,
                        payment_date: this.payment.payment_date,
                        amount: this.payment.amount,
                        payment_mode: this.payment.payment_mode,
                        net_amount: this.payment.net_amount,
                    },
                    { headers: { Authorization: `Bearer ${token}` } }
                );

                // console.log("Payment API Response:", paymentResponse.data);

                if (paymentResponse.data) {
                    // console.log("Payment successful.");
                    this.successMessage = paymentResponse.data.message || "Payment successful!";

                    this.showPaymentForm = false;

                    setTimeout(async () => {
                        await this.fetchCurrentSubscription();
                    }, 500);
                } else {
                    this.errorMessage = "Payment was successful, but there was an issue updating your subscription.";
                }
            } catch (error) {
                // console.error("Payment Error:", error);
                this.errorMessage = error.response?.data?.message || "An error occurred during payment.";
            } finally {
                this.submitting = false;
            }
        },

        formatDate(date) {
            return new Date(date).toLocaleDateString();
        },

        isCurrentPlan(planId) {
            return this.currentSubscription && this.currentSubscription.plan.id === planId;
        }
    }
};
</script>

<style scoped>
h1, h2 {
    text-align: center;
}
ul {
    list-style: none;
    padding: 0;
}
li {
    border: 1px solid #ddd;
    padding: 15px;
    margin: 10px 0;
    border-radius: 5px;
    background-color: #f9f9f9;
}
button {
    margin-top: 10px;
    padding: 8px 12px;
    border: none;
    background-color: #007bff;
    color: white;
    border-radius: 4px;
    cursor: pointer;
    transition: 0.3s;
}
button:hover {
    background-color: #0056b3;
}
button:disabled {
    background-color: gray;
    cursor: not-allowed;
}
.success {
    color: green;
    text-align: center;
    margin-top: 10px;
}
.error {
    color: red;
    text-align: center;
    margin-top: 10px;
}

/* Modal Styles */
.modal {
    position: fixed;
    top: 0;
    left: 0;
    width: 100%;
    height: 100%;
    background: rgba(0, 0, 0, 0.5);
    display: flex;
    justify-content: center;
    align-items: center;
}
.modal-content {
    background: white;
    padding: 20px;
    border-radius: 8px;
    width: 400px;
    text-align: center;
}
.close-button {
    margin-top: 10px;
    padding: 8px 12px;
    border: none;
    background-color: red;
    color: white;
    border-radius: 4px;
    cursor: pointer;
}
</style>
