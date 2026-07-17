const API_URL = (import.meta.env.VITE_API_URL ?? '').replace(/\/$/, '');

let csrfToken: string | null = null;
let csrfRequest: Promise<string> | null = null;

function url(path: string): string {
    return `${API_URL}${path}`;
}

async function getCsrfToken(): Promise<string> {
    if (csrfToken) {
        return csrfToken;
    }

    csrfRequest ??= fetch(url('/api/csrf'), {
        credentials: 'include',
    })
        .then(async (response) => {
            const data = await response.json();

            if (!response.ok || typeof data.token !== 'string') {
                throw new Error(data.error || 'Не удалось получить CSRF-токен');
            }

            csrfToken = data.token;
            return data.token;
        })
        .finally(() => {
            csrfRequest = null;
        });

    return csrfRequest;
}

export async function apiFetch(
    path: string,
    options: RequestInit = {},
): Promise<Response> {
    const method = (options.method ?? 'GET').toUpperCase();
    const requiresCsrf = ['POST', 'PUT', 'PATCH', 'DELETE'].includes(method);

    async function send(): Promise<Response> {
        const headers = new Headers(options.headers);

        if (requiresCsrf) {
            headers.set('X-CSRF-Token', await getCsrfToken());
        }

        return fetch(url(path), {
            ...options,
            headers,
            credentials: 'include',
        });
    }

    let response = await send();

    if (requiresCsrf && response.status === 419) {
        csrfToken = null;
        response = await send();
    }

    return response;
}
