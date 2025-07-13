import { Controller } from '@hotwired/stimulus';
import { copyToClipboard } from '../modules/copy_to_clipboard.js';

/*
* The following line makes this controller "lazy": it won't be downloaded until needed
* See https://symfony.com/bundles/StimulusBundle/current/index.html#lazy-stimulus-controllers
*/

/* stimulusFetch: 'lazy' */
export default class extends Controller {
    static values = {
        csrfToken: String,
        entityId: String,
        likeUrl: String,
        commentUrl: String,
    }

    static targets = ['likeIcon', 'likeCount','commentIcon','commentCount'];

    initialize() {
        // alert('ok');
        // Called once when the controller is first instantiated (per element)

        // Here you can initialize variables, create scoped callables for event
        // listeners, instantiate external libraries, etc.
        // this._fooBar = this.fooBar.bind(this)
    }

     connect() {
        // console.log('Connected:', this.hasLikeIconTarget, this.likeIconTarget);
        // Called every time the controller is connected to the DOM
        // (on page load, when it's added to the DOM, moved in the DOM, etc.)

        // Here you can add event listeners on the element or target elements,
        // add or remove classes, attributes, dispatch custom events, etc.
        // this.fooTarget.addEventListener('click', this._fooBar)
    }

    // Add custom controller actions here
    // fooBar() { this.fooTarget.classList.toggle(this.bazClass) }

    disconnect() {
        // Called anytime its element is disconnected from the DOM
        // (on page change, when it's removed from or moved in the DOM, etc.)

        // Here you should remove all event listeners added in "connect()" 
        // this.fooTarget.removeEventListener('click', this._fooBar)
    }

    /**
     * 
     * @param {*} event 
     */
    like(event) {
        event.preventDefault();
        fetch(this.likeUrlValue, {
            method: 'POST',
            headers: this._jsonHeaders(),
        })
        .then(response => response.json())
        .then(data => {
            if (data.status === 'redirect') {
                return window.location.href = data.url;
            }
            
            this.likeCountTarget.textContent = data.likes;
            this.likeIconTarget.innerHTML = this._renderLikeIcon(data.action === 'like');
        })
        .catch(console.error);
    }

    /**
     * 
     * @param {*} event 
     */
    getCommentForm(event) {
        event.preventDefault();
        const container = document.getElementById('comment-container-' + this.entityIdValue);

        if (container.classList.toggle('open') && container.dataset.formLoaded !== 'true') {
            fetch(this.commentUrlValue, {
                method: 'GET',
                headers: { 'X-Requested-With': 'XMLHttpRequest' },
            })
            .then(res => res.text())
            .then(html => {
                container.innerHTML = html;
                container.dataset.formLoaded = 'true';
                container.querySelector('form')?.addEventListener('submit', this._boundSubmit());
            })
            .catch(console.error);
        }
    }

    /**
     * 
     * @param {*} event 
     */
    submitCommentForm(event) {
        event.preventDefault();
        const form = event.target;
        const formData = new FormData(form);

        fetch(this.commentUrlValue, {
            method: 'POST',
            body: formData,
            headers: { 'X-Requested-With': 'XMLHttpRequest' },
        })
        .then(res => res.json())
        .then(data => {
            const container = document.getElementById('comment-container-' + this.entityIdValue);
            const comments = container.querySelector('.comments');
            const formWrapper = container.querySelector('.comment-form');
            
            if (data.status === 'success') {
                comments?.insertAdjacentHTML('afterbegin', data.comment_item);
                if (this.hasCommentCountTarget) {
                    this.commentCountTarget.innerText = +this.commentCountTarget.innerText + 1;
                }
            }

            if (formWrapper) {
                formWrapper.innerHTML = data.form;
                formWrapper.querySelector('form')?.addEventListener('submit', this._boundSubmit());
            }
        })
        .catch(console.error);
    }

    /**
     * 
     * @param {*} event 
     */
    copyEntityUrl(event) {
        const button = event.currentTarget,
            url = button.getAttribute('data-entity-url'),
            labelCopied = button.getAttribute('data-label-copied'),
            buttonSpan = button.querySelector('span');

        copyToClipboard(url,
            function() {
                buttonSpan.innerText = labelCopied;
                setTimeout(() => {
                    buttonSpan.innerText = '';
                }, 2000);
            },
            function(err) {
                console.error('Erratum : ', err);
            }
        );
    }

    /**
     * Delete a user interaction comment: Post or Wall
     * 
     * @param {*} event 
     * @returns 
     */
    deleteComment(event) {
        event.preventDefault();
        const button = event.currentTarget;
        const { confirm: confirmMsg, url } = button.dataset;
        
        if (!confirm(confirmMsg)) return;

        fetch(url, {
            method: 'POST',
            headers: this._jsonHeaders(),
        })
        .then(response => response.json())
        .then(data => {
            if (data.status === 'success') {
                button.closest('.comment-item')?.remove();
                if (this.hasCommentCountTarget) {
                    this.commentCountTarget.innerText = +this.commentCountTarget.innerText - 1;
                }
            }
        })
        .catch(error => {
            console.error('Erratum!', error);
        });
    }

    _jsonHeaders() {
        return {
            'Content-Type': 'application/json',
            'X-Requested-With': 'XMLHttpRequest',
            'X-CSRF-TOKEN': this.csrfTokenValue,
        };
    }

    _renderLikeIcon(isLiked) {
        const path = isLiked
            ? 'M8 1.314C12.438-3.248 23.534 4.735 8 15C-7.534 4.736 3.562-3.248 8 1.314'
            : 'm8 2.748l-.717-.737C5.6.281 2.514.878 1.4 3.053c-.523 1.023-.641 2.5.314 4.385c.92 1.815 2.834 3.989 6.286 6.357c3.452-2.368 5.365-4.542 6.286-6.357c.955-1.886.838-3.362.314-4.385C13.486.878 10.4.28 8.717 2.01zM8 15C-7.333 4.868 3.279-3.04 7.824 1.143q.09.083.176.171a3 3 0 0 1 .176-.17C12.72-3.042 23.333 4.867 8 15';
        const fillRule = isLiked ? 'evenodd' : '';

        return `<svg viewBox="0 0 16 16" fill="currentColor" class="basic-icon text-danger" aria-hidden="true"><path fill="currentColor" fill-rule="${fillRule}" d="${path}"></path></svg>`;
    }

    _boundSubmit() {
        this._submitBound = this._submitBound || this.submitCommentForm.bind(this);
        
        return this._submitBound;
    }
}
