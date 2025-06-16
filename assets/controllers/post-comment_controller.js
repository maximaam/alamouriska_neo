import { Controller } from '@hotwired/stimulus';

/*
* The following line makes this controller "lazy": it won't be downloaded until needed
* See https://symfony.com/bundles/StimulusBundle/current/index.html#lazy-stimulus-controllers
*/

/* stimulusFetch: 'lazy' */
export default class extends Controller {
    static values = {
        url: String,
        id: String,
        // csrfToken: String,
    }

    // static targets = ['icon', 'count']

    // static targets = ["container"];

    getForm(event) {
        event.preventDefault();
        fetch(this.urlValue, {
            method: 'GET',
            headers: {
                'X-Requested-With': 'XMLHttpRequest'
            },
        })
        .then(res => res.text())
        .then(html => {
            const formContainer = document.getElementById('comment-container-' + this.idValue);
            formContainer.innerHTML = html;
            const form = formContainer.querySelector('form');
            form.addEventListener('submit', this.submitForm.bind(this));
        });
    }

    submitForm(event) {
        event.preventDefault();

        const form = event.target;
        const formData = new FormData(form);

        fetch(this.urlValue, {
            method: 'POST',
            body: formData,
            headers: { 'X-Requested-With': 'XMLHttpRequest' },
        })
        .then(res => res.json())
        .then(data => {
            if (data.status === 'success') {
                const commentsContainer = document.querySelector('.comments');
                commentsContainer?.insertAdjacentHTML('beforeend', data.comment_item);
                
                const actionsWrapper = commentsContainer
                    ?.closest('[data-controller="post-comment-delete"]')
                    ?.previousElementSibling; // class="actions"
                
                const counter = actionsWrapper
                    ?.querySelector('[data-post-comment-target="count"]');

                if (counter) {
                    counter.innerText = Number(counter.innerText) + 1;
                }
            }

            const formContainer = document.querySelector('.comment-form');
            if (!formContainer) return;

            formContainer.innerHTML = data.form;

            const newForm = formContainer.querySelector('form');
            if (newForm) {
                newForm.removeEventListener('submit', this.submitFormBound); // prevent double bind
                this.submitFormBound = this.submitFormBound || this.submitForm.bind(this);
                newForm.addEventListener('submit', this.submitFormBound);
            }
        });
    }
}
