import { useEffect, useState } from 'react';
import { Route, Routes } from 'react-router-dom';
import Navbar from './components/Navbar';

import CatsPage from './pages/CatsPage';

import LoginPage from './pages/LoginPage';
import RegisterPage from './pages/RegisterPage';

import GuestRoute from './components/GuestRoute';
import ProtectedRoute from './components/ProtectedRoute';

import ProfilePage from './pages/ProfilePage';
import { apiFetch } from './api/client';

type User = {
    id: number;
    email: string;
};

type MeResponse = {
    user: User;
};

function App() {
    const [user, setUser] = useState<User | null>(null);
    const [authChecked, setAuthChecked] = useState(false);

    useEffect(() => {
        apiFetch('/api/me')
            .then(async (response) => {
                const json = await response.json();

                if (!response.ok) {
                    setUser(null);
                    return;
                }

                const data = json as MeResponse;
                setUser(data.user);
            })
            .catch(() => {
                setUser(null);
            })
            .finally(() => {
                setAuthChecked(true);
            });
    }, []);

    if (!authChecked) {
        return (
            <main className="flex min-h-screen items-center justify-center bg-blue-50 text-slate-900">
                <p>Проверяем авторизацию...</p>
            </main>
        );
    }

    return (
        <>
            <Navbar user={user} setUser={setUser} />
            <Routes>
                <Route
                    path="/"
                    element={
                        <ProtectedRoute user={user}>
                            <CatsPage />
                        </ProtectedRoute>
                    }
                />
                <Route
                    path="/login"
                    element={
                        <GuestRoute user={user}>
                            <LoginPage onLogin={setUser} />
                        </GuestRoute>
                    }
                />
                <Route
                    path="/register"
                    element={
                        <GuestRoute user={user}>
                            <RegisterPage onLogin={setUser} />
                        </GuestRoute>
                    }
                />
                <Route
                    path="/profile"
                    element={
                        <ProtectedRoute user={user}>
                            <ProfilePage user={user} setUser={setUser} />
                        </ProtectedRoute>
                    }
                />
                <Route path="*" element={<h1>404 — страница не найдена</h1>} />
            </Routes>
        </>
    );
}

export default App;
