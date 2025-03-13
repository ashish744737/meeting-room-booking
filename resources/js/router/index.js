import { createRouter, createWebHistory } from 'vue-router';
import Login from '../components/auth/Login.vue';
import Register from '../components/auth/Register.vue';
import Dashboard from '../components/Dashboard.vue';
import BookingForm from '../components/booking/BookingForm.vue';
import MyBookings from '../components/booking/MyBookings.vue';
import MeetingRooms from '../components/meetings/MeetingRooms.vue';
import SubscriptionPlans from '../components/subscriptions/SubscriptionPlans.vue';

const routes = [
    {path: '/subscription-plans',name: 'SubscriptionPlans',component: SubscriptionPlans},
    { path: '/', redirect: '/login' },
    { path: '/login', component: Login },
    { path: '/register', component: Register },
    { path: '/dashboard', component: Dashboard, meta: { requiresAuth: true } },
    { path: '/book-room', component: BookingForm },
    { path: '/my-bookings', component: MyBookings },
    { path: '/meeting-rooms', component: MeetingRooms }
];

const router = createRouter({
    history: createWebHistory(),
    routes
});

export default router;
