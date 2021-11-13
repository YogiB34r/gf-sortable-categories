<?php

namespace GF\Util;

class CategoryFunctions
{
    public static function filterOutJunkCats(\WP_Term $term)
    {
        $cat1 = get_term_by('slug', 'specijalne-promocije', 'product_cat');
        $cat2 = get_term_by('slug', 'uncategorized', 'product_cat');

        if ($term->term_id === $cat1->term_id || $term->term_id === $cat2->term_id ||
            $term->parent === $cat1->term_id || $term->parent === $cat2->term_id) {
            return false;
        }
        return $term;
    }

    public static function getProductUrl($post, $permalink, $absolute = true)
    {
        $cats = [];
        foreach (wp_get_post_terms($post->ID, 'product_cat') as $term) {
            $cat = static::filterOutJunkCats($term);
            if ($cat) {
                $cats[] = $cat;
            }
        }
        if (count($cats) === 0) {
            $cats[] = (object) ['slug' => 'uncategorized'];
        }
        if ($absolute) {

            return home_url() . '/'. $cats[count($cats)-1]->slug .'/'. basename($permalink) .'/';
        }

        return $cats[count($cats)-1]->slug .'/'. basename($permalink) .'/';
    }

    public static function buildTermPath($term)
    {
        $slug = urldecode($term->slug);
        $ancestors = get_ancestors($term->term_id, 'product_cat');
        foreach ($ancestors as $ancestor) {
            $ancestor_object = get_term($ancestor, 'product_cat');
            if (static::gf_check_level_of_category($term->term_id) === 3) {
                if (!$ancestor_object->parent){
                    $slug = urldecode($ancestor_object->slug) . '/' . $slug;
                }
            } else {
                $slug = urldecode($ancestor_object->slug) . '/' . $slug;
            }
        }
        return $slug;
    }

    public static function gf_check_level_of_category($cat_id)
    {
        $cat = get_term_by('id', $cat_id, 'product_cat');
        if ($cat->parent === 0){
            return 1;
        }
        if (get_term($cat->parent, 'product_cat')->parent === 0){
            return 2;
        }
        return 3;
    }

    public static function gf_get_category_children_ids($slug)
    {
        $cat = get_term_by('slug', $slug, 'product_cat');
        $childrenIds = [];
        if ($cat) {
            $catChildren = get_term_children($cat->term_id, 'product_cat');
            $childrenIds[] = $cat->term_id;
            foreach ($catChildren as $child) {
                $childrenIds[] = $child;
            }
        }
        return $childrenIds;
    }

    public static function gf_get_top_level_categories($exclude = array())
    {
        $top_level_categories = [];
        foreach (static::gf_get_categories($exclude) as $category) {
            if (!$category->parent) {
                $top_level_categories[] = $category;
            }
        }
        return $top_level_categories;
    }

    public static function gf_get_categories($exclude = array())
    {
        $args = array(
            'orderby' => 'name',
            'order' => 'asc',
            'hide_empty' => false,
            'exclude' => $exclude,
        );
        return get_terms('product_cat', $args);
    }
}