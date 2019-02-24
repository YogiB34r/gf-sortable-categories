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
require_once(plugin_dir_path(__FILE__) . '/../gf-widgets/includes/GF_Cache.php');
load_plugin_textdomain('gf-sortable-categories', '', plugins_url() . '/gf-sortable-categories/languages');
function gf_sortable_categories_admin_scripts()
{
    if (is_admin()) {
        wp_enqueue_style('jqueri-ui-css', '//code.jquery.com/ui/1.12.1/themes/base/jquery-ui.css');
//        wp_enqueue_style('gf-sortable-categories-admin-css', plugins_url() . '/gf-sortable-categories/css/gf-sortable-categories-admin.css');
//        wp_register_script('sortable-categories-admin-js', plugins_url() . '/gf-sortable-categories/js/sortable-categories-admin.js', array('jquery'), '', true);
        wp_enqueue_script('sortable-categories-admin-js');
        wp_enqueue_script('jquery-ui-widget');
        wp_enqueue_script('jquery-ui-sortable');
        wp_enqueue_script('jquery-ui-accordion');
//        wp_enqueue_style('gf-sortable-categories-admin-css', plugins_url() . '/gf-sortable-categories/css/gf-sortable-categories-admin.css');
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
    register_setting('gf-sortable-categories-settings-group', 'reset-categories');
}
function gf_clear_megamenu_cache()
{
    $cache = new GF_Cache();
    $key = 'gf-megamenu';
    $key1 = 'gf-megamenu-mobile';
    $cache->redis->del($key);
    $cache->redis->del($key1);
}
function gf_reset_category_order()
{
    global $wpdb;
    $sql = "UPDATE wp_options SET option_value = ''  WHERE option_name = 'filter_fields_order'";
    $wpdb->query($sql);
}
function gf_sortable_categories_options_page()
{
    if (isset($_REQUEST['settings-updated'])) {
        gf_clear_megamenu_cache();
    }
    if (isset($_REQUEST['reset-categories'])) {
        gf_reset_category_order();
        gf_clear_megamenu_cache();
        header("Location: http://$_SERVER[HTTP_HOST]$_SERVER[REQUEST_URI]&message=success");
        exit;
    }
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
    $topLevelCats = gf_get_top_level_categories($gf_slider_id, $uncategorized_id);
    foreach ($topLevelCats as $cat) {
        if ($cat->term_id === 3152) {
            continue;
        }
        $catTermChildren = get_term_children($cat->term_id, 'product_cat');
        if (empty($catTermChildren)) {
            $product_cats[$cat->term_id]['cat'] = [
                'cat_id' => $cat->term_id,
                'name' => $cat->name,
                'parent' => $cat->parent,
            ];
        } else {
            $product_cats[$cat->term_id]['cat'] = [
                'cat_id' => $cat->term_id,
                'name' => $cat->name,
                'parent' => $cat->parent,
            ];

            foreach ($catTermChildren as $second_level_cat_id) {
                if (gf_check_level_of_category($second_level_cat_id) == 2) {
                    $secondCatTermChildren = get_term_children($second_level_cat_id, 'product_cat');
                    $second_level_cat = get_term($second_level_cat_id, 'product_cat');
                    $product_cats[$cat->term_id]['children'][$second_level_cat_id]['cat'] = [
                        'cat_id' => $second_level_cat->term_id,
                        'name' => $second_level_cat->name,
                        'parent' => $second_level_cat->parent,
                    ];

                    if (!empty($secondCatTermChildren)) {
                        foreach ($secondCatTermChildren as $third_level_cat_id) {
                            $third_level_cat = get_term($third_level_cat_id, 'product_cat');
                            $product_cats[$cat->term_id]['children'][$second_level_cat_id]['children'][$third_level_cat_id]['cat'] = [
                                'cat_id' => $third_level_cat->term_id,
                                'name' => $third_level_cat->name,
                                'parent' => $third_level_cat->parent,
                            ];
                        }
                    }
                }
            }
        }
    }
    $number_of_categories = count($topLevelCats);
    if (!empty(get_option('number_of_categories_in_sidebar'))) {
        $number_of_categories = esc_attr(get_option('number_of_categories_in_sidebar'));
    }
    $fields_order_default = $product_cats;

    global $wpdb;

    $data = $wpdb->get_var($wpdb->prepare("SELECT option_value FROM $wpdb->options WHERE option_name = %s LIMIT 1", 'filter_fields_order'));

//                if (empty(get_option('filter_fields_order'))) {
    if (!$data) {
        $filter_fields_order = $fields_order_default;
    } else {
        $filter_fields_order = [];
        foreach (unserialize($data) as $termId => $data) {
            $termData = unserialize(array_keys($data)[0]);
            if ($termData['cat']['parent']) {
                $myParent = get_term($termData['cat']['parent'], 'product_cat');
                if (is_object($myParent)) {
                    if ($myParent->parent) { //3rd level
                        $myParentsParent = get_term($myParent->parent, 'product_cat');
                        if (is_object($myParentsParent)) {
                            $filter_fields_order[$myParentsParent->term_id]['children'][$myParent->term_id]['children'][$termId]['cat'] = $termData['cat'];
                        }
                    } else { //2nd level
                        $filter_fields_order[$myParent->term_id]['children'][$termId]['cat'] = $termData['cat'];
                    }
                }
            } else {
                $filter_fields_order[$termId]['cat'] = $termData['cat'];
            }
        }
    }
    ?>
    <div class="wrap">
        <h2><?= _e('Opcije sortiranja kategorija', 'gf-sortable-categories') ?></h2>
        <br/>
        <?php if (isset($_GET['message'])): ?>
        <div class="notice notice-success is-dismissible">
            <p>Uspešno ste resetovali redosled kategorija</p>
        </div>
        <?php endif; ?>

        <?php settings_errors(); ?>
        <form method="post" action="options.php" id="theme-options-form">
            <?php settings_fields('gf-sortable-categories-settings-group'); ?>
            <?php do_settings_sections('gf-sortable-categories-settings-group'); ?>
            <div class="admin-module gf-sortable-categories-wrapper">
                <label><b><?= __('Sortirajuća lista') ?> </b>
                    <em><?= __('(Postavite redosled kategorija prevlačenjem)') ?></em></label>
                <ul class="filter-fields-list">
                <?php
                //Check for diff in saved and original categories for added/removed ones
                if (md5(serialize($fields_order_default)) !== md5(serialize($filter_fields_order))) {
                    /**
                     * when relations are changed, that means one item is removed from displayed, and added to original,
                     * so both actions will be performed.
                     */
                    $added = arrayRecursiveDiff($fields_order_default, $filter_fields_order);
                    $removed = arrayRecursiveDiff($filter_fields_order, $fields_order_default);

//                    if (!empty($removed) && empty($added)) {
                    /**
                     * will remove elements from displayed array that are not found in the original one.
                     */
                    if (!empty($removed)) {
                        var_dump('removing...');
                        foreach ($removed as $mainCatID => $mainCatData) {
                            if (!is_array($mainCatData['children'])) { //remove first level cat
                                unset($filter_fields_order[$mainCatID]);
                            } else {
                                foreach ($mainCatData['children'] as $subCatId => $subCatData) {
                                    if (!isset($subCatData['children']) || !is_array($subCatData['children'])) { //remove second level cat
                                        unset($filter_fields_order[$mainCatID]['children'][$subCatId]);
                                    } else { //remove third level cat
                                        foreach ($subCatData['children'] as $subCatChildId => $subCatChildData) {
                                            unset($filter_fields_order[$mainCatID]['children'][$subCatId]['children'][$subCatChildId]);
                                        }
                                    }
                                }
                            }
                        }
                    }

//                    if (!empty($added) && empty($removed)) {
                    /**
                     * will add elements into displayed array, that are added to original one
                     */
                    if (!empty($added)) {
                        var_dump('adding...');
                        foreach ($added as $mainCatID => $mainCatData) {
                            if (!is_array($mainCatData['children'])) { // add first level cat only
                                $filter_fields_order[$mainCatID] = $mainCatData;
                            } else {
                                foreach ($mainCatData['children'] as $subCatId => $subCatData) {
                                    if (!isset($subCatData['children']) || !is_array($subCatData['children'])) {
                                        if (!isset($filter_fields_order[$mainCatID])) {
                                            //add first if not there
                                            $filter_fields_order[$mainCatID] = $mainCatData;
                                        }
                                        //add second level cat
                                        $filter_fields_order[$mainCatID]['children'][$subCatId] = $subCatData;
                                    } else { //add second level if not there
                                        if (!isset($filter_fields_order[$mainCatID]['children'][$subCatId])) {
                                            $filter_fields_order[$mainCatID]['children'][$subCatId] = $subCatData;
                                        }
                                        //add third level cat
                                        foreach ($subCatData['children'] as $subCatChildId => $subCatChildData) {
                                            $filter_fields_order[$mainCatID]['children'][$subCatId]['children'][$subCatChildId] = $subCatChildData;
                                        }
                                    }
                                }
                            }
                        }
                    }
                }

                //require(realpath(__DIR__ . '/template-parts/gf-categories.php'))

                foreach ($filter_fields_order as $catId => $catData) : ?>
                <li class="parent-cat accordion-first-level">
                <input type="hidden" name="filter_fields_order[<?=$catId?>][<?=htmlspecialchars(serialize($catData))?>]" value="<?=$catId?>"/>
                <h2 class="parent-header first-level-cat"><?=$catData['cat']['name']?></h2>

                <?php if (isset($catData['children']) && count($catData['children']) > 0): ?>
                <div>
                    <ul class="parent-cat-children"><!-- open second level ul -->
                    <?php foreach ($catData['children'] as $secondLvlCatId => $secondLevelCatData): ?>
                        <li class="child-cat accordion-second-level"> <!-- open second level li -->
                            <input type="hidden" name="filter_fields_order[<?=$secondLvlCatId?>][<?=htmlspecialchars(serialize($secondLevelCatData))?>]" value="<?=$secondLvlCatId?>"/>
                            <h4 class="child-header second-level-cat"><?=$secondLevelCatData['cat']['name']?></h4>

                            <?php if (isset($secondLevelCatData['children']) && count($secondLevelCatData['children']) > 0): ?>
                            <div>
                            <ul class="child-cat-children"><!-- open third level ul -->
                            <?php foreach ($secondLevelCatData['children'] as $thirdLvlCatId => $thirdLevelCatData): ?>
                                <li class="child-child-cat accordion-second-level">
                                    <input type="hidden" name="filter_fields_order[<?=$thirdLvlCatId?>][<?=htmlspecialchars(serialize($thirdLevelCatData))?>]" value="<?=$secondLvlCatId?>"/>
                                    <h4 class="child-header second-level-cat"><?=$thirdLevelCatData['cat']['name']?></h4>
                                </li>
                            <?php endforeach; ?>
                            </ul><!-- close third level ul -->
                            </div>
                            <?php endif; ?>
                        </li><!-- close second level li -->
                    <?php endforeach; ?>
                    </ul><!-- close second level ul -->
                </div>
                <?php endif; ?>

                <?php endforeach; ?>
                </ul><!--filter_fields_list-->
            </div><!--gf-sortable-categories-wrapper-->
            <label for="number_of_categories"><?= __('Broj kategorija koje će biti prikazane na bočnom meniju') ?></label>
            <input type="number" name="number_of_categories_in_sidebar"
                   value="<?= $number_of_categories ?>"/>
            <?php submit_button('Sačuvajte izmene', 'primary', 'gf-sortable-categories'); ?>
        </form>
        <form id="category-reset" method="post" action="">
            <input class="button button-primary" type="submit" name="reset-categories"
                   value="Resetujte redosled kategorija"/>
        </form>
    </div><!--WRAP-->
    <script type="text/javascript">
        jQuery(document).ready(function () {
            $('#category-reset').click(function (e) {
                if (confirm("Da li ste sigurni da želite da resetujete redosled kategorija ?")) {
                    $('#category-reset').submit();
                }
            });
        });
    </script>
    <?php
}

function arrayRecursiveDiff($aArray1, $aArray2) {
    $aReturn = array();

    foreach ($aArray1 as $mKey => $mValue) {
        if (array_key_exists($mKey, $aArray2)) {
            if (is_array($mValue)) {
                $aRecursiveDiff = arrayRecursiveDiff($mValue, $aArray2[$mKey]);
                if (count($aRecursiveDiff)) { $aReturn[$mKey] = $aRecursiveDiff; }
            } else {
                if ($mValue != $aArray2[$mKey]) {
                    $aReturn[$mKey] = $mValue;
                }
            }
        } else {
            $aReturn[$mKey] = $mValue;
        }
    }
    return $aReturn;
}


// Category sidebar
add_shortcode('gf-category-megamenu', 'gf_category_megamenu_shortcode');
function gf_category_megamenu_shortcode()
{
    $key = 'gf-megamenu';
    $cache = new GF_Cache();
    $html = $cache->redis->get($key);
    if ($html === false) {
        ob_start();
        printMegaMenu();
        $html = ob_get_clean();
        $cache->redis->set($key, $html, 60 * 60); // 1 hour
    }

    echo $html;
}
/**
 * Prints out mega menu with categories
 */
function printMegaMenu()
{
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
    if (!empty(get_option('number_of_categories_in_sidebar'))) {
        $number_of_categories = esc_attr(get_option('number_of_categories_in_sidebar'));
    }

    $product_cats_array = get_option('filter_fields_order');
    if (!empty($product_cats_array)) {
        foreach ($product_cats_array as $termId => $data) {
            $product_cats[] = get_term($termId, 'product_cat');
        }
    } else {
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
    $i = 0; //counter for number of ccategories
    $c = 0; //counter for child cats children
    $pcc = 0; //counter for parent cat children
    echo
    '<div id="gf-wrapper">
	     <div class="gf-sidebar">
		     <div class="gf-toggle"><i class="fa fa-bars"></i></div>
		       <div class="gf-navblock">';
    if ($i <= $number_of_categories) {
        foreach ($product_cats as $cat) {
            if ($cat->parent == 0) {
                $parent_children_count = count(get_term_children($cat->term_id, 'product_cat'));
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
            if ($pcc == $parent_children_count) {
                echo '</div>
                    </div>
                </li>
            </ul>';
                $pcc = 0;
            }
        }
    }
    echo '</div>
	</div>
</div>';
}
add_shortcode('gf-category-mobile', 'gf_category_mobile_toggle_shortcode');
function gf_category_mobile_toggle_shortcode()
{
//    if (wp_is_mobile()) {
    $key = 'gf-megamenu-mobile';
    $cache = new GF_Cache();
    $html = $cache->redis->get($key);
    if ($html === false) {
        ob_start();
        printMobileMegaMenu();
        $html = ob_get_clean();
        $cache->redis->set($key, $html, 60 * 60); // 1 hour
    }
    echo $html;
//    }
}
function printMobileMegaMenu() {
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
    if (!empty(get_option('number_of_categories_in_sidebar'))) {
        $number_of_categories = esc_attr(get_option('number_of_categories_in_sidebar'));
    }
    $product_cats_array = get_option('filter_fields_order');
    if (!empty($product_cats_array)) {
        foreach ($product_cats_array as $termId => $data) {
            $product_cats[] = get_term($termId, 'product_cat');
        }
//        $product_cats_array = get_option('filter_fields_order');
//        foreach ($product_cats_array as $product_cat) {
//            $product_cats[] = get_term($product_cat['term_id'], 'product_cat');
//        }
    } else {
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
    $i = 0; //counter for number of ccategories
    $c = 0; //counter for child cats children
    $pcc = 0; //counter for parent cat children
    echo '<div class="gf-category-mobile-toggle"><i class="fas fa-bars" id="gf-bars-icon-toggle"></i></div>';
    echo '<div class="gf-category-accordion">';
    echo '<div class="gf-category-accordion__item gf-category-accordion__item--main"><h5>Kategorije</h5></div>';
    if ($i <= $number_of_categories) {
        foreach ($product_cats as $cat) {
            if ($cat->parent == 0) {
                $parent_children_count = count(get_term_children($cat->term_id, 'product_cat'));
                $i++;
                require(realpath(__DIR__ . '/template-parts/category-megamenu/mobile/first-level.php'));
            }
            if (gf_check_level_of_category($cat->term_id) == 2) {
                $child_count = count(get_term_children($cat->term_id, 'product_cat'));
                require(realpath(__DIR__ . '/template-parts/category-megamenu/mobile/second-level.php'));
                $pcc++;
            }
            if (gf_check_level_of_category($cat->term_id) == 3) {
                require(realpath(__DIR__ . '/template-parts/category-megamenu/mobile/third-level.php'));
                $c++;
                $pcc++;
            }
            if (gf_check_level_of_category($cat->term_id) == 2 || gf_check_level_of_category($cat->term_id) == 3) {
                if ($c == $child_count) {
                    echo '</div>';
                    $c = 0;
                }
            }
            if ($pcc == $parent_children_count) {
                echo '</div>';
                $pcc = 0;
            }
        }
    };
    echo '</div>';
}
