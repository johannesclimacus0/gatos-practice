import { Link } from 'react-router-dom';

type User = {
    id: number;
    email: string;
};

type NavbarProps = {
    user: User | null;
    setUser: (user: User | null) => void;
};
function Navbar({ user }: NavbarProps) {
    return (
        <header className="sticky top-0 z-50 border-b border-slate-800 bg-slate-900">
            <div className="mx-auto flex h-14 max-w-5xl items-center justify-between px-4">
                <Link to="/" className="flex items-center gap-2.5">
                    <div>
                        <img
                            src="/cat.svg"
                            className="h-8 w-8 rounded-md object-cover"
                            alt="Gatos"
                        />
                    </div>

                    <div>
                        <p className="text-base font-semibold leading-none text-white">
                            Gatos
                        </p>
                        <p className="mt-0.5 text-[11px] text-slate-500">Cat API</p>
                    </div>
                </Link>

                <div className="hidden items-center gap-3 md:flex">
                    {user ? (
                        <Link
                            to="/profile"
                            className="rounded-md bg-yellow-600 px-3 py-1.5 text-sm font-medium text-white transition hover:bg-yellow-700"
                        >
                            Профиль
                        </Link>
                    ) : (
                        <>
                            <Link
                                to="/login"
                                className="rounded-md px-3 py-1.5 text-sm font-medium text-slate-300 transition hover:bg-white/10 hover:text-white"
                            >
                                Войти
                            </Link>

                            <Link
                                to="/register"
                                className="rounded-md bg-yellow-600 px-3 py-1.5 text-sm font-medium text-white transition hover:bg-yellow-700"
                            >
                                Регистрация
                            </Link>
                        </>
                    )}
                </div>

                {user ? (
                    <Link
                        to="/profile"
                        className="rounded-md bg-yellow-600 px-3 py-1.5 text-sm font-medium text-white md:hidden"
                    >
                        Профиль
                    </Link>
                ) : (
                    <Link
                        to="/login"
                        className="rounded-md bg-yellow-600 px-3 py-1.5 text-sm font-medium text-white md:hidden"
                    >
                        Войти
                    </Link>
                )}
            </div>
        </header>
    );
}

export default Navbar;
