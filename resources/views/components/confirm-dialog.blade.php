{{--
    One shared confirm dialog for the whole admin.
    Put <x-confirm-dialog /> ONCE in the main layout (just before </body>).

    Then any form can ask for confirmation just by adding attributes:

        <form method="POST" action="..."
              data-confirm="Brand Toyota will be removed permanently."
              data-confirm-title="Delete brand"      (optional, default "Are you sure?")
              data-confirm-ok="Delete"               (optional, default "Confirm")
              data-confirm-tone="danger|default"     (optional, default "danger")
              data-confirm-if="css selector">        (optional: only ask when the form matches this selector)
--}}

<style>
    @keyframes app-confirm-in { from { opacity: 0; transform: translateY(8px) scale(.98); } to { opacity: 1; transform: none; } }
    #app-confirm[open] { animation: app-confirm-in .15s ease-out; }
    @media (prefers-reduced-motion: reduce) { #app-confirm[open] { animation: none; } }
</style>

<dialog id="app-confirm" aria-labelledby="app-confirm-title" aria-describedby="app-confirm-message"
        class="m-auto w-[min(26rem,calc(100vw-2rem))] overflow-hidden rounded-2xl border border-zinc-200 bg-white p-0 text-zinc-900 shadow-2xl
               backdrop:bg-black/60 backdrop:backdrop-blur-sm dark:border-zinc-800 dark:bg-zinc-900 dark:text-white">

    <div data-confirm-bar class="h-1 bg-red-600"></div>

    <div class="p-6">
        <div class="flex items-start gap-4">
            <span data-confirm-icon class="grid h-11 w-11 shrink-0 place-items-center rounded-xl bg-red-500/15 text-red-500">
                <svg class="h-5 w-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
                    <path d="M10.3 3.9 1.8 18a2 2 0 0 0 1.7 3h17a2 2 0 0 0 1.7-3L13.7 3.9a2 2 0 0 0-3.4 0Z"/><path d="M12 9v4M12 17h.01"/>
                </svg>
            </span>

            <div class="min-w-0">
                <h2 id="app-confirm-title" class="text-lg font-semibold">Are you sure?</h2>
                <p id="app-confirm-message" class="mt-1 text-sm text-zinc-500"></p>
            </div>
        </div>

        <div class="mt-6 flex justify-end gap-2">
            <button type="button" data-confirm-cancel
                    class="inline-flex h-10 items-center justify-center rounded-xl border border-zinc-300 px-5 text-sm font-semibold text-zinc-700 transition
                           hover:border-zinc-400 focus:outline-none focus:ring-2 focus:ring-zinc-400/40 dark:border-zinc-700 dark:text-zinc-300">
                Cancel
            </button>
            <button type="button" data-confirm-ok
                    class="inline-flex h-10 items-center justify-center rounded-xl bg-red-600 px-5 text-sm font-semibold text-white transition
                           hover:bg-red-500 focus:outline-none focus:ring-2 focus:ring-red-500/40">
                Confirm
            </button>
        </div>
    </div>
</dialog>

<script>
    (function () {
        // Guard so the listener is only registered once, even if this component renders twice.
        if (window.__appConfirmReady) return;
        window.__appConfirmReady = true;

        var pending = null;

        // Full class names live here so Tailwind keeps them.
        var tones = {
            danger: {
                bar:  'h-1 bg-red-600',
                icon: 'grid h-11 w-11 shrink-0 place-items-center rounded-xl bg-red-500/15 text-red-500',
                ok:   'inline-flex h-10 items-center justify-center rounded-xl bg-red-600 px-5 text-sm font-semibold text-white transition hover:bg-red-500 focus:outline-none focus:ring-2 focus:ring-red-500/40'
            },
            'default': {
                bar:  'h-1 bg-sky-500',
                icon: 'grid h-11 w-11 shrink-0 place-items-center rounded-xl bg-sky-500/15 text-sky-500',
                ok:   'inline-flex h-10 items-center justify-center rounded-xl bg-zinc-900 px-5 text-sm font-semibold text-white transition hover:bg-zinc-700 focus:outline-none focus:ring-2 focus:ring-zinc-400/40 dark:bg-white dark:text-zinc-900 dark:hover:bg-zinc-200'
            }
        };

        function dialog() { return document.getElementById('app-confirm'); }

        function openFor(form, submitter) {
            var dlg = dialog();
            if (!dlg || typeof dlg.showModal !== 'function') return false;

            var tone = tones[form.dataset.confirmTone] || tones.danger;

            dlg.querySelector('#app-confirm-title').textContent   = form.dataset.confirmTitle || 'Are you sure?';
            dlg.querySelector('#app-confirm-message').textContent = form.dataset.confirm || '';
            dlg.querySelector('[data-confirm-ok]').textContent    = form.dataset.confirmOk || 'Confirm';
            dlg.querySelector('[data-confirm-bar]').className     = tone.bar;
            dlg.querySelector('[data-confirm-icon]').className    = tone.icon;
            dlg.querySelector('[data-confirm-ok]').className      = tone.ok;

            pending = { form: form, submitter: submitter || null };
            dlg.showModal();
            dlg.querySelector('[data-confirm-cancel]').focus(); // safe default for destructive actions
            return true;
        }

        function proceed() {
            var dlg = dialog(), job = pending;
            pending = null;
            if (dlg && dlg.open) dlg.close();
            if (!job) return;

            job.form.dataset.confirmed = '1';
            try {
                if (job.form.requestSubmit) job.form.requestSubmit(job.submitter || undefined);
                else job.form.submit();
            } finally {
                delete job.form.dataset.confirmed;
            }
        }

        function cancel() {
            var dlg = dialog();
            pending = null;
            if (dlg && dlg.open) dlg.close();
        }

        // Intercept submits of any form that has data-confirm.
        document.addEventListener('submit', function (e) {
            var form = e.target;
            if (!(form instanceof HTMLFormElement) || !form.hasAttribute('data-confirm')) return;
            if (form.dataset.confirmed === '1') return;

            var cond = form.dataset.confirmIf;
            if (cond && !form.querySelector(cond)) return; // condition not met -> no question needed

            if (openFor(form, e.submitter)) {
                e.preventDefault();
                e.stopImmediatePropagation();
            }
            // If <dialog> is unsupported we simply let the form submit.
        }, true);

        // Buttons, backdrop and Esc.
        document.addEventListener('click', function (e) {
            var dlg = dialog();
            if (!dlg || !dlg.open) return;

            if (e.target.closest('[data-confirm-ok]') && dlg.contains(e.target)) proceed();
            else if (e.target.closest('[data-confirm-cancel]') && dlg.contains(e.target)) cancel();
            else if (e.target === dlg) cancel(); // click on the dimmed backdrop
        });

        document.addEventListener('close', function (e) {
            if (e.target && e.target.id === 'app-confirm') pending = null;
        }, true);
    })();
</script>