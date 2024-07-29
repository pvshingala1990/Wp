<?php 
// Breadcrumb Code
function custom_breadcrumb() {
    // Get the current post/page ID
    $post_id = get_queried_object_id();

    // Initialize an empty breadcrumb array
    $breadcrumbs = array();

    // Add a link to the home page
    $breadcrumbs[] = '<li class="breadcrumb-item"><a href="' . home_url('/') . '">' . __('Home', 'textdomain') . '</a></li>';

    // Get the page hierarchy
    $ancestors = get_post_ancestors($post_id);

    // If there are ancestors, add them to the breadcrumb array
    if ($ancestors) {
        $ancestors = array_reverse($ancestors);

        foreach ($ancestors as $ancestor) {
            $breadcrumbs[] = '<li class="breadcrumb-item"><a href="' . get_permalink($ancestor) . '">' . get_the_title($ancestor) . '</a></li>';
        }
    }

    // Add the current page to the breadcrumb array
    $breadcrumbs[] = '<li class="breadcrumb-item active">' . get_the_title($post_id) . '</li>';

    // Output the breadcrumb trail
    echo '<ol class="breadcrumb">' . implode(' ', $breadcrumbs) . '</ol>';
}
// Pagination
?>
<nav aria-label="Page navigation example">
<?php
$total_pages = $lr_post_query->max_num_pages;
if ($total_pages > 1) {
    echo '<ul class="pagination justify-content-center">';    
    // Numeric pagination links
    $current_page = max(1, get_query_var('paged'));
    $argsp = array(
        'total'        => $total_pages,
        'current'      => $current_page,
        'show_all'     => false,
        'end_size'     => 1,
        'mid_size'     => 3,
        'prev_next'    => true,
        'type'         => 'array',
        'prev_text'          => __( '<span class="bi bi-chevron-left"></span>' ),
		'next_text'          => __( '<span class="bi bi-chevron-right"></span>' ),
    );

    $pagination_links = paginate_links($argsp);
    if ($pagination_links) {
        foreach ($pagination_links as $page_link) {
            echo '<li class="page-item">' . str_replace(array('page-numbers','current','prev','next'),array('page-numbers page-link','active','icon-link','icon-link'),$page_link) . '</li>';
        }
    }    
    echo '</ul>';
}
?>
</nav>
<?php 
// Hook to add meta box
add_action('add_meta_boxes', 'custom_page_header_title');

// Function to add meta box
function custom_page_header_title() {
    add_meta_box(
        'custom_page_header_title', // Unique ID
        'Header Title Section', // Box title
        'render_custom_page_header_title', // Content callback function
        array('page','post'), // Post type
        'normal', // Context (normal, advanced, side)
        'high' // Priority (high, core, default, low)
    );
}

// Function to render meta box content
function render_custom_page_header_title($post) {
    // Retrieve existing values from the database
    $custom_title = get_post_meta($post->ID, '_custom_title', true);
    $custom_content = get_post_meta($post->ID, '_custom_content', true);
    $custom_image = get_post_meta($post->ID, '_custom_image', true);

    // Output fields
    ?>
    <label for="custom_title">Custom Title:</label>
    <input type="text" id="custom_title" name="custom_title" value="<?php echo esc_attr($custom_title); ?>" style="width: 100%;">

    <br>

    <label for="custom_content">Custom Content:</label>
    <textarea id="custom_content" name="custom_content" style="width: 100%;"><?php echo esc_textarea($custom_content); ?></textarea>

    <br>

    <label for="custom_image">Custom Image:</label>
    <div>
        <input type="text" id="custom_image" name="custom_image" value="<?php echo esc_attr($custom_image); ?>" style="width: 60%;" readonly>
        <input type="button" id="upload_image_button" class="button" value="Upload Image">
    </div>

    <script>
        jQuery(document).ready(function ($) {
            // Media uploader script
            $('#upload_image_button').click(function (e) {
                e.preventDefault();

                var customUploader = wp.media({
                    title: 'Choose Image',
                    button: {
                        text: 'Choose Image'
                    },
                    multiple: false
                });

                customUploader.on('select', function () {
                    var attachment = customUploader.state().get('selection').first().toJSON();
                    $('#custom_image').val(attachment.url);
                });

                customUploader.open();
            });
        });
    </script>
    <?php
}

// Hook to save meta box data
add_action('save_post', 'save_custom_page_header_title');

// Function to save meta box data
function save_custom_page_header_title($post_id) {

    // Check if nonce is set
    if (!isset($_POST['custom_page_header_title'])) {
        //return;
    }

   
    // Check if autosave
    if (defined('DOING_AUTOSAVE') && DOING_AUTOSAVE) {
        return;
    }

    // Check user permissions
    if (isset($_POST['post_type']) && 'page' == $_POST['post_type'] && current_user_can('edit_page', $post_id)) {
        // Save custom title
        if (isset($_POST['custom_title'])) {
            update_post_meta($post_id, '_custom_title', sanitize_text_field($_POST['custom_title']));
        }

        // Save custom content
        if (isset($_POST['custom_content'])) {
            update_post_meta($post_id, '_custom_content', wp_kses_post($_POST['custom_content']));
        }

        // Save custom image
        if (isset($_POST['custom_image'])) {
            update_post_meta($post_id, '_custom_image', esc_url_raw($_POST['custom_image']));
        }
    }
}

function get_post_read_time_from_content($post = null) {
    $post = get_post($post);
    $words_per_minute = 200; // average reading speed
    $words = str_word_count(strip_tags($post->post_content));
    $minutes = ceil($words / $words_per_minute);
    return $minutes;
}


// Hide ACF menu
function hide_acf_menu()
{
    remove_menu_page('edit.php?post_type=acf-field-group');
    remove_menu_page('tools.php');
    remove_submenu_page('options-general.php', 'options-permalink.php');
    remove_submenu_page('options-general.php', 'options-privacy.php');
    remove_submenu_page('options-general.php', 'options-media.php');    
    remove_submenu_page('options-general.php', 'options-reading.php');    
    remove_submenu_page('options-general.php', 'options-writing.php');
    remove_menu_page('edit.php');
    remove_menu_page('wpcf7');
    remove_menu_page('plugins.php');

    remove_submenu_page('index.php','update-core.php');

    global $submenu;
    unset($submenu['edit.php?post_type=page'][10]);
       
    
    remove_menu_page('edit-comments.php');    

    define('DISALLOW_FILE_EDIT', true);
}
add_action('admin_menu', 'hide_acf_menu');


// Woocommerce hide update database notice
add_action('admin_init', 'hide_woocommerce_db_update_notice');
function hide_woocommerce_db_update_notice() {    
    WC_Admin_Notices::remove_notice('update');    
}