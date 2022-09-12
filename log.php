<?php
function log($text){
	$file = $_SERVER['DOCUMENT_ROOT'] . '/local/log/log_block_BP.html';
	file_put_contents($file, json_encode($text));
}
?>
