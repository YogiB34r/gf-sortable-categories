<?php
    $numberOfCategories = count($topLevelCats);
    if (!empty(get_option('number_of_categories_in_sidebar'))) {
        $numberOfCategories = esc_attr(get_option('number_of_categories_in_sidebar'));
    }
?>

<div class="wrap">
        <h2><?= __('Opcije sortiranja kategorija', 'gf-sortable-categories') ?></h2>
        <br />
        <?php if (isset($_GET['message'])) : ?>
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
                    foreach ($filterFieldOrder as $catId => $catData) : ?>
                            <?php include(__DIR__.'/category-levels/first-level.php'); ?>
                            <?php if (isset($catData['children']) && count($catData['children']) > 0) : ?>
                                <div>
                                    <ul class="parent-cat-children">
                                        <!-- open second level ul -->
                                        <?php foreach ($catData['children'] as $secondLvlCatId => $secondLevelCatData) : ?>
                                            <?php include(__DIR__.'/category-levels/second-level.php'); ?>
                                                
                                                <!-- third level categories -->
                                                <?php if (isset($secondLevelCatData['children']) && count($secondLevelCatData['children']) > 0){
                                                    include(__DIR__.'/category-levels/third-level.php');        
                                                }
                                                ?>

                                            </li><!-- close second level li -->
                                        <?php endforeach; ?>
                                    </ul><!-- close second level ul -->
                                </div>
                            <?php endif; ?>
                    <?php endforeach; ?>
                </ul>
                <!--filter_fields_list-->
            </div>
            <!--gf-sortable-categories-wrapper-->
            <label for="number_of_categories"><?= __('Broj kategorija koje će biti prikazane na bočnom meniju') ?></label>
            <input type="number" name="number_of_categories_in_sidebar" value="<?= $numberOfCategories ?>" />
            <?php submit_button('Sačuvajte izmene', 'primary', 'gf-sortable-categories'); ?>
        </form>
        <form id="category-reset" method="post" action="">
            <input class="button button-primary" type="submit" name="reset-categories" value="Resetujte redosled kategorija" />
        </form>
    </div>
    <!--WRAP-->
    <script type="text/javascript">
        jQuery(document).ready(function() {
            $('#category-reset').click(function(e) {
                if (confirm("Da li ste sigurni da želite da resetujete redosled kategorija ?")) {
                    $('#category-reset').submit();
                }
            });
        });
    </script>