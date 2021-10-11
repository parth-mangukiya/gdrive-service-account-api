<?php

define('DRIVE_TYPE', 'shared'); // there are 2 type of drive 'own' , 'shared'  Select accordingly your needs
define('DRIVE_ID', '0AKmdVV3sQwP-Uk9PVA'); // this is The Shared drive id where magic happen
define('FOLDER_ID', '1yriwrn4BWYhSxTP-IVf7nof_nO-XG1bt'); // this is the folder id in which all megic happen

require_once './action.php';
$googleDrive = new googleDrive;

$folderPath = 'test child'; // this is the folder path in which you wants to upoad

# get list of files
$list_of_files = $googleDrive->getlist($folderPath); // return Folder and flies list


# create a folder
$googleDrive->createFolder($folderPath);


// #  upload file to google drive D
$filePath = array(
    'D:/xampp/htdocs/drive2/test-img.png',
    'D:/xampp/htdocs/drive2/img.png',
    'D:/xampp/htdocs/drive2/download.jpg',
    'D:/xampp/htdocs/drive2/dummy.pdf',
); // file path that you wants to upload in google drive please use in array
$upload = $googleDrive->uploadFile($folderPath, $filePath);

# download the google drive
$download = $googleDrive->download($folderPath);
