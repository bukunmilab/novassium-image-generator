=== Novassium Image Generator ===
Contributors: LoquiSoft
Tags: image generation, Novassium, API, WooCommerce, AI images, content creation
Requires at least: 5.0
Tested up to: 6.7
Stable tag: 2.0
License: GPLv2 or later
License URI: https://www.gnu.org/licenses/gpl-2.0.html

Generate images for posts, pages, and products using the Novassium API. Includes a dashboard, usage tracking, template libraries, batch processing, smart prompting, and convenient shortcodes.

== Description ==

**Novassium Image Generator** is a comprehensive WordPress plugin that integrates with the Novassium API to generate images for your content. Whether you're creating blog posts, pages, or WooCommerce products, this plugin simplifies the process of generating and managing images directly from your WordPress dashboard.

**Features:**

- Enter and manage your Novassium API key.
- Generate images and set them as featured images for posts, pages, and products.
- User-friendly dashboard/playground for image generation and management.
- Track API credit usage with visual statistics.
- Template library with pre-built and industry-specific prompts.
- Batch processing for generating images for multiple items simultaneously.
- Convenient shortcode for embedding the generator anywhere on your site.
- Download generated images directly to your device.
- Multiple style presets to customize your image generation.

== Installation ==

1. Upload the `novassium-image-generator` folder to the `/wp-content/plugins/` directory.
2. Activate the plugin through the 'Plugins' menu in WordPress.
3. Navigate to **NovassiumGen** in the admin sidebar.
4. Enter your Novassium API key in the settings page.
5. Use the **Image Generator**, **Batch Processing**, and **Template Library** from the admin menu to start generating images.

== Frequently Asked Questions ==

= How do I enter my Novassium API key? =

Navigate to **NovassiumGen** > **Settings** and enter your API key in the designated field.

= Can I generate images for multiple posts at once? =

Yes, use the **Batch Processing** feature under **NovassiumGen** to select multiple posts, pages, or products and generate images in bulk.

= What are usage credits? =

Each image generation request consumes API credits based on the number of samples requested. Track your usage in the **Usage Tracking** section of the dashboard.

= How can I add the image generator to my website? =

You can use the shortcode [nig_image_generator] on any page or post to display a frontend image generation form. Only logged-in users will be able to use this feature.

= How do I download the generated images? =

After generating an image, you'll see two buttons below each image: "View Full Size" and "Download Image". Click the download button to save the image directly to your device.

== Screenshots ==

1. Admin Settings Page
2. Image Generation Dashboard
3. Usage Tracking Dashboard
4. Template Library
5. Batch Processing Interface
6. Frontend Shortcode Display
7. Multiple Style Presets

== Changelog ==

= 2.0 =
* Complete update with Novassium API integration.
* Improved user interface for easier image generation.
* Added direct image download functionality.
* Added frontend shortcode [nig_image_generator] for public-facing image generation.
* Enhanced batch processing with improved progress tracking.
* Added support for multiple aspect ratios and output formats.
* Expanded style preset options with 17 different styles.
* Improved error handling and user feedback.
* Added negative prompt capability for more precise image control.
* Added seed value option for reproducible results.
* Optimized API calls for faster processing.
* Improved image preview and results display.
* Updated documentation and help resources.

= 1.0 =
* Initial release.

== Upgrade Notice ==

= 2.0 =
Major update with enhanced API integration, new frontend shortcode, direct image downloading, and expanded style options. Update is strongly recommended for all users.
