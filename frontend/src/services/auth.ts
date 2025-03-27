import axios from 'axios';
import { LoginRequest, AuthResponse, User } from '../types/auth';
import { config } from '../config/env';

const API_URL = config.API_URL;

function parseJwt(token: string) {
    try {
        const base64Url = token.split('.')[1];
        const base64 = base64Url.replace(/-/g, '+').replace(/_/g, '/');
        const jsonPayload = decodeURIComponent(atob(base64).split('').map(function(c) {
            return '%' + ('00' + c.charCodeAt(0).toString(16)).slice(-2);
        }).join(''));

        return JSON.parse(jsonPayload);
    } catch (e) {
        console.error('Error parsing JWT:', e);
        return null;
    }
}

export const authService = {
    login: async (credentials: LoginRequest): Promise<AuthResponse> => {
        try {
            const response = await axios.post(`${API_URL}/login`, credentials);
            console.log('Login response:', response.data);
            
            if (!response.data.token) {
                throw new Error('Token not found in response');
            }

            const token = response.data.token;
            const decodedToken = parseJwt(token);
            
            if (!decodedToken) {
                throw new Error('Invalid token format');
            }

            const user: User = {
                username: decodedToken.username,
                roles: decodedToken.roles,
                id: 0 // ID is not needed in this case
            };
            
            console.log('Decoded token:', decodedToken);
            console.log('Created user object:', user);
            
            localStorage.setItem('token', token);
            localStorage.setItem('user', JSON.stringify(user));
            
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
