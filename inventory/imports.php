<?php

function import_page()
{
    render_page();

    if (isset($_POST['upload_data']) && !empty($_FILES['upload_csv']['tmp_name'])) {
        $count = process_uploaded_csv_file($_FILES['upload_csv']['tmp_name']);
        echo '<div class="updated"><p>File imported successfully! ' . intval($count) . ' entries added.</p></div>';
    }
}

function render_page()
{
?>
    <div class="wrap">
        <h1>Import data</h1>
        <form method="post" enctype="multipart/form-data">
            <input type="file" name="upload_csv" accept=".csv" required>
            <input type="submit" name="upload_data" class="button button-primary" value="Upload">
        </form>
        <p><strong>Format:</strong> QTY | SKU# | Article | Sub-Article | Date ent. | Cost | Real Cost | Retail | Retail [tax-in] | Supplier SKU | Supplier | Description</p>
    </div>
<?php
}


function process_uploaded_csv_file($file_path)
{
    global $wpdb;
    $inventory_units_table = $wpdb->prefix . 'mji_product_inventory_units';
    $models_table = $wpdb->prefix . 'mji_models';
    $brands_table = $wpdb->prefix . 'mji_brands';

    $handle = fopen($file_path, 'r');
    if ($handle) {
        $header = true;
        $count = 0;
        while (($row = fgetcsv($handle)) !== false) {

            if ($header) {
                $header = false;
                continue;
            }

            $sku = trim($row[0]);
            $supplier_sku = trim($row[1]);
            $description = trim($row[2]);
            $category = trim(str_replace("TP", "", $row[3]));
            $brand = trim(strtolower($row[4]));
            $cost   = preg_replace('/[^\d.-]/', '', trim($row[6]));
            $retail = preg_replace('/[^\d.-]/', '', trim($row[7]));
            $location = trim($row[8]);
            $child_category = trim(preg_replace('/\s*collection[s]?\b/i', '', $row[9]));

            if ($location === "odbcmetrotownNEW") {
                $location = "Metrotown";
            }

            $product_id = wc_get_product_id_by_sku($supplier_sku);
            if ($brand === 'watches') {
                $data = parse_watch_description($description);
            } else {
                $data = parse_jewelry_description($description);
            }
            $serial = trim($row[5]) ?: $data['serial'] ?? "";
            $variation_id = null;

            if (!$product_id) {

                $product = new WC_Product();
                $product->set_name(str_replace($category, '', $data['title']));
                $product->set_sku($supplier_sku);
                $product->set_regular_price($retail);
                $product->set_manage_stock(true);

                $attributes = [];
                $position = 0;
                foreach ($data as $key => $value) {
                    if (empty($value)) continue;

                    if ($key == "title" || $key == "serial") continue;


                    if (is_array($value)) {
                        $value = implode(', ', array_filter($value));
                        if (empty($value)) continue;
                    }

                    // Slug version of attribute (no spaces, lowercase)
                    $slug = sanitize_title($key);

                    $attr = new WC_Product_Attribute();
                    $attr->set_id(0); // 0 for custom (non-global) attribute
                    $attr->set_name(ucwords(str_replace('_', ' ', $key)));
                    $attr->set_options([$value]);
                    $attr->set_position($position);
                    $attr->set_visible(true);
                    $attr->set_variation(false);

                    $position++;
                    $attributes[$slug] = $attr;
                }

                if (!empty($attributes)) {
                    $product->set_attributes($attributes);
                }

                $main_term = get_term_by('name', $category, 'product_cat');
                // Adding categories
                if ($main_term) {
                    $category_id = $main_term->term_id;
                    $parent_id = $main_term->parent;
                    $sub_category = get_term_by('name', $child_category, 'product_cat');

                    if ($sub_category && $sub_category->parent == $category_id) {
                        $sub_category_id = $sub_category->term_id;
                    } else {
                        $new_sub = wp_insert_term(
                            $child_category,
                            'product_cat',
                            ['parent' => $category_id]
                        );
                        if (!is_wp_error($new_sub)) {
                            $sub_category_id = $new_sub['term_id'];
                        }
                    }

                    // Jewellery has two categories so need to add it
                    if ($brand && $brand !== "watches") {
                        $category_name = "Jewellery";
                        $jewellery_category = get_term_by('name', $category_name, 'product_cat');

                        if ($jewellery_category && !is_wp_error($jewellery_category)) {
                            if ($brand == 'Bangle') $brand = 'Bracelets';
                            if (
                                str_contains($brand, 'necklace') ||
                                str_contains($brand, 'pendant')
                            ) {
                                $brand = 'Pendants & Necklaces';
                            }
                            if (
                                str_contains($brand, 'earring')
                            ) {
                                $brand = 'Earrings';
                            }
                            $sub_category = get_term_by('name', $brand, 'product_cat');
                            $jewellery_category_id = $jewellery_category->term_id;

                            if ($sub_category && $sub_category->parent == $jewellery_category_id) {
                                $second_sub_category_id = $sub_category->term_id;
                            } else {
                                $new_sub = wp_insert_term(
                                    $brand,
                                    'product_cat',
                                    ['parent' => $jewellery_category_id]
                                );
                                if (!is_wp_error($new_sub)) {
                                    $second_sub_category_id = $new_sub['term_id'];
                                }
                            }
                        } else {
                            custom_log("The jewellery_category_id was not found for sku " . $supplier_sku);
                        }
                    }

                    $cat_ids = array_filter([
                        $parent_id,
                        $category_id,
                        $sub_category_id ?? null,
                        $jewellery_category_id ?? null,
                        $second_sub_category_id ?? null,
                    ], function ($id) {
                        return !empty($id) && is_numeric($id) && $id > 0;
                    });

                    $cat_ids = array_values(array_unique($cat_ids)); // remove duplicates & reindex
                    $product->set_category_ids($cat_ids);
                }

                $product_id = $product->save();

                if (!$product_id) {
                    custom_log("Wasn't able to create the product.");
                    continue;
                }
                update_post_meta($product_id, 'rank_math_primary_product_cat', $category_id);
                wp_update_post([
                    'ID' => $product_id,
                    'post_status' => 'private',
                ]);
                update_post_meta($product_id, '_cost_price', $cost);
                custom_log("Product created " . $product_id);
            } else {

                $product = wc_get_product($product_id);
                if ($product->is_type('variation')) {
                    $variation_id = $product_id;
                    $product_id = $product->get_parent_id();
                }

                update_post_meta($product_id, '_cost_price', $cost);
                $category_id = get_post_meta($product_id, 'rank_math_primary_product_cat', true);

                if (!$category_id) {
                    $term = get_term_by('name', $category, 'product_cat');
                    if ($term) {
                        $category_id = $term->term_id;
                        update_post_meta($product_id, 'rank_math_primary_product_cat', $category_id);
                    } else {
                        custom_log("Was not able to work on this product due to primary category missing for SKU " . $supplier_sku);
                        continue;
                    }
                }
                custom_log("Product updated");
            }

            $brand = get_term($category_id, 'product_cat')->name;
            $model = $supplier_sku;

            $model_id = get_brand_model_id($models_table, $model);
            $brand_id = get_brand_model_id($brands_table, $brand);

            $locations = mji_get_locations();

            $location_id = array_find($locations, function ($item) use ($location) {
                return $item->name == $location;
            })->id;

            $sql = $wpdb->prepare(
                "SELECT sku FROM $inventory_units_table WHERE sku = %s LIMIT 1",
                $sku
            );
            $existing_id = $wpdb->get_var($sql);

            if (!$existing_id) {
                $data_to_insert = [
                    'wc_product_id'      => $product_id,
                    'wc_product_variant_id'    => $variation_id,
                    'model_id'        => $model_id,
                    'brand_id'        => $brand_id,
                    'location_id'     => $location_id,
                    'sku'             => $sku,
                    'serial'          => $serial ? $serial : NULL,
                    'cost_price'      => floatval($cost),
                    'retail_price'    => floatval($retail),
                    'status'          => 'in_stock'
                ];
                try {
                    $result = $wpdb->insert(
                        $inventory_units_table,
                        $data_to_insert
                    );

                    if ($result === false) {
                        custom_log('Database error: ' . $wpdb->last_error);
                        custom_log('SKU: ' . $sku);
                        continue;
                    }
                    $count++;

                    custom_log("Created product units.");
                } catch (Exception $e) {
                    custom_log("Error " . $e->getMessage());
                    custom_log('SKU: ' . $sku);
                }
            } else {
                custom_log("Skipped due to duplicate entries: " . $sku);
            }
        }
        fclose($handle);
        return $count;
    }
}


/**
 * Parse product description into structured attributes
 *
 *
 * @param string $desc Full product description (with ||| separators)
 * @return array Parsed data ready for WooCommerce
 */
function parse_jewelry_description($desc)
{
    // Normalize and split
    $desc = mb_convert_encoding($desc, 'UTF-8', 'Windows-1252');
    $parts = array_map('trim', explode('|||', $desc));

    $data = [
        'title'      => '',
        'material'   => '',
        'weight'     => '',
        'gemstones'  => [],
        'model'   => '',
        'serial'     => '',
        'id_no'      => '',
        'size'       => '',
        'ceramic'    => '',
        'backing'    => '',
        'extra'      => [],
    ];

    // Title cleanup
    if (!empty($parts[0])) {
        $cleaned = preg_replace("/''[^']*''/", '', $parts[0]); // Remove internal quoted names
        $cleaned = preg_replace('/\s+/', ' ', trim($cleaned)); // Normalize spaces
        $data['title'] = $cleaned;
    }

    // Loop through remaining parts
    foreach (array_slice($parts, 1) as $part) {
        $part = trim(preg_replace('/^[•\-]+\s*/', '', $part)); // remove bullets or dashes

        // Material & weight
        if (stripos($part, '18K') !== false || stripos($part, 'gold') !== false) {
            $data['material'] = $part;
            if (preg_match('/\(([\d\.]+)g\)/i', $part, $m)) {
                $data['weight'] = $m[1] . 'g';
            }
        }

        // Gemstones (including sublines)
        elseif (stripos($part, 'Set with') !== false || preg_match('/\d+=\d*\.\d*ct/i', $part)) {
            $data['gemstones'][] = $part;
        }

        // Ceramic
        elseif (stripos($part, 'ceramic') !== false) {
            $data['ceramic'] = $part;
        }

        // Backings
        elseif (stripos($part, 'backing') !== false) {
            $data['backing'] = $part;
        }

        // ID or Serial
        elseif (preg_match('/ID\s*no\./i', $part)) {
            $data['id_no'] = trim(str_ireplace(['ID no.', 'ID No.', 'ID#'], '', $part));
        } elseif (stripos($part, 'Serial') !== false) {
            $data['serial'] = trim(str_ireplace(['Serial:', 'Serial No.'], '', $part));
        }

        // Style / Model
        elseif (preg_match('/Style/i', $part)) {
            $data['model'] = trim(str_ireplace(['Style:', 'Style', 'Style No.'], '', $part));
        } elseif (stripos($part, 'Model') !== false) {
            $data['model'] = trim(str_ireplace(['Model:', 'Model', 'Model number:', 'Model Number'], '', $part));
        }

        // Size or Length
        elseif (stripos($part, 'Size') !== false || stripos($part, 'length') !== false) {
            $data['size'] = trim(str_ireplace('Size', '', $part));
        }

        // Catch-all
        else {
            if (!empty($part)) {
                $data['extra'][] = $part;
            }
        }
    }

    return $data;
}

/**
 * Parse watch product description into structured attributes
 *
 * @param string $desc Full product description (with ||| separators)
 * @return array Parsed data ready for WooCommerce
 */
function parse_watch_description($desc)
{
    // Normalize and split
    $desc = mb_convert_encoding($desc, 'UTF-8', 'Windows-1252');
    $parts = array_map('trim', explode('|||', $desc));

    $data = [
        'title'           => '',
        'case'            => [],
        'dial'            => '',
        'functions'       => '',
        'crystal'         => '',
        'strap'           => '',
        'movement'        => '',
        'water_resistance' => '',
        'model'           => '',
        'old_model'           => '',
        'serial'          => '',
        'limited_edition' => '',
        'thickness'       => '',
        'extra'           => [],
    ];

    if (!empty($parts[0])) {
        $cleaned = preg_replace("/''[^']*''/", '', $parts[0]);
        $cleaned = preg_replace('/\s+/', ' ', trim($cleaned));
        $data['title'] = $cleaned;
    }

    // --- Parse each line ---
    foreach (array_slice($parts, 1) as $part) {
        $part = trim(preg_replace('/^[•\-]+\s*/', '', $part));


        // Strap / Bracelet
        if (preg_match('/(strap|bracelet|buckle|clasp)/i', $part)) {
            $data['strap'] = $part;
        }

        // Dial
        elseif (stripos($part, 'dial') !== false) {
            $data['dial'] = $part;
        }

        // Case & thickness
        elseif (preg_match('/\b(stainless steel|ceramic|bronze|titanium|case)\b/i', $part)) {
            $data['case'][] = $part;
            if (preg_match('/\(([\d\.]+)mm thickness\)/i', $part, $m)) {
                $data['thickness'] = $m[1] . 'mm';
            }
        }

        // Functions
        elseif (stripos($part, 'function') !== false) {
            $data['functions'] = $part;
        }

        // Crystal
        elseif (stripos($part, 'crystal') !== false) {
            $data['crystal'] = $part;
        }

        // Movement / Calibre
        elseif (preg_match('/(Calibre|Caliber|movement)/i', $part)) {
            $data['movement'] = $part;
        }

        // Water Resistance
        elseif (preg_match('/(Water[- ]?resistant|bar|m\/ft)/i', $part)) {
            $data['water_resistance'] = $part;
        }

        // old model
        elseif (preg_match('/\bOld\s+Model\b/i', $part)) {
            $data['old_model'] = trim(str_ireplace([
                'Old Model:',
                'Old Model No.',
                'Old Model number:',
                'Old Model No',
                'Old Model'
            ], '', $part));
        }
        // Model number
        elseif (preg_match('/Model/i', $part)) {
            $data['model'] = trim(str_ireplace(['Model:', 'Model No.', 'Model number:', 'Model No'], '', $part));
        }

        // Serial number
        elseif (preg_match('/Serial/i', $part)) {
            $data['serial'] = trim(str_ireplace(['Serial:', 'Serial No.', 'Serial number:', 'Serial No'], '', $part));
        }

        // Limited edition / Ltd No.
        elseif (preg_match('/(Limited|Ltd)/i', $part)) {
            $data['limited_edition'] = $part;
        }

        // Catch-all
        else {
            if (!empty($part)) {
                $data['extra'][] = $part;
            }
        }
    }

    return $data;
}
