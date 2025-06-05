import { Controller } from '@hotwired/stimulus';

export default class extends Controller {
    connect() {
        const vichContainer = this.element.parentNode;
        
        this.removeDownloadLink(vichContainer);
        this.previewImage(vichContainer);
    }

    /**
     * VichUploader renders the preview image wrapped
     * in a <a> with download link.
     * Remove the link.
     */
    removeDownloadLink(vichContainer) {
        const aTag = vichContainer.querySelector('a');

        // No preview image, new upload
        if (!aTag) {
            return false;
        }

        const imgTag = aTag.querySelector('img');
        if (!imgTag) {
            return false;
        }

        // Move the img out of the <a> and into the container
        vichContainer.insertBefore(imgTag, aTag);
        aTag.remove();
    }

    previewImage(vichContainer) {
        const inputFile = vichContainer?.querySelector('input[type="file"]');

        if (!inputFile) {
            return false;
        }

        inputFile.addEventListener('change', function () {
            let imgTag = vichContainer.querySelector('img'); // re-query each time
            const file = this.files[0];

            if (file && file.type.startsWith('image/')) {
                const objectURL = URL.createObjectURL(file);

                if (!imgTag) {
                    imgTag = document.createElement('img');
                    imgTag.src = objectURL;
                    inputFile.parentNode.insertBefore(imgTag, inputFile.nextSibling);
                } else {
                    imgTag.src = objectURL;
                }

                // Revoke the object URL after the image loads to free memory
                imgTag.onload = () => URL.revokeObjectURL(objectURL);
            }
        });
        
    }
}
