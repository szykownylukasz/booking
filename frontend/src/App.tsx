import React, { useEffect, useState } from 'react';
import { BrowserRouter as Router } from 'react-router-dom';
import { Container, Typography } from '@mui/material';
import { ReservationList } from './components/ReservationList';
import { ReservationForm } from './components/ReservationForm';
import { api } from './services/api';
import { Reservation } from './types/reservation';

function App() {
  const [reservations, setReservations] = useState<Reservation[]>([]);
  const [loading, setLoading] = useState(true);

  const fetchReservations = async () => {
    try {
      setLoading(true);
      const data = await api.getReservations();
      setReservations(data);
    } catch (error) {
      console.error('Failed to fetch reservations:', error);
    } finally {
      setLoading(false);
    }
  };

  const handleCancel = async (id: number) => {
    try {
      await api.cancelReservation(id);
      await fetchReservations();
    } catch (error) {
      console.error('Failed to cancel reservation:', error);
    }
  };

  useEffect(() => {
    fetchReservations();
  }, []);

  return (
    <Router>
      <Container>
        <Typography variant="h4" component="h1" sx={{ mt: 4, mb: 4 }}>
          Booking System
        </Typography>
        <ReservationForm onSuccess={fetchReservations} />
        <ReservationList 
          reservations={reservations} 
          onCancel={handleCancel}
          loading={loading}
        />
      </Container>
    </Router>
  );
}

export default App;
