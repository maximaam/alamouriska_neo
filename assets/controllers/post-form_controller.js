import { Controller } from '@hotwired/stimulus';

export default class extends Controller {
    connect() {
        // controller targets the "type" element
        this.form = this.element.form;
        this.title = document.getElementById('post_form_title');
        this.description = document.getElementById('post_form_description');

        this.presetForm();
        this.hideTitle();
    }

    presetForm() {
        this.title.labels[0].innerText = this.element.options[this.element.selectedIndex].text;
    }

    hideTitle() {
        const title = this.title;
        this.element.addEventListener('change', (event) => {
            const { value, options, selectedIndex } = event.target,
                postTypeValue = Number(value),
                postTypeText = options[selectedIndex]?.text,
                isHidden = postTypeValue === 3 || postTypeValue === 4;

            title.disabled = isHidden;
            title.parentNode.style.display = isHidden ? 'none' : 'block';
            if (isHidden) {
                title.removeAttribute('required');
            }


            if (!isHidden && title.labels?.[0]) {
                title.labels[0].innerText = postTypeText;
            }
        });
    }
}
