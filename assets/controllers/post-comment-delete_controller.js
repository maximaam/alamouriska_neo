import { Controller } from '@hotwired/stimulus';

/*
* The following line makes this controller "lazy": it won't be downloaded until needed
* See https://symfony.com/bundles/StimulusBundle/current/index.html#lazy-stimulus-controllers
*/

/* stimulusFetch: 'lazy' */
export default class extends Controller {
    static targets = ["button"]

    delete(event) {
        event.preventDefault();

        const button = event.currentTarget;
        const { confirm: confirmMsg, csrf, url } = button.dataset;
        
        if (!confirm(confirmMsg)) return;

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
                const actionsWrapper = button.closest('[data-controller="post-comment-delete"]')
                    ?.previousElementSibling; // class="actions"
                const counter = actionsWrapper?.querySelector('[data-post-comment-target="count"]');

                if (commentItem) commentItem.remove();
                if (counter) counter.innerText = Number(counter.innerText) - 1;
            }
        })
        .catch(error => {
            console.error('Erratum!', error);
        });
    }
}
