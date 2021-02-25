<div>
    <ul class="child-cat-children">
    <!-- open third level ul -->
    <?php foreach ($secondLevelCatData['children'] as $thirdLvlCatId => $thirdLevelCatData):?>
        <li class="child-child-cat accordion-second-level">
            <input type="hidden" name="filter_fields_order[<?= $thirdLvlCatId ?>][<?= htmlspecialchars(serialize($thirdLevelCatData)) ?>]" value="<?= $secondLvlCatId ?>" />
            <h4 class="child-header second-level-cat"><?= $thirdLevelCatData['cat']['name'] ?></h4>
        </li>
    <?php endforeach;?>
    </ul><!-- close third level ul -->
</div>

