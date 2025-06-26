import { Controller } from '@hotwired/stimulus';

export default class extends Controller {
    connect() {
        this.form = this.element.form;
        this.title = document.getElementById('post_form_title');
        this.description = document.getElementById('post_form_description');

        this.handleTypeChange(); // set up event listener
    }

    handleTypeChange() {
        this.element.addEventListener('change', (event) => {
            const { value, options, selectedIndex } = event.target;
            const postTypeValue = Number(value);
            const postTypeText = options[selectedIndex]?.text || '';
            const shouldHide = postTypeValue === 4;

            if (this.title) {
                this.title.disabled = shouldHide;
                this.title.parentNode.parentNode.parentNode.style.display = shouldHide ? 'none' : 'block';

                if (shouldHide) {
                    this.title.removeAttribute('required');
                } else {
                    this.title.placeholder = `${postTypeText}...`;
                }
            }
        });
    }
}
