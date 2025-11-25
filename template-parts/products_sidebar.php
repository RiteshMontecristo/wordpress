<?php

$children = get_terms(array(
    'taxonomy' => 'product_cat',
    'parent' => 0,
    'hide_empty' => false,
));

if (is_search()) {
?>

    <sidebar id="products-sidebar">
        <div class="filter-container" id="filter-container">
            <div class="filter category filter-hide">
                <h3>Brand</h3>
                <ul class="hidden">
                    <?php foreach ($children as $child_term) {
                        if ($child_term->name !== "Uncategoriezed" && $child_term->name !== "Jewellery") {
                            $brandsCatgory = get_terms(array(
                                'taxonomy' => 'product_cat',
                                'parent' => $child_term->term_id,
                            ));

                            foreach ($brandsCatgory as $brandCategory) {
                    ?>
                                <li>
                                    <input type="checkbox" name="category[]" id="<?php echo esc_attr($brandCategory->slug); ?>"
                                        value="<?php echo esc_attr($brandCategory->slug); ?>">
                                    <label
                                        for="<?php echo esc_attr($brandCategory->slug); ?>"><?php echo esc_html($brandCategory->name); ?></label>
                                </li>
                    <?php
                            }
                        }
                    } ?>
                </ul>
            </div>
        </div>
    </sidebar>

<?php
    return;
}

$current_category = get_queried_object();
$active_brand = "";

if ($current_category->parent === 0) {
    $parent_category = "";
    $parent_category_name = "";
    $parent_category_slug = "";
} else {
    $parent_category = get_term($current_category->parent, 'product_cat');
    $parent_category_name = $parent_category->name;
    $parent_category_slug = $parent_category->slug;
}

// grabbing the child categories of the current category
$children = get_terms(array(
    'taxonomy' => 'product_cat',
    'parent' => $current_category->term_id,
    'hide_empty' => false,
));

$current_slug = $current_category->slug;


if (!empty($children) || $parent_category_name == "Jewellery") {
?>
    <sidebar id="products-sidebar" class="">
        <div class="filter-container" id="filter-container">

            <!-- If it has children -->
            <?php if (!empty($children) && !is_wp_error($children)) {
                if ($current_slug == "watches" || $current_slug == "designer") {
                    $heading = "Brand";

                    if ($current_slug == "designer") {
                        $active_brand = 'montecristo';
                    } else {
                        $active_brand = 'bellross';
                    }
                } else if ($current_slug == "jewellery") {
                    $heading = "Type";
                } else {
                    $heading = "Collection";
                }
            ?>
                <div class="filter category filter-hide">
                    <h3><?php echo $heading ?></h3>
                    <ul class="hidden">
                        <?php foreach ($children as $child_term) { ?>
                            <li>
                                <input <?php echo ($child_term->slug == $active_brand) ? "checked" : ""; ?> type="checkbox"
                                    name="category[]" id="<?php echo esc_attr($child_term->slug); ?>"
                                    value="<?php echo esc_attr($child_term->slug); ?>">
                                <label
                                    for="<?php echo esc_attr($child_term->slug); ?>"><?php echo esc_html($child_term->name); ?></label>
                            </li>
                        <?php } ?>
                    </ul>
                </div>
            <?php } ?>

            <?php
            if ($parent_category_name == "Watches" || $current_slug == "watches") {
            ?>
                <div class="filter filter-hide tags">
                    <h3>Gender</h3>
                    <ul class="hidden">
                        <li>
                            <input type="checkbox" name="target_group[]" id="ladies-watch" value="ladies-watch">
                            <label for="ladies-watch">Ladies</label>
                        </li>
                        <li>
                            <input type="checkbox" name="target_group[]" id="mens-watch" value="mens-watch">
                            <label for="mens-watch">Men</label>
                        </li>
                        <li>
                            <input type="checkbox" name="target_group[]" id="unisex" value="unisex">
                            <label for="unisex">Unisex</label>
                        </li>
                    </ul>
                </div>
                <div class="filter filter-hide tags">
                    <h3>Materials</h3>
                    <ul class="hidden">
                        <li>
                            <input type="checkbox" name="materials[]" id="stainless-steel" value="stainless-steel">
                            <label for="stainless-steel">Stainless Steel</label>
                        </li>
                        <li>
                            <input type="checkbox" name="materials[]" id="18k-rose-gold" value="18k-rose-gold">
                            <label for="18k-rose-gold">Rose Gold</label>
                        </li>
                        <li>
                            <input type="checkbox" name="materials[]" id="18k-yellow-gold" value="18k-yellow-gold">
                            <label for="18k-yellow-gold">Yellow Gold</label>
                        </li>
                        <li>
                            <input type="checkbox" name="materials[]" id="ceramic" value="ceramic">
                            <label for="ceramic">Ceramic</label>
                        </li>
                        <li>
                            <input type="checkbox" name="materials[]" id="titanium" value="titanium">
                            <label for="titanium">Titanium</label>
                        </li>
                        <li>
                            <input type="checkbox" name="materials[]" id="platinum" value="platinum">
                            <label for="platinum">Platinum</label>
                        </li>
                    </ul>
                </div>
            <?php } else {
            ?>
                <?php
                if ($parent_category_name != "Jewellery" && $current_slug != "jewellery") {
                    $jewellery = get_term_by("slug", "jewellery", "product_cat");

                    $jewellery_children = get_terms(array(
                        'taxonomy' => 'product_cat',
                        'parent' => $jewellery->term_id,
                        'hide_empty' => false,
                    ));
                ?>
                    <div class="filter filter-hide tags">
                        <h3>Type</h3>
                        <ul class="hidden">
                            <?php foreach ($jewellery_children as $child_term) { ?>
                                <li>
                                    <input type="checkbox" name="type[]" id="<?php echo esc_attr($child_term->slug); ?>"
                                        value="<?php echo esc_attr($child_term->slug); ?>">
                                    <label
                                        for="<?php echo esc_attr($child_term->slug); ?>"><?php echo esc_html($child_term->name); ?></label>
                                </li>
                            <?php } ?>
                        </ul>
                    </div>
                <?php } ?>
                <div class="filter filter-hide tags">
                    <h3>Gemstone</h3>
                    <ul class="hidden">
                        <li>
                            <input type="checkbox" name="gemstone[]" id="diamond" value="diamond">
                            <label for="diamond">Diamond</label>
                        </li>
                        <li>
                            <input type="checkbox" name="gemstone[]" id="no-stone" value="no-stone">
                            <label for="no-stone">No Diamond</label>
                        </li>
                        <li>
                            <input type="checkbox" name="gemstone[]" id="colored-gems" value="colored-gems">
                            <label for="colored-gems">Colored Gemstone</label>
                        </li>
                    </ul>
                </div>

                <div class="filter filter-hide tags">
                    <h3>Material</h3>
                    <ul class="hidden">
                        <li>
                            <input type="checkbox" name="materials[]" id="18k-yellow-gold" value="18k-yellow-gold">
                            <label for="18k-yellow-gold">Yellow Gold</label>
                        </li>
                        <li>
                            <input type="checkbox" name="materials[]" id="18k-white-gold" value="18k-white-gold">
                            <label for="18k-white-gold">White Gold</label>
                        </li>
                        <li>
                            <input type="checkbox" name="materials[]" id="18k-rose-gold" value="18k-rose-gold">
                            <label for="18k-rose-gold">Rose Gold</label>
                        </li>
                        <li>
                            <input type="checkbox" name="materials[]" id="platinum" value="platinum">
                            <label for="platinum">Platinum</label>
                        </li>
                    </ul>
                </div>
                <!-- <div class="filter filter-hide tags">
                    <h3>Gifts</h3>
                    <ul class="hidden">
                        <li>
                            <input type="checkbox" name="gifts[]" id="anniversary" value="anniversary">
                            <label for="anniversary">Anniversary</label>
                        </li>
                        <li>
                            <input type="checkbox" name="gifts[]" id="graduation" value="graduation">
                            <label for="graduation">Graduation</label>
                        </li>
                        <li>
                            <input type="checkbox" name="gifts[]" id="for-her" value="for-her">
                            <label for="for-her">For Her</label>
                        </li>
                        <li>
                            <input type="checkbox" name="gifts[]" id="for-him" value="for-him">
                            <label for="for-him">For Him</label>
                        </li>
                    </ul>
                </div> -->
            <?php }

            // Do not display price for jewellery and montecristo categories
            if (!($parent_category_slug == "jewellery" || $current_slug == "montecristo")) {
            ?>
                <div class="filter filter-hide price">
                    <h3>Price</h3>
                    <div class="pricecontainer">
                        <div>
                            <label>Min</label>
                            <input type="number" name="price[]" id="min-price" min="0">
                        </div>
                        <span></span>
                        <div>
                            <label>Max</label>
                            <input type="number" name="price[]" id="max-price" min="0">
                        </div>
                    </div>
                </div>
            <?php } ?>

            <!-- <div class="filter reset">
                <button id="reset-filters">Reset
                    <svg fill="#000000" width="800px" height="800px" viewBox="0 0 32 32" id="icon" xmlns="http://www.w3.org/2000/svg">
                        <defs>
                            <style>
                                .cls-1 {
                                    fill: none;
                                }
                            </style>
                        </defs>
                        <path d="M22.5,9A7.4522,7.4522,0,0,0,16,12.792V8H14v8h8V14H17.6167A5.4941,5.4941,0,1,1,22.5,22H22v2h.5a7.5,7.5,0,0,0,0-15Z" />
                        <path d="M26,6H4V9.171l7.4142,7.4143L12,17.171V26h4V24h2v2a2,2,0,0,1-2,2H12a2,2,0,0,1-2-2V18L2.5858,10.5853A2,2,0,0,1,2,9.171V6A2,2,0,0,1,4,4H26Z" />
                        <rect id="_Transparent_Rectangle_" data-name="&lt;Transparent Rectangle&gt;" class="cls-1" width="16" height="16" />
                    </svg>
                </button>
            </div> -->
        </div>

    </sidebar>

<?php }
?>