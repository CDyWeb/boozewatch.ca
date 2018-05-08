<?

/** @var array $user */

$edit=array();
if (isset($_GET['edit'])) {
    $edit = getOneRow('select * from `alert` where customer='.$user['id'].' and id='.intval($_GET['edit']));
}

if (!empty($_POST)) {
    $post=[];
    foreach ($_POST as $key=>$value) {
        $post[$key] = trim(strip_tags($value));
    }
}
if (!empty($post['name'])) {

    $settings = array();
    $settings['category'] = isset($_POST['category']) && is_array($_POST['category']) ? $_POST['category'] : array();
    $settings['product'] = isset($_POST['product']) && is_array($_POST['product']) ? $_POST['product'] : array();
    $settings['origin'] = isset($_POST['origin']) && is_array($_POST['origin']) ? $_POST['origin'] : array();
    $settings['varietal'] = isset($_POST['varietal']) && is_array($_POST['varietal']) ? $_POST['varietal'] : array();
    $settings['producer'] = isset($_POST['producer']) && is_array($_POST['producer']) ? $_POST['producer'] : array();
    $settings['sale'] = isset($_POST['sale']) ? true : false;
    $settings['vqa'] = isset($_POST['vqa']) ? true : false;
    $settings['ocb'] = isset($_POST['ocb']) ? true : false;

    $db=db_connect();
    if (empty($edit)) {
        $st = $db->prepare('insert into `alert` set `active`=?, `customer`=?, `name`=?, `stores`=?, `settings`=?');
        $st->execute(array(
            $_POST['active'],
            $user['id'],
            $_POST['name'],
            implode(',',$_POST['stores']),
            json_encode($settings),
        ));
    } else {
        $st = $db->prepare('update `alert` set `active`=?, `name`=?, `stores`=?, `settings`=? where id=?');
        $st->execute(array(
            $_POST['active'],
            $_POST['name'],
            implode(',',$_POST['stores']),
            json_encode($settings),
            $edit['id']
        ));
    }

    $this->output->redirect($this->url());
    return;
}

if (!empty($edit)) {
    echo '<hr><h3>Edit email alert: '.$edit['name'].'</h3>';
    $stores = explode(',',$edit['stores']);
} else {
    echo '<hr><h3>Create a new email alert</h3>';
    $stores = explode(',',$customer['stores']);
}

?>
<form class="form form-horizontal" action="?<?= empty($edit)?'add':'edit='.$edit['id'] ?>" method="post">

    <div class="form-group">
        <label class="col-sm-3">Name:</label>
        <div class="col-sm-9">
            <input required="required" class="form-control" type="text" name="name" value="<?= @$edit['name']?$edit['name']:'Email alert' ?>">
        </div>
    </div>

    <div class="form-group">
        <label class="col-sm-3">Active:</label>
        <div class="col-sm-9">
            <select required="required" class="form-control" name="active">
                <option value="1" <?= empty($edit) || ($edit{'active'}==1)?'selected="selected"':'' ?>>Yes</option>
                <option value="0" <?= !empty($edit) && ($edit{'active'}==0)?'selected="selected"':'' ?>>No</option>
            </select>
        </div>
    </div>

    <div class="form-group">
        <label class="col-sm-3">Apply alert to these stores:</label>
        <div class="col-sm-9">
		<select class="select2" style="width:100%" multiple="multiple" name="stores[]">
			<?php foreach(getTableArray('select * from store order by city, name') as $line) { ?>
			<option <?= in_array($line['id'],$stores)?'selected="selected"':'' ?> value="<?= $line['id'] ?>"><?= $line['city'] ?>, <?= $line['name'] ?></option>
			<?php } ?>
		</select>
        </div>
    </div>



    <hr>
    <p><b>Alert me when products are (re)stocked matching the following conditions...</b></p>

    <div class="form-group">
        <label class="col-sm-3">Product categories (hold down CTRL key to select multiple rows):</label>
        <div class="col-sm-9">
            <select class="form-control" id="select-category" name="category[]" multiple="multiple" onchange="categorySelected()" style="height:300px;">
                <? foreach (getTableArray("SELECT distinct concat(`primary`,';',`secondary`,';',`tertiary`) as n FROM `store_count` order by n") as $line) {
                    $e=explode(';',trim($line['n'],';'));
                    ?>
                    <option value="<?= $line['n'] ?>" <?= @$edit['category']==$line['n']?'selected="selected"':'' ?>><?= implode(' > ',$e) ?></option>
                <? } ?>
            </select>
        </div>
    </div>

    <div class="form-group" id="div-product" style="display:none">
        <label class="col-sm-3">Specific products (optional):</label>
        <div class="col-sm-9">
            <select class="form-control" id="select-product" name="product[]" multiple="mutiple" style="height:300px;">
                <option></option>
            </select>
        </div>
    </div>

    <div class="form-group" id="div-origin" style="display:none">
        <label class="col-sm-3">Origins (optional):</label>
        <div class="col-sm-9">
            <select class="form-control" id="select-origin" name="origin[]" multiple="mutiple" style="height:300px;">
                <option></option>
            </select>
        </div>
    </div>

    <div class="form-group" id="div-producer" style="display:none">
        <label class="col-sm-3">Producers (optional):</label>
        <div class="col-sm-9">
            <select class="form-control" id="select-producer" name="producer[]" multiple="mutiple" style="height:300px;">
                <option></option>
            </select>
        </div>
    </div>

    <div class="form-group" id="div-varietal" style="display:none">
        <label class="col-sm-3">Varietals (optional):</label>
        <div class="col-sm-9">
            <select class="form-control" id="select-varietal" name="varietal[]" multiple="mutiple" style="height:300px;">
                <option></option>
            </select>
        </div>
    </div>

    <div class="form-group" id="div-sale">
        <label class="col-sm-3">On sale:</label>
        <div class="col-sm-9">
            <input id="checkbox-sale" type="checkbox" name="sale">
        </div>
    </div>

    <div class="form-group" id="div-vqa">
        <label class="col-sm-3">Is VQA:</label>
        <div class="col-sm-9">
            <input id="checkbox-vqa" type="checkbox" name="vqa">
        </div>
    </div>

    <div class="form-group" id="div-ocb">
        <label class="col-sm-3">Is Ontario Craft Beer:</label>
        <div class="col-sm-9">
            <input id="checkbox-ocb" type="checkbox" name="ocb">
        </div>
    </div>

    <div class="form-group">
        <label class="col-sm-3"></label>
        <div class="col-sm-9">
            <button type="submit" class="btn btn-primary">Save</button>
            &nbsp;&nbsp;&nbsp;
            <a href="<?= $this->url() ?>">cancel</a>
        </div>
        <? if (!empty($edit)) { ?>
            <p class="text-right">
                <a href="?delete=<?= $edit['id'] ?>" class="" onclick="return window.confirm('Confirm to delete this email alert');">delete this email alert</a>
            </p>
        <? } ?>
    </div>

</form>

<script>

    $('.select2').select2();

    <? if (!empty($edit)) {
        $settings = json_decode($edit['settings'], true);
    ?>
    $(document).ready(function() {
        $('#select-category').val(<?= json_encode($settings['category']) ?>);
        categorySelected(function() {
            $('#select-product').val(<?= json_encode($settings['product']) ?>);
            $('#select-origin').val(<?= json_encode($settings['origin']) ?>);
            $('#select-varietal').val(<?= json_encode($settings['varietal']) ?>);
            $('#select-producer').val(<?= json_encode($settings['producer']) ?>);
        });

        <?php if ($settings['sale']) { ?> $('#checkbox-sale').attr('checked','checked'); <? } ?>
        <?php if ($settings['vqa']) { ?> $('#checkbox-vqa').attr('checked','checked'); <? } ?>
        <?php if ($settings['ocb']) { ?> $('#checkbox-ocb').attr('checked','checked'); <? } ?>
    });

    <? } ?>


    function categorySelected(cb) {
        var v=$('#select-category').val();
        if (v) {
            $('#select-product').html('<option>loading...</option>');
            $('#select-origin').html('<option>loading...</option>');
            $('#select-varietal').html('<option>loading...</option>');
            $('#select-producer').html('<option>loading...</option>');
            $('#div-product').show();
            $('#div-origin').show();
            $('#div-varietal').show();
            $('#div-producer').show();
            $.post('?',{categorySelected:v}, function(response) {
                $('#select-product').html(response.products);
                $('#select-origin').html(response.origins);
                $('#select-varietal').html(response.varietals);
                $('#select-producer').html(response.producers);
                <? if (!empty($settings)) { ?>
                $('#select-product').val(<?= json_encode($settings['product']) ?>);
                $('#select-origin').val(<?= json_encode($settings['origin']) ?>);
                $('#select-varietal').val(<?= json_encode($settings['varietal']) ?>);
                $('#select-producer').val(<?= json_encode($settings['producer']) ?>);
                <? } ?>
                if (cb) cb();
            }, 'json');
        } else {
            $('#div-product').hide();
            $('#div-origin').hide();
            $('#div-varietal').hide();
            $('#div-producer').hide();
        }
    }

</script>






