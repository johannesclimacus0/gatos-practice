import { useEffect, useState } from 'react';
import { apiFetch } from '../api/client';

type Cat = {
    id: number;
    name: string;
    lang: string;
};

type ApiResponse = {
    cats: Cat[];
};

type CatFactsResponse = {
    facts: string[];
};

type MutationResponse = {
    message: string;
    cat: Cat;
};

type ErrorResponse = {
    error?: string;
    redirectTo?: string;
};

type NewCat = {
    name: string;
    lang: string;
};

function CatsPage() {
    const [cats, setCats] = useState<Cat[]>([]);
    const [name, setName] = useState('');
    const [lang, setLang] = useState('');
    const [success, setSuccess] = useState('');
    const [error, setError] = useState('');
    const [isLoading, setIsLoading] = useState(true);
    const [isCreating, setIsCreating] = useState(false);
    const [pageFacts, setPageFacts] = useState<string[]>([]);
    const [pageFactsError, setPageFactsError] = useState('');
    const [arePageFactsLoading, setArePageFactsLoading] = useState(true);

    const [selectedCat, setSelectedCat] = useState<Cat | null>(null);
    const [editName, setEditName] = useState('');
    const [editLang, setEditLang] = useState('');
    const [isSaving, setIsSaving] = useState(false);

    function openEditModal(cat: Cat) {
        setSelectedCat(cat);
        setEditName(cat.name);
        setEditLang(cat.lang);
        setError('');
        setSuccess('');
    }

    function closeEditModal() {
        setSelectedCat(null);
        setEditName('');
        setEditLang('');
    }

    function requestCats(): Promise<Cat[]> {
        return apiFetch('/api/cats', {
            method: 'GET',
        })
            .then(async (response) => {
                const json = await response.json();

                if (!response.ok) {
                    const errorData = json as ErrorResponse;
                    throw new Error(errorData.error || 'Не удалось загрузить список');
                }

                return (json as ApiResponse).cats ?? [];
            });
    }

    function requestPageFacts(): Promise<string[]> {
        return apiFetch('/api/catfacts/1/180', {
            method: 'GET',
        })
            .then(async (response) => {
                const json = await response.json();

                if (!response.ok) {
                    const errorData = json as ErrorResponse;
                    throw new Error(errorData.error || 'Не удалось загрузить факт');
                }

                return (json as CatFactsResponse).facts ?? [];
            });
    }

    function loadPageFacts() {
        setArePageFactsLoading(true);
        setPageFactsError('');

        requestPageFacts()
            .then(setPageFacts)
            .catch((error: Error) => {
                setPageFactsError(error.message);
                setPageFacts([]);
            })
            .finally(() => {
                setArePageFactsLoading(false);
            });
    }

    function submitHandler(event: React.FormEvent<HTMLFormElement>) {
        event.preventDefault();

        setSuccess('');
        setError('');
        setIsCreating(true);

        const newCat: NewCat = {
            name: name.trim(),
            lang: lang.trim(),
        };

        apiFetch('/api/cats', {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify(newCat),
        })
            .then(async (response) => {
                const json = await response.json();

                if (!response.ok) {
                    const errorData = json as ErrorResponse;
                    throw new Error(errorData.error || 'Не удалось добавить запись');
                }

                return json as MutationResponse;
            })
            .then((json) => {
                setSuccess(json.message || 'Запись добавлена');
                setError('');
                setName('');
                setLang('');

                setCats((currentCats) => [json.cat, ...currentCats]);
            })
            .catch((error: Error) => {
                setError(error.message);
            })
            .finally(() => {
                setIsCreating(false);
            });
    }

    function updateSelectedCat(event: React.FormEvent<HTMLFormElement>) {
        event.preventDefault();

        if (!selectedCat) {
            return;
        }

        setIsSaving(true);
        setError('');
        setSuccess('');

        apiFetch(`/api/cats/${selectedCat.id}`, {
            method: 'PATCH',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify({
                name: editName.trim(),
                lang: editLang.trim(),
            }),
        })
            .then(async (response) => {
                const json = await response.json();

                if (!response.ok) {
                    const errorData = json as ErrorResponse;
                    throw new Error(errorData.error || 'Не удалось обновить запись');
                }

                return json as MutationResponse;
            })
            .then((json) => {
                setCats((currentCats) =>
                    currentCats.map((cat) =>
                        cat.id === json.cat.id ? json.cat : cat
                    )
                );

                setSuccess(json.message || 'Запись обновлена');
                closeEditModal();
            })
            .catch((error: Error) => {
                setError(error.message);
            })
            .finally(() => {
                setIsSaving(false);
            });
    }

    function deleteSelectedCat() {
        if (!selectedCat) {
            return;
        }

        const confirmed = window.confirm('Удалить эту запись?');

        if (!confirmed) {
            return;
        }

        setIsSaving(true);
        setError('');
        setSuccess('');

        apiFetch(`/api/cats/${selectedCat.id}`, {
            method: 'DELETE',
        })
            .then(async (response) => {
                const json = await response.json();

                if (!response.ok) {
                    const errorData = json as ErrorResponse;
                    throw new Error(errorData.error || 'Не удалось удалить запись');
                }

                return json as MutationResponse;
            })
            .then((json) => {
                setCats((currentCats) =>
                    currentCats.filter((cat) => cat.id !== json.cat.id)
                );

                setSuccess(json.message || 'Запись удалена');
                closeEditModal();
            })
            .catch((error: Error) => {
                setError(error.message);
            })
            .finally(() => {
                setIsSaving(false);
            });
    }

    useEffect(() => {
        requestCats()
            .then(setCats)
            .catch((error: Error) => {
                setError(error.message);
                setCats([]);
            })
            .finally(() => setIsLoading(false));

        requestPageFacts()
            .then(setPageFacts)
            .catch((error: Error) => {
                setPageFactsError(error.message);
                setPageFacts([]);
            })
            .finally(() => setArePageFactsLoading(false));
    }, []);

    return (
        <main className="min-h-[calc(100vh-56px)] bg-blue-50 px-4 py-6 text-slate-900">
            <section className="mx-auto max-w-3xl">
                <div className="rounded-xl border border-slate-200 bg-white p-5 shadow-sm sm:p-6">
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

                    <form onSubmit={submitHandler} className="mb-5 grid gap-4 sm:grid-cols-[1fr_1fr_auto] sm:items-end">
                        <div>
                            <label htmlFor="name" className="mb-1.5 block text-sm font-medium text-slate-700">
                                Имя
                            </label>

                            <input
                                id="name"
                                type="text"
                                value={name}
                                onChange={(event) => setName(event.target.value)}
                                placeholder="Kisa"
                                className="w-full rounded-md border border-slate-300 bg-white px-3 py-2 text-sm text-slate-900 outline-none transition placeholder:text-slate-400 focus:border-yellow-600 focus:ring-2 focus:ring-yellow-100"
                            />
                        </div>

                        <div>
                            <label htmlFor="lang" className="mb-1.5 block text-sm font-medium text-slate-700">
                                Язык
                            </label>

                            <input
                                id="lang"
                                type="text"
                                value={lang}
                                onChange={(event) => setLang(event.target.value)}
                                placeholder="meow"
                                className="w-full rounded-md border border-slate-300 bg-white px-3 py-2 text-sm text-slate-900 outline-none transition placeholder:text-slate-400 focus:border-yellow-600 focus:ring-2 focus:ring-yellow-100"
                            />
                        </div>

                        <button
                            type="submit"
                            disabled={isCreating}
                            className="rounded-md bg-yellow-600 px-4 py-2 text-sm font-semibold text-white transition hover:bg-yellow-700 disabled:cursor-not-allowed disabled:bg-slate-400"
                        >
                            {isCreating ? 'Добавляю...' : 'Добавить запись'}
                        </button>
                    </form>

                    <section className="mb-5 rounded-lg border border-yellow-200 bg-yellow-50 p-4">
                        <div className="mb-3 flex items-center justify-between gap-3">
                            <h2 className="text-lg font-bold text-slate-900">
                                Факт
                            </h2>

                            <button
                                type="button"
                                onClick={loadPageFacts}
                                disabled={arePageFactsLoading}
                                className="rounded-md bg-slate-900 px-3 py-1.5 text-xs font-medium text-white transition hover:bg-slate-700 disabled:cursor-not-allowed disabled:bg-slate-400"
                            >
                                {arePageFactsLoading ? 'Загружаю...' : 'Обновить'}
                            </button>
                        </div>

                        {arePageFactsLoading && (
                            <p className="text-sm text-slate-500">Загружаю факт...</p>
                        )}

                        {pageFactsError && (
                            <p className="text-sm font-medium text-red-700">{pageFactsError}</p>
                        )}

                        {!arePageFactsLoading && !pageFactsError && pageFacts.length > 0 && (
                            <p className="rounded-xl bg-white px-4 py-3 text-sm text-slate-700">
                                {pageFacts[0]}
                            </p>
                        )}
                    </section>

                    <h2 className="mb-3 text-base font-semibold">Список записей</h2>

                    {isLoading && <p className="text-slate-500">Загружаю список...</p>}

                    {!isLoading && cats.length === 0 && !error && (
                        <p className="text-slate-500">Записей пока нет.</p>
                    )}

                    {!isLoading && cats.length > 0 && (
                        <div className="space-y-3">
                            {cats.map((cat) => (
                                <button
                                    key={cat.id}
                                    type="button"
                                    onClick={() => openEditModal(cat)}
                                    className="w-full rounded-lg border border-slate-200 bg-slate-50 px-3 py-2 text-left transition hover:border-yellow-500 hover:bg-yellow-50"
                                >
                                    <p className="font-semibold text-slate-900">
                                        #{cat.id} {cat.name}
                                    </p>

                                    <p className="text-sm text-slate-500">
                                        {cat.lang}
                                    </p>
                                </button>
                            ))}
                        </div>
                    )}
                </div>
            </section>

            {selectedCat && (
                <div className="fixed inset-0 z-50 flex items-center justify-center bg-slate-950/50 px-4">
                    <div className="w-full max-w-sm rounded-xl border border-slate-200 bg-white p-5 shadow-xl">
                        <div className="mb-4 flex items-start justify-between gap-4">
                            <div>
                                <h2 className="text-lg font-semibold text-slate-900">
                                    Редактирование
                                </h2>

                                <p className="mt-1 text-sm text-slate-500">
                                    ID: {selectedCat.id}
                                </p>
                            </div>

                            <button
                                type="button"
                                onClick={closeEditModal}
                                className="rounded-md px-2 py-1 text-base font-medium text-slate-500 hover:bg-slate-100 hover:text-slate-900"
                            >
                                x
                            </button>
                        </div>

                        <form onSubmit={updateSelectedCat} className="space-y-4">
                            <div>
                                <label htmlFor="edit-name" className="mb-1.5 block text-sm font-medium text-slate-700">
                                    Имя
                                </label>

                                <input
                                    id="edit-name"
                                    type="text"
                                    value={editName}
                                    onChange={(event) => setEditName(event.target.value)}
                                    className="w-full rounded-md border border-slate-300 bg-white px-3 py-2 text-sm text-slate-900 outline-none transition focus:border-yellow-600 focus:ring-2 focus:ring-yellow-100"
                                />
                            </div>

                            <div>
                                <label htmlFor="edit-lang" className="mb-1.5 block text-sm font-medium text-slate-700">
                                    Язык
                                </label>

                                <input
                                    id="edit-lang"
                                    type="text"
                                    value={editLang}
                                    onChange={(event) => setEditLang(event.target.value)}
                                    className="w-full rounded-md border border-slate-300 bg-white px-3 py-2 text-sm text-slate-900 outline-none transition focus:border-yellow-600 focus:ring-2 focus:ring-yellow-100"
                                />
                            </div>

                            <div className="flex flex-wrap gap-2">
                                <button
                                    type="submit"
                                    disabled={isSaving}
                                    className="flex-1 rounded-md bg-yellow-600 px-3 py-2 text-sm font-medium text-white transition hover:bg-yellow-700 disabled:cursor-not-allowed disabled:bg-slate-400"
                                >
                                    {isSaving ? 'Сохраняю...' : 'Сохранить'}
                                </button>

                                <button
                                    type="button"
                                    onClick={deleteSelectedCat}
                                    disabled={isSaving}
                                    className="rounded-md bg-red-600 px-3 py-2 text-sm font-medium text-white transition hover:bg-red-700 disabled:cursor-not-allowed disabled:bg-slate-400"
                                >
                                    Удалить
                                </button>

                                <button
                                    type="button"
                                    onClick={closeEditModal}
                                    className="rounded-md border border-slate-300 px-3 py-2 text-sm font-medium text-slate-700 transition hover:bg-slate-50"
                                >
                                    Отмена
                                </button>
                            </div>
                        </form>
                    </div>
                </div>
            )}
        </main>
    );
}

export default CatsPage;
