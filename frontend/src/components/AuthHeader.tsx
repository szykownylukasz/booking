import React, { useState } from 'react';
import { Box, Button, TextField, Dialog, DialogTitle, DialogContent, DialogActions, Typography, CircularProgress } from '@mui/material';
import { useAuth } from '../context/AuthContext';

export const AuthHeader: React.FC = () => {
    const { user, login, logout } = useAuth();
    const [open, setOpen] = useState(false);
    const [username, setUsername] = useState('');
    const [password, setPassword] = useState('');
    const [error, setError] = useState('');
    const [isLoading, setIsLoading] = useState(false);

    const handleLogin = async () => {
        try {
            setIsLoading(true);
            await login(username, password);
            setOpen(false);
            setError('');
            setUsername('');
            setPassword('');
        } catch (err) {
            setError('Invalid credentials');
        } finally {
            setIsLoading(false);
        }
    };

    const handleKeyPress = (event: React.KeyboardEvent) => {
        if (event.key === 'Enter') {
            handleLogin();
        }
    };

    return (
        <Box sx={{ display: 'flex', justifyContent: 'flex-end', alignItems: 'center', p: 2 }}>
            {user ? (
                <Box sx={{ display: 'flex', alignItems: 'center', gap: 2 }}>
                    <Typography>
                        Welcome, {user.username}
                    </Typography>
                    <Button variant="outlined" onClick={logout}>
                        Logout
                    </Button>
                </Box>
            ) : (
                <Button variant="contained" onClick={() => setOpen(true)}>
                    Login
                </Button>
            )}

            <Dialog 
                open={open} 
                onClose={() => setOpen(false)}
                PaperProps={{
                    sx: {
                        minWidth: '400px',
                        boxShadow: 1,
                        p: 2
                    }
                }}
            >
                <DialogTitle sx={{ pb: 3, fontSize: 25 }}>Login</DialogTitle>
                <DialogContent>
                    <Box sx={{ display: 'flex', flexDirection: 'column', gap: 3, pt: 1 }}>
                        <TextField
                            label="Username"
                            value={username}
                            onChange={(e) => setUsername(e.target.value)}
                            fullWidth
                            disabled={isLoading}
                        />
                        <TextField
                            label="Password"
                            type="password"
                            value={password}
                            onChange={(e) => setPassword(e.target.value)}
                            onKeyPress={handleKeyPress}
                            fullWidth
                            disabled={isLoading}
                        />
                        {error && (
                            <Typography color="error">
                                {error}
                            </Typography>
                        )}
                    </Box>
                </DialogContent>
                <DialogActions sx={{ px: 3, pb: 2 }}>
                    <Button onClick={() => setOpen(false)} disabled={isLoading}>Cancel</Button>
                    <Button 
                        onClick={handleLogin} 
                        variant="contained" 
                        disabled={isLoading}
                        startIcon={isLoading ? <CircularProgress size={20} /> : null}
                    >
                        Login
                    </Button>
                </DialogActions>
            </Dialog>
        </Box>
    );
};
