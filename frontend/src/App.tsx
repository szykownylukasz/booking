import React, { useEffect, useState } from 'react';
import { BrowserRouter as Router } from 'react-router-dom';
import { Container, Typography, Button, Box } from '@mui/material';
import { ReservationList } from './components/ReservationList';
import { ReservationForm } from './components/ReservationForm';
import { AuthHeader } from './components/AuthHeader';
import { AuthProvider } from './context/AuthContext';
import { useAuth } from './context/AuthContext';
import { api } from './services/api';
import { Reservation } from './types/reservation';
import { SettingsModal } from './components/SettingsModal';

const AppContent = () => {
  const [reservations, setReservations] = useState<Reservation[]>([]);
  const [loading, setLoading] = useState(true);
  const [settingsOpen, setSettingsOpen] = useState(false);
  const { user, isAdmin } = useAuth();

  const fetchReservations = async () => {
    try {
      setLoading(true);
      if (user) {
        const data = await api.getReservations();
        setReservations(data);
      } else {
        setReservations([]);
      }
    } catch (error) {
      console.error('Failed to fetch reservations:', error);
      setReservations([]);
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

  // Get reservations on login/logout
  useEffect(() => {
    fetchReservations();
  }, [user]); // Add user as dependency

  return (
    <Container>
      <AuthHeader />
      <Box sx={{ display: 'flex', justifyContent: 'space-between', alignItems: 'center', mt: 4, mb: 4 }}>
        <Typography variant="h4" component="h1">
          Booking System
        </Typography>
        {isAdmin && (
          <Button
            variant="contained"
            color="primary"
            onClick={() => setSettingsOpen(true)}
          >
            Settings
          </Button>
        )}
      </Box>
      {user && !isAdmin && <ReservationForm onSuccess={fetchReservations} />}
      {user && (
        <ReservationList 
          reservations={reservations} 
          onCancel={handleCancel}
          loading={loading}
          showUsername={isAdmin}
        />
      )}
      {isAdmin && (
        <SettingsModal
          open={settingsOpen}
          onClose={() => setSettingsOpen(false)}
        />
      )}
    </Container>
  );
};

function App() {
  return (
    <Router>
      <AuthProvider>
        <AppContent />
      </AuthProvider>
    </Router>
  );
}

export default App;
