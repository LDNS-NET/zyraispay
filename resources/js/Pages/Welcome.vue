<script setup>
import { Head, Link } from '@inertiajs/vue3';

defineProps({
    canLogin: Boolean,
    canRegister: Boolean,
    laravelVersion: String,
    phpVersion: String,
});

// Enhanced Features
const detailedFeatures = [
    {
        icon: '🔗',
        title: 'Seamless M-Pesa Integration',
        description: 'Accept payments via STK Push, C2B, B2C with instant reconciliation. Enable auto-receipts and reduce manual financial tasks.',
    },
    {
        icon: '📡',
        title: 'Deep Mikrotik RouterOS Integration',
        description: 'Directly manage PPPoE, DHCP, and Hotspot users. Suspend/resume users, assign plans, apply queues, and track sessions through Mikrotik API in real-time.',
    },
    {
        icon: '⚡',
        title: 'Advanced Bandwidth Management',
        description: 'Shape traffic dynamically using Mikrotik queues. Automatically throttle or disconnect users based on payment status or quota usage.',
    },
    {
        icon: '🗓️',
        title: 'Automated Invoicing & Billing',
        description: 'Generate invoices, send payment reminders, and provide receipts via SMS and email. Handle VAT, discounts, and overdue notices with automation.',
    },
    {
        icon: '👤',
        title: 'Customer Self-Service Portal',
        description: 'Customers can view their invoices, usage, renew service, and submit tickets. Supports mobile and desktop access.',
    },
    {
        icon: '📈',
        title: 'Real-Time Analytics & Insights',
        description: 'Monitor payments, usage trends, churn, delinquency, and customer growth with live dashboards and downloadable reports.',
    },
    {
        icon: '🔐',
        title: 'Secure, Scalable & Modular',
        description: 'Laravel-based with Inertia & Vue 3, protected by SSL, 2FA, and audit logging. Scales with your business.',
    },
];

// How It Works Steps
const howItWorksSteps = [
    {
        step: '1',
        title: 'Connect Mikrotik & Set Plans',
        description: 'Easily link Mikrotik routers, configure PPPoE/Hotspot access, and create speed packages and pricing tiers.',
    },
    {
        step: '2',
        title: 'Onboard Subscribers',
        description: 'Create or import subscribers, assign them plans, and automate service activation and credentials provisioning.',
    },
    {
        step: '3',
        title: 'Automate Billing & Payments',
        description: 'Invoices, reminders, and M-Pesa payment verification happen automatically. Users are notified instantly.',
    },
    {
        step: '4',
        title: 'Monitor, Optimize & Grow',
        description: 'Track user sessions, enforce data limits, receive router uptime alerts, and make data-driven decisions.',
    },
];
</script>

<template>
<Head title="Zyraispay | Next-Gen ISP Billing with Mikrotik Integration" />

<div class=" border-8 border-e-purple-500 bg-gradient-to-r from-green-900 via-cyan-900 to-blue-900 min-h-screen px-6 py-16 sm:py-24 flex flex-col justify-between">

    <!-- Hero Section -->
    <div class="max-w-7xl mx-auto flex flex-col-reverse lg:flex-row items-center gap-12 pt-8">
        <div class="text-center lg:text-left max-w-2xl">
            <h1 class="text-4xl sm:text-5xl lg:text-6xl font-extrabold text-gray-900 dark:text-white leading-tight mb-4">
                Empower Your ISP with <span class="text-blue-600 dark:text-blue-400">Zyraispay</span>
            </h1>
            <p class="mt-6 text-lg text-gray-700 dark:text-gray-300 leading-relaxed">
                An all-in-one billing, bandwidth, and customer experience platform built for African ISPs. Seamless M-Pesa payments, advanced Mikrotik controls, and automated invoicing – all from one dashboard.
            </p>
            <div class="mt-10 flex flex-col sm:flex-row gap-4 justify-center lg:justify-start">
                <Link v-if="canRegister" :href="route('register')" class="px-8 py-4 text-white font-semibold bg-blue-600 rounded-lg shadow-lg hover:bg-blue-700 transition transform hover:scale-105">Start Free Trial</Link>
                <Link v-if="canLogin" :href="route('login')" class="px-8 py-4 text-blue-600 font-semibold border-2 border-blue-600 bg-white rounded-lg shadow-lg hover:bg-blue-50 dark:bg-gray-800 dark:text-blue-400 dark:border-blue-400 dark:hover:bg-gray-700 transition transform hover:scale-105">Log In</Link>
            </div>
        </div>
        <div class="flex-shrink-0 max-w-24">
            <img src="/images/isp-dashboard.png" alt="ISP Dashboard" class="w-full rounded-2xl shadow-2xl ring-2 ring-blue-200 dark:ring-blue-700" />
        </div>
    </div>

    <!-- Feature Grid -->
    <section class="max-w-7xl mx-auto grid sm:grid-cols-2 lg:grid-cols-4 gap-8 text-center py-16">
        <div class="p-6 bg-[#F0F9FF] dark:bg-gray-800 rounded-xl shadow-md hover:shadow-lg transition transform hover:-translate-y-1">
            <h3 class="text-xl font-bold text-blue-700 dark:text-blue-300 mb-2">Mikrotik API Control</h3>
            <p class="text-gray-700 dark:text-gray-400">Suspend, resume, throttle, and monitor Mikrotik users directly via secure RouterOS API.</p>
        </div>
        <div class="p-6 bg-[#FFF5F5] dark:bg-gray-800 rounded-xl shadow-md hover:shadow-lg transition transform hover:-translate-y-1">
            <h3 class="text-xl font-bold text-red-700 dark:text-red-300 mb-2">M-Pesa Reconciliation</h3>
            <p class="text-gray-700 dark:text-gray-400">Supports STK Push, C2B, and B2C with automatic updates to user accounts in real-time.</p>
        </div>
        <div class="p-6 bg-[#F3FDF3] dark:bg-gray-800 rounded-xl shadow-md hover:shadow-lg transition transform hover:-translate-y-1">
            <h3 class="text-xl font-bold text-green-700 dark:text-green-300 mb-2">Automated Invoicing</h3>
            <p class="text-gray-700 dark:text-gray-400">Send digital invoices, overdue alerts, and SMS/email receipts without lifting a finger.</p>
        </div>
        <div class="p-6 bg-[#FEFAF1] dark:bg-gray-800 rounded-xl shadow-md hover:shadow-lg transition transform hover:-translate-y-1">
            <h3 class="text-xl font-bold text-yellow-700 dark:text-yellow-300 mb-2">Self-Service Access</h3>
            <p class="text-gray-700 dark:text-gray-400">Customers manage renewals, top-ups, usage, and support from anywhere, anytime.</p>
        </div>
    </section>

    <!-- How It Works -->
    <section class="max-w-7xl mx-auto py-16">
        <h2 class="text-4xl font-extrabold text-gray-900 dark:text-white text-center mb-12">How Zyraispay Works</h2>
        <div class="grid md:grid-cols-2 lg:grid-cols-4 gap-8">
            <div v-for="step in howItWorksSteps" :key="step.step" class="relative p-6 bg-white dark:bg-gray-800 rounded-xl shadow-lg border border-gray-100 dark:border-gray-700 flex flex-col items-center text-center group">
                <div class="absolute -top-6 left-1/2 transform -translate-x-1/2 w-12 h-12 bg-blue-600 text-white rounded-full flex items-center justify-center text-2xl font-bold border-4 border-white dark:border-gray-900">
                    {{ step.step }}
                </div>
                <h3 class="mt-8 text-xl font-bold text-gray-900 dark:text-white mb-2">{{ step.title }}</h3>
                <p class="text-gray-700 dark:text-gray-300 text-sm">{{ step.description }}</p>
            </div>
        </div>
    </section>

    <!-- Advanced Mikrotik Features -->
    <section class="py-10">
        <div class="max-w-7xl mx-auto px-4">
            <h2 class="text-4xl font-extrabold text-gray-900 dark:text-white text-center mb-12">Advanced Mikrotik Features</h2>
            <div class="grid md:grid-cols-2 gap-12">
                <div class="bg-white dark:bg-gray-800 p-6 rounded-xl shadow-lg border dark:border-gray-700">
                    <h3 class="text-xl font-semibold text-blue-700 dark:text-blue-300 mb-2">Real-Time User Control</h3>
                    <p class="text-gray-700 dark:text-gray-300">Suspend and reconnect users instantly. Push updated queue rules or credentials without needing to log in to the router manually.</p>
                </div>
                <div class="bg-white dark:bg-gray-800 p-6 rounded-xl shadow-lg border dark:border-gray-700">
                    <h3 class="text-xl font-semibold text-blue-700 dark:text-blue-300 mb-2">Hotspot Voucher Management</h3>
                    <p class="text-gray-700 dark:text-gray-300">Generate, assign, and validate Mikrotik hotspot vouchers. Monitor session time and device count live from the dashboard.</p>
                </div>
                <div class="bg-white dark:bg-gray-800 p-6 rounded-xl shadow-lg border dark:border-gray-700">
                    <h3 class="text-xl font-semibold text-blue-700 dark:text-blue-300 mb-2">Usage-Based Throttling</h3>
                    <p class="text-gray-700 dark:text-gray-300">Apply caps and reset limits based on GB used or time online. Automatically throttle users after limits are exceeded.</p>
                </div>
                <div class="bg-white dark:bg-gray-800 p-6 rounded-xl shadow-lg border dark:border-gray-700">
                    <h3 class="text-xl font-semibold text-blue-700 dark:text-blue-300 mb-2">Router Monitoring & Alerts</h3>
                    <p class="text-gray-700 dark:text-gray-300">Track CPU load, uptime, and online status. Get notified on disconnects or unusual bandwidth spikes in real time.</p>
                </div>
            </div>
        </div>
    </section>

    <!-- Why Choose Us -->
    <section class="py-10 ">
        <div class="max-w-7xl mx-auto px-4 bg-gradient-to-r from-slate-900 py-10 ">
            <h2 class="text-4xl font-extrabold text-gray-300 dark:text-gray-300 text-center mb-12">
                Why Zyraispay is the Smart Choice
            </h2>
            <div class=" py-9 px-10 grid md:grid-cols-2 gap-12 ">
                <div v-for="feature in detailedFeatures" :key="feature.title" class="flex items-start">
                    <div class="flex-shrink-0 text-3xl text-blue-600 dark:text-blue-400 mr-4">{{ feature.icon }}</div>
                    <div>
                        <h3 class="text-xl font-bold text-gray-900 dark:text-white mb-1">{{ feature.title }}</h3>
                        <p class="text-gray-700 dark:text-gray-300">{{ feature.description }}</p>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- CTA -->
    <section class="max-w-7xl mx-auto text-center py-20 bg-gradient-to-r from-slate-900 px-64">
        <h2 class="text-4xl font-extrabold text-gray-900 dark:text-white mb-6">Ready to Supercharge Your ISP?</h2>
        <p class="text-xl text-gray-700 dark:text-gray-300 mb-10">Let Zyraispay help you automate billing, optimize networks, and grow with confidence.</p>
        <Link v-if="canRegister" :href="route('register')" class="inline-flex items-center px-10 py-5 text-xl font-bold text-white bg-green-500 rounded-lg shadow-xl hover:bg-green-600 transition transform hover:scale-105">
            Get Started Free
            <svg class="ml-3 w-6 h-6" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M10.293 15.707a1 1 0 010-1.414L14.586 10l-4.293-4.293a1 1 0 111.414-1.414l5 5a1 1 0 010 1.414l-5 5a1 1 0 01-1.414 0z" clip-rule="evenodd" /></svg>
        </Link>
    </section>

    <!-- Footer -->
    <footer class="mt-24 text-center font-extrabold text-sm text-gray-500 dark:text-blue-900">
        LDNS NETWORKS &copy; {{ new Date().getFullYear() }}. All rights reserved. Powered by LN.
    </footer>
</div>
</template>

<style scoped>
/* Scoped styles here if needed */
</style>
