import React, { useState } from 'react';
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

interface ReservationListProps {
  reservations: Reservation[];
  onCancel: (id: number) => void;
  loading: boolean;
  showUsername?: boolean;
}

const getStatusColor = (status: string) => {
  switch (status.toLowerCase()) {
    case 'active':
      return 'success.main';
    case 'cancelled':
      return 'error.main';
    default:
      return 'text.primary';
  }
};

export const ReservationList: React.FC<ReservationListProps> = ({ 
  reservations, 
  onCancel, 
  loading,
  showUsername = false 
}) => {
  const [cancellingId, setCancellingId] = useState<number | null>(null);

  const handleCancel = async (id: number) => {
    try {
      setCancellingId(id);
      await onCancel(id);
    } finally {
      setCancellingId(null);
    }
  };

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
    <Box sx={{ mt: 6 }}>
      <TableContainer 
        component={Paper} 
        sx={{ 
          boxShadow: 'none',
          border: '1px solid #e0e0e0',
          tableLayout: 'fixed',
          width: '100%'
        }}
      >
        <Table>
          <TableHead>
            <TableRow sx={{ backgroundColor: 'grey.100', fontSize: 12, textTransform: 'uppercase', fontWeight: 'bold' }}>
              <TableCell align="center" sx={{ fontWeight: 'bold', textTransform: 'uppercase', fontSize: 12 }}>Start Date</TableCell>
              <TableCell align="center" sx={{ fontWeight: 'bold', textTransform: 'uppercase', fontSize: 12 }}>End Date</TableCell>
              <TableCell align="center" sx={{ fontWeight: 'bold', textTransform: 'uppercase', fontSize: 12 }}>Total Price</TableCell>
              <TableCell align="center" sx={{ fontWeight: 'bold', textTransform: 'uppercase', fontSize: 12 }}>Status</TableCell>
              {showUsername && <TableCell align="center" sx={{ fontWeight: 'bold', textTransform: 'uppercase', fontSize: 12 }}>User</TableCell>}
              <TableCell align="center" sx={{ fontWeight: 'bold', textTransform: 'uppercase', fontSize: 12 }}>Actions</TableCell>
            </TableRow>
          </TableHead>
          <TableBody>
            {reservations.map((reservation) => (
              <TableRow key={reservation.id}>
                <TableCell align="center">{new Date(reservation.startDate).toLocaleDateString()}</TableCell>
                <TableCell align="center">{new Date(reservation.endDate).toLocaleDateString()}</TableCell>
                <TableCell align="center">${reservation.totalPrice}</TableCell>
                <TableCell align="center">
                  <Typography color={getStatusColor(reservation.status)}>
                    {reservation.status}
                  </Typography>
                </TableCell>
                {showUsername && <TableCell align="center">{reservation.userUsername || reservation.user?.username}</TableCell>}
                <TableCell align="center">
                  {reservation.status === 'active' && (
                    <Button
                      variant="outlined"
                      color="error"
                      onClick={() => handleCancel(reservation.id)}
                      disabled={cancellingId === reservation.id}
                    >
                      {cancellingId === reservation.id ? 'Cancelling...' : 'Cancel'}
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
