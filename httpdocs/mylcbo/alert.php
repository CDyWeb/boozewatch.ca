<?php

function alerts($sandbox=false) {
    global $db;

    if ($sandbox) $d = date('Y-m-d', strtotime('- 3 day'));
    else $d = date('Y-m-d');

    foreach ($db->query('select * from `alert` where `active`=1')->fetchAll() as $alert) {
        echo "alerting: {$alert['customer']} {$alert['name']}\n";
        $settings = json_decode($alert['settings'], true);
        $categories = @$settings['category'];
        $products = @$settings['product'];
        $origins = @$settings['origin'];
        $varietals = @$settings['varietal'];
        $producers = @$settings['producer'];
        $is_vqa = @$settings['vqa'];
        $is_ocb = @$settings['ocb'];
        $is_sale = @$settings['sale'];

        $stores = @$alert['stores'];
        if (empty($stores)) continue;

        $all_stores = array();
        foreach ($db->query('select * from `store`')->fetchAll() as $s) {
            $all_stores[$s['id']] = $s['name'].' '.$s['city'];
        }

        $found = array();
        if (empty($categories)) {
            if ($is_sale) {
                $st=$db->prepare('select * from product join inventory on inventory.product_id=product.id where limited_time_offer_start>=? and store_id in (?) and product.is_dead=0 order by name, package');
                $st->execute(array($d, $stores));
                $found = $st->fetchAll();
            } else if ($is_vqa) {
                $st=$db->prepare('select * from product join inventory on inventory.product_id=product.id where is_vqa=1 and added_on>=? and store_id in (?) and product.is_dead=0 order by name, package');
                $st->execute(array($d, $stores));
                $found = $st->fetchAll();
            } else if ($is_ocb) {
                $st=$db->prepare('select * from product join inventory on inventory.product_id=product.id where is_ocb=1 and added_on>=? and store_id in (?) and product.is_dead=0 order by name, package');
                $st->execute(array($d, $stores));
                $found = $st->fetchAll();
            }
        } else {
            foreach ($categories as $category) {
                $e=explode(';', $category);
                echo " category: {$category}\n";
                if ($e[2]) {
                    $st = $db->prepare('select * from product join inventory on inventory.product_id=product.id where added_on>=? and store_id in (?) and product.is_dead=0 and primary_category=? and secondary_category=? and tertiary_category=? order by name, package');
                    $st->execute(array($d, $stores, $e[0], $e[1], $e[2]));
                } else if ($e[1]) {
                    $st = $db->prepare('select * from product join inventory on inventory.product_id=product.id where added_on>=? and store_id in (?) and product.is_dead=0 and primary_category=? and secondary_category=? order by name, package');
                    $st->execute(array($d, $stores, $e[0], $e[1]));
                } else {
                    $st = $db->prepare('select * from product join inventory on inventory.product_id=product.id where added_on>=? and store_id in (?) and product.is_dead=0 and primary_category=? order by name, package');
                    $st->execute(array($d, $stores, $e[0]));
                }
                foreach ($st->fetchAll() as $product) {
                    $key = $product['name'].','.$product['package'];
                    $found[$key] = $product;
                }
            }
            ksort($found);
        }
        $matching = array();
        foreach ($found as $product) {
            $matches = true;
            if (!empty($products) && !in_array($product['id'], $products)) $matches = false;
            if (!empty($origins) && !in_array($product['origin'], $origins)) $matches = false;
            if (!empty($producers) && !in_array($product['producer_name'], $producers)) $matches = false;
            if (!empty($varietals) && !in_array($product['varietal'], $varietals)) $matches = false;
            if ($is_vqa && !$product['is_vqa']) $matches = false;
            if ($is_ocb && !$product['is_ocb']) $matches = false;
            if ($is_sale && !$product['has_limited_time_offer']) $matches = false;
            if ($matches) $matching[] = $product;
        }
        if (empty($matching)) continue;
        echo " ".count($matching)." products match!\n";

        $msg = '';
        foreach ($matching as $product) {
            $price = $product['regular_price_in_cents']/100;
            $msg.=
<<<HTML
<p>
    <b>{$product['name']}</b>, {$product['package']}<br>
    {$product['origin']}, \${$price}<br>
    {$all_stores[$product['store_id']]}, quantity: {$product['quantity']}
</p>
HTML;
;
        }

        $user = $db->query('select * from ccms_customer where id='.$alert['customer'])->fetch();

        $msg =
<<<HTML
<p>Dear {$user['first_name']},</p>
<p>Your e-mail alert matched the following beer(s) today:</p>
{$msg}
<p>Check out new products in your local stores on the website.<br><a href="http://boozewatch.ca/new-booze-watch">http://boozewatch.ca/new-booze-watch</a></p>
<p>This is an automated e-mail message, you can change your e-mail preferences here:<br><a href="http://boozewatch.ca/account">http://boozewatch.ca/account</a></p>
HTML;


        $mail = new PHPMailer;
        $mail->setFrom('noreply@boozewatch.ca', 'BoozeWatch.ca');
        $mail->addAddress($user['email']);
        $mail->Subject = 'Alert: '.$alert['name'];
        $mail->msgHTML($msg);

        if (!$sandbox || ($user['id']==2)) {
            $mail->send();
        }
    }
}
