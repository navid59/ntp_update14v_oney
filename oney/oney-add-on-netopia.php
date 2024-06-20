<?php

/**

 * Plugin Name: Oney Addon Netopia - 3x4x Rate prin cardul de debit

 * Plugin URI: https://oney.ro/

 * Description: This plugin adds an Oney widget displaying important info regarding the payment option throughZ the Netopia plugin.

 * Version: 1.4

 * Author: Oney Romania

 * Author URI: https://oney.ro/

 **/
 
/* BEGIN PLUGIN SETTIGN SPAGE */
// Define a global variable to store the link to metoda de plata value

function create_oney_netopia_page() {
    global $wpdb;

    // Create the table if not exists
    $table_name = $wpdb->prefix . 'oney_netopia_vars';
    
    $sql = "CREATE TABLE IF NOT EXISTS $table_name (
        id INT AUTO_INCREMENT PRIMARY KEY,
        oney_name TEXT,
        oney_value TEXT,
        created_at DATETIME DEFAULT CURRENT_TIMESTAMP
    ) DEFAULT CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;";

    // Execute the query
    require_once(ABSPATH . 'wp-admin/includes/upgrade.php');
    dbDelta($sql);
    
    // Check if the page already exists
    $page_query = new WP_Query( array(
        'post_type' => 'page',
        'post_status' => array( 'publish'), // Include all statuses
        'posts_per_page' => 1,
        'title' => 'Oferta Rate Oney'
    ) );

    if( ! $page_query->have_posts() ) {
        // Page doesn't exist, so create it
        $page_args = array(
            'post_title'    => 'Oferta Rate Oney',
            'post_content'  => '[oney-netopia-metoda-plata]',
            'post_status'   => 'publish',
            'post_type'     => 'page'
        );

        // Insert the post into the database and store the ID
        $page_id = wp_insert_post( $page_args );
        

    } else {
        // Page already exists, so retrieve its ID
        $page = $page_query->posts[0];
        $page_id = $page->ID;
    }
    
    // Check if an entry with oney_name = 'oney_netopia_details_page_id' exists
    $existing_entry = $wpdb->get_row( $wpdb->prepare( "SELECT * FROM $table_name WHERE oney_name = %s", 'oney_netopia_details_page_id' ) );

    if ( $existing_entry ) {
        // Entry already exists, so update its value
        $wpdb->update( 
            $table_name, 
            array( 
                'oney_value' => $page_id
            ), 
            array( 
                'oney_name' => 'oney_netopia_details_page_id'
            ) 
        );
    } else {
        // Entry doesn't exist, so insert a new entry
        $wpdb->insert( 
            $table_name, 
            array( 
                'oney_name' => 'oney_netopia_details_page_id',
                'oney_value' => $page_id
            ) 
        );
    }

    // Restore original post data
    wp_reset_postdata();
}


// Hook into the activation function and create the page
register_activation_hook( __FILE__, 'create_oney_netopia_page' );

function get_oney_netopia_details_page_id() {
    global $wpdb;

    // Query the database to get the oney_value of 'oney_netopia_details_page_id'
    $table_name = $wpdb->prefix . 'oney_netopia_vars';
    $result = $wpdb->get_row( $wpdb->prepare( "SELECT oney_value FROM $table_name WHERE oney_name = %s", 'oney_netopia_details_page_id' ) );

    if ($result) {
        // The value was retrieved successfully, return it
        return $result->oney_value;
    } else {
        // The value was not found in the database, return null or handle as needed
        return null;
    }
}



// Register plugin settings
function oney_addon_netopia_register_settings() {
    // Add a section for plugin settings
    add_settings_section(
        'oney_addon_netopia_settings_section',
        'Oney Addon Netopia Settings',
        'oney_addon_netopia_settings_section_callback',
        'oney_addon_netopia'
    );

    // Add a field for "Pagina detalii metode de plata" URL
    /*
    add_settings_field(
        'oney_addon_netopia_detalii_metode_plata_url',
        'Pagina detalii metode de plata',
        'oney_addon_netopia_detalii_metode_plata_url_callback',
        'oney_addon_netopia',
        'oney_addon_netopia_settings_section'
    );
    */
    
    // Add a field for "Titlu metoda de plata" with default value
    add_settings_field(
        'oney_addon_netopia_titlu_metoda_plata',
        'Titlu Checkout Metoda de plata',
        'oney_addon_netopia_titlu_metoda_plata_callback',
        'oney_addon_netopia',
        'oney_addon_netopia_settings_section',
        array(
            'default' => '<span>sau în 3-4 rate prin </span>'
        )
    );
    
    // Add a field for hiding payment options
    add_settings_field(
        'oney_addon_netopia_hide_payment_options',
        'Ascunde optiunile de plata din checkout generate Netopia(Credit Card, SMS, Bitcoin, etc.)',
        'oney_addon_netopia_hide_payment_options_callback',
        'oney_addon_netopia',
        'oney_addon_netopia_settings_section'
    );

    // Register the settings
    register_setting('oney_addon_netopia_settings_group', 'oney_addon_netopia_titlu_metoda_plata');
    register_setting('oney_addon_netopia_settings_group', 'oney_addon_netopia_hide_payment_options');
    //register_setting('oney_addon_netopia_settings_group', 'oney_addon_netopia_detalii_metode_plata_url');
}
add_action('admin_init', 'oney_addon_netopia_register_settings');

// Section callback function
function oney_addon_netopia_settings_section_callback() {
    echo '<p>Aici vei putea sa iti configurezi setarile modului.</p>';
// Display the shortcode in a box with a different background color and border
    echo '<div style="background-color: #f9f9f9; border: 1px solid #ccc; padding: 10px; margin-top: 10px;">';
    echo '<p style="margin-bottom: 5px;">Informațiile de plata pentru metoda de plata Oney sunt automat generate si introduse in pagina <a href="/oferta-rate-oney" target="_blank">/oferta-rate-oney</a> </p>';
    echo '<p style="margin-top: 0;">In cazul in care aveti o pagina dedicata cu toate metodele de plata active de pe site, atunci tot ce trebuie sa faceti este să copiați shortcode-ul următor <strong style="font-size:20px;">[oney-netopia-metoda-plata]</strong> si sa il plasati in pagina & pozitia dorita.</p>';
    echo '</div>';    
}

// Field callback function
/*
function oney_addon_netopia_detalii_metode_plata_url_callback() {
    $value = get_option('oney_addon_netopia_detalii_metode_plata_url');
    echo '<input style="width:100%;" type="text" name="oney_addon_netopia_detalii_metode_plata_url" value="' . esc_attr($value) . '" placeholder="/metode-de-plata"/>';
}
*/

// Field callback function for "Titlu metoda de plata"
function oney_addon_netopia_titlu_metoda_plata_callback() {
    $value = get_option('oney_addon_netopia_titlu_metoda_plata');
    $default = '<span> sau în 3-4 rate prin <img src="/wp-content/plugins/oney-add-on-netopia/images/oney3x4x-logo.png" style="display: inline; width: 95px;"></span>';

    echo '<div class="oney-titlu-metoda-plata-editor">';
    wp_editor($value ?: $default, 'oney_addon_netopia_titlu_metoda_plata', array('textarea_name' => 'oney_addon_netopia_titlu_metoda_plata', 'textarea_rows' => 5, 'editor_class' => 'widefat', 'default_editor' => 'html', 'media_buttons' => false, 'quicktags' => true, 'teeny' => false, 'dfw' => false, 'tinymce' => true, 'drag_drop_upload' => false));
    echo '<div>Varianta default: '.$default.'</div>';
    echo '</div>';
}


// Callback function for hiding payment options
function oney_addon_netopia_hide_payment_options_callback() {
    $hide_payment_options = get_option('oney_addon_netopia_hide_payment_options', 'yes'); // Default value is 'yes'

    echo '<select id="oney_addon_netopia_hide_payment_options" name="oney_addon_netopia_hide_payment_options">';
    echo '<option value="yes" ' . selected('yes', $hide_payment_options, false) . '>DA</option>';
    echo '<option value="no" ' . selected('no', $hide_payment_options, false) . '>NU</option>';
    echo '</select>';
}


// Add plugin settings page
function oney_addon_netopia_add_settings_page() {
    add_options_page(
        'Oney Addon Netopia Settings',
        'Oney Addon Netopia',
        'manage_options',
        'oney_addon_netopia_settings',
        'oney_addon_netopia_settings_page'
    );
}
add_action('admin_menu', 'oney_addon_netopia_add_settings_page');

// Render settings page
function oney_addon_netopia_settings_page() {
    ?>
    <div class="wrap">
        <h2>Oney Addon Netopia Settings</h2>
        <form method="post" action="options.php">
            <?php settings_fields('oney_addon_netopia_settings_group'); ?>
            <?php do_settings_sections('oney_addon_netopia'); ?>
            <?php submit_button(); ?>
        </form>
    </div>
    <?php
}

// Add settings link on plugins page
function oney_addon_netopia_add_settings_link($links) {
    $settings_link = '<a href="options-general.php?page=oney_addon_netopia_settings">Settings</a>';
    array_unshift($links, $settings_link);
    return $links;
}
add_filter('plugin_action_links_' . plugin_basename(__FILE__), 'oney_addon_netopia_add_settings_link');


/* END PLUGIN SETTINGS */





function enqueue_custom_scripts() {
    // Enqueue the image script
    wp_enqueue_script('oney-logo', get_template_directory_uri() . '/wp-content/plugins/oney-add-on-netopia/images/oney3x4x-logo.png');
}
add_action('wp_enqueue_scripts', 'enqueue_custom_scripts');

 
// ENQUE CSS
 function enqueue_oney_netopia_addon_css() {
    // Enqueue the CSS file provided by the plugin
    wp_enqueue_style( 'oney-netopia-addon-css', plugins_url( '/css/oney-netopia-addon.css', __FILE__ ) );
}
add_action( 'wp_enqueue_scripts', 'enqueue_oney_netopia_addon_css' );
 
function oneynetopia_34_single_product_page_simulator($product_price, $cart_total, $display){
    $oney_netopia_details_page_id = get_oney_netopia_details_page_id();
    $oney_details_page_url = get_permalink( $oney_netopia_details_page_id );
    
    $html = "<script>
    // Function to update cart total dynamically
    function updateCartTotal() {
        jQuery.ajax({
            type: 'POST',
            url: '". admin_url('admin-ajax.php')."',
            data: {
                'action': 'get_cart_total'
            },
            success: function(response) {
                // Update the cart total on the page
                jQuery('.cart-total-oney-netopia').text(response);
            }
        });
    }

    // Call the function to update cart total on page load
    jQuery(document).ready(function($) {
        updateCartTotal();
    });

    // Trigger AJAX update after adding to cart
    jQuery(document).on('added_to_cart removed_from_cart', function() {
        updateCartTotal();
    });
    </script>
    <div class='oney-netopia-container-main' style='display:".$display."'>
        <div class='oney-netopia-container-single-product' style=''>
            <div class='cart-total-oney-netopia' style='display:none;'>". $cart_total ."</div>
    
            <img id='oney-netopia-image' src='/wp-content/plugins/oney-add-on-netopia/images/oney-3-4-rate-logo.png' title='Oney 3-4 Rate cu cardul de debit' style=''>
    
    
            <p class='text-oney-netopia-single-product'>Plătește online în <strong>3 sau 4 rate</strong> în doar câțiva pași! <a href='".$oney_details_page_url."' class='oney-netopia-details' target='_blank'>Vezi detalii</a></p>
            
             <div class='oney-netopia-rates-wrapper'>
                <div class='oney-netopia-rate'>
                    <span>3 Rate: </span>
                    <span class='oney-netopia-rate-value'><strong id='oney-netopia-3rate'>". number_format($product_price/3, 2)." RON</strong>/lună</span>
                </div>
                <div class='oney-netopia-rate'>
                    <span>4 Rate: </span>
                    <span class='oney-netopia-rate-value'><strong id='oney-netopia-4rate'>". number_format($product_price/4, 2)." RON</strong>/lună</span>
                </div>
            </div>
    
            
    
        </div>
    </div>";
    
    return $html;
}

function oneynetopia_single_product_variation_current_price() {
    $html = "<script>!function(t) {
  function handleProductDisplayOneyNetopia() {
    var selectedVariation = t('form.variations_form').data('product_variations').find(function(variation) {
      return Object.keys(variation.attributes).every(function(attribute) {
        return t('select[name=\\\"' + attribute + '\\\"]').val() === variation.attributes[attribute];
      });
    });

    if (selectedVariation) {
      var price = parseFloat(selectedVariation.display_price);
      var targetDiv = t('.oney-netopia-container-main');
      var targetDiv2 = t('.oney-netopia-payment-progress-bar.oney-netopia-style-bordered');

      var targetPrice3x = t('#oney-netopia-3rate');
      var targetPrice4x = t('#oney-netopia-4rate');

      targetDiv.css('display', (price >= 450 && price < 12000) ? 'block' : 'none');
      targetDiv2.css('display', (price < 450 ) ? 'block' : 'none');
      
      //var remainingAmount = Math.max(0, minPurchaseAmount - cartTotal);
      //remainingAmount = remainingAmount.toFixed(2); // Ensure only 2 decimal places

                //jQuery('.oney-netopia-remaining-amount').text(remainingAmount + ' RON');
                
        targetPrice3x.text((price/3).toFixed(2) + ' RON');
        targetPrice4x.text((price/4).toFixed(2) + ' RON')
    }
  }

  t('form.variations_form').on('change', 'select', function() {
    handleProductDisplayOneyNetopia();
  });
}(jQuery);

</script>";
    return $html;
}

 
function oneynetopia_single_product_page()
{
    global $product;
    global $woocommerce;
    
    
    // Ensure the product exists
    if (! $product) {
        return;
    }

    // Get variation ID if applicable
    $variation_id = $product->get_id();
    $product_price = 0;
    // If product has variations
    if ($product->is_type('variable')) {
        // Get variation data
        $variation_data = $product->get_available_variations();
        
        $first_var_price = 0;
        // Loop through each variation data
        foreach ($variation_data as $variation) {
            // Check if variation is currently selected
            if ($first_var_price == 0 ){
                $first_var_price =$variation['display_price'];
            }
            if ($variation['variation_is_visible'] && $variation_id === $variation['variation_id']) {
                // Get variation price
                $product_price = $variation['display_price'];
                break;
            }
        }
        
        if($product_price == 0){
            $product_price = $first_var_price;
        }
    } else {
        // For simple products
        $product_price = $product->get_price();
    }
    
    // cart total
    //$cart_total = wc_format_decimal(WC()->cart->get_cart_total());
    
    $cart_total_raw = WC()->cart->get_cart_total(); // Get the raw cart total as a string

    // Get the number of decimals set in WooCommerce
    $decimals = wc_get_price_decimals();
    
    // Remove thousand separator if decimals are 0
    if ($decimals === 0) {
        $thousand_separator = wc_get_price_thousand_separator();
        $cart_total_raw = str_replace($thousand_separator, '', $cart_total_raw);
    }
    
    // Format the cart total to a decimal
    $cart_total = wc_format_decimal($cart_total_raw);
    
    if($product_price >= 450 && $product_price <= 12000 && $cart_total <= 12000){
        $html = oneynetopia_34_single_product_page_simulator($product_price, $cart_total, 'block');
        $html .= oney_450_section('none');
    } else if ($product_price < 450 && $cart_total <= 12000){
        $html = oneynetopia_34_single_product_page_simulator($product_price, $cart_total, 'none');
        $html .= oney_450_section('block');
    } else if ($cart_total > 12000){
        $html = oneynetopia_34_single_product_page_simulator($product_price, $cart_total, 'none');
        $html .= oney_450_section('none');
    } else if ($product_price > 12000){
        $html = oneynetopia_34_single_product_page_simulator($product_price, $cart_total, 'none');
        $html .= oney_450_section('none');
    }
    
    $html .=oneynetopia_single_product_variation_current_price();
    echo $html;
}

add_action('woocommerce_after_add_to_cart_form', 'oneynetopia_single_product_page');


// AJAX handler to get cart total
add_action('wp_ajax_get_cart_total', 'get_cart_total_callback');
add_action('wp_ajax_nopriv_get_cart_total', 'get_cart_total_callback');

function get_cart_total_callback() {
    // Return the cart total
    $cart_total_raw = WC()->cart->get_cart_total(); // Get the raw cart total as a string

    // Get the number of decimals set in WooCommerce
    $decimals = wc_get_price_decimals();
    
    // Remove thousand separator if decimals are 0
    if ($decimals === 0) {
        $thousand_separator = wc_get_price_thousand_separator();
        $cart_total_raw = str_replace($thousand_separator, '', $cart_total_raw);
    }
    
    // Format the cart total to a decimal
    $cart_total = wc_format_decimal($cart_total_raw);
    
    echo $cart_total;
    //wp_die();
}



function oney_450_section($display = 'block') {
    
    // Get the minimum purchase amount (adjust accordingly)
    $min_purchase_amount = 450;
    
    global $wpdb;

    $oney_netopia_details_page_id = get_oney_netopia_details_page_id();
    $oney_details_page_url = get_permalink( $oney_netopia_details_page_id );
    
    //echo $oney_details_page_url;
    // Get cart total
    // Updated to cover the case where decimals are not set
    //$cart_total = wc_format_decimal(WC()->cart->get_cart_total());
    
    $cart_total_raw = WC()->cart->get_cart_total(); // Get the raw cart total as a string

    // Get the number of decimals set in WooCommerce
    $decimals = wc_get_price_decimals();
    
    // Remove thousand separator if decimals are 0
    if ($decimals === 0) {
        $thousand_separator = wc_get_price_thousand_separator();
        $cart_total_raw = str_replace($thousand_separator, '', $cart_total_raw);
    }
    
    // Format the cart total to a decimal
    $cart_total = wc_format_decimal($cart_total_raw);
    
    // Calculate the remaining amount for free shipping
    $remaining_amount = max(0, $min_purchase_amount - $cart_total);

    // Calculate the progress percentage
    $progress_percentage = ($cart_total / $min_purchase_amount) * 100;
    $progress_percentage = min($progress_percentage, 100); // Ensure it doesn't exceed 100%


    // GET PLUGIN VERISON 
    $plugin_data = get_plugin_data( __FILE__ );
    $plugin_vers = $plugin_data['Version'];
    
    
    // Output the shipping progress bar HTML
    // ob_start(); 
    $html = "<script>
    // Function to update cart total dynamically
    function updateCartTotalProgress() {
        jQuery.ajax({
            type: 'POST',
            url: '". admin_url('admin-ajax.php')."',
            data: {
                'action': 'get_cart_total'
            },
            success: function(response) {
                // Update the cart total on the page
                const minPurchaseAmount = 450;
                var cartTotal = response;
                var remainingAmount = Math.max(0, minPurchaseAmount - cartTotal);
                remainingAmount = remainingAmount.toFixed(2); // Ensure only 2 decimal places

                jQuery('.oney-netopia-remaining-amount').text(remainingAmount + ' RON');
                
                if (remainingAmount == minPurchaseAmount ){
                    jQuery('#acord-remaining-amount').text('Adaugă în coș produse de minim');
                    jQuery('#post-acord-remaining-amount').text('și poți plăti ');

                    
                } else if (remainingAmount < minPurchaseAmount && remainingAmount > 0  ){
                    jQuery('#acord-remaining-amount').text('Coșului tău îi lipsesc încă ');
                    jQuery('#post-acord-remaining-amount').text('pentru a putea plăti ');
                } else if (remainingAmount == 0){
                    jQuery('#acord-remaining-amount').text('Comanda ta poate fi plătită');
                    jQuery('.oney-netopia-remaining-amount').text('');
                    jQuery('#post-acord-remaining-amount').text('');

                }
                
                // Calculate the progress percentage
                var progressPercentage = (cartTotal / minPurchaseAmount) * 100;
                progressPercentage = Math.min(progressPercentage, 100); // Ensure it doesn't exceed 100%
                progressPercentage = progressPercentage.toFixed(2);
                // Update the width of the progress bar
                jQuery('#oney-netopia-progress-bar').css('width', progressPercentage + '%');
            }
        });
    }

    // Call the function to update cart total on page load
    jQuery(document).ready(function($) {
        updateCartTotalProgress();
    });

    // Trigger AJAX update after adding to cart
    jQuery(document).on('added_to_cart removed_from_cart', function() {
        updateCartTotalProgress();
    });
    </script>";
    
    $html .= '<div class="oney-netopia-payment-progress-bar oney-netopia-style-bordered" style="display:'.$display.'">
        <!-- Plugin Version: ' . $plugin_vers . ' -->
        <div class="oney-netopia-progress-bar oney-netopia-free-progress-bar">';
        
    if ($remaining_amount <= 0) {
        $html .= '<div class="oney-netopia-progress-msg"><span id="acord-remaining-amount">Comanda ta poate fi plătită</span><span class="oney-netopia-remaining-amount"></span><span id="post-acord-remaining-amount"></span> în 3 sau 4 rate prin <img src="/wp-content/plugins/oney-add-on-netopia/images/oney3x4x-logo.png" style="display: inline; width: 95px; margin-bottom: -4px;"> ! <a href="'.$oney_details_page_url.'" class="oney-netopia-details">Vezi detalii</a></div>';
    } else if($remaining_amount < 450 ) {
        $html .= '<div class="oney-netopia-progress-msg"><div class="cumpara-text"> <span id="acord-remaining-amount">Coșului tău îi lipsesc încă</span> <span class="oney-netopia-remaining-amount">' . number_format($remaining_amount, 2) . ' RON</span> <span id="post-acord-remaining-amount">pentru a putea plăti</span> în 3 sau 4 rate prin <img src="/wp-content/plugins/oney-add-on-netopia/images/oney3x4x-logo.png" style="display: inline; width: 95px; margin-bottom: -4px;"> ! <a href="'.$oney_details_page_url.'" class="oney-netopia-details">Vezi detalii</a></div></div>';
    } else if($remaining_amount == $min_purchase_amount) {
        $html .= '<div class="oney-netopia-progress-msg"><div class="cumpara-text"> <span id="acord-remaining-amount">Adaugă în coș produse de minim</span> <span class="oney-netopia-remaining-amount">' . number_format($remaining_amount, 2) . ' RON</span> <span id="post-acord-remaining-amount">și poți plăti</span> în 3 sau 4 rate prin <img src="/wp-content/plugins/oney-add-on-netopia/images/oney3x4x-logo.png" style="display: inline; width: 95px; margin-bottom: -4px;"> ! <a href="'.$oney_details_page_url.'" class="oney-netopia-details">Vezi detalii</a></div></div>';
    }
    
    $html .= '<div class="oney-netopia-progress-area">
                <div id="oney-netopia-progress-bar" class="oney-netopia-progress-bar" style="width: '.$progress_percentage.'%"></div>
            </div>
        </div>
    </div>';

    return $html;
}

function oneynetopia_cart_450_reminder(){
    
    //$cart_total = wc_format_decimal(WC()->cart->get_cart_total());
    
    $cart_total_raw = WC()->cart->get_cart_total(); // Get the raw cart total as a string

    // Get the number of decimals set in WooCommerce
    $decimals = wc_get_price_decimals();
    
    // Remove thousand separator if decimals are 0
    if ($decimals === 0) {
        $thousand_separator = wc_get_price_thousand_separator();
        $cart_total_raw = str_replace($thousand_separator, '', $cart_total_raw);
    }
    
    // Format the cart total to a decimal
    $cart_total = wc_format_decimal($cart_total_raw);
    
    
    if($cart_total <= 12000){
        $html = oney_450_section('block');
    } else {
        $html = oney_450_section('none');
    }
    
    echo $html;
}
add_action('woocommerce_before_cart_table', 'oneynetopia_cart_450_reminder');

add_action('woocommerce_checkout_before_customer_details', 'oneynetopia_cart_450_reminder');




/* BEGIN SHORTCODE */

// Define a function to handle the shortcode
function oney_netopia_metoda_plata_shortcode() {
    // Retrieve the setting value
        $home_url = home_url(); // Get the home URL
        $parsed_url = parse_url($home_url); // Parse the URL to get its components
        $domain = $parsed_url['host']; 
        
        $html = '<div id="oney-netopia-info-section"><h3>Cum poți plăti în 3-4 rate prin Oney?</h3>
        <div class="landing-page-oney-netopia-images-container">
        <img src="/wp-content/plugins/oney-add-on-netopia/images/oney-pasul-1.png">
        <img src="/wp-content/plugins/oney-add-on-netopia/images/oney-pasul-2.png">
        <img src="/wp-content/plugins/oney-add-on-netopia/images/oney-pasul-3.png">
        <img src="/wp-content/plugins/oney-add-on-netopia/images/oney-pasul-4.png">
        </div><h3>CE ESTE SOLUȚIA 3X4X ONEY?</h3>
    <p>Această metodă de plată presupune plata coșului de cumpărături în <strong>3 sau 4 rate</strong> cu un <strong>card de DEBIT</strong>, și este oferită fără costuri (dobândă 0%).</p>
    <p><strong><sup>**</sup>Foarte important:</strong></p>
    <ul>
        <li>Prima rată se achită pe loc, deci trebuie să aveți disponibilă suma primei rate pe card</li>
        <li>Cardul trebuie să nu expire până la achitarea ratelor</li>
    </ul>
    <h3>CINE POATE BENEFICIA DE SOLUȚIA DE CREDITARE 3X4X ONEY?</h3>
    <p>Soluția 3x4x Oney este dedicată doar persoanelor fizice, majore și rezidente în România</p>
    <h3>CE CARDURI SUNT ACCEPTATE?</h3>
    <p>Sunt acceptate cardurile de DEBIT Visa și MasterCard, emise de băncile sau instituțiile financiare din România.</p>
    <p>Nu sunt acceptate:</p>
    <ul>
        <li>Carduri preplătite, virtuale sau carduri emise în altă țară</li>
        <li>Carduri de credit</li>
        <li>Carduri business (emise pentru persoanele juridice)</li>
        <li>Cardurile Revolut, cardurile Visa Electron sau Maestro</li>
    </ul>
    <h3>CE DOCUMENTE SUNT NECESARE?</h3>
    <p>Pentru a utiliza această soluție de creditare, nu aveți nevoie de niciun document, ci doar de un card de debit.</p>
    <p>Nu solicităm o justificare a venitului lunar încasat de dumneavoastră.</p>
    <h3>CUM POT BENEFICIA DE SOLUȚIA DE CREDITARE 3X4X ONEY?</h3>
    <ol>
        <li>Adaugi produsele dorite în coș</li>
        <li>Alegi opțiune de plată 3X4X ONEY prin Netopia la check-out</li>
        <li>Apeși butonul "Timite comanda"</li>
        <li>În pagina de plată Netopia, vei bifa plata prin Oney (sus în partea dreaptă) și vei fi redirecționat către pagina de plată Oney, unde completezi datele solicitate.</li>
    </ol>
    <h3>Termeni si conditii</h3>
     <p>Soluțiile de Creditare Oney (3xOney si 4xOney) sunt furnizate de Oney Bank persoanelor fizice eligibile gratuit, fiind rambursabile în maximum 60 de zile (pentru 3xOney) sau 90 de zile, dar nu mai mult de 3 luni (pentru 4xOney).</p>
    <p>Exemplu reprezentativ pentru 3x Oney cu titlu gratuit :</p>
    <p>Pentru un credit de 900 de lei acordat prin 3x Oney pentru achiziții de pe <strong>'.$domain.'</strong>, clientul va plăti următoarele sume :</p>
    <ul>
        <li>o rată inițială de 300 lei, apoi</li>
        <li>2 rate lunare de 300 lei fiecare, care se rambursează la fiecare 30 de zile de la plata ratei inițiale.</li>
    </ul>
    <p>Pentru un credit rambursat în termen de 60 de zile, rata dobânzii fixe este 0%, iar dobânda anuala efectiva (DAE) este 0%. Obținerea creditului pentru client este gratuită, iar valoarea totală a creditului este de 900 lei.</p>
    <p>Exemplu pentru 4x Oney cu titlu gratuit :</p>
    <p>Pentru un credit de 1.600 de lei acordat prin 4x Oney pentru achiziții de pe <strong>'.$domain.'</strong>, clientul va plăti următoarele sume :</p>
    <ul>
        <li>o rată inițială de 400 lei, apoi</li>
        <li>3 rate lunare de 400 lei fiecare, care se rambursează la fiecare 30 de zile de la plata ratei inițiale.</li>
    </ul>
    <p>Pentru un credit rambursat în termen de 90 de zile (și nu mai mult de 3 luni), rata dobânzii fixe este 0% și dobânda anuala efectiva (DAE) este 0%. Obținerea creditului pentru client este gratuită, iar valoarea totală a creditului este de 1.600 lei.</p>
    <p>Acesta reprezintă exemplu indicativ pentru o soluție de creditare. Informațiile finale vor fi indicate în e-mailul de confirmare incluzând graficul de rambursare final. Informațiile prezentate în simulatorul de credit sunt orientative și nu reprezintă o obligație contractuală a Oney Bank de a furniza o Soluție de Creditare Oney. Valoarea maximă a creditului pe care îl puteți primi va fi determinat pe baza informațiilor furnizate de dvs. Și va fi supus evaluării interne a Oney Bank.</p>
    <p>Prevederile OUG nr. 50/2010 nu se aplică Soluțiilor de Creditare Oney, care rămân supuse legislației generale privind protecția consumatorilor, incluzând, dar fără a se limita la Ordonanța de Guvern nr. 85/2004 privind protecția consumatorilor la încheierea şi executarea contractelor la distanță privind serviciile financiare (“OUG nr. 85/2004”). Conform OUG nr. 85/2004 beneficiați de un termen de 14 zile calendaristice pentru a vă retrage din contractul de credit începând cu data încheierii contractului de credit. Contractul de credit se consideră a fi încheiat la data recepționarii e-mailului de confirmare, sub rezerva acceptării electronice (prin bifarea căsuței electronice) de către dvs., a Termenilor și Condițiilor Generale aplicabile acordării online a Soluțiilor de Creditare Oney (3xOney și 4xOney) (“Termenii și Condițiile Generale”) și a solicitării creditului de către dvs. prin click pe butonul “Plata în rate” pe website-ul a Oney Bank.</p>
    <p>Îndeplinirea tuturor criteriilor de eligibilitate pentru acordarea Soluțiilor de Creditare Oney, menționate în Termenii si Condițiile Generale, nu vă conferă în mod automat dreptul de a obține Soluțiile de Creditare Oney, care rămân condiționate de aprobarea Oney Bank pe baza analizei sale interne.</p>
    <p>Vă rugăm să rețineți că ulterior efectuării verificărilor interne (in maximum 14 zile de la data acceptării electronice de către dvs. a ofertei de creditare și a Termenilor și Condițiilor Generale) creditul vă poate fi refuzat.</p>
    <p>Soluțiile de Creditare Oney sunt disponibile exclusiv pentru produsele și serviciile vândute online și sunt valabile pentru un credit de minimum 450 Lei și maxim 12.000 Lei.</p>
    <p>Soluțiile de Creditare Oney sunt disponibile și rezervate doar persoanelor fizice majore (minimum 18 ani) având capacitate de exercițiu deplină la data acceptării Termenilor și Condițiilor Generale, care îndeplinesc în mod cumulativ următoarele condiții: (i) sunt rezidenți in România (cetățeni români și cetățeni străini rezidenți români); (ii) sunt consumatori, în înțelesul legislației aplicabile și, prin urmare, nu acționează în scopuri de natură profesională sau comercială; (iii) dețin un card de debit Visa sau Mastercard valabil (excluzând carduri preplătite/carduri care solicită sistematic autorizări cum ar fi VISA Electron și Maestro), emise de o instituție de credit din România sau de o sucursală din România a unei instituții de credit din UE, având un termen de valabilitate ce depășește cu mai mult de 2 luni de zile durata Soluției de Creditare Oney selectate sau dețin orice alte metode de plată, așa cum sunt specificate acestea de Oney Bank. </p>
    <p>Oney Bank este o instituție de credit franceza autorizata sa desfășoare activități de creditare si de plata de Autoritatea Franceză de Control Prudențial și Rezoluție (“ACPR”), organizată și funcționând ca societate pe acțiuni în conformitate cu legislația din Franța, cu un capital social de 51.286.585 euro, înregistrată în Registrul Comerțului și Societăților din Lille Metropole sub numărul 546 380 197, cu sediul situat în str. Avenue de Flandre, nr. 34, Croix (59170), adresă de email: <a href="mailto:serviciulclienti.ro@oney.com">serviciulclienti.ro@oney.com</a>.</p>
    <p>Oney Bank este supravegheată, în primul rând, de autoritatea competentă franceză, ACPR, precum și de Banca Națională a României (“BNR”) și furnizează Soluțiile de Creditare Oney transfrontalier, fără prezență fizică pe teritoriul României (având în vedere natura exclusiv online a activității de creditare), desfășurând activități de creditare a consumatorilor în România în condițiile libertății de a furniza servicii în conformitate cu prevederile Ordonanței Guvernului 99/2006 privind instituțiile de credit și adecvarea capitalului, cu modificările ulterioare, fiind înregistrată în registrul public al BNR cu privire la instituțiile de credit UE care au notificat prestarea de servicii în mod direct în România, conform notificării din 31/10/2019 (a se vedea lista BNR <a href="https://www.bnr.ro/Registre-si-Liste-717.aspx#IC" target="_blank">https://www.bnr.ro/Registre-si-Liste-717.aspx#IC</a>).</p>
    <strong>Pentru mai multe detalii, accesați următorul link : <a href="https://oney-bank.ro/3x-4x-oney/" target="_blank">3x 4x Oney | Oney (oney-bank.ro)</a></strong></div>';
    
    return $html;
    
        // Output the URL
        //return '<a href="' . esc_url($metoda_plata_url) . '">Metoda de plata</a>';

}

// Register the shortcode
add_shortcode('oney-netopia-metoda-plata', 'oney_netopia_metoda_plata_shortcode');

/* END SHORTCODE */






/* BEGIN CHECKOUT */

// Hook into the WooCommerce payment method title filter
add_filter('woocommerce_gateway_title', 'customize_payment_method_title', 10, 2);

// Function to customize the payment method title
function customize_payment_method_title($title, $payment_method) {

    
    // Check if the payment method is the one you want to customize (replace 'netopiapayments' with your payment method ID)
    if ($payment_method === 'netopiapayments') {
        
        // Get the value of the option set in the settings page
        $cart_total = WC()->cart->total; // Get the total amount from WooCommerce cart
    
        if ($cart_total < 450 || $cart_total > 12000){
            return $title;
        }
            
            // Get the value of the option set in the settings page
        $custom_title = get_option('oney_addon_netopia_titlu_metoda_plata');
        $htmlImage= '<img style="display: inline; width: 95px;" src="' . esc_url(plugins_url('/oney-add-on-netopia/images/oney3x4x-logo.png')) . '" />';;

        // Append the custom title to the existing title
        $title .= ' ' . $custom_title. $htmlImage;
    }

    return $title;
}

// Hook into the WooCommerce payment method description filter
add_filter('woocommerce_available_payment_gateways', 'customize_payment_method_description');

// Function to customize the payment method description
// Function to customize the payment method description
function customize_payment_method_description($gateways) {
    // Check if the action has been performed before
    if (did_action('woocommerce_available_payment_gateways_customized')) {
        return $gateways; // Return original gateways if customization has already been applied
    }
    $plugin_data = get_plugin_data( __FILE__ );
    $plugin_vers = $plugin_data['Version'];

    $oney_netopia_details_page_id = get_oney_netopia_details_page_id();
    $oney_details_page_url = get_permalink($oney_netopia_details_page_id);
    
    // Check if the WC cart is initialized and not null
    if (is_null(WC()->cart)) {
        return $gateways; // Return the original gateways without modifications
    }
    
    // Get the value of the option set in the settings page
    $cart_total = WC()->cart->total; // Get the total amount from WooCommerce cart
    $cart_total_divided_by_3 = number_format($cart_total / 3, 2); // Calculate total divided by 3 rates and limit to 2 decimals
    $cart_total_divided_by_4 = number_format($cart_total / 4, 2); // Calculate total divided by 4 rates and limit to 2 decimals

    if ($cart_total < 450 || $cart_total > 12000){
        if (isset($gateways['netopiapayments'])) {
             if (get_option('oney_addon_netopia_hide_payment_options') =="yes") {
                $gateways['netopiapayments']->description .= "<style>.woocommerce-checkout-payment div#netopia-methods {display: none;}</style>";
            }
            $custom_description = '<p> Comenzile de minim 450 și maxim 12.000 de RON pot fi plătite în <strong>3-4 rate fără dobândă</strong> direct cu cardul tău de debit!</p> ';
            $gateways['netopiapayments']->description .= ' ' . $custom_description;
            
            // Mark the action as performed to prevent duplication
            do_action('woocommerce_available_payment_gateways_customized');
        }
        return $gateways;
    }

    // Check if netopiapayments gateway exists and customize its description
    if (isset($gateways['netopiapayments'])) {
        

        // Custom description HTML
        $custom_description = '<strong class="oney-netopia-checkout-new">NOU</strong> <p> Comenzile de minim 450 de RON pot fi plătite în <strong>3-4 rate fără dobândă</strong> direct cu cardul tău de debit!</p><div class="oney-netopia-container-main" style="display:block">
            <!-- Plugin Version: ' . $plugin_vers . ' -->
            <div class="oney-netopia-container-single-product" style="">
                <div class="cart-total-oney-netopia" style="display:none;">' . $cart_total . '</div>
        
                <img id="oney-netopia-image" src="/wp-content/plugins/oney-add-on-netopia/images/oney-3-4-rate-logo.png" title="" style="">
        
                <p class="text-oney-netopia-single-product">Plătește online în <strong>3 sau 4 rate</strong> în doar câțiva pași! <a href="'.$oney_details_page_url.'" class="oney-netopia-details" target="_blank">Vezi detalii</a></p>
                <div class="oney-netopia-rates-wrapper">
                    <div class="oney-netopia-rate">
                        <span>3 Rate: </span>
                        <span class="oney-netopia-rate-value"><strong id="oney-netopia-3rate">' . $cart_total_divided_by_3 . '</strong>/lună</span>
                    </div>
                    <div class="oney-netopia-rate">
                        <span>4 Rate: </span>
                        <span class="oney-netopia-rate-value"><strong id="oney-netopia-4rate">' . $cart_total_divided_by_4 . '</strong>/lună</span>
                    </div>
                </div>
        
            </div>
        </div>';

        // Append the custom description to the existing description
        $gateways['netopiapayments']->description .= ' ' . $custom_description;
    
        if (get_option('oney_addon_netopia_hide_payment_options') =="yes") {
            $gateways['netopiapayments']->description .= "<style>.woocommerce-checkout-payment div#netopia-methods {display: none;}</style>";
        }
        // Mark the action as performed to prevent duplication
        do_action('woocommerce_available_payment_gateways_customized');
    }

    return $gateways;
}



/* END CHECKOUT */