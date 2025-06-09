<?php
if ( ! defined( 'ABSPATH' ) ) {
    exit; // Exit if accessed directly
}

/**
 * Add Batch Processing Subpage
 */
function nig_add_batch_processing_menu() {
    add_submenu_page(
        'nig_settings',
        __( 'Batch Processing', 'novassium-image-generator' ),
        __( 'Batch Processing', 'novassium-image-generator' ),
        'manage_options',
        'nig_batch_processing',
        'nig_batch_processing_page'
    );
}
add_action( 'admin_menu', 'nig_add_batch_processing_menu' );

/**
 * Render Batch Processing Page
 */
function nig_batch_processing_page() {
    ?>
    <div class="wrap">
        <h1><?php _e( 'Batch Image Generation', 'novassium-image-generator' ); ?></h1>
        
        <div class="nig-intro-section notice notice-info" style="padding: 15px; margin: 15px 0;">
            <h3 style="margin-top: 0;"><?php _e('Introduction to Batch Processing', 'novassium-image-generator'); ?></h3>
            <p>
                <?php _e('This tool allows you to generate AI images for multiple content items at once.', 'novassium-image-generator'); ?>
            </p>
            <h4><?php _e('How to use:', 'novassium-image-generator'); ?></h4>
            <ol>
                <li><?php _e('Select the content type (Posts, Pages, or Products)', 'novassium-image-generator'); ?></li>
                <li><?php _e('Choose specific items from the list (hold Ctrl/Cmd to select multiple)', 'novassium-image-generator'); ?></li>
                <li><?php _e('Configure image options, style preset, and size', 'novassium-image-generator'); ?></li>
                <li><?php _e('Decide how to handle content that already has featured images', 'novassium-image-generator'); ?></li>
                <li><?php _e('Click "Generate Images" to start the process', 'novassium-image-generator'); ?></li>
            </ol>
            <p>
                <strong><?php _e('Note:', 'novassium-image-generator'); ?></strong> 
                <?php _e('The process may take several minutes depending on the number of items selected.', 'novassium-image-generator'); ?>
            </p>
        </div>
        
        <div id="nig-batch-status"></div>
        
        <form id="nig-batch-generation-form">
            <table class="form-table">
                <tr>
                    <th scope="row">
                        <label for="nig_batch_type"><?php _e( 'Batch Type', 'novassium-image-generator' ); ?></label>
                    </th>
                    <td>
                        <select id="nig_batch_type" name="batch_type" required>
                            <option value=""><?php _e( 'Select Content Type', 'novassium-image-generator' ); ?></option>
                            <option value="posts"><?php _e( 'Posts', 'novassium-image-generator' ); ?></option>
                            <option value="pages"><?php _e( 'Pages', 'novassium-image-generator' ); ?></option>
                            <option value="products"><?php _e( 'WooCommerce Products', 'novassium-image-generator' ); ?></option>
                        </select>
                        <p class="description"><?php _e('Select the type of content to generate images for.', 'novassium-image-generator'); ?></p>
                    </td>
                </tr>
                <tr id="items-selection-row" style="display:none;">
                    <th scope="row">
                        <label for="nig_selected_ids"><?php _e( 'Select Items', 'novassium-image-generator' ); ?></label>
                    </th>
                    <td>
                        <div id="nig-loading-items" style="display:none;">
                            <span class="spinner is-active" style="float:left;margin-top:0;"></span>
                            <?php _e('Loading items...', 'novassium-image-generator'); ?>
                        </div>
                        
                        <select id="nig_selected_ids" name="selected_ids[]" multiple size="15" style="width: 100%;" required>
                            <!-- Options will be populated via JavaScript -->
                        </select>
                        <p class="description"><?php _e('Hold Ctrl/Cmd key to select multiple items.', 'novassium-image-generator'); ?></p>
                        <div style="margin-top:10px;">
                            <button type="button" id="select-all-items" class="button"><?php _e('Select All', 'novassium-image-generator'); ?></button>
                            <button type="button" id="deselect-all-items" class="button"><?php _e('Deselect All', 'novassium-image-generator'); ?></button>
                            <button type="button" id="select-no-featured-image" class="button"><?php _e('Select Only Items Without Featured Image', 'novassium-image-generator'); ?></button>
                        </div>
                    </td>
                </tr>
                <tr id="featured-image-handling-row" style="display:none;">
                    <th scope="row">
                        <label for="nig_featured_image_handling"><?php _e('Featured Image Handling', 'novassium-image-generator'); ?></label>
                    </th>
                    <td>
                        <select id="nig_featured_image_handling" name="featured_image_handling">
                            <option value="replace"><?php _e('Replace existing featured images', 'novassium-image-generator'); ?></option>
                            <option value="skip"><?php _e('Skip content that already has a featured image', 'novassium-image-generator'); ?></option>
                            <option value="always"><?php _e('Generate for all selected content', 'novassium-image-generator'); ?></option>
                        </select>
                        <p class="description"><?php _e('Determine how to handle content that already has featured images.', 'novassium-image-generator'); ?></p>
                    </td>
                </tr>
                <tr id="style-selection-row" style="display:none;">
                    <th scope="row">
                        <label for="nig_style_preset"><?php _e( 'Style Preset', 'novassium-image-generator' ); ?></label>
                    </th>
                    <td>
                        <select id="nig_style_preset" name="style_preset">
                            <option value="enhance"><?php _e('Enhance', 'novassium-image-generator'); ?></option>
                            <option value="cinematic"><?php _e('Cinematic', 'novassium-image-generator'); ?></option>
                            <option value="3d-model"><?php _e('3D Model', 'novassium-image-generator'); ?></option>
                            <option value="analog-film"><?php _e('Analog Film', 'novassium-image-generator'); ?></option>
                            <option value="anime"><?php _e('Anime', 'novassium-image-generator'); ?></option>
                            <option value="comic-book"><?php _e('Comic Book', 'novassium-image-generator'); ?></option>
                            <option value="digital-art"><?php _e('Digital Art', 'novassium-image-generator'); ?></option>
                            <option value="fantasy-art"><?php _e('Fantasy Art', 'novassium-image-generator'); ?></option>
                            <option value="isometric"><?php _e('Isometric', 'novassium-image-generator'); ?></option>
                            <option value="line-art"><?php _e('Line Art', 'novassium-image-generator'); ?></option>
                            <option value="low-poly"><?php _e('Low Poly', 'novassium-image-generator'); ?></option>
                            <option value="modeling-compound"><?php _e('Modeling Compound', 'novassium-image-generator'); ?></option>
                            <option value="photographic"><?php _e('Photographic', 'novassium-image-generator'); ?></option>
                            <option value="neon-punk"><?php _e('Neon Punk', 'novassium-image-generator'); ?></option>
                            <option value="origami"><?php _e('Origami', 'novassium-image-generator'); ?></option>
                            <option value="pixel-art"><?php _e('Pixel Art', 'novassium-image-generator'); ?></option>
                            <option value="tile-texture"><?php _e('Tile Texture', 'novassium-image-generator'); ?></option>
                        </select>
                        <p class="description"><?php _e('Select the visual style for the generated images.', 'novassium-image-generator'); ?></p>
                    </td>
                </tr>
                <tr id="size-selection-row" style="display:none;">
                    <th scope="row">
                        <label for="nig_image_size"><?php _e( 'Image Size', 'novassium-image-generator' ); ?></label>
                    </th>
                    <td>
                        <select id="nig_image_size" name="image_size">
                            <option value="1024x1024"><?php _e('Square 1024x1024', 'novassium-image-generator'); ?></option>
                            <option value="1024x576"><?php _e('Landscape 16:9 (1024x576)', 'novassium-image-generator'); ?></option>
                            <option value="576x1024"><?php _e('Portrait 9:16 (576x1024)', 'novassium-image-generator'); ?></option>
                            <option value="1024x768"><?php _e('Landscape 4:3 (1024x768)', 'novassium-image-generator'); ?></option>
                            <option value="768x1024"><?php _e('Portrait 3:4 (768x1024)', 'novassium-image-generator'); ?></option>
                        </select>
                        <p class="description"><?php _e('Choose the dimensions of the generated images.', 'novassium-image-generator'); ?></p>
                    </td>
                </tr>
                <tr id="negative-prompt-row" style="display:none;">
                    <th scope="row">
                        <label for="nig_negative_prompt"><?php _e('Negative Prompt', 'novassium-image-generator'); ?></label>
                    </th>
                    <td>
                        <textarea id="nig_negative_prompt" name="negative_prompt" rows="3" style="width:100%;"><?php echo esc_textarea('text, watermark, signature, blurry, low quality, distorted, deformed'); ?></textarea>
                        <p class="description"><?php _e('Specify elements to avoid in the generated images.', 'novassium-image-generator'); ?></p>
                    </td>
                </tr>
                <tr id="seed-row" style="display:none;">
                    <th scope="row">
                        <label for="nig_seed"><?php _e('Seed', 'novassium-image-generator'); ?></label>
                    </th>
                    <td>
                        <div style="display:flex; align-items:center;">
                            <input type="number" id="nig_seed" name="seed" min="1" max="9999999" value="0" style="width:150px;">
                            <label style="margin-left:10px;">
                                <input type="checkbox" id="nig_random_seed" name="random_seed" checked>
                                <?php _e('Use random seed (recommended)', 'novassium-image-generator'); ?>
                            </label>
                        </div>
                        <p class="description"><?php _e('A specific seed will produce consistent results. Random seeds produce variety.', 'novassium-image-generator'); ?></p>
                    </td>
                </tr>
            </table>
            
            <div id="submit-container" style="display:none; margin-top:20px;">
                <button type="submit" id="nig_generate_button" class="button button-primary"><?php _e( 'Generate Images', 'novassium-image-generator' ); ?></button>
                <div id="nig-batch-processing" style="display:none; margin-top:20px;">
                    <div class="progress-bar-container" style="height:20px; background:#f0f0f0; border-radius:3px; margin-bottom:10px; width:100%;">
                        <div id="nig-progress-bar" style="height:100%; width:0%; background:#2271b1; border-radius:3px;"></div>
                    </div>
                    <p id="nig-progress-text"><?php _e('Processing: ', 'novassium-image-generator'); ?> <span id="nig-current">0</span>/<span id="nig-total">0</span></p>
                    <p id="nig-current-item-text"><?php _e('Current item: ', 'novassium-image-generator'); ?> <span id="nig-current-item">None</span></p>
                </div>
            </div>
        </form>
        
        <div id="nig-batch-result" style="margin-top:20px;"></div>
    </div>

    <script>
    jQuery(document).ready(function($){
        var totalItems = 0;
        var processedItems = 0;
        var selectedIds = [];
        var batchResults = [];
        var itemsWithFeaturedImage = [];
        var itemsData = [];
        var processingActive = false;
        var batchTypeSelected = '';
        
        // Handle random seed checkbox
        $('#nig_random_seed').on('change', function() {
            if($(this).is(':checked')) {
                $('#nig_seed').val('0').prop('disabled', true);
            } else {
                $('#nig_seed').prop('disabled', false).val('1234567');
            }
        });
        
        // Initialize seed field state
        $('#nig_seed').prop('disabled', $('#nig_random_seed').is(':checked'));
        
        // When batch type changes, load the items
        $('#nig_batch_type').on('change', function(){
            var batchType = $(this).val();
            batchTypeSelected = batchType;
            
            if (!batchType) {
                $('#items-selection-row, #featured-image-handling-row, #style-selection-row, #size-selection-row, #negative-prompt-row, #seed-row, #submit-container').hide();
                return;
            }
            
            loadItems(batchType);
        });
        
        // Select all items
        $('#select-all-items').on('click', function() {
            $('#nig_selected_ids option').prop('selected', true);
        });
        
        // Deselect all items
        $('#deselect-all-items').on('click', function() {
            $('#nig_selected_ids option').prop('selected', false);
        });
        
        // Select only items without featured image
        $('#select-no-featured-image').on('click', function() {
            $('#nig_selected_ids option').prop('selected', false);
            
            $('#nig_selected_ids option').each(function() {
                var postId = $(this).val();
                if (itemsWithFeaturedImage.indexOf(parseInt(postId)) === -1) {
                    $(this).prop('selected', true);
                }
            });
        });
        
        function loadItems(batchType) {
            // Reset data
            itemsWithFeaturedImage = [];
            itemsData = [];
            
            // Show loading indicator
            $('#nig_selected_ids').empty().hide();
            $('#nig-loading-items').show();
            $('#items-selection-row').show();
            
            var data = {
                action: 'nig_get_batch_items',
                batch_type: batchType,
                nonce: '<?php echo wp_create_nonce( 'nig_get_batch_items_nonce' ); ?>'
            };
            
            $.post(ajaxurl, data, function(response) {
                $('#nig-loading-items').hide();
                
                if (response.success && response.data && response.data.length > 0) {
                    // Store items data
                    itemsData = response.data;
                    
                    // Record items with featured images
                    $.each(response.data, function(i, item) {
                        if (item.has_featured_image) {
                            itemsWithFeaturedImage.push(parseInt(item.id));
                        }
                    });
                    
                    // Clear previous options
                    $('#nig_selected_ids').empty();
                    
                    // Add items to select dropdown
                    $.each(response.data, function(i, item) {
                        var optionText = item.title + ' (ID: ' + item.id + ')';
                        
                        // Indicate if it has a featured image
                        if (item.has_featured_image) {
                            optionText += ' [' + '<?php _e("Has Featured Image", "novassium-image-generator"); ?>' + ']';
                        }
                        
                        $('#nig_selected_ids').append(
                            $('<option>', {
                                value: item.id,
                                text: optionText
                            })
                        );
                    });
                    
                    // Show select box and additional options
                    $('#nig_selected_ids').show();
                    $('#featured-image-handling-row, #style-selection-row, #size-selection-row, #negative-prompt-row, #seed-row, #submit-container').show();
                } else {
                    // Show error message if no items found
                    $('#nig-batch-status').html('<div class="notice notice-error"><p>' + 
                        (response.data && response.data.message ? response.data.message : '<?php _e("No items found. Please create some content first.", "novassium-image-generator"); ?>') + 
                        '</p></div>');
                    
                    $('#nig_selected_ids').hide();
                }
            }).fail(function() {
                $('#nig-loading-items').hide();
                $('#nig-batch-status').html('<div class="notice notice-error"><p><?php _e("Failed to load items. Please try again.", "novassium-image-generator"); ?></p></div>');
            });
        }
        
        // Form submission
        $('#nig-batch-generation-form').on('submit', function(e) {
            e.preventDefault();
            
            // Prevent double submission
            if (processingActive) {
                return false;
            }
            
            // Validate form
            var batchType = $('#nig_batch_type').val();
            selectedIds = $('#nig_selected_ids').val();
            
            if (!batchType) {
                $('#nig-batch-status').html('<div class="notice notice-error"><p><?php _e("Please select a content type.", "novassium-image-generator"); ?></p></div>');
                return false;
            }
            
            if (!selectedIds || selectedIds.length === 0) {
                $('#nig-batch-status').html('<div class="notice notice-error"><p><?php _e("Please select at least one item.", "novassium-image-generator"); ?></p></div>');
                return false;
            }
            
            // Initialize batch processing
            totalItems = selectedIds.length;
            processedItems = 0;
            batchResults = [];
            processingActive = true;
            
            // Update UI
            $('#nig_generate_button').prop('disabled', true).html('<?php _e("Processing...", "novassium-image-generator"); ?>');
            $('#nig-total').text(totalItems);
            $('#nig-current').text(processedItems);
            $('#nig-progress-bar').width('0%');
            $('#nig-current-item').text('<?php _e("Starting...", "novassium-image-generator"); ?>');
            $('#nig-batch-processing').show();
            $('#nig-batch-result').empty();
            $('#nig-batch-status').empty();
            
            // Process items one by one
            processNextItem();
            
            return false;
        });
        
        function processNextItem() {
            if (processedItems >= totalItems) {
                // All items processed
                finalizeBatchProcessing();
                return;
            }
            
            var postId = selectedIds[processedItems];
            var stylePreset = $('#nig_style_preset').val();
            var imageSizeParts = $('#nig_image_size').val().split('x');
            var width = parseInt(imageSizeParts[0]);
            var height = parseInt(imageSizeParts[1]);
            var featuredImageHandling = $('#nig_featured_image_handling').val();
            var negativePrompt = $('#nig_negative_prompt').val();
            
            // Handle seed
            var useRandomSeed = $('#nig_random_seed').is(':checked');
            var seed = useRandomSeed ? Math.floor(Math.random() * 9999999) + 1 : parseInt($('#nig_seed').val());
            
            // Find current item data
            var currentItem = itemsData.find(function(item) {
                return item.id == postId;
            });
            
            var currentTitle = currentItem ? currentItem.title : 'Item #' + postId;
            var currentPermalink = currentItem && currentItem.permalink ? currentItem.permalink : '';
            
            $('#nig-current-item').text(currentTitle);
            
            // Check if we should skip this item
            if (featuredImageHandling === 'skip' && itemsWithFeaturedImage.indexOf(parseInt(postId)) !== -1) {
                // Record skip result
                batchResults.push({
                    postId: postId,
                    success: true,
                    message: '<?php _e("Skipped - Already has featured image", "novassium-image-generator"); ?>',
                    title: currentTitle,
                    status: 'skipped',
                    permalink: currentPermalink
                });
                
                // Update progress
                processedItems++;
                $('#nig-current').text(processedItems);
                var progress = (processedItems / totalItems) * 100;
                $('#nig-progress-bar').width(progress + '%');
                
                // Process next item
                processNextItem();
                return;
            }
            
            var data = {
                action: 'nig_generate_batch_item',
                post_id: postId,
                style_preset: stylePreset,
                width: width,
                height: height,
                negative_prompt: negativePrompt,
                seed: seed,
                featured_image_handling: featuredImageHandling,
                nonce: '<?php echo wp_create_nonce( 'nig_generate_batch_item_nonce' ); ?>'
            };
            
            $.post(ajaxurl, data, function(response) {
                // Record result
                batchResults.push({
                    postId: postId,
                    success: response.success,
                    message: response.data ? response.data.message : '',
                    title: currentTitle,
                    status: response.success ? 'success' : 'failed',
                    permalink: currentPermalink
                });
                
                // Update progress
                processedItems++;
                $('#nig-current').text(processedItems);
                var progress = (processedItems / totalItems) * 100;
                $('#nig-progress-bar').width(progress + '%');
                
                // Process next item
                processNextItem();
            }).fail(function() {
                // Record failure
                batchResults.push({
                    postId: postId,
                    success: false,
                    message: '<?php _e("Request failed. Network error or timeout.", "novassium-image-generator"); ?>',
                    title: currentTitle,
                    status: 'failed',
                    permalink: currentPermalink
                });
                
                // Update progress
                processedItems++;
                $('#nig-current').text(processedItems);
                var progress = (processedItems / totalItems) * 100;
                $('#nig-progress-bar').width(progress + '%');
                
                // Process next item
                processNextItem();
            });
        }
        
        function finalizeBatchProcessing() {
            // Reset processing state
            processingActive = false;
            $('#nig_generate_button').prop('disabled', false).html('<?php _e("Generate Images", "novassium-image-generator"); ?>');
            
            // Count results by status
            var successes = 0;
            var failures = 0;
            var skipped = 0;
            
            batchResults.forEach(function(result) {
                if (result.status === 'success') {
                    successes++;
                } else if (result.status === 'skipped') {
                    skipped++;
                } else {
                    failures++;
                }
            });
            
            // Create result summary
            var resultHtml = '<h3><?php _e("Batch Processing Results", "novassium-image-generator"); ?></h3>';
            
            resultHtml += '<div class="notice notice-info"><p>';
            resultHtml += '<?php _e("Completed: ", "novassium-image-generator"); ?>' + totalItems + ' <?php _e("items", "novassium-image-generator"); ?><br>';
            resultHtml += '<?php _e("Successful: ", "novassium-image-generator"); ?>' + successes + '<br>';
            resultHtml += '<?php _e("Skipped: ", "novassium-image-generator"); ?>' + skipped + '<br>';
            resultHtml += '<?php _e("Failed: ", "novassium-image-generator"); ?>' + failures;
            resultHtml += '</p></div>';
            
            // Create detailed results table
            resultHtml += '<h4><?php _e("Detailed Results", "novassium-image-generator"); ?></h4>';
            resultHtml += '<table class="widefat striped">';
            resultHtml += '<thead><tr>';
            resultHtml += '<th><?php _e("Content", "novassium-image-generator"); ?></th>';
            resultHtml += '<th><?php _e("Status", "novassium-image-generator"); ?></th>';
            resultHtml += '<th><?php _e("Message", "novassium-image-generator"); ?></th>';
            resultHtml += '<th><?php _e("Actions", "novassium-image-generator"); ?></th>';
            resultHtml += '</tr></thead><tbody>';
            
            batchResults.forEach(function(result) {
                resultHtml += '<tr>';
                
                // Content title with permalink (if available)
                if (result.permalink) {
                    resultHtml += '<td><a href="' + result.permalink + '" target="_blank">' + result.title + ' (ID: ' + result.postId + ')</a></td>';
                } else {
                    // If permalink not available, just show the title
                    resultHtml += '<td>' + result.title + ' (ID: ' + result.postId + ')</td>';
                }
                
                // Status with color coding
                if (result.status === 'success') {
                    resultHtml += '<td><span style="color:green;"><?php _e("Success", "novassium-image-generator"); ?></span></td>';
                } else if (result.status === 'skipped') {
                    resultHtml += '<td><span style="color:blue;"><?php _e("Skipped", "novassium-image-generator"); ?></span></td>';
                } else {
                    resultHtml += '<td><span style="color:red;"><?php _e("Failed", "novassium-image-generator"); ?></span></td>';
                }
                
                // Message
                resultHtml += '<td>' + result.message + '</td>';
                
                // Action links
                resultHtml += '<td>';
                
                // Edit link
                resultHtml += '<a href="<?php echo admin_url(); ?>post.php?post=' + result.postId + '&action=edit" target="_blank" class="button button-small">';
                resultHtml += '<span class="dashicons dashicons-edit" style="vertical-align: text-bottom;"></span> <?php _e("Edit", "novassium-image-generator"); ?>';
                resultHtml += '</a> ';
                
                // View link using the permalink
                if (result.permalink) {
                    resultHtml += '<a href="' + result.permalink + '" target="_blank" class="button button-small">';
                    resultHtml += '<span class="dashicons dashicons-visibility" style="vertical-align: text-bottom;"></span> <?php _e("View", "novassium-image-generator"); ?>';
                    resultHtml += '</a>';
                } else {
                    // If permalink not available, use a fallback link that will at least attempt to view the content
                    resultHtml += '<a href="<?php echo home_url('?p='); ?>' + result.postId + '" target="_blank" class="button button-small">';
                    resultHtml += '<span class="dashicons dashicons-visibility" style="vertical-align: text-bottom;"></span> <?php _e("View", "novassium-image-generator"); ?>';
                    resultHtml += '</a>';
                }
                
                resultHtml += '</td>';
                
                resultHtml += '</tr>';
            });
            
            resultHtml += '</tbody></table>';
            
            // Display results
            $('#nig-batch-result').html(resultHtml);
            $('#nig-batch-processing').hide();
        }
    });
    </script>
    <?php
}

/**
 * Enqueue Batch Processing Scripts
 */
function nig_enqueue_batch_scripts( $hook ) {
    if ( strpos( $hook, 'nig_batch_processing' ) === false ) {
        return;
    }

    wp_enqueue_style( 'nig-admin-css', NIG_PLUGIN_URL . 'assets/css/admin.css', array(), '1.0' );
    wp_enqueue_style( 'dashicons' );
    wp_enqueue_script( 'jquery' );
}
add_action( 'admin_enqueue_scripts', 'nig_enqueue_batch_scripts' );

/**
 * AJAX Handler to Get Batch Items
 */
function nig_handle_get_batch_items_ajax() {
    // Verify nonce
    if (!isset($_POST['nonce']) || !wp_verify_nonce($_POST['nonce'], 'nig_get_batch_items_nonce')) {
        wp_send_json_error(array('message' => __('Security check failed.', 'novassium-image-generator')));
    }

    if (!current_user_can('manage_options')) {
        wp_send_json_error(array('message' => __('You do not have permission to do this.', 'novassium-image-generator')));
    }

    $batch_type = isset($_POST['batch_type']) ? sanitize_text_field($_POST['batch_type']) : '';

    if (empty($batch_type)) {
        wp_send_json_error(array('message' => __('No batch type specified.', 'novassium-image-generator')));
    }

    $args = array(
        'post_type'      => '',
        'posts_per_page' => 100, // Limit to prevent performance issues
        'post_status'    => 'publish',
        'orderby'        => 'title',
        'order'          => 'ASC',
    );

    switch ($batch_type) {
        case 'posts':
            $args['post_type'] = 'post';
            break;
        case 'pages':
            $args['post_type'] = 'page';
            break;
        case 'products':
            $args['post_type'] = 'product';
            break;
        default:
            wp_send_json_error(array('message' => __('Invalid batch type.', 'novassium-image-generator')));
    }

    $query = new WP_Query($args);

    if (!$query->have_posts()) {
        wp_send_json_error(array('message' => sprintf(__('No %s found.', 'novassium-image-generator'), $batch_type)));
    }

    $items = array();
    while ($query->have_posts()) {
        $query->the_post();
        $post_id = get_the_ID();
        $has_thumbnail = has_post_thumbnail($post_id);
        $permalink = get_permalink($post_id);
        
        $items[] = array(
            'id'                => $post_id,
            'title'             => get_the_title(),
            'has_featured_image' => $has_thumbnail,
            'permalink'         => $permalink
        );
    }
    wp_reset_postdata();

    wp_send_json_success($items);
}
add_action('wp_ajax_nig_get_batch_items', 'nig_handle_get_batch_items_ajax');

/**
 * AJAX Handler for Processing a Single Batch Item
 */
function nig_handle_generate_batch_item() {
    // Verify nonce
    if (!isset($_POST['nonce']) || !wp_verify_nonce($_POST['nonce'], 'nig_generate_batch_item_nonce')) {
        wp_send_json_error(array('message' => __('Security check failed.', 'novassium-image-generator')));
    }

    if (!current_user_can('manage_options')) {
        wp_send_json_error(array('message' => __('You do not have permission to do this.', 'novassium-image-generator')));
    }

    $post_id = isset($_POST['post_id']) ? intval($_POST['post_id']) : 0;
    $style_preset = isset($_POST['style_preset']) ? sanitize_text_field($_POST['style_preset']) : 'photographic';
    $width = isset($_POST['width']) ? intval($_POST['width']) : 1024;
    $height = isset($_POST['height']) ? intval($_POST['height']) : 1024;
    $featured_image_handling = isset($_POST['featured_image_handling']) ? sanitize_text_field($_POST['featured_image_handling']) : 'replace';
    $negative_prompt = isset($_POST['negative_prompt']) ? sanitize_textarea_field($_POST['negative_prompt']) : 'text, watermark, signature, blurry, low quality, distorted, deformed';
    $seed = isset($_POST['seed']) && intval($_POST['seed']) > 0 ? intval($_POST['seed']) : mt_rand(1, 9999999);

    if (empty($post_id)) {
        wp_send_json_error(array('message' => __('No post ID specified.', 'novassium-image-generator')));
    }

    $post = get_post($post_id);
    if (!$post) {
        wp_send_json_error(array('message' => __('Post not found.', 'novassium-image-generator')));
    }

    // Check if the post already has a featured image and handle according to setting
    $has_thumbnail = has_post_thumbnail($post_id);
    if ($has_thumbnail && $featured_image_handling === 'skip') {
        wp_send_json_success(array(
            'message' => __('Skipped - Item already has a featured image.', 'novassium-image-generator'),
            'title' => $post->post_title,
            'permalink' => get_permalink($post_id)
        ));
    }

    // Get post content and title
    $content = $post->post_content;
    $title = $post->post_title;
    $post_type = get_post_type($post_id);

    // Map post_type to nig_type for smart prompting
    $nig_type = 'post';
    if ($post_type === 'page') {
        $nig_type = 'page';
    } elseif ($post_type === 'product') {
        $nig_type = 'product';
    }

    // Generate smart prompt
    $prompt = nig_smart_prompting($title, $content, $nig_type);

    // Set up the image generation arguments
    $args = array(
        'prompt'          => $prompt,
        'style_preset'    => $style_preset,
        'samples'         => 1,
        'negative_prompt' => $negative_prompt,
        'seed'            => $seed,
        'output_format'   => 'jpeg',
        'width'           => $width,
        'height'          => $height,
    );

    // Generate the image
    $response = nig_generate_image($args);

    if (!isset($response['success']) || $response['success'] !== true) {
        // If image generation failed
        $error_message = isset($response['message']) ? $response['message'] : __('Image generation failed.', 'novassium-image-generator');
        wp_send_json_error(array(
            'message' => $error_message,
            'title' => $title,
            'permalink' => get_permalink($post_id)
        ));
    }

    // Process the generated images
    if (empty($response['images'])) {
        wp_send_json_error(array(
            'message' => __('No images were generated.', 'novassium-image-generator'),
            'title' => $title,
            'permalink' => get_permalink($post_id)
        ));
    }

    // Get the first image
    $image_url = is_array($response['images'][0]) && isset($response['images'][0]['url']) 
              ? $response['images'][0]['url'] 
              : $response['images'][0];

    // Download and save the image
    $image_data = file_get_contents($image_url);
    if (!$image_data) {
        wp_send_json_error(array(
            'message' => __('Failed to download the generated image.', 'novassium-image-generator'),
            'title' => $title,
            'permalink' => get_permalink($post_id)
        ));
    }

    // Generate a filename
    $filename = 'nig-' . sanitize_title($title) . '-' . uniqid() . '.jpg';

    // Save to media library
    $attachment_id = nig_save_image_to_media_library_and_get_id($image_data, $filename, $post_id);

    if (is_wp_error($attachment_id)) {
        wp_send_json_error(array(
            'message' => $attachment_id->get_error_message(),
            'title' => $title,
            'permalink' => get_permalink($post_id)
        ));
    }

    // If there's an existing featured image and we're replacing it, note that in the message
    $message = __('Image generated and set as featured image successfully.', 'novassium-image-generator');
    if ($has_thumbnail) {
        if ($featured_image_handling === 'replace') {
            $message = __('Image generated and replaced existing featured image.', 'novassium-image-generator');
        }
    }

    // Set as featured image
    set_post_thumbnail($post_id, $attachment_id);

    // Update usage credits
    $current_credits = get_option('nig_usage_credits', 0);
    update_option('nig_usage_credits', $current_credits + 1);

    wp_send_json_success(array(
        'message' => $message,
        'title' => $title,
        'permalink' => get_permalink($post_id)
    ));
}
add_action('wp_ajax_nig_generate_batch_item', 'nig_handle_generate_batch_item');

/**
 * Save image to media library and return attachment ID
 */
function nig_save_image_to_media_library_and_get_id($image_data, $filename, $post_id) {
    $upload_dir = wp_upload_dir();
    
    if (!is_dir($upload_dir['path'])) {
        wp_mkdir_p($upload_dir['path']);
    }
    
    $file_path = $upload_dir['path'] . '/' . sanitize_file_name($filename);
    
    if (!file_put_contents($file_path, $image_data)) {
        return new WP_Error('file_write_error', __('Failed to write image data to file.', 'novassium-image-generator'));
    }
    
    $filetype = wp_check_filetype($filename, null);
    
    $attachment = array(
        'guid'           => $upload_dir['url'] . '/' . basename($filename),
        'post_mime_type' => $filetype['type'],
        'post_title'     => sanitize_file_name(pathinfo($filename, PATHINFO_FILENAME)),
        'post_content'   => '',
        'post_status'    => 'inherit',
        'post_parent'    => $post_id,
    );
    
    $attach_id = wp_insert_attachment($attachment, $file_path, $post_id);
    
    if (is_wp_error($attach_id)) {
        return $attach_id;
    }
    
    require_once(ABSPATH . 'wp-admin/includes/image.php');
    
    $attach_data = wp_generate_attachment_metadata($attach_id, $file_path);
    wp_update_attachment_metadata($attach_id, $attach_data);
    
    return $attach_id;
}
