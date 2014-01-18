<?php
require_once('bootstrap.php');

//phpinfo();

// echo "<pre>";
// print_r($CFG);
// echo "</pre>";


try{
	$post_sorter = new PostSorter($CFG->datadir . "/posts.csv", "json", "full");
	$post_sorter->generateReports();
	$post_sorter->getTopPostsOutput();
	$post_sorter->getOtherPostsOutput();
	$post_sorter->getDailyBestOutput();
}catch(Exception $error){
	echo "Script failed: " . $e->getMessage();
}