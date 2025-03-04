<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import {Head, useForm} from '@inertiajs/vue3';
import { useToast } from 'vue-toastification';
import { Inertia } from '@inertiajs/inertia';
import {onMounted} from "vue";
import $ from 'jquery';

const toast = useToast();

const form = useForm({
    title: '',
    description: '',
    appointment_date: '',
    time: '',
    location: '',
    guests: [],
    reminder: '',
});

const isWeekday = (date) => {
    const day = new Date(date).getDay();
    return day !== 0 && day !== 6; // 0 is Sunday, 6 is Saturday
};

const submit = () => {
    if (!isWeekday(form.start_time)) {
        toast.error('Please select a weekday for the appointment.');
        return;
    }
    form.post(route('appointment.store'), {
        onSuccess: (res) => {
            if (res.props.error) {
                toast.error(res.props.error);
            } else {
                toast.success('Appointment created successfully.');
                Inertia.visit(route('appointment.index'));
            }
        },
        onError: (errors) => {
            if (errors.appointment_date) {
                toast.error(errors.appointment_date);
            }
            if (errors.response.status === 422) {
                toast.error('Validation error: Please check your input.');
            } else {
                console.error('error', errors);
            }
        }
    });
};

const openDatePicker = (id) => {
    document.getElementById(id).showPicker();
};

const addGuest = () => {
    form.guests.push({ name: '', email: '' });
};

const removeGuest = (index) => {
    form.guests.splice(index, 1);
};
</script>

<template>
    <Head title="Create - Appointment"/>

    <AuthenticatedLayout>
        <template #header>
            <h2
                class="text-xl font-semibold leading-tight text-gray-800"
            >
                Create Appointment
            </h2>
        </template>

        <div class="py-12">
            <div class="mx-auto max-w-7xl sm:px-6 lg:px-8">
                <div
                    class="overflow-hidden bg-white shadow-sm sm:rounded-lg"
                >
                    <div class="p-6 text-gray-900">
                        <form @submit.prevent="submit">
                            <div class="mb-4">
                                <label for="title" class="block text-sm font-medium text-gray-700">Title</label>
                                <input v-model="form.title" type="text" id="title" class="mt-1 block w-full" required/>
                            </div>

                            <textarea v-model="form.description" id="description" class="mt-1 block w-full"></textarea>

                            <div class="mb-4">
                                <label for="start_time" class="block text-sm font-medium text-gray-700 picker">Appointment Date</label>
                                <input v-model="form.appointment_date" type="datetime-local" id="start_time"
                                       class="mt-1 block w-full" required @click="openDatePicker('start_time')"/>
                            </div>

                            <div class="mb-4">
                                <label for="reminder" class="block text-sm font-medium text-gray-700 picker">Reminder</label>
                                <input v-model="form.reminder" type="datetime-local" id="reminder"
                                       class="mt-1 block w-full" @click="openDatePicker('reminder')"/>
                            </div>

                            <div v-for="(guest, index) in form.guests" :key="index" class="mb-4 flex space-x-4 items-center">
                                <div class="flex-1">
                                    <label :for="'guest_name_' + index" class="block text-sm font-medium text-gray-700">Guest Name</label>
                                    <input v-model="guest.name" :id="'guest_name_' + index" type="text" class="mt-1 block w-full" required/>
                                </div>
                                <div class="flex-1">
                                    <label :for="'guest_email_' + index" class="block text-sm font-medium text-gray-700">Guest Email</label>
                                    <input v-model="guest.email" :id="'guest_email_' + index" type="email" class="mt-1 block w-full" required/>
                                </div>
                                <button type="button" @click="removeGuest(index)" class="text-red-500 hover:text-red-700 flex items-center justify-center text-2xl mt-5">
                                    &times;
                                </button>
                            </div>

                            <div class="mb-4">
                                <button type="button" @click="addGuest" class="inline-flex items-center px-4 py-2 bg-green-500 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-green-700 active:bg-green-900 focus:outline-none focus:border-green-900 focus:ring ring-green-300 disabled:opacity-25 transition ease-in-out duration-150">
                                    Add Guest
                                </button>
                            </div>

                            <div class="flex items-center justify-end">
                                <button type="submit"
                                        class="ml-3 inline-flex items-center px-4 py-2 bg-blue-500 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-blue-700 active:bg-blue-900 focus:outline-none focus:border-blue-900 focus:ring ring-blue-300 disabled:opacity-25 transition ease-in-out duration-150">
                                    Create
                                </button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </AuthenticatedLayout>
</template>

<style scoped>

</style>
