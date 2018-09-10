<?php if ($parent == 0):
    $parent_children_count = count(get_term_children($id, 'product_cat'));
    ?>
    <li class="parent-cat accordion-first-level">
    <input type="hidden" name="filter_fields_order[<?= $id ?>][term_id]"
           value="<?= $id ?>"/>
    <h2 class="parent-header first-level-cat"><?= $name ?></h2>
    <?php if ($parent_children_count != 0) {
    echo '<ul class="parent-cat-children">';
}
    ?>

<?php endif;
if (gf_check_level_of_category($id) == 2):
    $c++;
    $pc++;
    $children_count = count(get_term_children($id, 'product_cat'));
    ?>
    <li class="child-cat accordion-second-level">
    <input type="hidden" name="filter_fields_order[<?= $id ?>][term_id]"
           value="<?= $id ?>"/>
    <h4 class="child-header second-level-cat"><?= $name ?></h4>
    <?php if ($children_count != 0) {
    echo '<ul class="child-cat-children">';
}
    ?>
<?php endif;
if (gf_check_level_of_category($id) == 3):
    $cc++;
    $pc++;
    ?>
    <li class="third-level-cat">
        <input type="hidden" name="filter_fields_order[<?= $id ?>][term_id]"
               value="<?= $id ?>"/>
        <h5 class="childs-of-child-header third-level-cat"><?= $name ?></h5>
    </li>
<?php endif;
if ($pc == $parent_children_count) {
    if ($parent_children_count != 0) {
        echo '</ul>';
    }
    echo '</li>';
    $pc = 0;
}
if (gf_check_level_of_category($id) == 3 || gf_check_level_of_category($id) == 2) {
    if ($cc == $children_count) {
        if ($children_count != 0) {
            echo '</ul>';
        }
        echo '</li>';

        $cc = 0;
    }
}
?>