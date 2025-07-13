export function copyToClipboard(text, onSuccess = null, onError = null) {
    navigator.clipboard.writeText(text)
        .then(() => {
            // console.log('📋 Text copied to clipboard:', text);
            if (typeof onSuccess === 'function') {
                onSuccess(text);
            }
        })
        .catch(err => {
            // console.error('❌ Failed to copy text: ', err);
            if (typeof onError === 'function') {
                onError(err);
            }
        });
}
