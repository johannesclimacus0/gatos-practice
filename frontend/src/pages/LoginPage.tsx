import { useState } from 'react';
import { Link, useNavigate } from 'react-router-dom';
import { apiFetch } from '../api/client';

type User = {
    id: number;
    email: string;
};

type LoginPageProps = {
    onLogin: (user: User) => void;
};

type LoginResponse = {
    user: User;
    redirectTo: string;
    message: string;
};

function LoginPage({ onLogin }: LoginPageProps) {
    const [email, setEmail] = useState('');
    const [password, setPassword] = useState('');
    const [remember_me, setRememberMe] = useState<boolean>(false);
    const [success, setSuccess] = useState('');
    const [error, setError] = useState('');
    const navigate = useNavigate();

    function submitHandler(event: React.FormEvent) {
        event.preventDefault();

        setSuccess('');
        setError('');

        const loginData = {
            email,
            password,
            remember_me,
        };

        apiFetch('/api/login', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
            },
            body: JSON.stringify(loginData),
        })
            .then(async (response) => {
                const json = await response.json();

                if (!response.ok) {
                    throw new Error(json.error);
                }

                return json as LoginResponse;
            })
            .then((json) => {
                setSuccess(json.message);
                onLogin(json.user);
                navigate(json.redirectTo || '/profile');
            })
            .catch((error: Error) => {
                setError(error.message);
            });
    }

    return (
        <main className="min-h-[calc(100vh-56px)] bg-blue-50 text-slate-900">
            <section className="flex min-h-[calc(100vh-56px)] w-full items-center justify-center px-4 py-8">
                <div className="w-full max-w-sm">
                    <div className="rounded-xl border border-slate-200 bg-white p-6 shadow-sm">
                        <div className="mb-5">
                            <h2 className=" text-xl font-semibold tracking-tight text-slate-900">
                                Вход в аккаунт
                            </h2>
                        </div>

                        {success && (
                            <p className="mb-4 rounded-md border border-green-200 bg-green-50 px-3 py-2 text-sm text-green-700">
                                {success}
                            </p>
                        )}
                        {error && (
                            <p className="mb-4 rounded-md border border-red-200 bg-red-50 px-3 py-2 text-sm text-red-700">
                                {error}
                            </p>
                        )}

                        <form onSubmit={submitHandler} className="space-y-4">
                            <div>
                                <label
                                    htmlFor="email"
                                    className="mb-1.5 block text-sm font-medium text-slate-700"
                                >
                                    Email
                                </label>

                                <input
                                    id="email"
                                    type="email"
                                    value={email}
                                    onChange={(event) => setEmail(event.target.value)}
                                    placeholder="email@example.com"
                                    className="w-full rounded-md border border-slate-300 bg-white px-3 py-2 text-sm text-slate-900 outline-none transition placeholder:text-slate-400 focus:border-yellow-600 focus:ring-2 focus:ring-yellow-100"
                                />
                            </div>

                            <div>
                                <div className="mb-1.5 flex items-center justify-between">
                                    <label
                                        htmlFor="password"
                                        className="block text-sm font-medium text-slate-700"
                                    >
                                        Пароль
                                    </label>
                                </div>

                                <input
                                    id="password"
                                    type="password"
                                    value={password}
                                    onChange={(event) => setPassword(event.target.value)}
                                    placeholder="********"
                                    className="w-full rounded-md border border-slate-300 bg-white px-3 py-2 text-sm text-slate-900 outline-none transition placeholder:text-slate-400 focus:border-yellow-600 focus:ring-2 focus:ring-yellow-100"
                                />
                            </div>

                            <div className="flex items-center justify-between">
                                <label className="flex items-center gap-2 text-sm text-slate-600">
                                    <input
                                        type="checkbox"
                                        checked={remember_me}
                                        className="h-4 w-4 rounded border-slate-300 text-indigo-600 focus:ring-indigo-500"
                                        onChange={(event) => setRememberMe(event.target.checked)}
                                    />
                                    Запомнить меня
                                </label>
                            </div>

                            <button
                                type="submit"
                                className="w-full rounded-md bg-yellow-600 px-4 py-2 text-sm font-semibold text-white hover:bg-yellow-700"
                            >
                                Войти
                            </button>
                        </form>

                        <div className="my-5 flex items-center gap-3">
                            <div className="h-px flex-1 bg-slate-200" />
                            <span className="text-sm text-slate-400">или</span>
                            <div className="h-px flex-1 bg-slate-200" />
                        </div>

                        <p className="text-center text-sm text-slate-500">
                            Нет аккаунта?{' '}
                            <Link
                                to="/register"
                                className="font-semibold text-yellow-600 transition hover:text-yellow-700"
                            >
                                Зарегистрироваться
                            </Link>
                        </p>
                    </div>
                </div>
            </section>
        </main>
    );
}

export default LoginPage;
