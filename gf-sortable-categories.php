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

        wp_enqueue_script('sortable-categories-admin-js');
        wp_enqueue_script('jquery-ui-widget');
        wp_enqueue_script('jquery-ui-sortable');
        wp_enqueue_script('jquery-ui-accordion');
    }
}
add_action('admin_enqueue_scripts', 'gf_sortable_categories_admin_scripts');
add_action('admin_menu', 'gf_sortable_categories_options_create_menu');
function gf_sortable_categories_options_create_menu()
{
    add_submenu_page('nss-panel', 'Sortable Categories', 'Sortiranje kategorija', 'administrator', 'sortable_categories_options', 'gf_sortable_categories_options_page', 10);
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
    $gfSliderId = '';
    $sliderCat = get_term_by('slug', 'specijalne-promocije', 'product_cat');
    if ($sliderCat) {
        $gfSliderId = $sliderCat->term_id;
    }

    $topLevelCats = \Gf\Util\CategoryFunctions::gf_get_top_level_categories($gfSliderId);
    $productCategories = [];
    global $wpdb;
    $data = $wpdb->get_var($wpdb->prepare("SELECT option_value FROM $wpdb->options WHERE option_name = %s LIMIT 1", 'filter_fields_order'));

    $productCategories = populateCategories($topLevelCats);

    $defaultFieldOrder = $productCategories;
    $filterFieldOrder = $defaultFieldOrder;

    if ($data) {
        $filterFieldOrder = [];
        $filterFieldOrder = setFilterFieldOrder($data);
    }

    //Check for diff in saved and original categories for added/removed ones
    if (md5(serialize($defaultFieldOrder)) !== md5(serialize($filterFieldOrder))) {
        /**
         * when relations are changed, that means one item is removed from displayed, and added to original,
         * so both actions will be performed.
         */
        $added = arrayRecursiveDiff($defaultFieldOrder, $filterFieldOrder);
        $removed = arrayRecursiveDiff($filterFieldOrder, $defaultFieldOrder);
        /**
         * will remove elements from displayed array that are not found in the original one.
         */
        if (!empty($removed)) {
            $filterFieldOrder = removeItemsFromDisplay($removed, $filterFieldOrder);
        }
        /**
         * will add elements into displayed array, that are added to original one
         */
        if (!empty($added)) {
            $filterFieldOrder = addItemsToDisplay($added, $filterFieldOrder);
        }
    }
    require_once(__DIR__ . '/template-parts/admin/gf-sortable-categories-template.php');
}

function populateCategories($topLevelCats)
{
    $productCategories = [];
    foreach ($topLevelCats as $cat) {
        if ($cat->term_id === 3152) {
            continue;
        }

        $catTermChildren = get_term_children($cat->term_id, 'product_cat');
        $productCategories[$cat->term_id]['cat'] = [
            'cat_id' => $cat->term_id,
            'name' => $cat->name,
            'parent' => $cat->parent,
        ];

        if (!empty($catTermChildren)) {
            foreach ($catTermChildren as $secondLevelCategoryId) {
                if (\Gf\Util\CategoryFunctions::gf_check_level_of_category($secondLevelCategoryId) == 2) {
                    $secondCatTermChildren = get_term_children($secondLevelCategoryId, 'product_cat');
                    $secondLevelCategory = get_term($secondLevelCategoryId, 'product_cat');
                    $productCategories[$cat->term_id]['children'][$secondLevelCategoryId]['cat'] = [
                        'cat_id' => $secondLevelCategory->term_id,
                        'name' => $secondLevelCategory->name,
                        'parent' => $secondLevelCategory->parent,
                    ];

                    if (!empty($secondCatTermChildren)) {
                        foreach ($secondCatTermChildren as $thirdLevelCategoryId) {
                            $thirdLevelCategory = get_term($thirdLevelCategoryId, 'product_cat');
                            $productCategories[$cat->term_id]['children'][$secondLevelCategoryId]['children'][$thirdLevelCategoryId]['cat'] = [
                                'cat_id' => $thirdLevelCategory->term_id,
                                'name' => $thirdLevelCategory->name,
                                'parent' => $thirdLevelCategory->parent,
                            ];
                        }
                    }
                }
            }
        }
    }
    return $productCategories;
}
function setFilterFieldOrder($data)
{
    $filterFieldOrder = [];
    foreach (unserialize($data) as $termId => $data) {
        $termData = unserialize(array_keys($data)[0]);
        if ($termData['cat']['parent']) {
            $myParent = get_term($termData['cat']['parent'], 'product_cat');
            if (is_object($myParent)) {
                if ($myParent->parent) { //3rd level
                    $myParentsParent = get_term($myParent->parent, 'product_cat');
                    if (is_object($myParentsParent)) {
                        $filterFieldOrder[$myParentsParent->term_id]['children'][$myParent->term_id]['children'][$termId]['cat'] = $termData['cat'];
                    }
                } else { //2nd level
                    $filterFieldOrder[$myParent->term_id]['children'][$termId]['cat'] = $termData['cat'];
                }
            }
        } else {
            $filterFieldOrder[$termId]['cat'] = $termData['cat'];
        }
    }
    return $filterFieldOrder;
}
function addItemsToDisplay($added, $filterFieldOrder)
{
    foreach ($added as $mainCatID => $mainCatData) {
        if (!is_array($mainCatData['children'])) { // add first level cat only
            $filterFieldOrder[$mainCatID] = $mainCatData;
        } else {
            foreach ($mainCatData['children'] as $subCatId => $subCatData) {
                if (!isset($subCatData['children']) || !is_array($subCatData['children'])) {
                    if (!isset($filterFieldOrder[$mainCatID])) {
                        //add first if not there
                        $filterFieldOrder[$mainCatID] = $mainCatData;
                    }
                    //add second level cat
                    $filterFieldOrder[$mainCatID]['children'][$subCatId] = $subCatData;
                } else { //add second level if not there
                    if (!isset($filterFieldOrder[$mainCatID]['children'][$subCatId])) {
                        $filterFieldOrder[$mainCatID]['children'][$subCatId] = $subCatData;
                    }
                    //add third level cat
                    foreach ($subCatData['children'] as $subCatChildId => $subCatChildData) {
                        $filterFieldOrder[$mainCatID]['children'][$subCatId]['children'][$subCatChildId] = $subCatChildData;
                    }
                }
            }
        }
    }
    return $filterFieldOrder;
}
function removeItemsFromDisplay($removed, $filterFieldOrder)
{
    foreach ($removed as $mainCatID => $mainCatData) {
        if (!is_array($mainCatData['children'])) { //remove first level cat
            unset($filterFieldOrder[$mainCatID]);
        } else {
            foreach ($mainCatData['children'] as $subCatId => $subCatData) {
                if (!isset($subCatData['children']) || !is_array($subCatData['children'])) { //remove second level cat
                    unset($filterFieldOrder[$mainCatID]['children'][$subCatId]);
                } else { //remove third level cat
                    foreach ($subCatData['children'] as $subCatChildId => $subCatChildData) {
                        unset($filterFieldOrder[$mainCatID]['children'][$subCatId]['children'][$subCatChildId]);
                    }
                }
            }
        }
    }
    return $filterFieldOrder;
}
function arrayRecursiveDiff($aArray1, $aArray2)
{
    $aReturn = array();

    foreach ($aArray1 as $mKey => $mValue) {
        if (array_key_exists($mKey, $aArray2)) {
            if (is_array($mValue)) {
                $aRecursiveDiff = arrayRecursiveDiff($mValue, $aArray2[$mKey]);
                if (count($aRecursiveDiff)) {
                    $aReturn[$mKey] = $aRecursiveDiff;
                }
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
 * Prints out mega menu with categories, all wrapped up
 */
function printMegaMenu()
{
    $sliderId = '';
    $productCats = [];
    $numberOfMainCategories = 24;
    $arrayProductCategories = get_option('filter_fields_order');
    if (get_term_by('slug', 'specijalne-promocije', 'product_cat')) {
        $sliderId = get_term_by('slug', 'specijalne-promocije', 'product_cat')->term_id;
    }

    if (!empty(get_option('number_of_categories_in_sidebar'))) {
        $numberOfMainCategories = esc_attr(get_option('number_of_categories_in_sidebar'));
    }

    if (!empty($arrayProductCategories)) {
        foreach ($arrayProductCategories as $termId => $data) {
            $productCats[] = get_term($termId, 'product_cat');
        }
    } else {
        foreach (\Gf\Util\CategoryFunctions::gf_get_top_level_categories($sliderId) as $cat) {
            if ($cat->term_id === 3152) {
                continue;
            }
            $catTermChildren = get_term_children($cat->term_id, 'product_cat');
            $productCats[] = $cat;
            if (!empty($catTermChildren)) {
                foreach ($catTermChildren as $secondLevelCategory) {
                    if (\Gf\Util\CategoryFunctions::gf_check_level_of_category($secondLevelCategory) == 2) {
                        $secondCatTermChildren = get_term_children($secondLevelCategory, 'product_cat');
                        $productCats[] = get_term($secondLevelCategory, 'product_cat');
                        if (!empty($secondCatTermChildren)) { 
                            foreach ($secondCatTermChildren as $thirdLevelCategory) {
                                $productCats[] = get_term($thirdLevelCategory, 'product_cat');
                            }
                        }
                    }
                }
            }
        }
    }
    $countCategories = 0; //counter for number of categories
    $countChildCategories = 0; //counter for child cats children
    $countParentCategories = 0; //counter for parent cat children
?>
    <div id="gf-wrapper" class="gf-sidebar">
        <div id="nssMegaNav" class="gf-navblock">
            <?php
            if ($countCategories <= $numberOfMainCategories) {
                foreach ($productCats as $cat) {
                    if (!$cat) {
                        continue;
                    }
                    if ($cat->parent == 0) {
                        $parentsChildrenCount = count(get_term_children($cat->term_id, 'product_cat'));
                        $countCategories++;
                        require(realpath(__DIR__ . '/template-parts/category-megamenu/first-level.php'));
                    }
                    $catLevel = \Gf\Util\CategoryFunctions::gf_check_level_of_category($cat->term_id);
                    if ($catLevel == 2) {
                        $childCount = count(get_term_children($cat->term_id, 'product_cat'));
                        require(realpath(__DIR__ . '/template-parts/category-megamenu/second-level.php'));
                        $countParentCategories++;
                    }
                    if ($catLevel == 3) {
                        require(realpath(__DIR__ . '/template-parts/category-megamenu/third-level.php'));
                        $countChildCategories++;
                        $countParentCategories++;
                    }
                    if ($catLevel == 3 || $catLevel == 2) :
                        if ($countChildCategories == $childCount) : ?>
                            </div>
                        <?php
                            $countChildCategories = 0;
                        endif;
                    endif;
                    if ($countParentCategories == $parentsChildrenCount) :
                    ?>
                 </div>
            </div>
        </div>
    <?php
                        $countParentCategories = 0;
                    endif;
                }
            }
            ?>
        </div>
    </div>
<?php
}

add_shortcode('gf-category-mobile', 'gf_category_mobile_toggle_shortcode');
function gf_category_mobile_toggle_shortcode()
{
    $key = 'gf-megamenu-mobile';
    $cache = new GF_Cache();
    $html = $cache->redis->get($key);
    if ($html === false) {
        ob_start();
        printMobileMegaMenu();
        $html = ob_get_clean();
        $cache->redis->set($key, $html, 60 * 60); // 1 hour
    }
    return $html;
}
/* prints categories for mobile mega menu, but without <div> wrapper and hamburger menu icon */
function printMobileMegaMenu()
{
    $gfSliderId = '';
    $productCats = [];
    $numOfCats = 24;
    $productCatsArray = get_option('filter_fields_order');

    if (get_term_by('slug', 'specijalne-promocije', 'product_cat')) {
        $gfSliderId = get_term_by('slug', 'specijalne-promocije', 'product_cat')->term_id;
    }

    if (!empty(get_option('number_of_categories_in_sidebar'))) {
        $numOfCats = esc_attr(get_option('number_of_categories_in_sidebar'));
    }

    if (!empty($productCatsArray)) {
        foreach ($productCatsArray as $termId => $data) {
            $productCats[] = get_term($termId, 'product_cat');
        }
    } else {
        foreach (\Gf\Util\CategoryFunctions::gf_get_top_level_categories($gfSliderId) as $cat) {
            if ($cat->term_id === 3152) {
                continue;
            }
            $catTermChildren = get_term_children($cat->term_id, 'product_cat');
            $productCats[] = $cat;
            if (!empty($catTermChildren)) {
                
                foreach ($catTermChildren as $secondLevelCat) {
                    if (\Gf\Util\CategoryFunctions::gf_check_level_of_category($secondLevelCat) == 2) {
                        $secondCatTermChildren = get_term_children($secondLevelCat, 'product_cat');
                        $productCats[] = get_term($secondLevelCat, 'product_cat');
                        if (!empty($secondCatTermChildren)) {
                            foreach ($secondCatTermChildren as $thirdLevelCat) {
                                $productCats[] = get_term($thirdLevelCat, 'product_cat');
                            }
                        }
                    }
                }
            }
        }
    }
    $catCounter = 0; //counter for number of ccategories
    $childrenCatCounter = 0; //counter for child cats children
    $parentCatsCounter = 0; //counter for parent cat children
    echo '<div class="gf-category-accordion__item gf-category-accordion__item--main"><h5>Kategorije</h5></div>';
    if ($catCounter <= $numOfCats) {
        foreach ($productCats as $cat) {
            if (!$cat) {
                continue;
            }
            if ($cat->parent == 0) {
                $parentChildrenCount = count(get_term_children($cat->term_id, 'product_cat'));
                $catCounter++;
                require(realpath(__DIR__ . '/template-parts/category-megamenu/mobile/first-level.php'));
            }
            $catLevel = \Gf\Util\CategoryFunctions::gf_check_level_of_category($cat->term_id);
            if ($catLevel == 2) {
                $childrenCount = count(get_term_children($cat->term_id, 'product_cat'));
                require(realpath(__DIR__ . '/template-parts/category-megamenu/mobile/second-level.php'));
                $parentCatsCounter++;
            }
            if ($catLevel == 3) {
                require(realpath(__DIR__ . '/template-parts/category-megamenu/mobile/third-level.php'));
                $childrenCatCounter++;
                $parentCatsCounter++;
            }
            if ($catLevel == 2 || $catLevel == 3) {
                if ($childrenCatCounter == $childrenCount) {
                    echo '</div>';
                    $childrenCatCounter = 0;
                }
            }
            if ($parentCatsCounter == $parentChildrenCount) {
                echo '</div>';
                $parentCatsCounter = 0;
            }
        }
    };
}