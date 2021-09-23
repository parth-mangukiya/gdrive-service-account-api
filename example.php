<?php

define('DRIVE_TYPE', 'own'); // there are 2 type of drive 'own' , 'shared'  Select accordingly your needs
define('DRIVE_ID', '0AKmdVV3sQwP-Uk9PVA'); // this is The Shared drive id where magic happen
define('FOLDER_ID', '12JEqKt5txxBmnX14FSo4q6_JSaHv4UA9'); // this is the folder id in which all megic happen

require_once './action.php';
$googleDrive = new googleDrive;
$folderPath = 'maindemo'; // this is the folder path in which you wants to upoad

// # get list of files
// $list_of_files = $googleDrive->getlist($folderPath); // return Folder and flies list

# create a folder
// $googleDrive->createFolders($folderPath);


#  upload file to google drive D
// $filePath = array(
//     'C:/Users/Freeware Sys/Downloads/images.png'
// ); // file path that you wants to upload in google drive please use in array
// $googleDrive->uploadFile($folderPath, $filePath);

# download the google drive
// $download = $googleDrive->download($folderPath);
