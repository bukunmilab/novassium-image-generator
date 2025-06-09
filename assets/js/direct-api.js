jQuery(document).ready(function ($) {
    // Only run on pages with the image generation form
    if (!$('#nig-image-generation-form').length) {
        return;
    }

    var processingActive = false;
    var originalBtnText = $('#nig-image-generation-form button.button-primary').text();
    var processedItems = 0;
    var totalItems = 0;
    var generatedImages = [];
    var directApiUrl = pluginBaseUrl + 'direct-api.php';

    // Add debug console
    function debugLog(message, data) {
        console.log('[NIG Debug]: ' + message, data || '');

        // Also display in a debug area if it exists
        if ($('#nig-debug-area').length === 0) {
            $('#nig-generation-result').after('<div id="nig-debug-area" style="margin-top:20px; padding:10px; background:#f8f9fa; border:1px solid #ddd; display:none;"><h4>Debug Information</h4><pre id="nig-debug-log" style="max-height:200px; overflow:auto;"></pre></div>');
        }

        $('#nig-debug-log').append(message + (data ? ': ' + JSON.stringify(data, null, 2) : '') + '\n');
    }

    // Add a debug toggle button
    $('<button type="button" id="nig-toggle-debug" class="button" style="margin-left:10px;">Show Debug Info</button>')
        .insertAfter('#nig-image-generation-form button.button-primary')
        .on('click', function () {
            $('#nig-debug-area').toggle();
            $(this).text($('#nig-debug-area').is(':visible') ? 'Hide Debug Info' : 'Show Debug Info');
        });

    // Handle form submission
    $('#nig-image-generation-form').on('submit', function (e) {
        e.preventDefault();

        if (processingActive) {
            return false;
        }

        // Clear debug log
        if ($('#nig-debug-log').length) {
            $('#nig-debug-log').empty();
        }

        // Get form elements
        var $form = $(this);
        var $submitBtn = $form.find('.button-primary');
        var $resultArea = $('#nig-generation-result');

        // Get parameters
        var prompt = $('#nig_prompt').val();
        var requestedSamples = parseInt($('#nig_samples').val()) || 1;

        debugLog('Form submission', {
            prompt: prompt,
            samples: requestedSamples,
            formData: $form.serialize()
        });

        // Validate form
        if (!prompt) {
            $resultArea.html('<div class="notice notice-error"><p>Please enter a prompt.</p></div>');
            return false;
        }

        // Initialize batch processing
        totalItems = requestedSamples;
        processedItems = 0;
        generatedImages = [];
        processingActive = true;

        // Update UI
        $submitBtn.prop('disabled', true).text('Processing...');

        // Create progress container
        $resultArea.html(
            '<div id="nig-processing-container">' +
            '<h3>Generating Images</h3>' +
            '<div class="progress-bar-container" style="height:20px; background:#f0f0f0; border-radius:3px; margin-bottom:10px; width:100%;">' +
            '<div id="nig-progress-bar" style="height:100%; width:0%; background:#2271b1; border-radius:3px;"></div>' +
            '</div>' +
            '<p id="nig-progress-text">Processing: <span id="nig-current">0</span>/<span id="nig-total">' + totalItems + '</span></p>' +
            '<p id="nig-current-item-text">Current prompt: <span id="nig-current-prompt">' + prompt + '</span></p>' +
            '<p style="font-style:italic; color:#606060;">Please wait while images are being generated. This process can take 30-60 seconds per image.</p>' +
            '</div>' +
            '<div id="nig-results-container" style="margin-top:20px;"></div>'
        );

        // Process images one by one
        processNextImage();

        return false;
    });

    function processNextImage() {
        if (processedItems >= totalItems) {
            // All items processed
            finalizeBatchProcessing();
            return;
        }

        // Get form values, but ensure we only generate one image at a time
        var formData = new FormData($('#nig-image-generation-form')[0]);

        // Remove action from formData (it's for WP AJAX)
        if (formData.has('action')) {
            formData.delete('action');
        }

        // Add our direct API action
        formData.append('nig_action', 'generate_image');
        formData.append('samples', 1); // Override to always generate just 1 image

        debugLog('Processing image ' + (processedItems + 1) + '/' + totalItems);
        debugLog('Form data', Object.fromEntries(formData));

        // Update progress UI
        $('#nig-current').text(processedItems + 1);
        var progress = ((processedItems + 0.5) / totalItems) * 100; // 0.5 to show it's in progress
        $('#nig-progress-bar').width(progress + '%');

        // Make the API request
        $.ajax({
            url: directApiUrl,
            type: 'POST',
            data: formData,
            processData: false,
            contentType: false,
            timeout: 120000, // 2 minute timeout
            success: function (response) {
                try {
                    // Parse response if it's a string
                    if (typeof response === 'string') {
                        response = JSON.parse(response);
                    }

                    debugLog('API response', response);

                    if (response.success) {
                        // Add the generated image to our collection
                        if (response.images && response.images.length > 0) {
                            response.images.forEach(function (imageUrl) {
                                generatedImages.push({
                                    url: imageUrl,
                                    status: 'success'
                                });
                            });

                            // Update the preview as we go
                            updateProgressPreview();
                        } else {
                            // Record as failed
                            generatedImages.push({
                                url: '',
                                status: 'failed',
                                message: 'No image URL returned from API'
                            });

                            debugLog('No images in response');
                        }
                    } else {
                        // Record failure
                        generatedImages.push({
                            url: '',
                            status: 'failed',
                            message: response.message || 'Unknown error'
                        });

                        debugLog('API error', response.message || 'Unknown error');
                        if (response.response) {
                            debugLog('Full API response', response.response);
                        }
                    }
                } catch (e) {
                    debugLog('Error parsing API response', {
                        error: e.toString(),
                        response: response
                    });

                    generatedImages.push({
                        url: '',
                        status: 'failed',
                        message: 'Invalid response format: ' + e.toString()
                    });
                }

                // Update progress
                processedItems++;
                var progress = (processedItems / totalItems) * 100;
                $('#nig-progress-bar').width(progress + '%');

                // Process next item
                processNextImage();
            },
            error: function (xhr, status, error) {
                var errorMsg = error || 'Request failed';

                if (status === 'timeout') {
                    errorMsg = 'Request timed out after 2 minutes';
                }

                debugLog('AJAX error', {
                    status: status,
                    error: errorMsg,
                    response: xhr.responseText
                });

                // Try to parse response if there is one
                try {
                    if (xhr.responseText) {
                        var errorResponse = JSON.parse(xhr.responseText);
                        if (errorResponse.message) {
                            errorMsg = errorResponse.message;
                        }
                        debugLog('Parsed error response', errorResponse);
                    }
                } catch (e) {
                    // Keep original error
                    debugLog('Could not parse error response', e);
                }

                // Even on error, still add an empty record so we maintain count
                generatedImages.push({
                    url: '',
                    status: 'failed',
                    message: 'Request failed. ' + errorMsg
                });

                // Update progress
                processedItems++;
                var progress = (processedItems / totalItems) * 100;
                $('#nig-progress-bar').width(progress + '%');

                // Process next item
                processNextImage();
            }
        });
    }

    function updateProgressPreview() {
        // Show a preview of successful images as they are generated
        var successfulImages = generatedImages.filter(function (img) {
            return img.status === 'success' && img.url;
        });

        if (successfulImages.length === 0) {
            return;
        }

        var previewHtml = '<h3>Generated Images (' + successfulImages.length + '/' + totalItems + ')</h3>' +
            '<div style="display:flex; flex-wrap:wrap; gap:15px;">';

        successfulImages.forEach(function (image) {
            previewHtml += '<div style="flex:0 0 calc(33.33% - 15px); max-width:calc(33.33% - 15px); margin-bottom:15px; box-shadow:0 2px 5px rgba(0,0,0,0.1); border-radius:5px; overflow:hidden;">' +
                '<img src="' + image.url + '" style="width:100%; height:auto; display:block;" />' +
                '</div>';
        });

        previewHtml += '</div>';

        $('#nig-results-container').html(previewHtml);
    }

    function finalizeBatchProcessing() {
        debugLog('Finalizing batch processing', {
            total: totalItems,
            processed: processedItems,
            images: generatedImages
        });

        // Reset processing state
        processingActive = false;
        $('#nig-image-generation-form .button-primary').prop('disabled', false).text(originalBtnText);

        // Count successes and failures
        var successes = generatedImages.filter(function (img) { return img.status === 'success'; }).length;
        var failures = generatedImages.filter(function (img) { return img.status === 'failed'; }).length;

        // Get successful images
        var successfulImages = generatedImages.filter(function (img) {
            return img.status === 'success' && img.url;
        });

        // Hide the progress display
        $('#nig-processing-container').hide();

        // Create final result HTML
        var resultHtml = '';

        // If we have at least one success, show them
        if (successfulImages.length > 0) {
            resultHtml += '<h3>Generated Images</h3>' +
                '<div class="nig-images-grid">';

            successfulImages.forEach(function (image, index) {
                // Generate a filename for download
                var filename = 'generated-image-' + (index + 1) + '.jpg';

                resultHtml += '<div class="nig-image-item">' +
                    '<div class="nig-image-container">' +
                    '<img src="' + image.url + '" alt="Generated image" />' +
                    '</div>' +
                    '<div class="nig-image-actions">' +
                    '<a href="' + image.url + '" class="nig-view-btn" target="_blank" style="margin-right: 5px;">' +
                    '<span class="dashicons dashicons-visibility" style="vertical-align: middle; margin-right: 3px;"></span> VIEW FULL SIZE' +
                    '</a>' +
                    '<a href="' + image.url + '" class="nig-download-btn" download="' + filename + '" target="_blank" style="background-color: #46b450; margin-left: 5px;">' +
                    '<span class="dashicons dashicons-download" style="vertical-align: middle; margin-right: 3px;"></span> DOWNLOAD' +
                    '</a>' +




                    '</div>' +
                    '</div>';
            });

            resultHtml += '</div>';

            // If there were failures, add a note
            if (failures > 0) {
                resultHtml += '<p style="color:#856404; background-color:#fff3cd; padding:10px; border-radius:4px;">' +
                    'Note: ' + successes + ' of ' + totalItems + ' requested images were successfully generated.' +
                    '</p>';
            }

            // Add note about browser download behavior
            resultHtml += '<div style="margin-top: 15px; padding: 10px; background-color: #e7f5fa; border-left: 4px solid #00a0d2; border-radius: 3px;">' +
                '<p><strong>Note:</strong> If the download button doesn\'t work, right-click on the image and select "Save Image As..." from the context menu.</p>' +
                '</div>';

            // Update credits display if available
            var currentCredits = parseInt($('#nig-total-credits').text() || '0') + successes;
            $('#nig-total-credits').text(currentCredits);

            // Update chart if available
            if (typeof window.updateUsageChart === 'function') {
                window.updateUsageChart(currentCredits);
            }
        } else {
            // All generations failed
            resultHtml += '<div class="notice notice-error"><p>Failed to generate any images. Please try again with a different prompt or settings.</p>';

            // Add error details if available
            var errorMessages = generatedImages
                .filter(function (img) { return img.status === 'failed' && img.message; })
                .map(function (img) { return img.message; });

            if (errorMessages.length > 0) {
                resultHtml += '<p>Error details: ' + errorMessages[0] + '</p>';
            }

            resultHtml += '</div>';
        }

        $('#nig-results-container').html(resultHtml);

        // Ensure we've loaded dashicons
        if (!$('link[href*="dashicons"]').length) {
            $('head').append('<link rel="stylesheet" href="' + pluginBaseUrl + '../../wp-includes/css/dashicons.min.css" type="text/css" media="all">');
        }
    }


    // Add API connection check button
    $('<button type="button" id="nig-check-api" class="button" style="margin: 0 10px;">Check API Connection</button>')
        .insertAfter('#nig-toggle-debug')
        .on('click', function () {
            var $btn = $(this);
            $btn.prop('disabled', true).text('Checking...');
            $('#nig-api-status').html('<div class="notice notice-info is-dismissible"><p>Checking API connection...</p></div>');

            // Create a direct request to check API
            var formData = new FormData();
            formData.append('nig_action', 'check_api');
            formData.append('nonce', $('#nig-image-generation-form input[name="nonce"]').val());

            $.ajax({
                url: directApiUrl,
                type: 'POST',
                data: formData,
                processData: false,
                contentType: false,
                timeout: 20000,
                success: function (response) {
                    try {
                        if (typeof response === 'string') {
                            response = JSON.parse(response);
                        }

                        if (response.success) {
                            $('#nig-api-status').html(
                                '<div class="notice notice-success is-dismissible"><p>' +
                                'API connection successful!' +
                                '</p></div>'
                            );
                        } else {
                            $('#nig-api-status').html(
                                '<div class="notice notice-error is-dismissible"><p>' +
                                'API connection failed: ' + (response.message || 'Unknown error') +
                                '</p></div>'
                            );
                        }
                    } catch (e) {
                        $('#nig-api-status').html(
                            '<div class="notice notice-error is-dismissible"><p>' +
                            'Error parsing API response: ' + e.toString() +
                            '</p></div>'
                        );
                    }
                    $btn.prop('disabled', false).text('Check API Connection');
                },
                error: function (xhr, status, error) {
                    $('#nig-api-status').html(
                        '<div class="notice notice-error is-dismissible"><p>' +
                        'API connection failed: ' + (error || 'Request failed') +
                        '</p></div>'
                    );
                    $btn.prop('disabled', false).text('Check API Connection');
                }
            });
        });
});
