jQuery(document).ready(function ($) {
    $('#upload_image_button').on('click', function (e) {
        e.preventDefault();

        var mediaUploader;
        if (mediaUploader) {
            mediaUploader.open();
            return;
        }

        mediaUploader = wp.media({
            title: 'Select Image',
            button: {
                text: 'Use this Image'
            },
            multiple: false
        });

        mediaUploader.on('select', function () {
            var attachment = mediaUploader.state().get('selection').first().toJSON();
            $('#notification_image').val(attachment.url); // Update input field
            $('#image_preview').attr('src', attachment.url).show(); // Update image preview
        });

        mediaUploader.open();
    });
});
