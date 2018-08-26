<?php if ($parent == 0) {
    $parent_children_count = count(get_term_children($id, 'product_cat'));
    echo '<li class="parent-cat">';
    echo '<h2 class="parent-header first-level-cat">' . $name . '</h2>
         <input type="hidden" name="filter_fields_order[' . $id . '][term_id]"
               value="' . $id . '"/>
        <input type="hidden" name="filter_fields_order[' . $id . '][name]"
               value="' . $name . '"/>
        <input type="hidden" name="filter_fields_order[' . $id . '][parent]"
               value="' . $parent . '"/>';
    echo '<ul class="parent-cat-children">';
}
if (gf_check_level_of_category($id) == 2) {
    $c++;
    $pc++;
    $children_count = count(get_term_children($id, 'product_cat'));
    echo '<li class="child-cat">';
    echo '<h4 class="child-header second-level-cat">' . $name . '</h4>
         <input type="hidden" name="filter_fields_order[' . $id . '][term_id]"
               value="' . $id . '"/>
        <input type="hidden" name="filter_fields_order[' . $id . '][name]"
               value="' . $name . '"/>
        <input type="hidden" name="filter_fields_order[' . $id . '][parent]"
               value="' . $parent . '"/>';
    echo '<ul class="child-cat-children">';
}
if (gf_check_level_of_category($id) == 3):$cc++;$pc++;?>
    <li class="third-level-cat">
        <?= '<h5>'.$name.'</h5>' ?>
        <input type="hidden" name="filter_fields_order[<?php echo $id; ?>][term_id]"
               value="<?php echo $id; ?>"/>
        <input type="hidden" name="filter_fields_order[<?php echo $id; ?>][name]"
               value="<?php echo $name; ?>"/>
        <input type="hidden" name="filter_fields_order[<?php echo $id; ?>][parent]"
               value="<?php echo $parent; ?>"/>
    </li>
<?php endif;
if ($pc == $parent_children_count) {
    echo '</ul>';
    echo '</li>';

    $pc = 0;
}
if (gf_check_level_of_category($id) == 3 || gf_check_level_of_category($id) == 2) {
    if ($cc == $children_count) {
        echo '</ul>';
        echo '</li>';

        $cc = 0;
    }
}
?>