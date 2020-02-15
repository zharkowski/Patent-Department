(function() {
    var orderMetadataBlock = document.querySelector('.patent-metadata');

    var editButton = orderMetadataBlock.querySelector('.edit-btn');
    var cancelButton = orderMetadataBlock.querySelector('.form__cancel');

    if (editButton) {
        editButton.addEventListener('click', function() {
            if (orderMetadataBlock.classList.contains('form-mode')) {
                orderMetadataBlock.classList.remove('form-mode');
            } else {
                orderMetadataBlock.classList.add('form-mode');
            }
        });
    }

    if (cancelButton) {
        cancelButton.addEventListener('click', function(e) {
            e.preventDefault();
            e.stopPropagation();
            orderMetadataBlock.classList.remove('form-mode');
        });
    }
})();

(function() {
    var errorWrapper = document.querySelector('.error-wrapper');
    if (!errorWrapper) {
        return;
    }
    var errorCloseBtn = errorWrapper.querySelector('.error-close-btn');
    errorCloseBtn.addEventListener('click', function() {
        errorWrapper.remove();
    });
})();