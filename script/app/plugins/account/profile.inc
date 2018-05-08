<?php
/*
------------------------------------------------------------

	CyaneCMS

$LastChangedRevision: 103 $
$LastChangedDate: 2009-05-22 22:51:00 +0200 (vr, 22 mei 2009) $
$LastChangedBy: erwin $

 Copyright (c) 2006-2009 Cyane Dynamic Web Solutions
 IT IS NOT ALLOWED TO USE OR MODIFY ANYTHING OF THIS SITE,
 WITHOUT THE PERMISION OF THE AUTHOR.

 Info? Mail to ccms@cyane.nl
------------------------------------------------------------
*/

if (!isset($_SESSION["customer"]["id"])) die("--!-");

$user=$_SESSION['customer'];
if (empty($user)) {
    $this->output->redirect($this->url());
    return;
}

if (isset($_POST['categorySelected']) && is_array($_POST['categorySelected'])) {
    $db=db_connect();
    $products=array();
    $origins=array();
    $varietals=array();
    $producers=array();
    foreach ($_POST['categorySelected'] as $c) {
        $e = explode(';', $c);

        if ($e[2]) {
            $st = $db->prepare('select * from product where is_dead=0 and primary_category=? and secondary_category=? and tertiary_category=? order by name, package');
            $st->execute(array($e[0], $e[1], $e[2]));
        } else if ($e[1]) {
            $st = $db->prepare('select * from product where is_dead=0 and primary_category=? and secondary_category=? order by name, package');
            $st->execute(array($e[0], $e[1]));
        } else {
            $st = $db->prepare('select * from product where is_dead=0 and primary_category=? order by name, package');
            $st->execute(array($e[0]));
        }
        foreach ($st->fetchAll() as $line) {
            $products[$line['id']] = '<option value="' . $line['id'] . '">' . $line['name'] . ', ' . $line['package'] . '</option>';
        }

        if ($e[2]) {
            $st = $db->prepare('select distinct origin from product where primary_category=? and secondary_category=? and tertiary_category=? order by origin');
            $st->execute(array($e[0], $e[1], $e[2]));
        } else if ($e[1]) {
            $st = $db->prepare('select distinct origin from product where primary_category=? and secondary_category=? order by origin');
            $st->execute(array($e[0], $e[1]));
        } else {
            $st = $db->prepare('select distinct origin from product where primary_category=? order by origin');
            $st->execute(array($e[0]));
        }
        foreach ($st->fetchAll() as $line) {
            $o = trim(str_replace(', Region Not Specified','',$line['origin']),', ');
            $origins[$o] = '<option value="'.$o.'">'.$o.'</option>';
        }
        ksort($origins);

        if ($e[2]) {
            $st = $db->prepare('select distinct varietal from product where primary_category=? and secondary_category=? and tertiary_category=? order by varietal');
            $st->execute(array($e[0], $e[1], $e[2]));
        } else if ($e[1]) {
            $st = $db->prepare('select distinct varietal from product where primary_category=? and secondary_category=? order by varietal');
            $st->execute(array($e[0], $e[1]));
        } else {
            $st = $db->prepare('select distinct varietal from product where primary_category=? order by varietal');
            $st->execute(array($e[0]));
        }
        foreach ($st->fetchAll() as $line) {
            if ($line['varietal']) $varietals[$line['varietal']] = '<option value="'.$line['varietal'].'">'.$line['varietal'].'</option>';
        }

        if ($e[2]) {
            $st = $db->prepare('select distinct producer_name from product where primary_category=? and secondary_category=? and tertiary_category=? order by producer_name');
            $st->execute(array($e[0], $e[1], $e[2]));
        } else if ($e[1]) {
            $st = $db->prepare('select distinct producer_name from product where primary_category=? and secondary_category=? order by producer_name');
            $st->execute(array($e[0], $e[1]));
        } else {
            $st = $db->prepare('select distinct producer_name from product where primary_category=? order by producer_name');
            $st->execute(array($e[0]));
        }
        foreach ($st->fetchAll() as $line) {
            if ($line['producer_name']) $producers[$line['producer_name']] = '<option value="'.$line['producer_name'].'">'.$line['producer_name'].'</option>';
        }

    }
    foreach ($products as $s) $result['products'] .= $s;
    foreach ($origins as $s) $result['origins'] .= $s;
    foreach ($varietals as $s) $result['varietals'] .= $s;
    foreach ($producers as $s) $result['producers'] .= $s;
    echo json_encode($result);
    return;
}

$alerts = getTableArray('select * from alert where customer='.$user['id']);

echo $this->page->text;

?>

<div class="col-sm-8">
    <h2>
        My e-mail alerts
  <span class="pull-right" style="font-size:14px;">
    <a href="?add" class="btn btn-primary">Add</a>
  </span>
    </h2>

    <?
    if (isset($_GET['add']) || isset($_GET['edit'])) {
        require 'edit-alert.inc';
    } else {

        if (isset($_GET['delete'])) {
            executeSql('delete from alert where customer='.$user['id'].' and id='.intval($_GET['delete']));
            $this->output->redirect($this->url());
            return;
        }

        if (empty($alerts)) {
            echo '<p>You have currently no email alerts configured.</p>';
            return;
        }

        ?>

        <table class="table table-striped">
            <colgroup>
                <col width="100">
                <col width="">
                <col width="50">
            </colgroup>
            <thead>
            <tr>
                <th>Name</th><th>Description</th><th>Active</th>
            </tr>
            </thead>
            <tbody>
            <? foreach ($alerts as $line) {
                $description='';
                ?>
                <tr>
                    <td><a href="?edit=<?= $line['id'] ?>"><?= $line['name'] ?></a></td>
                    <td><?= $description ?></td>
                    <td><?= $line['active']?'<span class="text-success">YES</span>':'<span class="text-danger">NO</span>' ?></td>
                </tr>
            <? } ?>
            </tbody>
        </table>

    <?


    }
    ?>


</div>
