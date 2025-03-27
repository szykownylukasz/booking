import React from 'react';
import {
    Table,
    TableBody,
    TableCell,
    TableContainer,
    TableHead,
    TableRow,
    Paper,
    Button,
    CircularProgress,
    Box,
    Typography
} from '@mui/material';
import { Reservation } from '../types/reservation';

interface Props {
    reservations: Reservation[];
    onCancel: (id: number) => void;
    loading: boolean;
    showUsername?: boolean;
}

export const ReservationList: React.FC<Props> = ({ reservations = [], onCancel, loading, showUsername }) => {
    if (loading) {
        return (
            <Box sx={{ display: 'flex', justifyContent: 'center', p: 3 }}>
                <CircularProgress />
            </Box>
        );
    }

    // Zabezpieczenie przed undefined/null
    const reservationsList = Array.isArray(reservations) ? reservations : [];

    if (reservationsList.length === 0) {
        return (
            <Box sx={{ p: 3, textAlign: 'center' }}>
                <Typography>No reservations found.</Typography>
            </Box>
        );
    }

    return (
        <TableContainer component={Paper} sx={{ mt: 4 }}>
            <Table>
                <TableHead>
                    <TableRow>
                        {showUsername && <TableCell>Username</TableCell>}
                        <TableCell>ID</TableCell>
                        <TableCell>Start Date</TableCell>
                        <TableCell>End Date</TableCell>
                        <TableCell>Total Price</TableCell>
                        <TableCell>Status</TableCell>
                        <TableCell>Created At</TableCell>
                        <TableCell>Actions</TableCell>
                    </TableRow>
                </TableHead>
                <TableBody>
                    {reservationsList.map((reservation) => (
                        <TableRow key={reservation.id}>
                            {showUsername && <TableCell>{reservation.username}</TableCell>}
                            <TableCell>{reservation.id}</TableCell>
                            <TableCell>{new Date(reservation.startDate).toLocaleDateString()}</TableCell>
                            <TableCell>{new Date(reservation.endDate).toLocaleDateString()}</TableCell>
                            <TableCell>${reservation.totalPrice}</TableCell>
                            <TableCell>{reservation.status}</TableCell>
                            <TableCell>{reservation.createdAt}</TableCell>
                            <TableCell>
                                <Button 
                                    variant="outlined" 
                                    color="error" 
                                    onClick={() => onCancel(reservation.id)}
                                >
                                    Cancel
                                </Button>
                            </TableCell>
                        </TableRow>
                    ))}
                </TableBody>
            </Table>
        </TableContainer>
    );
};
