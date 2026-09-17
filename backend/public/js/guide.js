(() => {
    const tabs = Array.from(document.querySelectorAll('[role="tab"]'));
    function selectTab(tab, focus = false) {
        tabs.forEach(item => {
            const selected = item === tab;
            item.setAttribute('aria-selected', String(selected));
            item.tabIndex = selected ? 0 : -1;
            document.getElementById(item.getAttribute('aria-controls')).hidden = !selected;
        });
        if (focus) tab.focus();
    }
    tabs.forEach((tab, index) => {
        const panel = document.getElementById(tab.getAttribute('aria-controls'));
        panel.setAttribute('role', 'tabpanel');
        panel.setAttribute('aria-labelledby', tab.id);
        panel.tabIndex = 0;
        tab.addEventListener('click', () => selectTab(tab));
        tab.addEventListener('keydown', event => {
            let next;
            if (event.key === 'ArrowRight') next = (index + 1) % tabs.length;
            if (event.key === 'ArrowLeft') next = (index + tabs.length - 1) % tabs.length;
            if (event.key === 'Home') next = 0;
            if (event.key === 'End') next = tabs.length - 1;
            if (next !== undefined) {
                event.preventDefault();
                selectTab(tabs[next], true);
            }
        });
    });
    if (tabs.length) {
        selectTab(tabs[0]);
        document.querySelector('.install-tabs').hidden = false;
    }

    const copy = document.getElementById('copy-tag');
    const status = document.getElementById('copy-status');
    if (copy && navigator.clipboard?.writeText) {
        copy.hidden = false;
        copy.addEventListener('click', async () => {
            status.textContent = '';
            try {
                await navigator.clipboard.writeText(document.getElementById('example-tag').textContent.trim());
                status.textContent = 'Example copied. Replace it with your own site tag before installation.';
            } catch {
                status.textContent = 'Clipboard unavailable. Select the example text to copy it.';
            }
        });
    }

    const links = Array.from(document.querySelectorAll('.docs-nav a'));
    const sections = links.map(link => document.getElementById(link.hash.slice(1)));
    let scheduled = false;
    function updateSection() {
        let active = 0;
        sections.forEach((section, index) => {
            if (section.getBoundingClientRect().top <= 110) active = index;
        });
        links.forEach((link, index) => {
            if (index === active) link.setAttribute('aria-current', 'location');
            else link.removeAttribute('aria-current');
        });
        scheduled = false;
    }
    window.addEventListener('scroll', () => {
        if (!scheduled) {
            scheduled = true;
            requestAnimationFrame(updateSection);
        }
    }, { passive: true });
    window.addEventListener('resize', updateSection);
    document.fonts.ready.then(updateSection);
    updateSection();
})();
