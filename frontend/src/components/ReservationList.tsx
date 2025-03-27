import React from 'react';
import { Table, TableBody, TableCell, TableContainer, TableHead, TableRow, Paper, Button, CircularProgress, Box } from '@mui/material';
import { Reservation } from '../types/reservation';

interface ReservationListProps {
  reservations: Reservation[];
  onCancel: (id: number) => void;
  loading: boolean;
  showUsername?: boolean;
}

export const ReservationList: React.FC<ReservationListProps> = ({ reservations, onCancel, loading, showUsername = false }) => {
  if (loading) {
    return (
      <Box display="flex" justifyContent="center" my={4}>
        <CircularProgress />
      </Box>
    );
  }

  if (reservations.length === 0) {
    return (
      <Box my={4}>
        No reservations found.
      </Box>
    );
  }

  return (
    <TableContainer component={Paper} sx={{ mt: 4 }}>
      <Table>
        <TableHead>
          <TableRow>
            {showUsername && <TableCell>User</TableCell>}
            <TableCell>Start Date</TableCell>
            <TableCell>End Date</TableCell>
            <TableCell>Total Price</TableCell>
            <TableCell>Status</TableCell>
            <TableCell>Actions</TableCell>
          </TableRow>
        </TableHead>
        <TableBody>
          {reservations.map((reservation) => (
            <TableRow key={reservation.id}>
              {showUsername && <TableCell>{reservation.userUsername}</TableCell>}
              <TableCell>{new Date(reservation.startDate).toLocaleDateString()}</TableCell>
              <TableCell>{new Date(reservation.endDate).toLocaleDateString()}</TableCell>
              <TableCell>${reservation.totalPrice}</TableCell>
              <TableCell>{reservation.status}</TableCell>
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
  );
};
