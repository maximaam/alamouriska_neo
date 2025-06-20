import { Controller } from '@hotwired/stimulus';

/*
* The following line makes this controller "lazy": it won't be downloaded until needed
* See https://symfony.com/bundles/StimulusBundle/current/index.html#lazy-stimulus-controllers
*/

/* stimulusFetch: 'lazy' */
export default class extends Controller {
    static values = {
        url: String
    }

    contact(event) {
        event.preventDefault();
        const form = event.target;
        const formData = new FormData(form);

        fetch(this.urlValue, {
            method: 'POST',
            body: formData,
            headers: {
                'X-Requested-With': 'XMLHttpRequest'
            },
        })
        .then(response => response.json())
        .then(data => {
            if ('success' === data.status) {
                const msg = data.msg ?? 'Message envoyé.';

                // Create success message element
                const successEl = document.createElement('div');
                successEl.className = 'alert alert-success mt-3';
                successEl.textContent = msg;

                // Append after form
                form.insertAdjacentElement('afterend', successEl);

                // Disable all form fields
                Array.from(form.elements).forEach(el => el.disabled = true);
            }

            if ('error' === data.status) {
                const errors = data.error?.children?.message?.errors;
                const errorMessage = errors?.[0]?.message ?? 'Une erreur est survenue.';
                alert(errorMessage); // or display in the UI
            }
        })
        .catch(err => console.error('Request error', err));
    }
}
