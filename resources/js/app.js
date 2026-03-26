import './bootstrap';

window.addEventListener('DOMContentLoaded', () => {
    document.querySelectorAll('.alert').forEach((element) => {
        if (element.classList.contains('alert-danger')) {
            return;
        }

        window.setTimeout(() => {
            element.classList.add('fade');
            window.setTimeout(() => element.remove(), 350);
        }, 5000);
    });
});
