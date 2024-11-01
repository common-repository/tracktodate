<?php
defined('ABSPATH') or die('No script kiddies please!');
/*
  Plugin Name: TrackToDate
  Plugin URI: #
  Description: End to End Tracking for any carrier.
  Author: hitstacks
  Version: 1.0.0
  Author URI: https://hitstacks.com/
  Text Domain: tracktodate
 */

if (!defined('ABSPATH')) {
    exit; // Exit if accessed directly.
}


// Include the main WooCommerce class.
if (in_array('woocommerce/woocommerce.php', apply_filters('active_plugins', get_option('active_plugins')))) {

    if (!class_exists('HIT_TrackToDate')) {
        class HIT_TrackToDate
        {
            public function __construct()
            {
                // add_action( 'wp_enqueue_scripts', array($this,'my_scripts'));
                add_filter('plugin_action_links_' . plugin_basename(__FILE__), array($this, 'hit_tracktodate_plugin_url'));
                add_action('admin_menu', array($this, 'hit_tracktodate_view'));
                add_action('admin_menu', array($this, 'tracktodate_main_menu'));
                add_action('init', array($this, 'hit_tracktodate_nonce_check'));
                add_action('add_meta_boxes', array($this, 'add_shop_order_meta_box'));
                add_action('save_post_shop_order', array($this, 'save_shop_order_meta_box_data'));
                add_action( 'woocommerce_view_order', array($this,'tracking_ui_order_detail' ));
                add_filter( 'wp_kses_allowed_html', array($this,'allow_iframes_filter' ));
                include_once('controllor/rest-api.php');
            }
            function tracking_ui_order_detail( $order_id ){
                $order = wc_get_order( $order_id );
                $tracking_number = get_post_meta($order_id, '_tracking_number', true);
                $is_initiated = get_option($order_id.'_ttd_track_ui');
                if($is_initiated){
                    echo wp_kses('<iframe class="responsive-iframe" src="http://tracktodate.loc/tracking/?no='.$tracking_number.'" frameborder="0" style="overflow:hidden;overflow-x:hidden;overflow-y:hidden;width:100%;top:0px;left:0px;right:0px;bottom:0px;height:70vh" width="100%"></iframe>','post');
                }
            }
            function allow_iframes_filter( $allowedposttags ) {

                // Only change for users who can publish posts
                if ( !current_user_can( 'publish_posts' ) ) return $allowedposttags;
            
                // Allow iframes and the following attributes
                $allowedposttags['iframe'] = array(
                    'align' => true,
                    'width' => true,
                    'height' => true,
                    'frameborder' => true,
                    'name' => true,
                    'src' => true,
                    'id' => true,
                    'class' => true,
                    'style' => true,
                    'scrolling' => true,
                    'marginwidth' => true,
                    'marginheight' => true,
                );
            
                return $allowedposttags;
            }
            public function hit_tracktodate_plugin_url($links)
            {
                $plugin_links = array(
                    '<a href="' . admin_url('admin.php?page=hit-tracktodate-setup') . '" style="color:green;">' . __('Configure', 'hitshippo_fedex') . '</a>',
                    '<a href="https://app.tracktodate.com/support" target="_blank" >' . __('Support', 'hitshippo_fedex') . '</a>'
                );
                return array_merge($plugin_links, $links);
            }
            function hit_tracktodate_view()
            {
                add_submenu_page('options-general.php', 'TrackToDate', 'TrackToDate', 'manage_options', 'hit-tracktodate-setup', array($this, 'hit_tracktodate_setup'));
            }
            function hit_tracktodate_setup()
            {
                include_once('views/settings.php');
            }
            function tracktodate_main_menu()
            {
                $page_title = 'TrackToDate';
                $menu_title = 'TrackToDate';
                $capability = 'manage_options';
                $menu_slug  = 'hit-tracktodate-setup';
                // $function   = 'hit_tracktodate_setup';
                $icon_url   = 'dashicons-location-alt';
                $position   = 4;
                add_menu_page($page_title, $menu_title, $capability, $menu_slug, array($this, 'hit_tracktodate_setup'), $icon_url, $position);
            }
            function hit_tracktodate_nonce_check()
            {
                $store_url = get_site_url();

                if (isset($_GET['tracktodate_nounce'])) {
                    $nounce = sanitize_text_field($_GET['tracktodate_nounce']);
                    $transient = get_transient('tracktodate_transient_temp');
                    if ($transient == $nounce) {
                        wp_send_json(array('nounce' => $nounce, 'url' => $store_url));
                        die();
                    }
                    
                }
                if (isset($_GET['plugin_key']) && !empty($_GET['plugin_key'])) {

                    //$link_site_url = "https://app.tracktodate.com/link_site.php";
                    $link_site_url = "http://tracktodate.loc/link_site.php";

                    $link_site_response = wp_remote_post(
                        $link_site_url,
                        array(
                            'method'      => 'POST',
                            'timeout'     => 45,
                            'redirection' => 5,
                            'httpversion' => '1.0',
                            'blocking'    => true,
                            'headers'     => array('Content-Type' => 'application/json; charset=utf-8'),
                            'body'        => json_encode(array('nounce' => $nounce, 'url' => $store_url)),
                        )
                    );
                    $link_site_response = (is_array($link_site_response) && isset($link_site_response['body'])) ? json_decode($link_site_response['body'], true) : array();
                   
                }
            }

            function add_shop_order_meta_box()
            {

                add_meta_box(
                    'custom_meta_box',
                    __('TrackToDate', 'woocommerce'),
                    array($this, 'shop_order_tracktodate_callback'),
                    'shop_order',
                    'advanced',
                    'high'
                );
            }

            function shop_order_tracktodate_callback($post)
            {
                // add_thickbox();
                include_once('views/order_meta_content.php');
            }

            function save_shop_order_meta_box_data($post_id)
            {
                // If this is an autosave, our form has not been submitted, so we don't want to do anything.
                if (defined('DOING_AUTOSAVE') && DOING_AUTOSAVE) {
                    return;
                }
                // print_r("SS");
                // die();
                if (isset($_POST['tracking_number'])) {

                    // Update the meta field in the database.
                    update_post_meta($post_id, '_tracking_number', sanitize_text_field($_POST['tracking_number']));
                }
                // if ( isset( $_POST['order_number'] ) ) {

                //     // Update the meta field in the database.
                //     // update_post_meta( $post_id, '_order_number', sanitize_text_field( $_POST['order_number'] ) );
                // }
                if (isset($_POST['insert_track'])) {
                    $tracking_num = isset($_POST['tracking_number']) ? sanitize_text_field($_POST['tracking_number']) : '';
                    $order_ref = isset($_POST['order_number']) ? sanitize_text_field($_POST['order_number']) : '';
                    $carrier = isset($_POST['ttd_carrier_select']) ? sanitize_text_field($_POST['ttd_carrier_select']) : '';

                    if (($tracking_num && $order_ref && $carrier) && $carrier != 'none') {

                        // integrationKey,carrier,TrackingNumber
                        $pushtrackig_url = "http://tracktodate.loc/json-api/v1/pushTrackingNumbers.php";
                        $plugin_key = get_option('tracktodate_plugin_key');
                        $pushtrackig_response = wp_remote_post(
                            $pushtrackig_url,
                            array(
                                'method'      => 'POST',
                                'timeout'     => 45,
                                'redirection' => 5,
                                'httpversion' => '1.0',
                                'blocking'    => true,
                                'headers'     => array('Content-Type' => 'application/json; charset=utf-8'),
                                'body'        => json_encode(array('integrationKey' => $plugin_key, 'carrier' => $carrier, 'TrackingNumber' => $tracking_num, 'refNumber' => $order_ref)),
                            )
                        );
                        update_option('ttd_ins_trk_response', (string)$pushtrackig_response['body']);
                        // $pushtrackig_response = (is_array($pushtrackig_response) && isset($pushtrackig_response['body'])) ? json_decode($pushtrackig_response['body'], true) : array();
                        // echo "<pre>";
                        // print_r($pushtrackig_response);
                        // die();


                    }
                    if (($tracking_num && $order_ref) && $carrier == 'none') {
                        $pushtrackig_response['status'] = 'error';
                        $pushtrackig_response['msg'] = 'Please Select One Shipping Carrier';
                        $pushtrackig_response = json_encode($pushtrackig_response);
                        update_option('ttd_ins_trk_response', (string)$pushtrackig_response);
                    }
                }
                if (isset($_POST['ttk_reset_tracking_number'])) {
                    delete_option('ttd_track_ui');
                }
            }

            // function my_scripts() {
            //     wp_enqueue_style('bootstrap4', 'https://stackpath.bootstrapcdn.com/bootstrap/4.1.1/css/bootstrap.min.css');
            //     wp_enqueue_script( 'boot1','https://code.jquery.com/jquery-3.3.1.slim.min.js', array( 'jquery' ),'',true );
            //     wp_enqueue_script( 'boot2','https://cdnjs.cloudflare.com/ajax/libs/popper.js/1.14.3/umd/popper.min.js', array( 'jquery' ),'',true );
            //     wp_enqueue_script( 'boot3','https://stackpath.bootstrapcdn.com/bootstrap/4.1.1/js/bootstrap.min.js', array( 'jquery' ),'',true );
            // }

        }
    }
    new HIT_TrackToDate();
}
