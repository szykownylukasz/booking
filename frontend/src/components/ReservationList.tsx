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
    Box,
    Typography
} from '@mui/material';
import { Reservation } from '../types/reservation';

interface Props {
    reservations: Reservation[];
    onCancel: (id: number) => void;
    loading: boolean;
}

export const ReservationList: React.FC<Props> = ({ reservations, onCancel, loading }) => {
    if (loading) {
        return <Typography>Loading reservations...</Typography>;
    }

    if (reservations.length === 0) {
        return <Typography>No reservations found.</Typography>;
    }

    return (
        <Box sx={{ mt: 4 }}>
            <TableContainer component={Paper}>
                <Table>
                    <TableHead>
                        <TableRow>
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
                        {reservations.map((reservation) => (
                            <TableRow key={reservation.id}>
                                <TableCell>{reservation.id}</TableCell>
                                <TableCell>{reservation.startDate}</TableCell>
                                <TableCell>{reservation.endDate}</TableCell>
                                <TableCell>${reservation.totalPrice}</TableCell>
                                <TableCell>{reservation.status}</TableCell>
                                <TableCell>{reservation.createdAt}</TableCell>
                                <TableCell>
                                    {reservation.status === 'active' && (
                                        <Button
                                            variant="outlined"
                                            color="error"
                                            onClick={() => onCancel(reservation.id)}
                                        >
                                            Cancel
                                        </Button>
                                    )}
                                </TableCell>
                            </TableRow>
                        ))}
                    </TableBody>
                </Table>
            </TableContainer>
        </Box>
    );
};
