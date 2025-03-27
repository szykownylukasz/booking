import axios from 'axios';
import { ReservationRequest } from '../types/reservation';
import { authService } from './auth';
import { config } from '../config/env';

interface Settings {
    maxReservationsPerDay: number;
    pricePerDay: number;
}

const getAuthHeaders = () => {
    const token = authService.getToken();
    return token ? { Authorization: `Bearer ${token}` } : {};
};

export const api = {
    createReservation: async (data: ReservationRequest): Promise<void> => {
        try {
            console.log('Creating reservation with data:', data);
            await axios.post(`${config.API_URL}/reservations`, data, {
                headers: getAuthHeaders()
            });
        } catch (error: any) {
            console.error('Error creating reservation:', error.response?.data || error);
            throw error;
        }
    },

    getReservations: async (): Promise<any[]> => {
        try {
            const response = await axios.get(`${config.API_URL}/reservations`, {
                headers: getAuthHeaders()
            });
            console.log('Reservations response:', response.data);
            const reservations = response.data.data || response.data;
            if (!Array.isArray(reservations)) {
                console.error('Reservations is not an array:', reservations);
                return [];
            }
            return reservations;
        } catch (error: any) {
            console.error('Error fetching reservations:', error.response?.data || error);
            throw error;
        }
    },

    cancelReservation: async (id: number): Promise<void> => {
        try {
            await axios.post(`${config.API_URL}/reservations/${id}/cancel`, {}, {
                headers: getAuthHeaders()
            });
        } catch (error: any) {
            console.error('Error canceling reservation:', error.response?.data || error);
            throw error;
        }
    },

    getSettings: async (): Promise<Settings> => {
        try {
            const response = await axios.get(`${config.API_URL}/settings`, {
                headers: getAuthHeaders()
            });
            console.log('Settings response:', response);
            console.log('Settings data:', response.data);
            return response.data;
        } catch (error: any) {
            console.error('Error fetching settings:', error.response?.data || error);
            throw error;
        }
    },

    updateSettings: async (settings: Settings): Promise<void> => {
        try {
            console.log('Updating settings with:', settings);
            const response = await axios.put(`${config.API_URL}/settings`, settings, {
                headers: getAuthHeaders()
            });
            console.log('Update settings response:', response.data);
        } catch (error: any) {
            console.error('Error updating settings:', error.response?.data || error);
            throw error;
        }
    }
};
