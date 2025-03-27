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
      console.log('Loaded settings:', data);
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
      setError('Failed to save settings. Please try again.');
    } finally {
      setSaving(false);
    }
  };

  if (!open) return null;

  return (
    <Dialog open={open} onClose={onClose} maxWidth="sm" fullWidth>
      <DialogTitle>Global Settings</DialogTitle>
      <DialogContent>
        {loading ? (
          <Box sx={{ display: 'flex', justifyContent: 'center', p: 3 }}>
            <CircularProgress />
          </Box>
        ) : settings ? (
          <Box sx={{ pt: 2 }}>
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
      <DialogActions>
        <Button onClick={onClose} disabled={loading || saving}>
          Cancel
        </Button>
        <Button 
          onClick={handleSave} 
          variant="contained" 
          disabled={loading || saving || !settings}
          startIcon={saving ? <CircularProgress size={20} /> : null}
        >
          Save
        </Button>
      </DialogActions>
    </Dialog>
  );
};
