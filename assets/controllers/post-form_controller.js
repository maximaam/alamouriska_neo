import { Controller } from '@hotwired/stimulus';

export default class extends Controller {
    connect() {
        this.form = this.element.form;
        this.title = document.getElementById('post_form_title');
        this.titleArabic = document.getElementById('post_form_titleArabic');
        this.description = document.getElementById('post_form_description');
        this.initialTitlePlaceholder = this.title?.placeholder || '';
        this.initialTitleArabicPlaceholder = this.titleArabic?.placeholder || '';

        this.updateTitleFields();
        this.handleTypeChange(); // set up event listener
        this.handleTitleChange();
    }

    handleTypeChange() {
       this.element?.addEventListener('change', () => this.updateTitleFields());
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

    updateTitleFields() {
        if (!this.title || !this.titleArabic) return;

        const selectedIndex = this.element.selectedIndex;
        const typeText = this.element.options[selectedIndex]?.text || '';
        const validSelection = selectedIndex > 0;
        const shouldDisable = !validSelection;

        // Enable or disable inputs
        this.title.disabled = shouldDisable;
        this.titleArabic.disabled = shouldDisable;

        // Set placeholder and required
        if (validSelection) {
            this.title.placeholder = `${typeText}...`;
            this.title.setAttribute('required', 'required');
        } else {
            this.title.placeholder = this.initialTitlePlaceholder;
            this.titleArabic.placeholder = this.initialTitleArabicPlaceholder;
        }
    }
}
