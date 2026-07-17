import { useState } from 'react';
import { useNavigate } from 'react-router-dom';
import { apiFetch } from '../api/client';

type User = {
    email: string;
};

type ProfilePageProps = {
    user: User | null;
    setUser: (user: User | null) => void;
};

function ProfilePage({ user, setUser }: ProfilePageProps) {
    const navigate = useNavigate();

    const [error, setError] = useState('');

    function logoutHandler() {
        setError('');

        apiFetch('/api/logout', {
            method: 'POST',
        })
            .then(async (response) => {
                if (!response.ok) {
                    let message;
                    try {
                        const json = await response.json();
                        message = json.error;
                    } catch {
                    }
                    throw new Error(message);
                }
                setUser(null);
                navigate('/login');
            })
            .catch((error: Error) => {
                setError(error.message);
            });
    }

    return (
        <main className="min-h-[calc(100vh-56px)] bg-blue-50 px-4 py-8 text-slate-900">
            <section className="mx-auto max-w-2xl">
            <h1 className="text-xl font-semibold">Профиль</h1>

            {error && (
                <p className="mt-4 rounded-md border border-red-200 bg-red-50 px-3 py-2 text-sm text-red-700">
                    {error}
                </p>
            )}
            {user && (
                <div className="mt-4 rounded-xl border border-slate-200 bg-white p-5 shadow-sm">
                    <p className="mt-2 text-sm text-slate-600">
                        <strong>Email:</strong> {user.email}
                    </p>

                    <button
                        type="button"
                        onClick={logoutHandler}
                        className="mt-5 cursor-pointer rounded-md bg-red-600 px-3 py-2 text-sm font-medium text-white transition hover:bg-red-700"
                    >
                        Выйти
                    </button>
                </div>
            )}
            </section>
        </main>
    );
}

export default ProfilePage;
