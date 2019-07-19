<?php


/**
 * The core plugin class.
 *
 * This is used to define admin-specific hooks and 
 *
 * Also maintains the unique identifier of this plugin as well as the current
 * version of the plugin.
 *
 * @since      1.0.0
 * @package    Woocommerce_onsale_page
 * @subpackage Woocommerce_onsale_page/classes
 * @author     Phạm Bá Trung Thành <phambatrungthanh@gmail.com>
 */

class Woocommerce_Order_Information {
    protected $order;
    protected $decimal_precision;
    protected $order_id;
    public function __construct(int $order_id, int $decimal_precision = 2)
    {
        $this->order_id = $order_id;
        $this->decimal_precision = $decimal_precision;
    }
    public function get(): array
    {
        $this->order = $this->getData();
        return $this->order;
    }
    private function getData(): array
    {
        if (is_wp_error($this->order_id)) {
            return [];
        }

        // Get the decimal precession
        $dp = $this->decimal_precision;
        $order = wc_get_order($this->order_id); //getting order Object
        if ($order === false) {
            return [];
        }

       $order_data = [
            'id' => $order->get_id(),
            'order_number' => $order->get_order_number(),
            'created_at' => $order->get_date_created()->date('Y-m-d H:i:s'),
            'updated_at' => $order->get_date_modified()->date('Y-m-d H:i:s'),
            'completed_at' => !empty($order->get_date_completed()) ? $order->get_date_completed()->date('Y-m-d H:i:s') : '',
            'status' => $order->get_status(),
            'currency' => $order->get_currency(),
            'total' => wc_format_decimal($order->get_total(), $dp),
            'subtotal' => wc_format_decimal($order->get_subtotal(), $dp),
            'total_line_items_quantity' => $order->get_item_count(),
            'total_tax' => wc_format_decimal($order->get_total_tax(), $dp),
            'total_shipping' => wc_format_decimal($order->get_total_shipping(), $dp),
            'cart_tax' => wc_format_decimal($order->get_cart_tax(), $dp),
            'shipping_tax' => wc_format_decimal($order->get_shipping_tax(), $dp),
            'total_discount' => wc_format_decimal($order->get_total_discount(), $dp),
            'shipping_methods' => $order->get_shipping_method(),
            'order_key' => $order->get_order_key(),
            'payment_details' => [
                'method_id' => $order->get_payment_method(),
                'method_title' => $order->get_payment_method_title(),
                'paid_at' => !empty($order->get_date_paid()) ? $order->get_date_paid()->date('Y-m-d H:i:s') : '',
            ],
            'billing_address' => [
                'first_name' => $order->get_billing_first_name(),
                'last_name' => $order->get_billing_last_name(),
                'company' => $order->get_billing_company(),
                'address_1' => $order->get_billing_address_1(),
                'address_2' => $order->get_billing_address_2(),
                'city' => $order->get_billing_city(),
                'state' => $order->get_billing_state(),
                'formated_state' => WC()->countries->states[$order->get_billing_country()][$order->get_billing_state()], //human readable formated state name
                'postcode' => $order->get_billing_postcode(),
                'country' => $order->get_billing_country(),
                'formated_country' => WC()->countries->countries[$order->get_billing_country()], //human readable formated country name
                'email' => $order->get_billing_email(),
                'phone' => $order->get_billing_phone()
            ],
            'shipping_address' => [
                'first_name' => $order->get_shipping_first_name(),
                'last_name' => $order->get_shipping_last_name(),
                'company' => $order->get_shipping_company(),
                'address_1' => $order->get_shipping_address_1(),
                'address_2' => $order->get_shipping_address_2(),
                'city' => $order->get_shipping_city(),
                'state' => $order->get_shipping_state(),
                'formated_state' => WC()->countries->states[$order->get_shipping_country()][$order->get_shipping_state()], //human readable formated state name
                'postcode' => $order->get_shipping_postcode(),
                'country' => $order->get_shipping_country(),
                'formated_country' => WC()->countries->countries[$order->get_shipping_country()] //human readable formated country name
            ],
            'note' => $order->get_customer_note(),
            'customer_ip' => $order->get_customer_ip_address(),
            'customer_user_agent' => $order->get_customer_user_agent(),
            'customer_id' => $order->get_user_id(),
            'view_order_url' => $order->get_view_order_url(),
            'line_items' => array(),
            'shipping_lines' => array(),
            'tax_lines' => array(),
            'fee_lines' => array(),
            'coupon_lines' => array(),
       ];
        //getting all line items
        foreach ($order->get_items() as $item_id => $item) {
            $product = $item->get_product();
            $product_id = null;
            $product_sku = null;
            // Check if the product exists.
            if (is_object($product)) {
                $product_id = $product->get_id();
                $product_sku = $product->get_sku();
            }
            $order_data['line_items'][] = [
                'id' => $item_id,
                'subtotal' => wc_format_decimal($order->get_line_subtotal($item, false, false), $dp),
                'subtotal_tax' => wc_format_decimal($item['line_subtotal_tax'], $dp),
                'total' => wc_format_decimal($order->get_line_total($item, false, false), $dp),
                'total_tax' => wc_format_decimal($item['line_tax'], $dp),
                'price' => wc_format_decimal($order->get_item_total($item, false, false), $dp),
                'quantity' => wc_stock_amount($item['qty']),
                'tax_class' => (!empty($item['tax_class']) ) ? $item['tax_class'] : null,
                'name' => $item['name'],
                'product_id' => (!empty($item->get_variation_id()) && ('product_variation' === $product->post_type )) ? $product->get_parent_id() : $product_id,
                'variation_id' => (!empty($item->get_variation_id()) && ('product_variation' === $product->post_type )) ? $product_id : 0,
                'product_url' => get_permalink($product_id),
                'product_thumbnail_url' => wp_get_attachment_image_src(get_post_thumbnail_id($product_id), 'thumbnail', TRUE)[0],
                'sku' => $product_sku,
                'meta' => wc_display_item_meta($item, ['echo' => false])
            ];
        }
        //getting shipping
        foreach ($order->get_shipping_methods() as $shipping_item_id => $shipping_item) {
            $order_data['shipping_lines'][] = [
                'id' => $shipping_item_id,
                'method_id' => $shipping_item['method_id'],
                'method_title' => $shipping_item['name'],
                'total' => wc_format_decimal($shipping_item['cost'], $dp),
            ];
        }
        //getting taxes
        foreach ($order->get_tax_totals() as $tax_code => $tax) {
            $order_data['tax_lines'][] = [
                'id' => $tax->id,
                'rate_id' => $tax->rate_id,
                'code' => $tax_code,
                'title' => $tax->label,
                'total' => wc_format_decimal($tax->amount, $dp),
                'compound' => (bool) $tax->is_compound,
            ];
        }
        //getting fees
        foreach ($order->get_fees() as $fee_item_id => $fee_item) {
            $order_data['fee_lines'][] = [
                'id' => $fee_item_id,
                'title' => $fee_item['name'],
                'tax_class' => (!empty($fee_item['tax_class']) ) ? $fee_item['tax_class'] : null,
                'total' => wc_format_decimal($order->get_line_total($fee_item), $dp),
                'total_tax' => wc_format_decimal($order->get_line_tax($fee_item), $dp),
            ];
        }
        //getting coupons
        foreach ($order->get_items('coupon') as $coupon_item_id => $coupon_item) {
            $order_data['coupon_lines'][] = [
                'id' => $coupon_item_id,
                'code' => $coupon_item['name'],
                'amount' => wc_format_decimal($coupon_item['discount_amount'], $dp),
            ];
        }
        
        return $order_data;
    }
}