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

    // Se eliminó el auto-submit en evento 'change' para que los filtros requieran pulsar el botón de Filtrar/Buscar.

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

    const suggestionTimers = new WeakMap();
    const suggestionRequests = new WeakMap();

    const hideEquipmentSuggestions = (form) => {
        const input = form.querySelector('[data-equipment-search-input], input[name="q"], input[name="search"]');
        const suggestions = form.querySelector('[data-equipment-suggestions]');

        suggestions?.classList.add('hidden');
        input?.setAttribute('aria-expanded', 'false');
    };

    const submitEquipmentSearch = (form) => {
        hideEquipmentSuggestions(form);
        form.requestSubmit();
    };

    const renderEquipmentSuggestions = (form, query, items) => {
        const input = form.querySelector('[data-equipment-search-input], input[name="q"], input[name="search"]');
        const list = form.querySelector('[data-equipment-suggestions]');
        if (!input || !list) return;

        list.replaceChildren();

        if (!items.length) {
            const empty = document.createElement('div');
            empty.className = 'p-4 text-center text-xs text-on-surface-variant dark:text-inverse-primary';
            empty.setAttribute('role', 'status');
            const message = document.createElement('p');
            message.textContent = `No encontramos coincidencias cercanas para “${query}”. Prueba con un nombre, código o categoría diferente, o continúa con el botón de búsqueda.`;
            empty.append(message);
            list.append(empty);
        } else {
            const heading = document.createElement('p');
            heading.className = 'bg-surface-container-low px-4 py-2 font-mono text-[10px] font-bold uppercase tracking-wider text-secondary dark:bg-primary';
            heading.textContent = 'Sugerencias · selecciona un equipo o continúa la búsqueda';
            list.append(heading);
            const optionsList = document.createElement('div');
            optionsList.setAttribute('role', 'listbox');
            list.append(optionsList);

            items.forEach((item) => {
                const option = document.createElement('button');
                option.type = 'button';
                option.setAttribute('role', 'option');
                option.setAttribute('aria-selected', 'false');
                option.setAttribute('data-equipment-option', '');
                option.className = 'flex w-full items-center gap-3 px-4 py-3 text-left transition-colors hover:bg-surface-container-low focus:bg-surface-container-low focus:outline-none dark:hover:bg-primary dark:focus:bg-primary';

                const icon = document.createElement('span');
                icon.className = 'material-symbols-outlined flex h-9 w-9 shrink-0 items-center justify-center rounded-lg bg-surface-container text-secondary dark:bg-primary';
                icon.textContent = 'devices';

                const details = document.createElement('span');
                details.className = 'min-w-0 flex-1';
                const name = document.createElement('span');
                name.className = 'block truncate text-xs font-bold text-primary dark:text-surface';
                name.textContent = item.name;
                const metadata = document.createElement('span');
                metadata.className = 'flex flex-wrap items-center gap-x-2 text-[11px] text-on-surface-variant dark:text-inverse-primary';
                metadata.textContent = [item.brand, item.model, item.category_name].filter(Boolean).join(' · ');
                details.append(name, metadata);

                const code = document.createElement('span');
                code.className = 'shrink-0 rounded bg-surface-container-low px-2 py-1 font-mono text-[10px] text-on-surface-variant dark:bg-primary dark:text-inverse-primary';
                code.textContent = item.inventory_code || item.category_code || '';
                option.append(icon, details, code);

                if (item.is_similar) {
                    const badge = document.createElement('span');
                    badge.className = 'hidden rounded-full bg-secondary-container/30 px-2 py-1 font-mono text-[9px] font-semibold text-secondary dark:text-secondary-container sm:inline';
                    badge.textContent = 'Aproximada';
                    option.append(badge);
                }

                option.addEventListener('click', () => {
                    input.value = item.inventory_code || item.name;
                    submitEquipmentSearch(form);
                });
                optionsList.append(option);
            });

        }

        list.classList.remove('hidden');
        input.setAttribute('aria-expanded', 'true');
    };

    document.addEventListener('input', (event) => {
        const input = event.target.closest('form[data-equipment-search] input[name="q"], form[data-equipment-search] input[name="search"]');
        if (!input) return;

        const form = input.form;
        const query = input.value.trim();
        const clearButton = form.querySelector('[data-clear-equipment-search]');
        clearButton?.classList.toggle('hidden', query.length === 0);
        clearTimeout(suggestionTimers.get(form));
        suggestionRequests.get(form)?.abort();

        if (query.length < 2) {
            hideEquipmentSuggestions(form);
            return;
        }

        const timer = setTimeout(async () => {
            const controller = new AbortController();
            suggestionRequests.set(form, controller);

            try {
                const url = new URL(form.dataset.suggestionsUrl, window.location.href);
                url.searchParams.set('q', query);
                new FormData(form).forEach((value, name) => {
                    const inputName = input.name;
                    if (name !== inputName && typeof value === 'string' && value !== '') {
                        url.searchParams.set(name, value);
                    }
                });
                const response = await fetch(url, {
                    credentials: 'same-origin',
                    signal: controller.signal,
                    headers: { Accept: 'application/json' },
                });

                if (!response.ok) {
                    throw new Error(`La solicitud de sugerencias respondió ${response.status}.`);
                }

                const results = await response.json();
                if (suggestionRequests.get(form) === controller) {
                    renderEquipmentSuggestions(form, query, results);
                }
            } catch (error) {
                if (error.name !== 'AbortError') {
                    console.error('No fue posible cargar las sugerencias de equipos.', error);
                    hideEquipmentSuggestions(form);
                }
            }
        }, 220);

        suggestionTimers.set(form, timer);
    });

    document.addEventListener('click', (event) => {
        const clearButton = event.target.closest('[data-clear-equipment-search]');
        if (clearButton) {
            const form = clearButton.closest('form[data-equipment-search]');
            const input = form?.querySelector('input[name="q"], input[name="search"]');
            if (form && input) {
                input.value = '';
                submitEquipmentSearch(form);
            }
        }

        document.querySelectorAll('form[data-equipment-search]').forEach((form) => {
            if (!form.contains(event.target)) hideEquipmentSuggestions(form);
        });
    });

    document.addEventListener('keydown', (event) => {
        const form = event.target.closest('form[data-equipment-search]');
        if (!form) return;

        if (event.key === 'Escape') {
            hideEquipmentSuggestions(form);
            form.querySelector('input[name="q"], input[name="search"]')?.focus();
            return;
        }

        if (!['ArrowDown', 'ArrowUp'].includes(event.key)) return;
        const options = Array.from(form.querySelectorAll('[data-equipment-option]'));
        if (!options.length) return;

        event.preventDefault();
        const currentIndex = options.indexOf(document.activeElement);
        const nextIndex = event.key === 'ArrowDown'
            ? (currentIndex + 1) % options.length
            : (currentIndex <= 0 ? options.length - 1 : currentIndex - 1);
        options[nextIndex].focus();
    });
});
