<?php
/*
UploadiFive
Copyright (c) 2012 Reactive Apps, Ronnie Garcia
*/

// Define a destination
$targetFolder = 'E:/wamp/www/v3.1.8/statics/uploads/shaidan/'; // Relative to the root and should match the upload folder in the uploader script
//file_put_contents('D:/wamp/www/yungou/statics/uploads/shaidan/sc.log', "targetFolder:".$targetFolder."\n",FILE_APPEND);

if (file_exists($_SERVER['DOCUMENT_ROOT'] . $targetFolder . '/' . $_POST['file_upload'])) {
	echo 1;
	//file_put_contents('D:/wamp/www/yungou/statics/uploads/shaidan/sc.log', "111:\n",FILE_APPEND);
} else {
	echo 0;
	//file_put_contents('D:/wamp/www/yungou/statics/uploads/shaidan/sc.log', "0000:\n",FILE_APPEND);
}
?>