import React, { useState } from 'react';
import { Box, Button, Stack, Alert } from '@mui/material';
import { DatePicker } from '@mui/x-date-pickers/DatePicker';
import { LocalizationProvider } from '@mui/x-date-pickers/LocalizationProvider';
import { AdapterDateFns } from '@mui/x-date-pickers/AdapterDateFns';
import { api } from '../services/api';
import { format, addDays } from 'date-fns';

export const ReservationForm: React.FC<{ onSuccess: () => void }> = ({ onSuccess }) => {
    const [startDate, setStartDate] = useState<Date | null>(null);
    const [endDate, setEndDate] = useState<Date | null>(null);
    const [error, setError] = useState<string>('');
    const [loading, setLoading] = useState(false);

    const handleSubmit = async (e: React.FormEvent) => {
        e.preventDefault();
        if (!startDate || !endDate) {
            setError('Please select both dates');
            return;
        }

        try {
            setLoading(true);
            await api.createReservation({
                startDate: format(startDate, 'yyyy-MM-dd'),
                endDate: format(endDate, 'yyyy-MM-dd')
            });
            setStartDate(null);
            setEndDate(null);
            setError('');
            onSuccess();
        } catch (err: any) {
            setError(err.response?.data?.message || 'Failed to create reservation');
        } finally {
            setLoading(false);
        }
    };

    const handleStartDateChange = (newValue: Date | null) => {
        setStartDate(newValue);
        if (endDate && newValue && endDate <= newValue) {
            setEndDate(null);
        }
    };

    return (
        <LocalizationProvider dateAdapter={AdapterDateFns}>
            <Box component="form" onSubmit={handleSubmit} sx={{ maxWidth: 400, margin: 'auto', mt: 4 }}>
                <Stack spacing={3}>
                    <DatePicker
                        label="Start Date"
                        value={startDate}
                        onChange={handleStartDateChange}
                        disablePast
                    />
                    <DatePicker
                        label="End Date"
                        value={endDate}
                        onChange={(newValue) => setEndDate(newValue)}
                        disablePast
                        minDate={startDate ? addDays(startDate, 1) : undefined}
                    />
                    {error && <Alert severity="error">{error}</Alert>}
                    <Button
                        variant="contained"
                        type="submit"
                        disabled={loading || !startDate || !endDate}
                    >
                        {loading ? 'Creating...' : 'Create Reservation'}
                    </Button>
                </Stack>
            </Box>
        </LocalizationProvider>
    );
};
