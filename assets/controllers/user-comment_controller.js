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
        const formContainer = document.getElementById('comment-container-' + this.idValue);

        if (formContainer.classList.contains('open')) {
            // Hide if open
            formContainer.classList.remove('open');
            return;
        }

        if (formContainer.dataset.contentLoaded === 'true') {
            // Already loaded before, just show it
            formContainer.classList.add('open');
            return;
        }

        fetch(this.urlValue, {
            method: 'GET',
            headers: {
                'X-Requested-With': 'XMLHttpRequest'
            },
        })
        .then(res => res.text())
        .then(html => {
            formContainer.innerHTML = html;
            const form = formContainer.querySelector('form');
            if (form) {
                form.addEventListener('submit', this.submitForm.bind(this));
            }

            formContainer.dataset.contentLoaded = 'true';
            requestAnimationFrame(() => {
                formContainer.classList.add('open');
            });
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
                const commentsWrapper = document.getElementById('comment-container-' + this.idValue);
                const commentsContainer = commentsWrapper.querySelector('.comments');
                commentsContainer?.insertAdjacentHTML('afterbegin', data.comment_item);
                
                const actionsWrapper = commentsContainer
                    ?.closest('[data-controller="user-comment-delete"]')
                    ?.previousElementSibling; // class="actions"
                
                const counter = actionsWrapper
                    ?.querySelector('[data-user-comment-target="count"]');

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
