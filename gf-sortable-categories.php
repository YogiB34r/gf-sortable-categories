<?php
/**
 * Plugin Name
 *
 * @package     PluginPackage
 * @author      Green Friends
 * @copyright   2016 Your Name or Company Name
 * @license     GPL-2.0+
 *
 * @wordpress-plugin
 * Plugin Name: GF sortable categories
 * Plugin URI:  https://example.com/plugin-name
 * Description: GF custom widgets
 * Version:     1.0.0
 * Author:      Green Friends
 * Author URI:  https://example.com
 * Text Domain: plugin-name
 * License:     GPL-2.0+
 * License URI: http://www.gnu.org/licenses/gpl-2.0.txt
 */

function gf_sortable_categories_admin_scripts() {
    if (is_admin()) {
        wp_enqueue_style('jqueri-ui-css','//code.jquery.com/ui/1.12.1/themes/base/jquery-ui.css');
        wp_enqueue_style('gf-sortable-categories-admin-css',plugins_url().'/gf-sortable-categories/css/gf-sortable-categories-admin.css');
        wp_register_script('admin-js', plugins_url() . '/gf-sortable-categories/js/admin.js', array('jquery'), '', true);
        wp_enqueue_script('admin-js');
        wp_enqueue_script( 'jquery-ui-sortable' );
    }
}
add_action('admin_enqueue_scripts', 'gf_sortable_categories_admin_scripts');

add_action('admin_menu', 'gf_sortable_categories_options_create_menu');

function gf_sortable_categories_options_create_menu() {

    //create new top-level menu
    add_menu_page('Sortable Categories', 'Sortable Categories Options', 'administrator', 'sortable_categories_options', 'gf_sortable_categories_options_page' , null, 99 );

    //call register settings function
    add_action( 'admin_init', 'register_gf_sortable_categories_options' );
}
function register_gf_sortable_categories_options() {
    register_setting( 'gf-sortable-categories-settings-group', 'filter_fields_order' );
    register_setting('gf-sortable-categories-settings-group', 'number_of_categories_in_sidebar');
}
function gf_sortable_categories_options_page() {
    $args = array(
        'taxonomy' => 'product_cat',
        'hide_empty' => false,
        'parent'   => 0
    );
    $product_cats = get_terms( $args );
    $number_of_categories = esc_attr(get_option('number_of_categories_in_sidebar'));
?>
    <div class="wrap">
        <h2>Theme Options</h2>
        <br/>

        <?php settings_errors(); ?>

        <form method="post" action="options.php" id="theme-options-form">
            <?php settings_fields( 'gf-sortable-categories-settings-group' ); ?>
            <?php do_settings_sections( 'gf-sortable-categories-settings-group' ); ?>

            <?php
            $fields_order_default = [];
            foreach ($product_cats as $cat){
                if ($cat->name != 'Uncategorized' && $cat->name != 'Gf-slider'){
                    $fields_order_default[] = (array)$cat;
                }

            }
            ?>


            <div class="admin-module">
                <label><b>Sortable List</b> <em>(Drag & drop to rearrange order)</em></label>
                <ul class="filter-fields-list">
                    <?php
                    $filter_fields_order = get_option('filter_fields_order', $fields_order_default);
                    $saved_categories=[];
                    foreach ($filter_fields_order as $v){
                        $saved_categories[]= $v['term_id'];
                    }
                    foreach ($fields_order_default as $category){
                        if(!in_array($category['term_id'],$saved_categories)){
                          $filter_fields_order[]= array(
                            'term_id' => $category['term_id'],
                            'name'    => $category['name']
                          );
                        }
                    }
                    $saved_categories_db=[];
                    foreach ($fields_order_default as $v){
                        $saved_categories_db[]= $v['term_id'];
                    }

                    foreach($filter_fields_order as $value) {
                        if(in_array($value['term_id'], $saved_categories_db)){
                            $id = $value['term_id'];
                            $name = get_term($id)->name;
                        }
                        ?>

                        <?php if(isset($name) and isset($id)): ?>

                        <li class="sortable-item">
                            <?php echo $name; ?>
                            <input type="hidden" name="filter_fields_order[<?php echo $id; ?>][term_id]" value="<?php echo $id; ?>" />
                            <input type="hidden" name="filter_fields_order[<?php echo $id; ?>][name]" value="<?php echo $name; ?>" />
                        </li>
                        <?php endif;?>
                    <?php } ?>
                </ul>
                <label for="number_of_categories">Number of categories on sidebar</label>
                <input type="number" name="number_of_categories_in_sidebar" value="<?=$number_of_categories?>" />

            </div>

            <?php submit_button(); ?>
        </form>

    </div>
    <?php
}