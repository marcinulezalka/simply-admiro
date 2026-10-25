/**
 * Podpis odręczny (x-admiro::signature-pad): rysowanie myszą, rysikiem albo palcem na [data-ss-signature] (canvas),
 * po każdym pociągnięciu obraz PNG (data URL) trafia do ukrytego pola formularza; [data-ss-signature-clear] czyści
 * podpis. Bez skryptu pole zostaje puste, a komunikat <noscript> mówi, że podpis wymaga JavaScriptu.
 */
(() => {
    const init = (pad) => {
        const canvas = pad.querySelector('[data-ss-signature]');
        const input = pad.querySelector('[data-ss-signature-value]');
        const clear = pad.querySelector('[data-ss-signature-clear]');

        if (!(canvas instanceof HTMLCanvasElement) || !(input instanceof HTMLInputElement)) {
            return;
        }

        const context = canvas.getContext('2d');
        let drawing = false;

        context.lineWidth = 2;
        context.lineCap = 'round';
        context.strokeStyle = '#111827';

        if (input.value.startsWith('data:image/png')) {
            const image = new Image();
            image.addEventListener('load', () => context.drawImage(image, 0, 0));
            image.src = input.value;
        }

        const point = (event) => {
            const box = canvas.getBoundingClientRect();

            return [(event.clientX - box.left) * (canvas.width / box.width), (event.clientY - box.top) * (canvas.height / box.height)];
        };

        canvas.addEventListener('pointerdown', (event) => {
            drawing = true;
            canvas.setPointerCapture(event.pointerId);
            context.beginPath();
            context.moveTo(...point(event));
        });

        canvas.addEventListener('pointermove', (event) => {
            if (drawing) {
                context.lineTo(...point(event));
                context.stroke();
            }
        });

        const end = () => {
            if (drawing) {
                drawing = false;
                input.value = canvas.toDataURL('image/png');
            }
        };

        canvas.addEventListener('pointerup', end);
        canvas.addEventListener('pointercancel', end);

        clear?.addEventListener('click', () => {
            context.clearRect(0, 0, canvas.width, canvas.height);
            input.value = '';
        });

        clear?.removeAttribute('hidden');
    };

    const start = () => document.querySelectorAll('[data-ss-signature-pad]').forEach(init);

    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', start);
    } else {
        start();
    }
})();
