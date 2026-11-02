<script src="{{ asset('js/vendor/chart.umd.min.js') }}"></script>
<script>
(function () {
    const charts = @json($charts);
    const css = getComputedStyle(document.documentElement);
    const P = (css.getPropertyValue('--p') || '#1F3A5F').trim() || '#1F3A5F';
    const A = (css.getPropertyValue('--a') || '#B8860B').trim() || '#B8860B';
    const dark = document.documentElement.classList.contains('dark');
    const ink = dark ? '#C9D1DC' : '#374151', grid = dark ? 'rgba(255,255,255,.08)' : 'rgba(0,0,0,.07)';
    const col = c => c === 'P' ? P : c === 'A' ? A : c === 'P20' ? P + '33' : c;
    const fix = v => Array.isArray(v) ? v.map(col) : col(v);
    const rp = v => v >= 1e9 ? 'Rp ' + (v / 1e9).toLocaleString('id-ID', {maximumFractionDigits: 1}) + ' M' : v >= 1e6 ? 'Rp ' + (v / 1e6).toLocaleString('id-ID', {maximumFractionDigits: 1}) + ' jt' : 'Rp ' + Math.round(v).toLocaleString('id-ID');
    function render() {
        if (!window.Chart) return setTimeout(render, 50);
        Chart.defaults.color = ink;
        Chart.defaults.font.family = getComputedStyle(document.body).fontFamily;
        document.querySelectorAll('canvas[data-chart]').forEach(el => {
            const c = charts[el.dataset.chart];
            if (!c || el._chart) return;
            const o = c.opt || {};
            c.data.datasets.forEach(ds => { ['backgroundColor', 'borderColor'].forEach(k => { if (ds[k]) ds[k] = fix(ds[k]); }); });
            const money = o.money ? { callback: v => rp(v) } : {};
            const scales = c.type === 'doughnut' ? {} : {
                x: { stacked: !!o.stacked, grid: { color: grid }, ticks: o.horizontal ? money : {} , beginAtZero: true },
                y: { stacked: !!o.stacked, grid: { color: grid }, ticks: o.horizontal ? {} : money, beginAtZero: true },
            };
            el._chart = new Chart(el, {
                type: c.type, data: c.data,
                options: {
                    responsive: true, maintainAspectRatio: false, indexAxis: o.horizontal ? 'y' : 'x', scales,
                    plugins: {
                        legend: { display: c.data.datasets.length > 1 || c.type === 'doughnut', position: 'bottom', labels: { boxWidth: 12 } },
                        tooltip: o.money ? { callbacks: { label: ctx => (ctx.dataset.label ? ctx.dataset.label + ': ' : (ctx.label ? ctx.label + ': ' : '')) + rp(ctx.raw) } } : {},
                    },
                },
            });
        });
    }
    render();
})();
</script>
