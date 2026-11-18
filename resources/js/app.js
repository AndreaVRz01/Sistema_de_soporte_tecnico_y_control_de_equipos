import './bootstrap';

window.addEventListener('DOMContentLoaded', () => {
    const pendingRequests = new WeakMap();

    const syncFilterForms = (url) => {
        document.querySelectorAll('form[data-ajax-filter]').forEach((form) => {
            form.querySelectorAll('[name]').forEach((control) => {
                const values = url.searchParams.getAll(control.name);

                if (control.type === 'checkbox' || control.type === 'radio') {
                    control.checked = values.includes(control.value);
                } else if (control.tagName === 'SELECT' && control.multiple) {
                    Array.from(control.options).forEach((option) => {
                        option.selected = values.includes(option.value);
                    });
                } else {
                    control.value = values[0] || '';
                }
            });
        });
    };

    const updateFilterResults = async (url, target, pushHistory = true) => {
        pendingRequests.get(target)?.abort();

        const controller = new AbortController();
        pendingRequests.set(target, controller);
        const privateMain = document.querySelector('#private-main');
        const usePrivateScroll = privateMain && privateMain.scrollHeight > privateMain.clientHeight;
        const scrollPosition = usePrivateScroll ? privateMain.scrollTop : window.scrollY;
        const scrollLeft = usePrivateScroll ? privateMain.scrollLeft : window.scrollX;

        target.setAttribute('aria-busy', 'true');

        try {
            const response = await fetch(url, {
                credentials: 'same-origin',
                signal: controller.signal,
                headers: {
                    'X-Requested-With': 'XMLHttpRequest',
                    'Accept': 'text/html',
                },
            });

            if (!response.ok) {
                throw new Error(`La solicitud de filtros respondió ${response.status}.`);
            }

            const html = await response.text();
            const doc = new DOMParser().parseFromString(html, 'text/html');
            const fragment = doc.querySelector(`#${target.id}`);

            if (!fragment) {
                throw new Error(`No se encontró el contenedor #${target.id} en la respuesta.`);
            }

            target.innerHTML = fragment.innerHTML;

            if (pushHistory) {
                history.pushState({}, '', url);
            }
            syncFilterForms(url);

            requestAnimationFrame(() => {
                if (usePrivateScroll) {
                    privateMain.scrollTo({ left: scrollLeft, top: scrollPosition, behavior: 'instant' });
                } else {
                    window.scrollTo({ left: scrollLeft, top: scrollPosition, behavior: 'instant' });
                }
            });
        } catch (error) {
            if (error.name === 'AbortError') {
                return;
            }

            console.error('No fue posible actualizar los filtros sin recargar la página.', error);
            window.location.assign(url);
        } finally {
            if (pendingRequests.get(target) === controller) {
                pendingRequests.delete(target);
                target.removeAttribute('aria-busy');
            }
        }
    };

    document.addEventListener('submit', (event) => {
        const form = event.target.closest('form[data-ajax-filter]');
        if (!form) {
            return;
        }

        const target = document.querySelector(form.dataset.filterTarget || '#filter-results');
        if (!target?.id) {
            return;
        }

        event.preventDefault();

        const url = new URL(form.action || window.location.href, window.location.href);
        url.search = '';

        new FormData(form).forEach((value, name) => {
            if (typeof value === 'string' && value !== '') {
                url.searchParams.append(name, value);
            }
        });

        updateFilterResults(url, target);
    });

    document.addEventListener('change', (event) => {
        if (event.target.matches('form[data-ajax-filter] select')) {
            event.target.form.requestSubmit();
        }
    });

    document.addEventListener('click', (event) => {
        const link = event.target.closest('a[href]');
        if (!link || link.target || link.hasAttribute('download') || link.getAttribute('href').startsWith('#')) {
            return;
        }

        const url = new URL(link.href, window.location.href);
        const targetSelector = link.dataset.filterTarget;
        const resultsContainer = link.closest('[data-ajax-filter-results]');
        const isSamePageFilter = resultsContainer && url.pathname === window.location.pathname;

        if (url.origin !== window.location.origin || (!targetSelector && !isSamePageFilter)) {
            return;
        }

        const target = document.querySelector(targetSelector || `#${resultsContainer.id}`);
        if (!target?.id) {
            return;
        }

        event.preventDefault();
        updateFilterResults(url, target);
    });

    window.addEventListener('popstate', () => {
        const target = document.querySelector('[data-ajax-filter-results]');
        if (target?.id) {
            updateFilterResults(new URL(window.location.href), target, false);
        }
    });
});
