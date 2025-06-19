import { Controller } from '@hotwired/stimulus';

/*
* The following line makes this controller "lazy": it won't be downloaded until needed
* See https://symfony.com/bundles/StimulusBundle/current/index.html#lazy-stimulus-controllers
*/

/* stimulusFetch: 'lazy' */
export default class extends Controller {
    static values = {
        url: String,
        csrfToken: String,
    }

    static targets = ['icon', 'count']

    contact(event) {
        event.preventDefault();
        fetch(this.urlValue, {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-Requested-With': 'XMLHttpRequest',
                'X-CSRF-TOKEN': this.csrfTokenValue
            },
        })
        .then(response => response.json())
        .then(data => {
            this.countTarget.textContent = data.likes;
            this.iconTarget.innerHTML = icon;
        });
    }
}
