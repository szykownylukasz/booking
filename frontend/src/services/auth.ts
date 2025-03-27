import axios from 'axios';
import { LoginRequest, AuthResponse } from '../types/auth';

const API_URL = 'http://localhost:81/api';

export const authService = {
    login: async (credentials: LoginRequest): Promise<AuthResponse> => {
        try {
            const response = await axios.post(`${API_URL}/login`, credentials);
            console.log('Login response:', response.data);
            
            if (!response.data.token) {
                throw new Error('Token not found in response');
            }

            const { token } = response.data;
            const user = {
                id: 1,
                username: credentials.username,
                roles: response.data.roles || ['ROLE_USER']
            };

            // Dodajemy console.log do debugowania
            console.log('Saving user to localStorage:', user);
            
            localStorage.setItem('token', token);
            localStorage.setItem('user', JSON.stringify(user));
            
            // Weryfikujemy czy dane zostały zapisane
            console.log('Verification - localStorage user:', localStorage.getItem('user'));
            console.log('Verification - localStorage token:', localStorage.getItem('token'));

            axios.defaults.headers.common['Authorization'] = `Bearer ${token}`;
            return { token, user };
        } catch (error) {
            console.error('Login error:', error);
            throw error;
        }
    },

    logout: () => {
        localStorage.removeItem('token');
        localStorage.removeItem('user');
        delete axios.defaults.headers.common['Authorization'];
    },

    getCurrentUser: () => {
        try {
            const userStr = localStorage.getItem('user');
            console.log('getCurrentUser - userStr:', userStr);
            
            if (!userStr) {
                return null;
            }
            
            const user = JSON.parse(userStr);
            console.log('getCurrentUser - parsed user:', user);
            return user;
        } catch (error) {
            console.error('Error getting current user:', error);
            // W przypadku błędu usuwamy potencjalnie uszkodzone dane
            localStorage.removeItem('user');
            return null;
        }
    },

    getToken: () => {
        return localStorage.getItem('token');
    },

    initializeAuth: () => {
        const token = localStorage.getItem('token');
        if (token) {
            axios.defaults.headers.common['Authorization'] = `Bearer ${token}`;
        }
    }
};
