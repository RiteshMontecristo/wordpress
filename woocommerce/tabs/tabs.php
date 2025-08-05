<?php

global $product;

// Get product description and attributes
$description = $product->get_description();
$attributes = $product->get_attributes();
?>

<div class="col-full">
    <section <?php if (! empty($attributes)) { ?> class="product-info" <?php } ?>>
        <div class="description">
            <?php echo apply_filters('the_content', $product->get_description()); ?>
        </div>

        <?php if (! empty($attributes)) { ?>
            <div class="specification">
                <h2>Specifications</h2>
                <ul>
                    <?php foreach ($attributes as $attribute) { ?>
                        <li>
                            <strong><?php echo wc_attribute_label($attribute->get_name()); ?>:</strong>
                            <?php if ($attribute->get_visible() && $attribute->is_taxonomy()) : ?>
                                <?php echo implode(', ', wc_get_product_terms($product->get_id(), $attribute->get_name(), array('fields' => 'names'))); ?>
                            <?php else : ?>
                                <?php echo esc_html(implode(', ', $attribute->get_options())); ?>
                            <?php endif; ?>
                        </li>
                    <?php } ?>
                </ul>
            </div>
        <?php } ?>
    </section>
</div>