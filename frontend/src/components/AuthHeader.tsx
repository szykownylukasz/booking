import React, { useState } from 'react';
import { Box, Button, TextField, Dialog, DialogTitle, DialogContent, DialogActions, Typography } from '@mui/material';
import { useAuth } from '../context/AuthContext';

export const AuthHeader: React.FC = () => {
    const { user, login, logout } = useAuth();
    const [open, setOpen] = useState(false);
    const [username, setUsername] = useState('');
    const [password, setPassword] = useState('');
    const [error, setError] = useState('');

    const handleLogin = async () => {
        try {
            await login(username, password);
            setOpen(false);
            setError('');
            setUsername('');
            setPassword('');
        } catch (err) {
            setError('Invalid credentials');
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

            <Dialog open={open} onClose={() => setOpen(false)}>
                <DialogTitle>Login</DialogTitle>
                <DialogContent>
                    <Box sx={{ display: 'flex', flexDirection: 'column', gap: 2, pt: 1 }}>
                        <TextField
                            label="Username"
                            value={username}
                            onChange={(e) => setUsername(e.target.value)}
                            fullWidth
                        />
                        <TextField
                            label="Password"
                            type="password"
                            value={password}
                            onChange={(e) => setPassword(e.target.value)}
                            onKeyPress={handleKeyPress}
                            fullWidth
                        />
                        {error && (
                            <Typography color="error">
                                {error}
                            </Typography>
                        )}
                    </Box>
                </DialogContent>
                <DialogActions>
                    <Button onClick={() => setOpen(false)}>Cancel</Button>
                    <Button onClick={handleLogin} variant="contained">
                        Login
                    </Button>
                </DialogActions>
            </Dialog>
        </Box>
    );
};
