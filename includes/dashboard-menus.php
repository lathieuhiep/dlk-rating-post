<?php
/**
 * Top Level Menu and submenu
 */

function dlk_rating_options_page() {

    // add top level menu page
    add_menu_page(
        esc_html__( 'Ratings', 'dlk-rating-post' ),
        esc_html__( 'Ratings', 'dlk-rating-post' ),
        'manage_options',
        'dlk_rating',
        'dlk_rating_page_html',
        'dashicons-star-empty'
    );

    add_submenu_page(
        'dlk_rating',
        esc_html__( 'Settings', 'dlk-rating-post' ),
        esc_html__( 'Settings', 'dlk-rating-post' ),
        'manage_options',
        'dlk_rating_settings',
        'dlk_rating_settings_html'
    );

}
add_action( 'admin_menu', 'dlk_rating_options_page' );

/* The page to display all rated posts */
function dlk_rating_page_html() {

    // check user capabilities
    if (!current_user_can('manage_options')) {
        return;
    }

    global $wpdb;

    // SQL query to get all the content which has the meta key 'dlk_rating'. Group the content by the ID and get an average rating on each
    $sql = "SELECT * FROM ( SELECT p.post_title 'title', p.guid 'link', post_id, AVG(meta_value) AS rating, count(meta_value) 'count' FROM {$wpdb->prefix}postmeta pm";
    $sql .= " LEFT JOIN wp_posts p ON p.ID = pm.post_id";
    $sql .= " where meta_key = 'dlk_rating' group by post_id ) as ratingTable ORDER BY rating DESC";

    $result = $wpdb->get_results( $sql, 'ARRAY_A' );

?>

    <div class="wrap">
        <h1>
            <?php echo esc_html(get_admin_page_title()); ?>
        </h1>

        <div id="poststuff">
            <table class="form-table widefat">
                <thead>
                    <tr>
                        <td>
                            <strong>
                                <?php esc_html_e( 'Content', 'dlk-rating-post' ); ?>
                            </strong>
                        </td>

                        <td>
                            <strong>
                                <?php esc_html_e( 'Rating', 'dlk-rating-post' ); ?>
                            </strong>
                        </td>

                        <td>
                            <strong>
                                <?php esc_html_e( 'No. of Ratings', 'dlk-rating-post' ); ?>
                            </strong>
                        </td>
                    </tr>
                </thead>

                <tbody>
                    <?php
                    foreach ( $result as $row ) {
                        echo '<tr>';
                        echo '<td>' . $row['title'] . '<br/><a href="' . $row['link'] . '" target="_blank">' . esc_html__( 'View the Content', 'dlk-rating-post' ) . '</a></td>';
                        echo '<td>' . round( $row['rating'], 2 ) . '</td>';
                        echo '<td>' . $row['count'] . '</td>';
                        echo '</tr>';
                    }
                    ?>
                </tbody>
            </table>
        </div>
    </div>

<?php

}

/**
 * Registering Settings for Rating Settings
 */
function dlk_ratings_settings_init()
{
    // Registering the setting 'dlk_rating_types' for the page 'dlk_rating_settings'
    register_setting( 'dlk_rating_settings', 'dlk_rating_types');

    // Registering the section 'dlk_rating_section' for the page 'dlk_rating_settings'
    add_settings_section(
        'dlk_rating_section',
        '',
        '',
        'dlk_rating_settings'
    );

    // Registering the field for the setting 'dlk_rating_types' on the page 'dlk_rating_settings' under section 'dlk_rating_section'
    add_settings_field(
        'dlk_rating_types', // as of WP 4.6 this value is used only internally
        // use $args' label_for to populate the id inside the callback
        __('Show Rating on Content:', 'wporg'),
        'dlk_rating_types_html',
        'dlk_rating_settings',
        'dlk_rating_section',
        [
            'label_for'         => 'dlk_rating_pages',
            'class'             => 'wporg_row',
            'wporg_custom_data' => 'custom',
        ]
    );
}
add_action('admin_init', 'dlk_ratings_settings_init');

function dlk_rating_types_html( $args ) {
    $post_types = get_post_types( array( 'public' => true ), 'objects' );

    // get the value of the setting we've registered with register_setting()
    $rating_types = get_option('dlk_rating_types', array());

    if( ! empty( $post_types ) ) {
        foreach ( $post_types as $key => $value ) {
            $isChecked = in_array( $key, $rating_types );
            echo '<input ' . ( $isChecked ? 'checked="checked"' : '' ) . ' type="checkbox" name="dlk_rating_types[]" value="' . $key . '" /> ' . $value->label . '<br/>';
        }
    }
}

function dlk_rating_settings_html() {

    // check user capabilities
    if (!current_user_can('manage_options')) {
        return;
    }

?>

    <div class="wrap">
        <h1>
            <?php echo esc_html(get_admin_page_title()); ?>
        </h1>

        <form action="options.php" method="post">
            <?php
            // output security fields for the registered setting "dlk_rating_settings"
            settings_fields('dlk_rating_settings');

            // output setting sections and their fields
            do_settings_sections('dlk_rating_settings');

            // output save settings button
            submit_button('Save Settings');
            ?>
        </form>
    </div>

<?php

}

/**
 * Checking for Rating
 * @return void
 */
function dlk_check_for_rating() {

    $rating_types = get_option( 'dlk_rating_types', array() );
    if( is_array( $rating_types ) && count( $rating_types ) > 0 && is_singular( $rating_types ) ) {
        $rate_id = get_the_id();
        $ratingCookie = isset( $_COOKIE['dlk_rating'] ) ? unserialize( base64_decode( $_COOKIE['dlk_rating'] ) ) : array();
        if( ! in_array( $rate_id, $ratingCookie ) ) {
            // This content has not been rated yet by that user
            add_action( 'wp_enqueue_scripts', 'dlk_rating_scripts');
            add_action( 'wp_footer', 'dlk_rating_render' );
        }
    }

}

add_action( 'template_redirect', 'dlk_check_for_rating' );

/**
 * Enqueueing Scripts
 * @return void
 */
function dlk_rating_scripts() {

    wp_enqueue_style( 'rating-css', dlk_rating_post_path . 'assets/css/dlk-rating-post.css', array(), '', 'screen' );
    wp_register_script( 'rating-js', dlk_rating_post_path . 'assets/js/dlk-rating-post.js', array('jquery'), '', true );
    wp_localize_script( 'rating-js', 'dlk_object', array(
        'ajax_url' => admin_url( 'admin-ajax.php' ),
        'nonce'    => wp_create_nonce( 'dlk_rating' ),
        'text'     => array(
            'close_rating' => __( 'Close Rating', 'dlk-rating-post' ),
            'rate_it' => __( 'Rate It', 'dlk-rating-post' ),
            'choose_rate' => __( 'Choose a Rate', 'dlk-rating-post' ),
            'submitting' => __( 'Submitting...', 'dlk-rating-post' ),
            'thank_you' => __( 'Thank You for Your Rating!', 'dlk-rating-post' ),
            'submit' => __( 'Submit', 'dlk-rating-post' ),
        )
    ));

    wp_enqueue_script( 'rating-js' );

}

add_action( 'wp_ajax_submit_rating', 'dlk_submit_rating' );
add_action( 'wp_ajax_nopriv_submit_rating', 'dlk_submit_rating' );
/**
 * Submitting Rating
 * @return string  JSON encoded array
 */
function dlk_submit_rating() {
    check_ajax_referer( 'dlk_rating', '_wpnonce', true );
    $result = array( 'success' => 1, 'message' => '' );
    $ratingCookie = isset( $_COOKIE['dlk_rating'] ) ? unserialize( base64_decode( $_COOKIE['dlk_rating'] ) ) : array();
    $rate_id = isset( $_POST['post_id'] ) ? $_POST['post_id'] : 0;

    if( ! $ratingCookie ) {
        $ratingCookie = array();
    }

    $ratingCookie = array();
    if( $rate_id > 0 ) {
        if( ! in_array( $rate_id, $ratingCookie ) ) {
            $rate_value = isset( $_POST['rating'] ) ? $_POST['rating'] : 0;
            if( $rate_value > 0 ) {

                $success = add_post_meta( $rate_id, 'dlk_rating', $rate_value );

                if( $success ) {
                    $result['message'] = __( 'Thank you for rating!', 'rc' );
                    $ratingCookie[] = $rate_id;
                    $expire = time() + 30*DAY_IN_SECONDS;
                    setcookie( 'dlk_rating', base64_encode(serialize( $ratingCookie )), $expire, COOKIEPATH, COOKIE_DOMAIN );
                    $_COOKIE['dlk_rating'] = base64_encode(serialize( $ratingCookie ));
                }
            } else {
                $result['success'] = 0;
                $result['message'] = __( 'Something went wrong. Try to rate later', 'rc' );
            }
        } else {
            $result['success'] = 0;
            $result['message'] = __( 'You have already rated this content.', 'rc' );
        }
    } else {
        $result['success'] = 0;
        $result['message'] = __( 'Something went wrong. Try to rate later', 'rc' );
    }
    echo json_encode( $result );
    wp_die();
}

/**
 * Render Rating
 * @return void
 */
function dlk_rating_render() {

    $ratingValues = 5;
    ?>

    <div id="contentRating" class="rc-rating">
        <button type="button" id="toggleRating" class="active">
            <span class="text">
                <?php _e( 'Rate It', 'rc' ); ?>
            </span>
            <span class="arrow"></span>
        </button>
        <div id="entryRating" class="rc-rating-content active">
            <div class="errors" id="ratingErrors"></div>
            <ul>
                <?php for( $i = 1; $i <= $ratingValues; $i++ ) {
                    echo '<li>';
                    echo '<input type="radio" name="ratingValue" value="' . $i . '" id="rating' . $i . '"/>';;

                    echo '<label for="rating' . $i . '">';
                    echo $i;
                    echo '</label>';
                    echo '</li>';
                }
                ?>

            </ul>
            <button type="button" data-rate="<?php echo get_the_id(); ?>"id="submitRating"><?php _e( 'Submit', 'rc' ); ?></button>
        </div>
    </div>
    <?php
}