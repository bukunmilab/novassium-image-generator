// File: assets/js/frontend.js

jQuery(document).ready(function($){
    // Store original button text
    var originalBtnText = $('#nig-frontend-generate-btn').text();
    var isProcessing = false;

    $('#nig-frontend-form').on('submit', function(e){
        e.preventDefault();

        // Prevent multiple submissions
        if (isProcessing) {
            return false;
        }

        isProcessing = true;

        var samples = parseInt($('#nig_frontend_samples').val(), 10);
        var generatedImages = [];

        // Disable button and show processing text
        $('#nig-frontend-generate-btn').prop('disabled', true).text(nig_ajax_params.processing_text);
        
        // Show loading message with spinner
        $('#nig-frontend-result').html('<div class="nig-loading"><p>' + nig_ajax_params.loading_message + '</p><div class="nig-spinner"></div></div>');

        // Function to process each sample sequentially
        function processSample(index) {
            if (index >= samples) {
                // All samples processed
                $('#nig-frontend-result').html('<div class="nig-images-grid">' + 
                    generatedImages.map(function(url) {
                        return '<div class="nig-image-item"><img src="' + url + '" alt="' + nig_ajax_params.generated_image_alt + '" /></div>';
                    }).join('') + 
                '</div>');
                isProcessing = false;
                $('#nig-frontend-generate-btn').prop('disabled', false).text(originalBtnText);
                return;
            }

            // Gather form data for the current sample
            var formData = {
                action: 'nig_generate_image_frontend',
                nonce: nig_ajax_params.nonce,
                prompt: $('#nig_frontend_prompt').val(),
                style_preset: $('#nig_frontend_style_preset').val(),
                aspect_ratio: $('#nig_frontend_aspect_ratio').val(),
                samples: 1, // Enforce single sample per API call
                negative_prompt: $('#nig_frontend_negative_prompt').val(),
                seed: $('#nig_frontend_seed').val(),
                output_format: $('#nig_frontend_output_format').val(),
            };

            // Perform the AJAX request with timeout
            $.ajax({
                type: 'POST',
                url: nig_ajax_params.ajax_url,
                data: formData,
                timeout: 360000, // 6 minutes
                success: function(response) {
                    if (response.success && response.data.images.length > 0) {
                        generatedImages.push(response.data.images[0]);
                        // Proceed to next sample
                        processSample(index + 1);
                    } else {
                        $('#nig-frontend-result').html('<p class="nig-error">' + 
                            (response.data.message || nig_ajax_params.unknown_error) + 
                        '</p>');
                        isProcessing = false;
                        $('#nig-frontend-generate-btn').prop('disabled', false).text(originalBtnText);
                    }
                },
                error: function(xhr, status, error) {
                    console.error('AJAX error:', error);
                    
                    var errorMessage = nig_ajax_params.server_error;
                    
                    if (status === 'timeout') {
                        errorMessage = nig_ajax_params.timeout_error;
                    }
                    
                    $('#nig-frontend-result').html('<p class="nig-error">' + errorMessage + '</p>');
                    
                    isProcessing = false;
                    $('#nig-frontend-generate-btn').prop('disabled', false).text(originalBtnText);
                }
            });
        }

        // Start processing the first sample
        processSample(0);
    });

    // Handle aspect ratio changes if needed
    $('#nig_frontend_aspect_ratio').on('change', function() {
        // Not updating any hidden fields since width/height are set in the backend handler
    });
});