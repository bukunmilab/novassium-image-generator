jQuery(document).ready(function($) {
    // Direct event handler for the submit button
    $('#nig-image-generation-form .button-primary').on('click', function(e) {
        e.preventDefault();
        console.log('Generate Image button clicked');
        
        var formData = {
            action: 'nig_generate_image',
            nonce: nig_ajax_object.nonce,
            prompt: $('#nig_prompt').val(),
            style_preset: $('#nig_style_preset').val(),
            aspect_ratio: $('#nig_aspect_ratio').val(),
            samples: $('#nig_samples').val(),
            negative_prompt: $('#nig_negative_prompt').val(),
            seed: $('#nig_seed').val(),
            output_format: $('#nig_output_format').val(),
            width: $('#nig_width').val(),
            height: $('#nig_height').val(),
        };

        $('#nig-generation-result').html('<p>Generating image...</p>');

        // Direct AJAX call to WordPress
        $.post(ajaxurl, formData, function(response) {
            console.log('Response received:', response);
            if (response.success) {
                var output = '<h2>Generated Images</h2>';
                response.data.images.forEach(function(url) {
                    output += '<img src="' + url + '" style="max-width: 300px; margin:10px;" />';
                });
                output += '<p>Remaining Credits: ' + response.data.remaining_credits + '</p>';
                $('#nig-generation-result').html(output);
            } else {
                var errorMsg = response.data && response.data.message ? response.data.message : 'An unknown error occurred';
                $('#nig-generation-result').html('<p style="color:red;">' + errorMsg + '</p>');
            }
        });
        
        return false;
    });
});
