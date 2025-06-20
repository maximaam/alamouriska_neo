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
            if ('error' === data.status) {
                const errors = data.error?.children?.message?.errors;
                const errorMessage = errors?.[0]?.message ?? 'Une erreur est survenue.';
                alert(errorMessage); // or display in the UI
            }
        })
        .catch(err => console.error('Request error', err));
    }
}
