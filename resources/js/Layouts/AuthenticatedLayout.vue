<script setup>
import { ref, onMounted, watch } from "vue";
import { useTheme } from "@/composables/useTheme";
import { Link } from "@inertiajs/vue3";
import ApplicationLogo from "@/Components/ApplicationLogo.vue";
import Dropdown from "@/Components/Dropdown.vue";
import DropdownLink from "@/Components/DropdownLink.vue";
import NavLink from "@/Components/NavLink.vue";

// Theme setup
const { theme, primaryColor, applyTheme, applyPrimaryColor } = useTheme();

onMounted(() => {
    // Load theme/color from localStorage if present
    const savedTheme = localStorage.getItem("ldns_theme");
    const savedColor = localStorage.getItem("ldns_primary_color");
    if (savedTheme) {
        theme.value = savedTheme;
        applyTheme(savedTheme);
    }
    if (savedColor) {
        primaryColor.value = savedColor;
        applyPrimaryColor(savedColor);
    }
});

watch(theme, (val) => {
    localStorage.setItem("ldns_theme", val);
    applyTheme(val);
});
watch(primaryColor, (val) => {
    localStorage.setItem("ldns_primary_color", val);
    applyPrimaryColor(val);
});

// Lucide icons
import {
    LayoutDashboard,
    User,
    Cog,
    LogOut,
    DollarSign,
    Users,
    Receipt,
    Inbox,
    Ticket,
    MessagesSquare,
    RadioTower,
    Server,
    Package,
    Coins,
    User2,
    FileText,
    Settings,
    PiggyBank,
    Banknote,
    Network,
    Clock,
    Calendar,
    AlertTriangle,
    CheckCircle,
    List,
} from "lucide-vue-next";
import { Activity } from "lucide-vue-next";
const sidebarOpen = ref(false); // Changed initial state to false for better mobile default

//const usersDropdownOpen = ref(false);
</script>

<template>
    <div :class="['flex min-h-screen', theme === 'dark' ? 'bg-gray-900 text-white' : 'bg-gradient-to-r from-white via-green-50 to-gray-100 text-gray-900']">
        <aside
            :class="[
                theme === 'dark' ? 'bg-gray-800 text-white' : 'bg-gray-200 text-gray-900',
                'shadow-lg transition-transform duration-200 ease-in-out',
                'fixed inset-y-0 left-0 z-30 w-64',
                sidebarOpen ? 'translate-x-0' : '-translate-x-full',
                'lg:relative lg:translate-x-0 lg:w-64 lg:flex-shrink-0',
            ]"
        >
            <div class="items-center justify-between mb-6 bg-cyan-200 px-2 py-2">
                <Link :href="route('dashboard')">
                    <!--<ApplicationLogo class="max-h-20 w-auto" />-->
                </Link>
                <button
                    @click="sidebarOpen = false"
                    class="lg:hidden text-gray-500 hover:text-gray-700"
                    aria-label="Close sidebar"
                >
                    <svg
                        class="h-6 w-6"
                        fill="none"
                        stroke="currentColor"
                        viewBox="0 0 24 24"
                    >
                        <path
                            stroke-linecap="round"
                            stroke-linejoin="round"
                            stroke-width="2"
                            d="M6 18L18 6M6 6l12 12"
                        />
                    </svg>
                </button>
                <div class="px-4 py-2 flex flex-col space-y-1 ">
                <NavLink :href="route('tenants.dashboard.index')" 
                :active="$page.url.startsWith('tenants/dashboard')"
                class="flex items-center">
                    <LayoutDashboard class="mr-2 h-4 w-4 text-black" />
                    <span class="text-black text-bold">Dashboard</span>
                </NavLink>
                </div>
            </div>

            <div class="px-4 py-2 flex flex-col space-y-1">
                <p
                    class="bg-gradient-to-r from-blue-600 to-indigo-500 text-transparent bg-clip-text"
                >
                    Users
                </p>

                <NavLink 
                    :href="route('tenants.activeusers.index')" 
                    :active="$page.url.startsWith('/tenants/activeusers')"
                    class="flex items-center p-2 m-2"
                    >
                    <Activity class="mr-2 h-4 w-4 text-gray-500" />
                    <span class="text-black text-bold">Active Users</span>
                </NavLink>

                <NavLink
                    :href="route('tenants.users.index')"
                    :active="$page.url.startsWith('/tenants/users')"
                    class="flex items-center p-2 m-2"
                >
                    <Users class="mr-2 h-4 w-4 text-gray-500" />
                    <span class="text-black text-bold">Users</span>
                </NavLink>

                <NavLink
                    :href="route('tenants.tickets.index')"
                    :active="$page.url.startsWith('/tenants/tickets')"
                    class="flex items-center p-2 m-2"
                >
                    <Ticket class="mr-2 h-4 w-4 text-gray-500" />
                    <span class="text-black text-bold">Tickets</span>
                </NavLink>

                <NavLink
                    :href="route('tenants.leads.index')"
                    :active="$page.url.startsWith('/tenants/leads')"
                    class="flex items-center p-2 m-2"
                >
                    <Inbox class="mr-2 h-4 w-4 text-gray-500" />
                    <span class="text-black text-bold">Leads</span>
                </NavLink>
            </div>

            <div class="px-4 py-2 flex flex-col space-y-1">
                <p
                    class="mb-2 text-xs font-bold text-cyan-500 bg-clip-text uppercase tracking-wide"
                >
                    Finance
                </p>
                <NavLink
                    :href="route('tenants.packages.index')"
                    :active="$page.url.startsWith('/tenants/packages')"
                    class="flex items-center p-2 m-2"
                >
                    <Package class="mr-2 h-4 w-4 text-gray-500" />
                    <span class="text-black text-bold">Packages</span>
                </NavLink>

                <NavLink
                    :href="route('tenants.payment.index')"
                    :active="$page.url.startsWith('/tenants/payment')"
                    class="flex items-center p-2 m-2"
                >
                    <Banknote class="mr-2 h-4 w-4 text-gray-500" />
                    <span class="text-black text-bold">Payments</span>
                </NavLink>

                <NavLink
                    :href="route('tenants.vouchers.index')"
                    :active="$page.url.startsWith('/tenants/vouchers')"
                    class="flex items-center p-2 m-2"
                >
                    <Coins class="mr-2 h-4 w-4 text-gray-500" />
                    <span class="text-black text-bold">Vouchers</span>
                </NavLink>
                <NavLink 
                    :href="route('tenants.invoices.index')" 
                    :active="$page.url.startsWith('/tenants/invoices')"
                    class="flex items-center p-2 m-2">
                    <Receipt class="mr-2 h-4 w-4 text-gray-500" />
                    <span class="text-black text-bold">Invoices</span>
                </NavLink>
                <NavLink 
                    :href="route('tenants.expenses.index')" 
                    :active="$page.url.startsWith('/tenants/expenses')"
                    class="flex items-center p-2 m-2">
                    <FileText class="mr-2 h-4 w-4 text-gray-500" />
                    <span class="text-black text-bold">Expenses</span>
                </NavLink>
            </div>

            <div class="px-4 py-2 flex flex-col space-y-1">
                <p
                    class="mb-2 text-xs font-bold text-purple-600 bg-clip-text uppercase tracking-wide"
                >
                    Communication
                </p>
                <NavLink
                    :href="route('tenants.sms.index')"
                    :active="$page.url.startsWith('/tenants/sms')"
                    class="flex items-center p-2 m-2"
                >
                    <MessagesSquare class="mr-2 h-4 w-4 text-gray-500" />
                    <span class="text-black text-bold">SMS</span>
                </NavLink>
            </div>

            <div class="px-4 py-2 flex flex-col space-y-1">
                <p
                    class="mb-2 text-xs font-bold bg-gradient-to-r from-green-500 to-emerald-600 text-transparent bg-clip-text uppercase tracking-wide"
                >
                    Network
                </p>
                <NavLink
                    :href="route('tenants.mikrotiks.index')"
                    :active="$page.url.startsWith('/tenants/mikrotiks')"
                    class="flex items-center p-2 m-2 "
                >
                    <Network class="mr-2 h-4 w-4 text-gray-500" />
                    <span class="text-black text-bold">Mikrotiks</span>
                </NavLink>
                <NavLink
                    :href="route('tenants.equipment.index')"
                    :active="$page.url.startsWith('/tenants/equipment')"
                    class="flex items-center p-2 m-2"
                >
                    <Server class="mr-2 h-4 w-4 text-gray-500" />
                    <span class="text-black text-bold">Equipment</span>
                </NavLink>
            </div>
        </aside>

        <div
            v-if="sidebarOpen"
            @click="sidebarOpen = false"
            class="fixed inset-0 z-20 bg-black opacity-50 lg:hidden"
        ></div>

    <div :class="['flex flex-1 flex-col', theme === 'dark' ? 'bg-gray-900 text-white' : 'bg-white text-gray-900']">
            <nav
                :class="[theme === 'dark' ? 'bg-gray-900 border-b border-gray-800 text-white' : 'bg-white border-b border-gray-200 text-gray-900', 'px-6 py-4 flex justify-between items-center']"
            >
                <button
                    v-if="!sidebarOpen"
                    @click="sidebarOpen = true"
                    class="lg:hidden text-gray-600 focus:outline-none"
                    aria-label="Open sidebar"
                >
                    <svg
                        class="h-6 w-6"
                        fill="none"
                        stroke="currentColor"
                        viewBox="0 0 24 24"
                    >
                        <path
                            stroke-linecap="round"
                            stroke-linejoin="round"
                            stroke-width="2"
                            d="M4 6h16M4 12h16M4 18h16"
                        />
                    </svg>
                </button>

                <div class="flex items-center space-x-4">
                    <button
                        type="button"
                        class="inline-flex items-center px-3 py-2 text-xl font-extrabold text-blue-800 hover:text-gray-900"
                    >
                        {{ $page.props.auth.user.business_name }}
                    </button>
                    
                    <!-- Subscription Status -->
                    <div v-if="$page.props.subscription" class="flex items-center space-x-2">
                        <!-- Trial Status -->
                        <div v-if="$page.props.subscription.is_on_trial" 
                             class="flex items-center space-x-1 bg-blue-100 text-blue-800 px-2 py-1 rounded-full text-sm">
                            <Clock class="w-4 h-4" />
                            <span class="font-medium">{{ $page.props.subscription.trial_days_remaining }} days left</span>
                        </div>
                        
                        <!-- Active Status -->
                        <div v-else-if="$page.props.subscription.is_active" 
                             class="flex items-center space-x-1 bg-green-100 text-green-800 px-2 py-1 rounded-full text-sm">
                            <CheckCircle class="w-4 h-4" />
                            <span class="font-medium">{{ $page.props.subscription.current_period_days_remaining }} days left</span>
                        </div>
                        
                        <!-- Expired Status -->
                        <div v-else-if="$page.props.subscription.is_expired" 
                             class="flex items-center space-x-1 bg-red-100 text-red-800 px-2 py-1 rounded-full text-sm">
                            <AlertTriangle class="w-4 h-4" />
                            <span class="font-medium">Expired</span>
                        </div>
                        
                        <!-- Renewal Button for Expired -->
                        <div v-if="$page.props.subscription.is_expired" class="ml-2">
                            <a :href="route('subscription.expired')" 
                               class="bg-red-600 text-white px-3 py-1 rounded text-sm hover:bg-red-700 transition-colors">
                                Renew Now
                            </a>
                        </div>
                    </div>
                </div>

                <div class="ml-auto relative bg-cyan-100 rounded-md max-h-40">
                    <Dropdown align="right" width="48">
                        <template #trigger>
                            <span class="inline-flex rounded-md">
                                <button
                                    type="button"
                                    class="inline-flex items-center px-3 py-2 text-sm font-medium text-gray-700 hover:text-gray-900"
                                >
                                    <Cog class="" />
                                </button>
                            </span>
                        </template>

                        <template #content>
                            <DropdownLink
                                class="flex items-center"
                                :href="route('tenants.settings.index')"
                                :active="
                                    $page.url.startsWith('/tenants/settings')
                                "
                            >
                                <Settings class="h-4 w-4 mr-2" />
                                <span class="text-black text-bold">Settings</span>
                            </DropdownLink>
                            
                            <!-- Subscription Management -->
                            <DropdownLink
                                v-if="$page.props.subscription && ($page.props.subscription.is_on_trial || $page.props.subscription.is_expired)"
                                :href="route('subscription.payment')"
                                class="flex items-center"
                            >
                                <DollarSign class="h-4 w-4 mr-2 text-green-500" />
                                <span>Manage Subscription</span>
                            </DropdownLink>
                            
                            <DropdownLink
                                :href="route('profile.edit')"
                                class="flex items-center"
                            >
                                <User2 class="h-4 w-4 mr-2 text-blue-500" />
                                <span>Profile</span>
                            </DropdownLink>
                            <DropdownLink
                                :href="route('logout')"
                                class="flex items-center"
                                method="post"
                                as="button"
                            >
                                <LogOut class="h-4 w-4 mr-2 text-red-900" />
                                <span>Log Out</span>
                            </DropdownLink>
                        </template>
                    </Dropdown>
                </div>
            </nav>

            <header v-if="$slots.header" :class="[theme === 'dark' ? 'bg-gray-900 text-white shadow' : 'bg-white text-gray-900 shadow']">
                <div class="max-w-7xl mx-auto py-6 px-4 sm:px-6 lg:px-8">
                    <slot name="header" />
                </div>
            </header>

            <main :class="[theme === 'dark' ? 'bg-gray-900 text-white' : 'bg-white text-gray-900', 'p-4']">
                <!-- Trial Warning Banner -->
                <div v-if="$page.props.subscription && $page.props.subscription.is_on_trial && $page.props.subscription.trial_days_remaining <= 3" 
                     class="bg-yellow-50 border-l-4 border-yellow-400 p-4 mb-6 rounded-r-lg">
                    <div class="flex items-center">
                        <AlertTriangle class="w-5 h-5 text-yellow-400 mr-2" />
                        <div class="flex-1">
                            <h3 class="text-sm font-medium text-yellow-800">Trial Ending Soon</h3>
                            <p class="text-sm text-yellow-700 mt-1">
                                Your trial period ends in {{ $page.props.subscription.trial_days_remaining }} days. 
                                <a :href="route('subscription.payment')" class="underline hover:text-yellow-800 font-medium">
                                    Renew now
                                </a> to continue using our services.
                            </p>
                        </div>
                    </div>
                </div>

                <!-- Subscription Expired Banner -->
                <div v-if="$page.props.subscription && $page.props.subscription.is_expired" 
                     class="bg-red-50 border-l-4 border-red-400 p-4 mb-6 rounded-r-lg">
                    <div class="flex items-center">
                        <AlertTriangle class="w-5 h-5 text-red-400 mr-2" />
                        <div class="flex-1">
                            <h3 class="text-sm font-medium text-red-800">Subscription Expired</h3>
                            <p class="text-sm text-red-700 mt-1">
                                Your subscription has expired. 
                                <a :href="route('subscription.expired')" class="underline hover:text-red-800 font-medium">
                                    Renew now
                                </a> to restore access to all features.
                            </p>
                        </div>
                    </div>
                </div>

                <slot />
            </main>
        </div>
    </div>
</template>
