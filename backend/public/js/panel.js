(() => {
    let preference;
    try { preference = localStorage.getItem('berack-panel-theme'); } catch {}
    const prefersDark = window.matchMedia('(prefers-color-scheme: dark)');
    document.documentElement.dataset.theme = preference || (prefersDark.matches ? 'dark' : 'light');
    prefersDark.addEventListener('change', (event) => {
        if (!preference) document.documentElement.dataset.theme = event.matches ? 'dark' : 'light';
    });

    document.addEventListener('DOMContentLoaded', () => {
        const userTimeZone = Intl.DateTimeFormat().resolvedOptions().timeZone || 'Local time';
        const formatLocalTime = (element) => {
            const date = new Date(element.dateTime);
            if (Number.isNaN(date.getTime())) return;
            const options = element.classList.contains('visit-time')
                ? {month: '2-digit', day: '2-digit', hour: '2-digit', minute: '2-digit', hour12: false}
                : element.closest('.step-head')
                    ? {hour: '2-digit', minute: '2-digit', second: '2-digit', hour12: false}
                    : {year: 'numeric', month: '2-digit', day: '2-digit', hour: '2-digit', minute: '2-digit', hour12: false};
            element.textContent = new Intl.DateTimeFormat('en-US', {...options, timeZone: userTimeZone}).format(date);
            element.title = userTimeZone;
        };
        document.querySelectorAll('.local-time').forEach(formatLocalTime);
        document.querySelectorAll('[data-timezone-label]').forEach((element) => { element.textContent = userTimeZone; });
        document.querySelector('[data-theme-toggle]')?.addEventListener('click', () => {
            preference = document.documentElement.dataset.theme === 'dark' ? 'light' : 'dark';
            document.documentElement.dataset.theme = preference;
            try { localStorage.setItem('berack-panel-theme', preference); } catch {}
        });
        const menuButton = document.querySelector('[data-menu-toggle]');
        const sidebar = document.getElementById('sidebar');
        const mobile = window.matchMedia('(max-width: 900px)');
        const syncNavigation = () => {
            if (!sidebar) return;
            const hidden = mobile.matches && !document.body.classList.contains('nav-open');
            sidebar.inert = hidden;
            sidebar.setAttribute('aria-hidden', String(hidden));
        };
        const closeMenu = () => {
            document.body.classList.remove('nav-open');
            menuButton?.setAttribute('aria-expanded', 'false');
            syncNavigation();
        };
        menuButton?.addEventListener('click', () => {
            const open = document.body.classList.toggle('nav-open');
            menuButton.setAttribute('aria-expanded', String(open));
            syncNavigation();
            if (open) sidebar.querySelector('a')?.focus();
        });
        mobile.addEventListener('change', () => { closeMenu(); });
        syncNavigation();
        document.querySelector('[data-menu-close]')?.addEventListener('click', closeMenu);
        document.addEventListener('keydown', (event) => {
            if (!document.body.classList.contains('nav-open')) return;
            if (event.key === 'Escape') { closeMenu(); menuButton?.focus(); }
            if (event.key === 'Tab') {
                const controls = [...sidebar.querySelectorAll('a, button, select')];
                const first = controls[0], last = controls.at(-1);
                if (event.shiftKey && document.activeElement === first) { event.preventDefault(); last.focus(); }
                if (!event.shiftKey && document.activeElement === last) { event.preventDefault(); first.focus(); }
            }
        });
        const list = document.querySelector('.visit-list');
        const selected = list?.querySelector('.is-selected');
        const revealSelection = () => {
            if (!selected) return;
            list.scrollTop += selected.getBoundingClientRect().top - list.getBoundingClientRect().top - (list.querySelector('.list-heading')?.offsetHeight || 0) - 1;
        };
        document.fonts.ready.then(revealSelection);
        window.addEventListener('resize', () => requestAnimationFrame(revealSelection));
        document.querySelector('[data-site-switch]')?.addEventListener('change', (event) => {
            window.location.assign(event.target.value);
        });
        document.querySelectorAll('[data-confirm]').forEach((form) => form.addEventListener('submit', (event) => {
            if (!window.confirm(form.dataset.confirm)) event.preventDefault();
        }));
        let toastTimeout;
        document.querySelectorAll('[data-copy]').forEach((button) => button.addEventListener('click', async () => {
            const target = document.getElementById(button.dataset.copy);
            const toast = document.getElementById('toast');
            try {
                await navigator.clipboard.writeText(target.textContent);
                toast.textContent = toast.dataset.success;
            } catch {
                const selection = window.getSelection();
                const range = document.createRange();
                range.selectNodeContents(target);
                selection.removeAllRanges();
                selection.addRange(range);
                toast.textContent = toast.dataset.failure;
            }
            clearTimeout(toastTimeout);
            toast.hidden = false;
            toastTimeout = setTimeout(() => { toast.hidden = true; }, 3000);
        }));
    });
})();
