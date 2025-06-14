import { Controller } from '@hotwired/stimulus';

/*
* The following line makes this controller "lazy": it won't be downloaded until needed
* See https://symfony.com/bundles/StimulusBundle/current/index.html#lazy-stimulus-controllers
*/

/* stimulusFetch: 'lazy' */
export default class extends Controller {
    static targets = ["button"]

    delete(event) {
        const button = event.currentTarget;
        const confirmMsg = button.dataset.confirm;

        if (!confirm(confirmMsg)) {
            return false;
        }

        const csrf = button.dataset.csrf,
            url = button.dataset.url;

        event.preventDefault();
        fetch(url, {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-Requested-With': 'XMLHttpRequest',
                'X-CSRF-TOKEN': csrf
            },
        })
        .then(response => response.json())
        .then(data => {
            if ('success' === data.status) {
                const commentItem = button.closest('.comment-item');
                if (commentItem) {
                    commentItem.remove();
                }
            }
        });

    }
}
