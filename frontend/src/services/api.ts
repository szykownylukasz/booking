import axios from 'axios';
import { Reservation, ReservationRequest } from '../types/reservation';
import { authService } from './auth';

const API_URL = 'http://localhost:81/api';

const getAuthHeaders = () => {
    const token = authService.getToken();
    return token ? { Authorization: `Bearer ${token}` } : {};
};

// Initialize axios with token if exists
authService.initializeAuth();

export const api = {
    createReservation: async (data: ReservationRequest): Promise<void> => {
        try {
            console.log('Creating reservation with data:', data);
            await axios.post(`${API_URL}/reservations`, data, {
                headers: getAuthHeaders()
            });
        } catch (error: any) {
            console.error('Error creating reservation:', error.response?.data || error);
            throw error;
        }
    },

    getReservations: async (): Promise<Reservation[]> => {
        try {
            const response = await axios.get(`${API_URL}/reservations`, {
                headers: getAuthHeaders()
            });
            console.log('Reservations response:', response.data);
            const reservations = response.data.data || response.data;
            if (!Array.isArray(reservations)) {
                console.error('Reservations is not an array:', reservations);
                return [];
            }
            return reservations;
        } catch (error) {
            console.error('Error fetching reservations:', error);
            return [];
        }
    },

    cancelReservation: async (id: number): Promise<void> => {
        await axios.post(`${API_URL}/reservations/${id}/cancel`, null, {
            headers: getAuthHeaders()
        });
    }
};
