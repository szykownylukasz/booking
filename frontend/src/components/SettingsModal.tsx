import React, { useState, useEffect } from 'react';
import { Dialog, DialogTitle, DialogContent, DialogActions, Button, TextField, Box, CircularProgress } from '@mui/material';
import { api } from '../services/api';

interface Settings {
  maxReservationsPerDay: number;
  pricePerDay: number;
}

interface SettingsModalProps {
  open: boolean;
  onClose: () => void;
}

export const SettingsModal: React.FC<SettingsModalProps> = ({ open, onClose }) => {
  const [settings, setSettings] = useState<Settings | null>(null);
  const [loading, setLoading] = useState(true);
  const [saving, setSaving] = useState(false);
  const [error, setError] = useState<string | null>(null);

  useEffect(() => {
    if (open) {
      loadSettings();
    }
  }, [open]);

  const loadSettings = async () => {
    try {
      setLoading(true);
      setError(null);
      const data = await api.getSettings();
      setSettings(data);
    } catch (error) {
      console.error('Failed to load settings:', error);
      setError('Failed to load settings. Please try again.');
    } finally {
      setLoading(false);
    }
  };

  const handleSave = async () => {
    if (!settings) return;

    try {
      setSaving(true);
      setError(null);
      await api.updateSettings(settings);
      onClose();
    } catch (error) {
      console.error('Failed to update settings:', error);
      setError('Failed to update settings. Please try again.');
    } finally {
      setSaving(false);
    }
  };

  if (!open) return null;

  return (
    <Dialog 
      open={open} 
      onClose={onClose}
      maxWidth="sm"
      fullWidth
      PaperProps={{
        sx: {
          maxWidth: '500px',
          p: 2,
          minHeight: '400px'
        }
      }}
    >
      <DialogTitle sx={{ pb: 3, fontSize: 25 }}>Global Settings</DialogTitle>
      <DialogContent sx={{ py: 3 }}>
        {loading ? (
          <Box sx={{ display: 'flex', justifyContent: 'center', alignItems: 'center', minHeight: '200px' }}>
            <CircularProgress />
          </Box>
        ) : settings ? (
          <Box sx={{ display: 'flex', flexDirection: 'column', gap: 4 }}>
            {error && (
              <Box sx={{ color: 'error.main', mb: 2 }}>
                {error}
              </Box>
            )}
            <TextField
              fullWidth
              label="Max Reservations Per Day"
              type="number"
              value={settings.maxReservationsPerDay}
              onChange={(e) => setSettings({ 
                ...settings, 
                maxReservationsPerDay: Math.max(1, parseInt(e.target.value) || 1)
              })}
              margin="normal"
              inputProps={{ min: 1 }}
              disabled={saving}
              sx={{ mb: 2 }}
            />
            <TextField
              fullWidth
              label="Price Per Day"
              type="number"
              value={settings.pricePerDay}
              onChange={(e) => setSettings({ 
                ...settings, 
                pricePerDay: Math.max(0, parseFloat(e.target.value) || 0)
              })}
              margin="normal"
              inputProps={{ min: 0, step: "0.01" }}
              disabled={saving}
            />
          </Box>
        ) : null}
      </DialogContent>
      <DialogActions sx={{ px: 3, pb: 3 }}>
        <Button onClick={onClose} disabled={loading || saving}>
          Cancel
        </Button>
        <Button 
          onClick={handleSave} 
          variant="contained" 
          disabled={loading || saving || !settings}
          startIcon={saving ? <CircularProgress size={20} /> : null}
        >
          {saving ? 'Saving...' : 'Save'}
        </Button>
      </DialogActions>
    </Dialog>
  );
};
