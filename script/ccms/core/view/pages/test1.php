<?php

$model=new CCMSManagedModel("ProductManager");
$manager=$model->getDomainManager();



$id=getOneValue("select id from ccms_product limit 1");
$data=array(
	"no_stock_action"=>"notify",
	"name"=>"Some 'name'",
);

executeSql("insert into ccms_productnotify set product={$id}, email='test123@cyane.nl', lang='nl'");

$manager->on_restock($id,$data);

//end