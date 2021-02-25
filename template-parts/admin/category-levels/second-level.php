<li class="child-cat accordion-second-level">
    <!-- open second level li -->
    <input type="hidden" name="filter_fields_order[<?= $secondLvlCatId ?>][<?= htmlspecialchars(serialize($secondLevelCatData)) ?>]" value="<?= $secondLvlCatId ?>" />
    <h4 class="child-header second-level-cat"><?= $secondLevelCatData['cat']['name'] ?></h4>
