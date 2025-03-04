<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import {Head, usePage} from '@inertiajs/vue3';
import { useToast } from 'vue-toastification';
import axios from 'axios';
import {onMounted, ref} from 'vue';
import DataTable from "datatables.net-vue3";
import DataTablesCore from "datatables.net-dt";
import "datatables.net-dt/css/dataTables.dataTables.min.css";
import $ from 'jquery';

const toast = useToast();

DataTable.use(DataTablesCore);

const { props } = usePage();
let appointments = props.appointments || []; // Ensure appointments is an array
const guests = ref([]);

const cancelAppointment = (id) => {
    console.log(id);
    axios.put(route('appointment.update', { appointment: id, status: 'cancelled' }))
        .then((res) => {
            toast.success('Appointment cancelled successfully.');

            appointments.forEach((appointment) => {
                if (appointment.id === id && appointment.status !== 'Cancelled') {
                    appointment.status = 'Cancelled';
                    // document.querySelector(`.cancel-btn-${id}`).disabled = true;
                    // document.querySelector(`.cancel-btn-${id}`).textContent = 'Cancelled';
                    $('.cancel-btn'+id).attr('disabled', 'disabled');
                    $('.cancel-btn'+id).text('Cancelled');
                }
            });
        })
        .catch((error) => {
            toast.error(error.response.data.error);
        });
};

const showGuests = (data) => {
    guests.value = data || [];
};

onMounted(() => {

    $('.display').on('click', '.cancel-button', function () {
        cancelAppointment($(this).data('id'));
    })

    // on click of the row, show the guests
    $('.display').on('click', 'tr', function () {
        // get the appointment
        const appointment = appointments.find(appointment => appointment.id === $(this).find('button').data('id'));

        // console.log(appointment.guest_invitations);
        showGuests(appointment.guest_invitations);
    });

    // showGuests(appointments.find(appointment => appointment.id === $(this).data('id')));
});
</script>

<template>
    <Head title="Appointment" />

    <AuthenticatedLayout>
        <template #header>
            <h2 class="text-xl font-semibold leading-tight text-gray-800">
                Appointment
            </h2>
        </template>

        <div class="py-12">
            <div class="mx-auto max-w-7xl sm:px-6 lg:px-8">
                <div>
                    <p>
                        <b>Scheduled: </b> {{ appointments.filter(appointment => appointment.status === 'Scheduled').length }}
                        &nbsp;&nbsp;||&nbsp;&nbsp;
                        <b>Cancelled: </b> {{appointments.filter(appointment => appointment.status === 'Cancelled').length }}<br>
                    </p>
                </div>
                <DataTable :data="appointments" :columns="[
                    { title: 'Title', data: 'title' },
                    { title: 'Description', data: 'description' },
                    { title: 'Appointment Time', data: null, render: (data, type, row) => `${row.n} - ${row.t}` },
                    { title: 'TimeZone', data: 'timezone' },
                    { title: 'Status', data: 'status' },
                    { title: 'Actions', data: null, render: (data, type, row) => {
                        return `<button class='cancel-btn-${row.id} text-red-500 hover:text-red-700 cancel-button' data-id='${row.id}' ${row.status === 'Cancelled' ? 'disabled' : ''}>
                                    ${row.status === 'Cancelled' ? 'Cancelled' : 'Cancel'}
                                </button>`;
                    }}
                ]" @row-click="showGuests" class="display">
                </DataTable>
                <div v-if="guests.length">
                    <ul>
                        <li><b>Guests</b></li>
                        <li v-for="guest in guests" :key="guest.id">
                            {{ guest.name }} ({{ guest.email }})
                        </li>
                    </ul>
                </div>
            </div>
        </div>
    </AuthenticatedLayout>
</template>

<style scoped>
</style>
