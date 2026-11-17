import './bootstrap';

window.addEventListener('DOMContentLoaded', () => {
    document.querySelectorAll('form[data-ajax-filter]').forEach((form) => {
        form.addEventListener('submit', async (event) => {
            const targetSelector = form.dataset.filterTarget || '#filter-results';
            const target = document.querySelector(targetSelector);

            if (!target) {
                return;
            }

            event.preventDefault();

            const formData = new FormData(form);
            const params = new URLSearchParams(formData).toString();
            const action = form.getAttribute('action') || window.location.pathname;
            const url = params ? `${action}?${params}` : action;

            try {
                const response = await fetch(url, {
                    headers: {
                        'X-Requested-With': 'XMLHttpRequest',
                        'Accept': 'text/html',
                    },
                });

                if (!response.ok) {
                    form.submit();
                    return;
                }

                const html = await response.text();
                const parser = new DOMParser();
                const doc = parser.parseFromString(html, 'text/html');
                const fragment = doc.querySelector(targetSelector);

                if (fragment) {
                    target.innerHTML = fragment.innerHTML;
                    history.replaceState({}, '', url);
                } else {
                    form.submit();
                }
            } catch (error) {
                form.submit();
            }
        });
    });
});
