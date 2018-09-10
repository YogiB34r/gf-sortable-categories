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
        wp_register_script('sortable-categories-admin-js', plugins_url() . '/gf-sortable-categories/js/sortable-categories-admin.js', array('jquery'), '', true);
        wp_enqueue_script('sortable-categories-admin-js');
        wp_enqueue_script('jquery-ui-widget');
        wp_enqueue_script('jquery-ui-sortable');
        wp_enqueue_script('jquery-ui-accordion');
        wp_enqueue_style('gf-sortable-categories-admin-css', plugins_url() . '/gf-sortable-categories/css/gf-sortable-categories-admin.css');
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

function gf_clear_megamenu_cache()
{
    $key = 'gf-megamenu';
    $redis = new Redis();
    $redis->connect('127.0.0.1');
    $redis->del($key);
}

function gf_sortable_categories_options_page()
{
    $gf_slider_id = '';
    $sliderCat = get_term_by('slug', 'specijalne-promocije', 'product_cat');
    if ($sliderCat) {
        $gf_slider_id = $sliderCat->term_id;
    }
    $uncategorized_id = '';
    $uncategorizedCat = get_term_by('slug', 'uncategorized', 'product_cat');
    if ($uncategorizedCat) {
        $uncategorized_id = $uncategorizedCat->term_id;
    }
    foreach (gf_get_top_level_categories($gf_slider_id, $uncategorized_id) as $cat) {
        $catTermChildren = get_term_children($cat->term_id, 'product_cat');
        if (empty($catTermChildren)) {
            $product_cats[] = $cat;
        } else {
            $product_cats[] = $cat;
            foreach ($catTermChildren as $second_level_cat) {
                if (gf_check_level_of_category($second_level_cat) == 2) {
                    $secondCatTermChildren = get_term_children($second_level_cat, 'product_cat');
                    $product_cats[] = get_term($second_level_cat, 'product_cat');
                    if (!empty($secondCatTermChildren)) {
                        foreach ($secondCatTermChildren as $third_level_cat) {
                            $product_cats[] = get_term($third_level_cat, 'product_cat');
                        }
                    }
                }
            }
        }
    }
    $number_of_categories = 24;
    if (!empty(get_option('number_of_categories_in_sidebar'))) {
        $number_of_categories = esc_attr(get_option('number_of_categories_in_sidebar'));
    }

    $fields_order_default = [];
    $pc = 0;//counter for childs of parent category
    $c = 0;//counter for second level categories
    $cc = 0;//counter for childs of second level category

    foreach ($product_cats as $cat) {
        $fields_order_default[] = (array)$cat;
    } ?>
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
                        <?php if (isset($name) and isset($id)): ?>
                            <?php require(realpath(__DIR__ . '/template-parts/gf-categories.php')) ?>
                        <?php endif; ?>
                    <?php }//foreach
                    ?>
                </ul><!--filter_fields_list-->
            </div><!--gf-sortable-categories-wrapper-->
            <label for="number_of_categories"><?= __('Broj kategorija koje će biti prikazane na bočnom meniju') ?></label>
            <input type="number" name="number_of_categories_in_sidebar"
                   value="<?= $number_of_categories ?>"/>
            <?php submit_button('', 'primary', 'gf-sortable-categories'); ?>
        </form>
    </div><!--WRAP-->
    <script type="text/javascript">
        $('#theme-options-form').submit(function (e) {
                e.preventDefault(); // don't submit multiple times
                this.submit(); // use the native submit method of the form element
                setTimeout(function () {
                    <?php gf_clear_megamenu_cache() ?>
                    console.log('radi');
                }, 100);
            },
        )
    </script>
    <?php
}
// Category sidebar
add_shortcode('gf-category-megamenu', 'gf_category_megamenu_shortcode');
function gf_category_megamenu_shortcode()
{
    $key = 'gf-megamenu';
//    $group = 'gf-sidebar-static';
    $redis = new Redis();
    $redis->connect('127.0.0.1');
//    $html = wp_cache_get($key, $group);
    $html = $redis->get($key);
    $html = false;
    if ($html === false) {
        ob_start();
        printMegaMenu();
        $html = ob_get_clean();
//        wp_cache_set($key, $html, $group, 300);
        $redis->set($key, $html, 60 * 60); // 1 hour
    }
    echo $html;
}

/**
 * Prints out mega menu with categories
 */
function printMegaMenu() {
    $gf_slider_id = '';
    if (get_term_by('slug', 'specijalne-promocije', 'product_cat')) {
        $gf_slider_id = get_term_by('slug', 'specijalne-promocije', 'product_cat')->term_id;
    }
    $uncategorized_id = '';
    if (get_term_by('slug', 'uncategorized', 'product_cat')) {
        $uncategorized_id = get_term_by('slug', 'uncategorized', 'product_cat')->term_id;
    }
    $product_cats = [];
    $number_of_categories = 24;
    if (!empty(get_option('filter_fields_order'))) {
        $product_cats_array = get_option('filter_fields_order');
        foreach ($product_cats_array as $product_cat) {
            $product_cats[] = get_term($product_cat['term_id']);
        }
//        $number_of_categories = esc_attr(get_option('number_of_categories_in_sidebar'));
    } else{
        foreach (gf_get_top_level_categories($gf_slider_id, $uncategorized_id) as $cat) {
            if ($cat->term_id === 3152) {
                continue;
            }
            $catTermChildren = get_term_children($cat->term_id, 'product_cat');
            if (empty($catTermChildren)) {
                $product_cats[] = $cat;
            } else {
                $product_cats[] = $cat;
                foreach ($catTermChildren as $second_level_cat) {
                    if (gf_check_level_of_category($second_level_cat) == 2) {
                        $secondCatTermChildren = get_term_children($second_level_cat, 'product_cat');
                        if (empty($secondCatTermChildren)) {
                            $product_cats[] = get_term($second_level_cat, 'product_cat');
                        } else {
                            $product_cats[] = get_term($second_level_cat, 'product_cat');
                            foreach ($secondCatTermChildren as $third_level_cat) {
                                $product_cats[] = get_term($third_level_cat, 'product_cat');
                            }
                        }
                    }
                }
            }
        }
    }
    $i = 0;
    $c = 0;
    $pcc = 0;
    echo
    '<div id="gf-wrapper">
	     <div class="gf-sidebar">
		     <div class="gf-toggle"><i class="fa fa-bars"></i></div>
		       <div class="gf-navblock">';
    if ($i <= $number_of_categories) {
        foreach ($product_cats as $cat) {
            if ($cat->parent == 0) {
                $parent_children_count = count(get_term_children($cat->term_id,'product_cat'));
                $i++;
                require(realpath(__DIR__ . '/template-parts/category-megamenu/first-level.php'));
            }
            if (gf_check_level_of_category($cat->term_id) == 2) {
                $child_count = count(get_term_children($cat->term_id, 'product_cat'));
                require(realpath(__DIR__ . '/template-parts/category-megamenu/second-level.php'));
                $pcc++;
            }
            if (gf_check_level_of_category($cat->term_id) == 3) {
                require(realpath(__DIR__ . '/template-parts/category-megamenu/third-level.php'));
                $c++;
                $pcc++;
            }

            if (gf_check_level_of_category($cat->term_id) == 3 || gf_check_level_of_category($cat->term_id) == 2) {
                if ($c == $child_count) {
                    echo '</ol>
                                    </div>';
                    $c = 0;
                }
            }
            if ($pcc == $parent_children_count){
                echo '</div>
                    </div>
                </li>
            </ul>';
                $pcc=0;
            }
        }

    }
    echo '</div>
	</div>
</div>';
}