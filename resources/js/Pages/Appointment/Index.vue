<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import {Head, usePage} from '@inertiajs/vue3';
import { useToast } from 'vue-toastification';
import axios from 'axios';
import { ref } from 'vue';

const toast = useToast();

const { props } = usePage();
const appointments = props.appointments;
const guests = ref([]);

const cancelAppointment = (id) => {
    // console.log(id);
    axios.put(route('appointment.update', { appointment: id, status: 'cancelled' }))
        .then((res) => {
            console.log(res);
            toast.success('Appointment cancelled successfully.');

            appointments.forEach((appointment) => {
                if (appointment.id === id && appointment.status !== 'Cancelled') {
                    appointment.status = 'Cancelled';
                    document.querySelector(`.cancel-btn-${id}`).disabled = true;
                    document.querySelector(`.cancel-btn-${id}`).textContent = 'Cancelled';
                }
            });
        })
        .catch((error) => {
            toast.error('Failed to cancel appointment.');
            console.error(error);
        });
};

const showGuests = (appointment) => {
    guests.value = appointment.guest_invitations || [];
};
</script>

<template>
    <Head title="Appointment" />

    <AuthenticatedLayout>
        <template #header>
            <h2
                class="text-xl font-semibold leading-tight text-gray-800"
            >
                Appointment
            </h2>
        </template>

        <div class="py-12">
            <div class="mx-auto max-w-7xl sm:px-6 lg:px-8">
                <table class="min-w-full divide-y divide-gray-200">
                    <thead class="bg-gray-50">
                    <tr>
                        <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Title</th>
                        <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Description</th>
                        <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Appointment Time</th>
                        <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">TimeZone</th>
                        <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Status</th>
                        <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Actions</th>
                    </tr>
                    </thead>
                    <tbody class="bg-white divide-y divide-gray-200">
                    <tr v-for="appointment in appointments" :key="appointment.id" @click="showGuests(appointment)">
                        <td class="px-6 py-4 whitespace-nowrap">{{ appointment.title }}</td>
                        <td class="px-6 py-4 whitespace-nowrap">{{ appointment.description }}</td>
                        <td class="px-6 py-4 whitespace-nowrap">{{ appointment.date + ' - ' + appointment.time }}</td>
                        <td class="px-6 py-4 whitespace-nowrap">{{ appointment.timezone }}</td>
                        <td class="px-6 py-4 whitespace-nowrap">{{ appointment.status }}</td>
                        <td class="px-6 py-4 whitespace-nowrap">
                            <button @click="cancelAppointment(appointment.id)"
                                        :class="`cancel-btn-${appointment.id}`"
                                    :disabled="appointment.status === 'Cancelled'"
                                    class="text-red-500 hover:text-red-700">
                                {{ appointment.status === 'Cancelled' ? 'Cancelled' : 'Cancel' }}
                            </button>
                        </td>
                    </tr>
                    </tbody>
                </table>

                <div v-if="guests.value">
                    <h3 class="text-lg font-semibold leading-tight text-gray-800 mt-6">Guest Invitations</h3>
                    <ul class="list-disc pl-5">
                        <li v-for="guest in guests.value" :key="guest.id">
                            {{ guest.name }} ({{ guest.email }}) - {{ guest.status }}
                        </li>
                    </ul>
                </div>

            </div>
        </div>
    </AuthenticatedLayout>
</template>

<style scoped>

</style>
