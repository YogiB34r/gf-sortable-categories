<?php if ($parent == 0):
    $parentChildrenCount = count(get_term_children($id, 'product_cat'));
    ?>
    <li class="parent-cat accordion-first-level">
    <input type="hidden" name="filter_fields_order[<?= $id ?>][term_id]" value="<?= $id ?>"/>
    <h2 class="parent-header first-level-cat"><?= $name ?></h2>
    <?php if ($parentChildrenCount != 0) {
    echo '<ul class="parent-cat-children"><!-- open second level ul -->';
    } ?>
<?php endif;

$currentCatLevel = gf_check_level_of_category($id);

if ($currentCatLevel == 2):
    $parentCounter++;
    $childrenCount = count(get_term_children($id, 'product_cat'));
    ?>
    <li class="child-cat accordion-second-level"> <!-- open second level li -->
    <input type="hidden" name="filter_fields_order[<?= $id ?>][term_id]" value="<?= $id ?>"/>
    <h4 class="child-header second-level-cat"><?= $name ?></h4>
    <?php if ($childrenCount != 0) {
    echo '<ul class="child-cat-children"><!-- open third level ul -->';
    } ?>
<?php endif;

if ($currentCatLevel == 3):
    $tempChildrenCounter++;
    $parentCounter++;
    ?>
    <li class="third-level-cat">
        <input type="hidden" name="filter_fields_order[<?= $id ?>][term_id]"
               value="<?= $id ?>"/>
        <h5 class="childs-of-child-header third-level-cat"><?= $name ?></h5>
    </li>
<?php endif;
if ($parentCounter == $parentChildrenCount) {
    die();
    if ($parentChildrenCount != 0) {
        echo '</ul> <!-- close second level ul -->';
    }
    echo '</li> <!-- close second level li -->';
    $parentCounter = 0;
}

if ($currentCatLevel == 3) {
    if ($tempChildrenCounter == $childrenCount) {
        if ($childrenCount != 0) {
            echo '</ul> <!-- close third level ul -->';
        }

        $tempChildrenCounter = 0;
    }
}

if ($currentCatLevel == 2) {
    if ($tempChildrenCounter == $childrenCount) {
        if ($childrenCount != 0) {
            echo '</ul> <!-- close second level ul -->';
        }
        echo '</li> <!-- close second level li -->';

        $tempChildrenCounter = 0;
    }
}
?>