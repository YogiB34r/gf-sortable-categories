<?php if ($parent == 0) :?>
<?php if ($i != 1 || $i == count($filter_fields_order)):?>
    </ul>
<?php endif;?>
    <ul class="sortable-list">
<?php endif;?>
<li class="sortable-item <?php if ($parent != 0) echo 'sub-cat';?>">
    <?=$name?>
    <input type="hidden" name="filter_fields_order[<?php echo $id; ?>][term_id]"
           value="<?php echo $id; ?>"/>
    <input type="hidden" name="filter_fields_order[<?php echo $id; ?>][name]"
           value="<?php echo $name; ?>"/>
    <input type="hidden" name="filter_fields_order[<?php echo $id; ?>][parent]"
           value="<?php echo $parent; ?>"/>
</li>