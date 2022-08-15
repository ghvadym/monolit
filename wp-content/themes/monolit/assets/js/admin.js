document.addEventListener('DOMContentLoaded', function () {
    var image = document.getElementById('adv-thumbnail-item');
    var imageData = document.getElementById('adv-attachment-id');
    var btnAdd = document.getElementById('adv-attachment-add');
    var btnRemove = document.getElementById('adv-attachment-remove');

    var uploader = wp.media({
        title: 'Select an Image',
        button: {
            text: 'Use this Image'
        },
        multiple: false
    });

    if (btnAdd) {
        btnAdd.addEventListener('click', function () {
            if (!uploader) {
                return;
            }

            uploader.open();
        });
    }

    if (btnRemove) {
        btnRemove.addEventListener('click', function () {
            if (image) {
                image.innerHTML = '';
            }

            if (imageData) {
                imageData.value = '';
            }

            this.classList.add('hidden');
        });
    }

    uploader.on('select', function () {
        if (!image) {
            return;
        }

        var img = '';
        var attachment = uploader.state().get('selection').first().toJSON();

        if (!image.children.length) {
            img = document.createElement('img');
        } else {
            img = image.querySelector('img');
        }

        img.src = attachment.url;
        img.alt = attachment.alt;

        img.setAttribute('width', '100%');
        img.setAttribute('height', '200');
        img.style.objectFit = 'contain';

        if (!image.children.length) {
            image.appendChild(img);
        }

        if (btnRemove && btnRemove.classList.contains('hidden')) {
            btnRemove.classList.remove('hidden');
        }

        if (imageData) {
            imageData.value = attachment.id;
        }
    });
});