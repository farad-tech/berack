(() => {
    const canvas = document.getElementById('journey-canvas');
    const context = canvas?.getContext('2d');
    if (!context) return;
    const visits = {
        shop: [['Home', '/', '00:00'], ['Collection', '/collection', '00:18'], ['Product', '/products/linen', '00:46'], ['Cart', '/cart', '01:12'], ['Product', '/products/linen', '01:34']],
        docs: [['Home', '/', '00:00'], ['Guide', '/guide', '00:09'], ['Installation', '/guide/install', '00:31'], ['API reference', '/reference', '02:05'], ['Guide', '/guide', '02:48']],
    };
    let selected = 'shop';
    let width = 0, height = 0, phase = 0, frame, previousTime = 0;
    let visible = true;
    const reducedMotion = window.matchMedia('(prefers-reduced-motion: reduce)');
    function draw() {
        context.clearRect(0, 0, width, height);
        const mobile = width < 530;
        const nodes = visits[selected];
        const margin = mobile ? 48 : 74;
        const points = nodes.map((_, index) => mobile
            ? { x: margin + (index < 3 ? index : 4 - index) * (width - margin * 2) / 2, y: index < 3 ? 40 : 140 }
            : { x: margin + index * (width - margin * 2) / 4, y: 62 });
        context.lineWidth = 1.5;
        points.slice(1).forEach((point, index) => {
            const previous = points[index];
            context.strokeStyle = '#b5c6ba';
            context.beginPath(); context.moveTo(previous.x, previous.y); context.lineTo(point.x, point.y); context.stroke();
            if (!reducedMotion.matches) {
                const t = (phase + index * .17) % 1;
                context.fillStyle = '#648c49'; context.beginPath();
                context.arc(previous.x + (point.x - previous.x) * t, previous.y + (point.y - previous.y) * t, 2.8, 0, Math.PI * 2); context.fill();
            }
        });
        nodes.forEach(([name, path, time], index) => {
            const { x, y } = points[index];
            const last = index === nodes.length - 1;
            context.fillStyle = last ? '#f0d2c2' : index === 0 ? '#c7f36b' : '#fff';
            context.strokeStyle = last ? '#d19c80' : '#b5c6ba';
            context.beginPath(); context.arc(x, y, 17, 0, Math.PI * 2); context.fill(); context.stroke();
            context.textAlign = 'center'; context.textBaseline = 'middle'; context.fillStyle = '#243b2e';
            context.font = '600 11px Vazirmatn, sans-serif'; context.fillText(String(index + 1).padStart(2, '0'), x, y + 1);
            context.font = `600 ${mobile ? 10 : 12}px Vazirmatn, sans-serif`; context.fillText(name, x, y + 32);
            context.fillStyle = '#63776a'; context.font = `${mobile ? 8 : 10}px Vazirmatn, sans-serif`; context.fillText(path, x, y + 49);
            context.fillText(last ? 'LAST RECORDED' : index === 0 ? 'ENTRY PAGE' : time, x, y - 29);
        });
    }
    function resize() {
        const rect = canvas.getBoundingClientRect(); width = rect.width; height = rect.height;
        const ratio = Math.min(window.devicePixelRatio || 1, 2);
        canvas.width = Math.round(width * ratio); canvas.height = Math.round(height * ratio);
        context.setTransform(ratio, 0, 0, ratio, 0, 0); draw();
    }
    function animate(time) {
        phase = (phase + Math.min(time - previousTime, 50) / 3600) % 1; previousTime = time;
        draw(); frame = requestAnimationFrame(animate);
    }
    function updateAnimation() {
        cancelAnimationFrame(frame);
        if (!reducedMotion.matches && visible && !document.hidden) frame = requestAnimationFrame(animate);
        else draw();
    }
    document.querySelectorAll('[data-visit]').forEach(button => button.addEventListener('click', () => {
        selected = button.dataset.visit;
        document.querySelectorAll('[data-visit]').forEach(option => option.setAttribute('aria-pressed', String(option === button)));
        canvas.setAttribute('aria-label', `Example ${selected} visit: ${visits[selected].map(node => node[0]).join(', then ')}. Last recorded page: ${visits[selected].at(-1)[0]}.`);
        document.getElementById('visit-summary').textContent = `${selected === 'shop' ? 'Shop' : 'Docs'} / 5 recorded steps / 1 visit / 1 tab`;
        phase = 0; draw();
    }));
    new ResizeObserver(resize).observe(canvas);
    new IntersectionObserver(entries => { visible = entries[0].isIntersecting; updateAnimation(); }).observe(canvas);
    document.addEventListener('visibilitychange', updateAnimation);
    reducedMotion.addEventListener('change', updateAnimation);
    document.fonts.ready.then(resize);
    resize();
})();
