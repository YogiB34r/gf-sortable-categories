<div class="gf-category-accordion__item gf-category-accordion__subitem mt-sm">
    <a class="" href="<?= get_term_link((int)$cat->term_id) ?>"><?= $cat->name ?></a>
    <?php if (!empty(get_term_children($cat->term_id, 'product_cat'))): ?>
    <i class="gf-category-accordion__expander fas fa-plus"></i>
    <?php endif ?>