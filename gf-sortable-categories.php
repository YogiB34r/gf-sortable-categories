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
 * Text Domain: gf-sortable-categories
 * Domain Path: /languages
 * License:     GPL-2.0+
 * License URI: http://www.gnu.org/licenses/gpl-2.0.txt
 */

load_plugin_textdomain('gf-sortable-categories', '', plugins_url() . '/gf-sortable-categories/languages');
function gf_sortable_categories_admin_scripts()
{
    if (is_admin()) {
        wp_enqueue_style('jqueri-ui-css', '//code.jquery.com/ui/1.12.1/themes/base/jquery-ui.css');
        wp_enqueue_style('gf-sortable-categories-admin-css', plugins_url() . '/gf-sortable-categories/css/gf-sortable-categories-admin.css');
        wp_register_script('admin-js', plugins_url() . '/gf-sortable-categories/js/admin.js', array('jquery'), '', true);
        wp_enqueue_script('admin-js');
        wp_enqueue_script('jquery-ui-widget');
        wp_enqueue_script('jquery-ui-sortable');
        wp_enqueue_script('jquery-ui-accordion');

    }
}

add_action('admin_enqueue_scripts', 'gf_sortable_categories_admin_scripts');

add_action('admin_menu', 'gf_sortable_categories_options_create_menu');

function gf_sortable_categories_options_create_menu()
{

    //create new top-level menu
    add_menu_page('Sortable Categories', 'Opcije sortiranja kategorija', 'administrator', 'sortable_categories_options', 'gf_sortable_categories_options_page', null, 99);

    //call register settings function
    add_action('admin_init', 'register_gf_sortable_categories_options');
}

function register_gf_sortable_categories_options()
{
    register_setting('gf-sortable-categories-settings-group', 'filter_fields_order');
    register_setting('gf-sortable-categories-settings-group', 'number_of_categories_in_sidebar');
}

function gf_sortable_categories_options_page()
{
    $gf_slider_id = '';
    if (get_term_by('slug', 'gf-slider', 'product_cat')) {
        $gf_slider_id = get_term_by('slug', 'gf-slider', 'product_cat')->term_id;
    }
    $args = array(
        'orderby' => 'term_id',
        'order' => 'asc',
        'hide_empty' => false,
        'exclude_tree' => $gf_slider_id
    );
    $product_cats = [];
    foreach (gf_get_top_level_categories($gf_slider_id) as $cat) {
        if (empty(get_term_children($cat->term_id, 'product_cat'))) {
            $product_cats[] = $cat;
        } else {
            $product_cats[] = $cat;
            foreach (get_term_children($cat->term_id, 'product_cat') as $second_level_cat) {
                if (gf_check_level_of_category($second_level_cat) == 2) {
                    if (empty(get_term_children($second_level_cat, 'product_cat'))) {
                        $product_cats[] = get_term($second_level_cat, 'product_cat');
                    } else {
                        $product_cats[] = get_term($second_level_cat, 'product_cat');
                        foreach (get_term_children($second_level_cat, 'product_cat') as $third_level_cat) {
                            $product_cats[] = get_term($third_level_cat, 'product_cat');
                        }
                    }
                }
            }
        }
    }
    $number_of_categories = esc_attr(get_option('number_of_categories_in_sidebar'));
    $fields_order_default = [];
    $c = 0;//counter for second level categories
    $all = 0;//counter for all categories
    $cc = 0;//counter for childs of second level category
    $pc = 0;//counter for childs of parent category
    foreach ($product_cats as $cat) {
        $fields_order_default[] = (array)$cat;
    }?>
    <div class="wrap">
        <h2><?= _e('Opcije sortiranja kategorija', 'gf-sortable-categories') ?></h2>
        <br/>

        <?php settings_errors(); ?>

        <form method="post" action="options.php" id="theme-options-form">
            <?php settings_fields('gf-sortable-categories-settings-group'); ?>
            <?php do_settings_sections('gf-sortable-categories-settings-group'); ?>
            <div class="admin-module gf-sortable-categories-wrapper">
                <label><b><?= __('Sortirajuća lista') ?> </b>
                    <em><?= __('(Postavite redosled kategorija prevlačenjem)') ?></em></label>
                <ul class="filter-fields-list">
                    <?php
                    if (empty(get_option('filter_fields_order'))) {
                        $filter_fields_order = $fields_order_default;
                    } else {
                        $filter_fields_order = get_option('filter_fields_order');
                    }
                    $saved_categories = [];
                    foreach ($filter_fields_order as $v) {
                        $saved_categories[] = $v['term_id'];
                    }
                    foreach ($fields_order_default as $category) {
                        if (!in_array($category['term_id'], $saved_categories)) {
                            $filter_fields_order[] = array(
                                'term_id' => $category['term_id'],
                                'name' => $category['name'],
                                'parent' => $category['parent']
                            );
                        }
                    }
                    $saved_categories_db = [];
                    foreach ($fields_order_default as $v) {
                        $saved_categories_db[] = $v['term_id'];
                    }

                    foreach ($filter_fields_order as $value) {
                        if (in_array($value['term_id'], $saved_categories_db)) {
                            $id = $value['term_id'];
                            $name = get_term($id)->name;
                            $parent = get_term($id)->parent;
                        }; ?>
                        <?php if (isset($name) and isset($id)):$all++ ?>
                            <?php require(realpath(__DIR__ . '/template-parts/gf-categories.php')) ?>
                        <?php endif; ?>
                    <?php }//foreach
                    ?>
                </ul><!--filter_fields_list-->
            </div><!--gf-sortable-categories-wrapper-->
            <label for="number_of_categories"><?= __('Broj kategorija koje će biti prikazane na bočnom meniju') ?></label>
            <input type="number" name="number_of_categories_in_sidebar"
                   value="<?= $number_of_categories ?>"/>
            <?php submit_button(); ?>
        </form>
    </div><!--WRAP-->
    <?php
}