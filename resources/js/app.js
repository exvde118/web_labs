import $ from 'jquery';
import { Collapse, Popover } from 'bootstrap';
window.$ = window.jQuery = $;
$(() => {
    document.querySelectorAll('[data-bs-toggle="popover"]').forEach(el => new Popover(el));
    $('.needs-validation').on('submit', function (event) {
        if (!this.checkValidity()) {
            event.preventDefault();
            event.stopPropagation();
        }
        this.classList.add('was-validated');
    });
});
