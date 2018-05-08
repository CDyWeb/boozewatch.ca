<?

interface CCMSManager {
	
	function getName();
	function create();
	function delete($id);
	function save($id,$data,&$err);

}

//end