<?php

function curl_http_get($url) {
    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, $url);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
    curl_setopt( $ch, CURLOPT_USERAGENT, 'Mozilla/5.0 (Windows NT 6.1; WOW64; rv:45.0) Gecko/20100101 Firefox/45.0');
#    curl_setopt($ch, CURLOPT_VERBOSE, true);
    $headers = array(
        'Accept: text/html,application/xhtml+xml,application/xml;q=0.9,*/*;q=0.8',
        'Accept-Language: en-GB,en;q=0.5',
        'Connection: keep-alive',
    );
    curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);

    $server_output = curl_exec ($ch);
    curl_close ($ch);

    return $server_output;
}

function rate_to_stars($r) {
    $result='';
    if ($r===null) return $result;
    $result.='<span title="'.(round($r)/10).'">';
    for ($i=10;$i<=90;$i+=20) {
        if ($r>=$i) $result.='<span class="glyphicon glyphicon-star"></span>'; else $result.='<span class="glyphicon glyphicon-star-empty"></span>';
    }
    $result.='</span>';
    return $result;
}


function rate_product() {
    $product = getOneRow('select * from product where id=' . intval($_GET['rate']));
    if ($product && $product['primary_category'] == 'Beer') {
        rate_beer($product);
    }
    if ($product && $product['primary_category'] == 'Wine') {
        rate_wine($product);
    }
}

function rate_beer($product, $rerate=false) {
    if (!$rerate) {
        $ratebeer = getOneRow('select rating from ratebeer_beer where product_id=' . $product['id']);
        if ($ratebeer) {
            echo rate_to_stars($ratebeer['rating']);
            return;
        }
    }

    $db=db_connect();
    $st = $db->prepare('select * from ratebeer_search where q=?');
    $st->execute(array($product['name']));
    $search = $st->fetch();
    $beer_id = false;
    if ($search) {
        $result = $search['result'];
        $beer_id = $search['beer_id'];
    } else {
        $result = shell_exec('/home/boozewatch/.rbenv/shims/ruby /ratebeer/bin/search "'.$product['name'].'" 2>&1');
        $st = $db->prepare('replace into ratebeer_search set q=?, result=?');
        $st->execute(array($product['name'], $result));
    }
    if (!$beer_id) {
        $json = json_decode($result, true);
        if (isset($json['beers'][0]['id']) && (count($json['beers']) == 1)) {
            $beer_id = $json['beers'][0]['id'];
        } else if (count($json['beers']) > 1) {
            foreach ($json['beers'] as $json_beer) {
                if ($json_beer['name'] == $product['name']) {
                    $beer_id = $json_beer['id'];
                }
            }
        }
    }
    if ($beer_id) {
        if ($search && !$search['beer_id']) {
            $st = $db->prepare('update ratebeer_search set beer_id=? where q=?');
            $st->execute(array($beer_id, $product['name']));
        }
        $result = shell_exec('/home/boozewatch/.rbenv/shims/ruby /ratebeer/bin/beer "'.$beer_id.'" 2>&1');
        $json = json_decode($result,true);
        $st = $db->prepare('replace into ratebeer_beer set product_id=?, result=?, rating=?');
        $st->execute(array($product['id'], $result, @$json['rating'][':overall']));
        echo rate_to_stars(@$json['rating'][':overall']);
    } else {
        $st = $db->prepare('replace into ratebeer_beer set product_id=?, result=?, rating=?');
        $st->execute(array($product['id'], null, null));
        echo rate_to_stars(null);
    }
}

function rate_wine($product) {
    $ratewine = getOneRow('select rating from ratewine_wine where product_id='.$product['id']);
    if ($ratewine) {
        echo rate_to_stars($ratewine['rating']);
    } else {
        $db=db_connect();
        $st = $db->prepare('select * from ratewine_search where q=?');
        $st->execute(array($product['id']));
        $search = $st->fetch();
        $wine_uri = false;
        if ($search) {
            $result = $search['result'];
            $wine_uri = $search['wine_uri'];
        } else {
            #--
            $result = '';
            $html = curl_http_get('http://www.nataliemaclean.com/winepicks/search/?dosearch=set&jmp=set&text='.$product['id']);
            libxml_use_internal_errors(true);
            $doc = new DOMDocument();
            $doc->loadHTML($html);
            $xpath = new DOMXpath($doc);
            $elements = $xpath->query("//div[@id='wines']/div[@class='row ']");
            if ($elements->length>0) {
                $element = $elements->item(0);
                $result = $doc->saveHTML($element);
                $el_id = $element->getAttribute('id');
                $e = explode('_', $el_id, 2);
                if ((count($e)==2) && (is_numeric($e[1]))) {
                    $remote_id = $e[1];
                    $ahrefs = $xpath->query("//div[@id='wines']/div[@class='row ']/div/a[@id='wurlfor_{$remote_id}']");
                    if ($ahrefs->length==1) {
                        $wine_uri = $ahrefs->item(0)->getAttribute('href');
                    }
                }
            }

            #--
            $st = $db->prepare('replace into ratewine_search set q=?, result=?, wine_uri=?');
            $st->execute(array($product['name'], $result, $wine_uri));
        }

        if ($wine_uri) {
            #--
            $result = '';
            $rating = null;
            $html = curl_http_get('http://www.nataliemaclean.com'.$wine_uri);
            libxml_use_internal_errors(true);
            $doc = new DOMDocument();
            $doc->loadHTML($html);
            $xpath = new DOMXpath($doc);
            $elements = $xpath->query("//span[@itemprop='ratingValue']");
            if ($elements->length==1) {
                $rating = $elements->item(0)->nodeValue;
                $result = $doc->saveHTML($elements->item(0));
            }
            #--
            $st = $db->prepare('replace into ratewine_wine set product_id=?, result=?, rating=?');
            $st->execute(array($product['id'], $result, $rating));
            echo rate_to_stars($rating);
        } else {
            $st = $db->prepare('replace into ratewine_wine set product_id=?, result=?, rating=?');
            $st->execute(array($product['id'], null, null));
            echo rate_to_stars(null);
        }
    }
}
