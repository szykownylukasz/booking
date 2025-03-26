import axios from 'axios';
import { Reservation, ReservationRequest } from '../types/reservation';

const API_URL = 'http://localhost:81/api';

export const api = {
    createReservation: async (data: ReservationRequest): Promise<Reservation> => {
        const response = await axios.post(`${API_URL}/reservations`, data);
        return response.data.data;
    },

    getReservations: async (): Promise<Reservation[]> => {
        const response = await axios.get(`${API_URL}/reservations`);
        return response.data.data;
    },

    cancelReservation: async (id: number): Promise<void> => {
        await axios.post(`${API_URL}/reservations/${id}/cancel`);
    }
};
