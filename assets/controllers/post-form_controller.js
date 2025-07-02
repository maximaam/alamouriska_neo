import { Controller } from '@hotwired/stimulus';

export default class extends Controller {
    connect() {
        this.form = this.element.form;
        this.title = document.getElementById('post_form_title');
        this.titleArabic = document.getElementById('post_form_titleArabic');
        this.description = document.getElementById('post_form_description');
        this.initialTitlePlaceholder = this.title?.placeholder || '';
        this.initialTitleArabicPlaceholder = this.titleArabic?.placeholder || '';

        this.handleTypeChange(); // set up event listener
        this.handleTitleChange();
    }

    handleTypeChange() {
        this.element?.addEventListener('change', (event) => {
            const selected = event.target;
            const postTypeValue = Number(selected.value);
            const postTypeText = selected.options[selected.selectedIndex]?.text || '';
            const typeIsJoke = postTypeValue === 4;
            const validSelection = selected.selectedIndex > 0;
            const postTitlesWrapper = document.getElementById('post-titles-wrapper');

            if (!this.title || !this.titleArabic || !postTitlesWrapper) return;

            // Toggle visibility
            postTitlesWrapper.style.display = typeIsJoke ? 'none' : 'flex';

            // Enable/disable input fields
            const shouldDisable = typeIsJoke || !validSelection;
            this.title.disabled = shouldDisable;
            this.titleArabic.disabled = shouldDisable;

            if (typeIsJoke) {
                this.title.removeAttribute('required');
            } else if (validSelection) {
                this.title.placeholder = `${postTypeText}...`;
                this.title.setAttribute('required', 'required');
            } else {
                // Restore initial placeholders
                this.title.placeholder = this.initialTitlePlaceholder;
                this.titleArabic.placeholder = this.initialTitleArabicPlaceholder;
            }
        });
    }

    handleTitleChange() {
        this.title.addEventListener('change', (event) => {
            const latinTitle = event.target.value;
            fetch('https://inputtools.google.com/request?itc=ar-t-i0-und&num=1', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/x-www-form-urlencoded;charset=UTF-8',
                },
                body: 'text=' + encodeURIComponent(latinTitle)
            })
            .then(response => response.json())
            .then(data => {
                if (data[0] === 'SUCCESS') {
                    const translated = data[1][0][1][0]; // get first transliteration
                    this.titleArabic.value = translated;
                    alert(this.element.options[this.element.options.selectedIndex]?.text + ' a été transformé en arabe par IA, à vérifier.');
                    this.titleArabic.style.backgroundColor = "yellow";
                } else {
                    alert('Transliteration failed:');
                }
            })
            .catch(err => console.error('Fetch error:', err));
        });
    }
}
