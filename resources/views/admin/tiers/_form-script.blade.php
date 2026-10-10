{{-- Included at the end of admin/tiers/_form.blade.php (uses $ladder and $isEdit from there) --}}
<script>
    (() => {
        const nameEl = document.getElementById('tier_name');
        const form = nameEl?.closest('form');

        if (!form || form.dataset.tierFormReady === 'true') return;
        form.dataset.tierFormReady = 'true';

        const tiers = @json($ladder);
        const isEdit = @json($isEdit);
        const find = id => form.querySelector('#' + id);

        const ptsEl = find('min_points');
        const disEl = find('discount_percent');
        const colorEl = find('tier_color');
        const swatches = form.querySelectorAll('[data-tier-swatch]');

        const fmt = value => Number(value).toLocaleString('en-US');

        function validColor(value) {
            return /^#[0-9a-f]{6}$/i.test(String(value))
                ? String(value).toUpperCase()
                : '#71717A';
        }

        function contrastColor(hex) {
            const rgb = [1, 3, 5].map(offset =>
                parseInt(hex.slice(offset, offset + 2), 16) / 255
            );

            const linear = rgb.map(value =>
                value <= 0.04045
                    ? value / 12.92
                    : Math.pow((value + 0.055) / 1.055, 2.4)
            );

            const luminance = linear[0] * 0.2126
                + linear[1] * 0.7152
                + linear[2] * 0.0722;

            return luminance > 0.179 ? '#18181B' : '#FFFFFF';
        }

        function read() {
            const points = ptsEl.value === ''
                ? null
                : Number(ptsEl.value);

            const discount = Number(disEl.value);

            return {
                name: nameEl.value.trim(),
                points: points !== null && Number.isFinite(points)
                    ? points
                    : null,
                discount: Number.isFinite(discount) ? discount : 0,
                color: validColor(colorEl.value),
            };
        }

        function render() {
            const data = read();

            find('pv-name').textContent = data.name || 'Tier name';
            find('pv-discount').textContent = String(
                Number(data.discount.toFixed(2))
            );
            find('pv-color').textContent = data.color;
            find('tier-color-code').textContent = data.color;

            const preview = find('tier-live-preview');
            preview.style.setProperty('--tier-color', data.color);
            preview.style.setProperty(
                '--tier-ink',
                contrastColor(data.color)
            );

            // The red ring is drawn by CSS from aria-pressed (see the <style> in _form).
            swatches.forEach(button => {
                const selected =
                    validColor(button.dataset.tierSwatch) === data.color;

                button.setAttribute('aria-pressed', String(selected));
            });

            // Highlight the quick-pick chips that match what is currently typed in
            form.querySelectorAll('.js-dis').forEach(button => {
                const on = disEl.value !== ''
                    && Number(button.dataset.value) === data.discount;
                button.setAttribute('aria-pressed', String(on));
            });

            form.querySelectorAll('.js-pts').forEach(button => {
                const on = data.points !== null
                    && Number(button.dataset.value) === data.points;
                button.setAttribute('aria-pressed', String(on));
            });

            form.querySelectorAll('.js-preset').forEach(button => {
                const on = data.name !== ''
                    && button.dataset.name.toLowerCase() === data.name.toLowerCase()
                    && (data.points === null || Number(button.dataset.min) === data.points);
                button.setAttribute('aria-pressed', String(on));
            });

            const sameName = data.name && tiers.find(tier =>
                tier.name.trim().toLowerCase() === data.name.toLowerCase()
            );

            const samePoints = data.points !== null && tiers.find(tier =>
                tier.min === data.points
            );

            const rows = tiers.map(tier => ({
                ...tier,
                isMine: false,
            }));

            if (data.points !== null) {
                rows.push({
                    id: 'mine',
                    name: data.name || (isEdit ? 'This tier' : 'New tier'),
                    min: data.points,
                    discount: data.discount,
                    color: data.color,
                    isMine: true,
                });
            }

            rows.sort((a, b) =>
                a.min - b.min || Number(a.isMine) - Number(b.isMine)
            );

            rows.forEach((row, index) => {
                row.max = rows[index + 1]
                    ? rows[index + 1].min - 1
                    : null;
            });

            const rangeOf = row => row.max === null
                ? fmt(row.min) + '+'
                : fmt(row.min) + ' – ' + fmt(Math.max(row.max, row.min));

            const mine = rows.find(row => row.isMine);

            find('pv-range').textContent = mine ? rangeOf(mine) : '—';

            const position = find('pv-position');

            if (!mine) {
                position.textContent = 'Enter minimum points to see where this tier fits.';
            } else {
                const index = rows.indexOf(mine);
                const below = rows[index - 1];
                const above = rows[index + 1];

                position.textContent = !below && !above
                    ? 'The only tier in the system'
                    : !above
                        ? 'Highest tier — above ' + below.name
                        : !below
                            ? 'Lowest tier — below ' + above.name
                            : 'Between ' + below.name + ' and ' + above.name;

                if (below && data.discount < below.discount) {
                    position.textContent +=
                        ' · Discount is lower than the tier below (' + below.discount + '%)';
                }
            }

            // Build the ladder with textContent so tier names are always rendered safely
            const ladder = find('ladder');
            const prevScroll = ladder.scrollTop;
            let mineItem = null;
            ladder.replaceChildren();

            rows.forEach(row => {
                const item = document.createElement('li');
                const color = validColor(row.color);

                item.className =
                    'flex items-center justify-between gap-3 rounded-lg border px-3 py-2 text-sm';

                item.style.setProperty('--tier-color', color);

                if (row.isMine) {
                    item.style.backgroundColor = color;
                    item.style.borderColor = color;
                    item.style.color = contrastColor(color);
                } else {
                    item.classList.add(
                        'border-zinc-200',
                        'bg-zinc-50',
                        'dark:border-zinc-700',
                        'dark:bg-zinc-800/60'
                    );
                }

                const info = document.createElement('div');
                info.className = 'min-w-0';

                const title = document.createElement('div');
                title.className = 'flex items-center gap-2 font-semibold';

                const dot = document.createElement('span');
                dot.className = 'h-2 w-2 shrink-0 rounded-full';
                dot.style.backgroundColor = row.isMine
                    ? contrastColor(color)
                    : color;

                const name = document.createElement('span');
                name.className = 'truncate';
                name.textContent = row.name;

                title.append(dot, name);

                if (row.isMine) {
                    const tag = document.createElement('span');
                    tag.className =
                        'shrink-0 text-[10px] font-bold uppercase opacity-80';
                    tag.textContent = isEdit ? 'Editing' : 'New';
                    title.append(tag);
                }

                const range = document.createElement('div');
                range.className = row.isMine
                    ? 'mt-1 text-xs opacity-80'
                    : 'mt-1 text-xs text-zinc-500';
                range.textContent = rangeOf(row) + ' pts';

                const discount = document.createElement('span');
                discount.className = 'shrink-0 font-bold';
                discount.textContent = row.discount + '%';

                info.append(title, range);
                item.append(info, discount);
                ladder.append(item);

                if (row.isMine) mineItem = item;
            });

            // The ladder scrolls inside its own box: keep the tier being edited in view
            ladder.scrollTop = mineItem
                ? Math.max(0, mineItem.offsetTop - (ladder.clientHeight - mineItem.offsetHeight) / 2)
                : prevScroll;

            if (rows.length === 0) {
                const empty = document.createElement('li');
                empty.className = 'text-xs text-zinc-500';
                empty.textContent = 'No tiers yet.';
                ladder.append(empty);
            }

            const message = sameName
                ? 'A tier named "' + sameName.name + '" already exists.'
                : samePoints
                    ? samePoints.name + ' already uses ' + fmt(data.points)
                        + ' minimum points. Please choose a different value.'
                    : '';

            find('tier-warning').classList.toggle('hidden', !message);
            find('tier-warning-text').textContent = message;

            find('tier-hint').textContent = message
                ? 'Fix the duplicate before saving'
                : !data.name
                    ? 'Enter a tier name to continue'
                    : data.points === null
                        ? 'Enter minimum points'
                        : 'Ready to save';
        }

        form.querySelectorAll('.js-preset').forEach(button => {
            button.addEventListener('click', () => {
                nameEl.value = button.dataset.name;

                if (!ptsEl.disabled) {
                    ptsEl.value = button.dataset.min;
                }

                disEl.value = button.dataset.discount;
                colorEl.value = button.dataset.color;
                render();
            });
        });

        form.querySelectorAll('.js-pts').forEach(button => {
            button.addEventListener('click', () => {
                if (!ptsEl.disabled) {
                    ptsEl.value = button.dataset.value;
                    render();
                }
            });
        });

        form.querySelectorAll('.js-dis').forEach(button => {
            button.addEventListener('click', () => {
                disEl.value = button.dataset.value;
                render();
            });
        });

        swatches.forEach(button => {
            button.addEventListener('click', () => {
                colorEl.value = button.dataset.tierSwatch;
                render();
            });
        });

        [nameEl, ptsEl, disEl, colorEl].forEach(input => {
            input.addEventListener('input', render);
            input.addEventListener('change', render);
        });

        render();
    })();
</script>