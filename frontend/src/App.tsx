import React, { useEffect, useState } from 'react';
import { BrowserRouter as Router } from 'react-router-dom';
import { Container, Typography, Button, Box, Paper, Grid } from '@mui/material';
import { ReservationList } from './components/ReservationList';
import { ReservationForm } from './components/ReservationForm';
import { AuthHeader } from './components/AuthHeader';
import { AuthProvider } from './context/AuthContext';
import { useAuth } from './context/AuthContext';
import { api } from './services/api';
import { Reservation } from './types/reservation';
import { SettingsModal } from './components/SettingsModal';

const WelcomeMessage = () => (
  <Paper sx={{ p: 4, mt: 4, textAlign: 'center' }}>
    <Typography variant="h4" component="h2" gutterBottom>
      Welcome to the Booking System
    </Typography>
    <Typography variant="body1" paragraph>
      This is a demonstration of a booking management system. Please log in to explore its features:
    </Typography>
    <Box sx={{ display: 'flex', flexDirection: 'column', gap: 2, maxWidth: 600, mx: 'auto' }}>
      <Typography variant="body1" sx={{ fontWeight: 'bold' }}>
        Available accounts:
      </Typography>
      <Grid container spacing={2}>
        <Grid item xs={12} md={4}>
          <Box sx={{ p: 2, border: 1, borderColor: 'divider', borderRadius: 1 }}>
            <Typography variant="subtitle1" color="primary" sx={{ fontWeight: 'bold' }}>
              Admin
            </Typography>
            <Typography variant="body2">
              Username: admin<br />
              Password: admin
            </Typography>
            <Typography variant="body2" color="text.secondary" sx={{ mt: 1 }}>
              Can view all reservations and manage global settings
            </Typography>
          </Box>
        </Grid>
        <Grid item xs={12} md={4}>
          <Box sx={{ p: 2, border: 1, borderColor: 'divider', borderRadius: 1 }}>
            <Typography variant="subtitle1" color="primary" sx={{ fontWeight: 'bold' }}>
              Regular User 1
            </Typography>
            <Typography variant="body2">
              Username: user1<br />
              Password: user1
            </Typography>
            <Typography variant="body2" color="text.secondary" sx={{ mt: 1 }}>
              Can manage their own reservations
            </Typography>
          </Box>
        </Grid>
        <Grid item xs={12} md={4}>
          <Box sx={{ p: 2, border: 1, borderColor: 'divider', borderRadius: 1 }}>
            <Typography variant="subtitle1" color="primary" sx={{ fontWeight: 'bold' }}>
              Regular User 2
            </Typography>
            <Typography variant="body2">
              Username: user2<br />
              Password: user2
            </Typography>
            <Typography variant="body2" color="text.secondary" sx={{ mt: 1 }}>
              Can manage their own reservations
            </Typography>
          </Box>
        </Grid>
      </Grid>
    </Box>
  </Paper>
);

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

  useEffect(() => {
    fetchReservations();
  }, [user]);

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
      {user ? (
        <>
          {!isAdmin && <ReservationForm onSuccess={fetchReservations} />}
          <ReservationList 
            reservations={reservations} 
            onCancel={handleCancel}
            loading={loading}
            showUsername={isAdmin}
          />
        </>
      ) : (
        <WelcomeMessage />
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
