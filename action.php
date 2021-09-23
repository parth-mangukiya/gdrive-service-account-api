<?php

use Google\Service\CloudResourceManager\Folder;

require_once './vendor/autoload.php';

class googleDrive
{
    private $client = '';
    private $service = ''; // service object 
    private $folderId = FOLDER_ID;
    private $driveType = DRIVE_TYPE;
    public function __construct()
    {
        if (defined('DRIVE_ID')) {
            $this->driveId = DRIVE_ID;
        }

        $credentialsFile =  __DIR__ . '/service-account.json';
        if (!file_exists($credentialsFile)) {
            throw new RuntimeException('Service account credentials Not Found!');
        }

        putenv('GOOGLE_APPLICATION_CREDENTIALS=' . __DIR__ . '/service-account.json');

        try {
            // Create and configure a new client object.        
            $this->client = new Google_Client();
            $this->client->useApplicationDefaultCredentials();
            $this->client->addScope(
                "https://www.googleapis.com/auth/drive",
                "https://www.googleapis.com/auth/drive.file",
                "https://www.googleapis.com/auth/drive.appdata",
                "https://www.googleapis.com/auth/drive.scripts",
                "https://www.googleapis.com/auth/drive.metadata"
            );
        } catch (Exception $e) {
            print "An error occurred: " . $e->getMessage();
        }

        $this->service = new Google_Service_Drive($this->client);
    }

    # this function used to get list of files and folders
    public function getlist($folderPath)
    {
        $this->folderId = FOLDER_ID;
        $folder = explode('/', $folderPath);
        $folder = array_filter($folder, function ($var) {
            return ($var !== NULL && $var !== FALSE && $var !== "");
        });

        # passed 0 to not create a folder if folder not Found
        # passed 1 to create a folder if folder not Found
        $listfolderName = $this->lastChildId($folder, 0); // call function to set magic folder id

        if ($listfolderName != false) {

            if ($this->driveType == 'shared') {
                $optParams = array(
                    'corpora' => 'drive',
                    'driveId' => $this->driveId,
                    'includeItemsFromAllDrives' => true,
                    'pageSize' => 100,
                    'supportsAllDrives' => true,
                    'fields' => 'nextPageToken, files(id, name)',
                    'q' => "'" . $this->folderId . "' in parents"
                );
            } else {
                $optParams = array(
                    'pageSize' => 100,
                    'fields' => "nextPageToken, files(contentHints/thumbnail,fileExtension,iconLink,id,name,size,thumbnailLink,webContentLink,webViewLink,mimeType,parents)",
                    'q' => "'" . $this->folderId . "' in parents"
                );
            }

            $last_Child_folder = $this->service->files->listFiles($optParams); // get list of all child Items 

            if (!empty($last_Child_folder['files'])) {
                $itemList = [];

                foreach ($last_Child_folder['files'] as $key => $item) {

                    $type = pathinfo(basename($item->name), PATHINFO_EXTENSION); // file extention

                    if ($type == '' || empty($type)) {
                        # this is the folder list so add to folder array
                        $get_sub_folder = $item->id;
                        $itemList['folder'][$key]['name'] = $item->name;
                        $itemList['folder'][$key]['Id'] =  $get_sub_folder;
                    } else {
                        # this is the Files list so add to Files array

                        $get_sub_folder = $item->id;
                        $itemList['files'][$key]['name'] = $item->name;
                        $itemList['files'][$key]['id'] =  $get_sub_folder;
                        $itemList['files'][$key]['download_link'] = 'https://drive.google.com/uc?export=download&id=' . $get_sub_folder;
                        $itemList['files'][$key]['preview_link'] = 'https://drive.google.com/file/d/' . $get_sub_folder . '/view';
                    }
                }

                $itemList = array_map('array_values', $itemList);

                return $itemList;
            } else {
                return 'No files found';
            }
        } else {
            return 'No such directory exists';
        }
    }


    # this function create a folder
    public function createFolders($folderPath)
    {
        $this->folderId = FOLDER_ID;
        $folder = explode('/', $folderPath);
        $folder = array_filter($folder, function ($var) {
            return ($var !== NULL && $var !== FALSE && $var !== "");
        });

        if (!empty($folder)) {
            $listfolderName = $this->lastChildId($folder); // call function to set magic folder id
            return 'Folder created Sucessfully';
        } else {
            return 'Required Folder path';
        }
    }

    public function uploadFile($folderPath, $filePaths)
    {
        $this->folderId = FOLDER_ID;
        $folder = explode('/', $folderPath);
        $folder = array_filter($folder, function ($var) {
            return ($var !== NULL && $var !== FALSE && $var !== "");
        });

        $uploadFolderName = $this->lastChildId($folder); // call function to set magic folder id

        if (!empty($filePaths)) {
            $return_arr = [];
            if (!is_array($filePaths)) {
                return 'Only array allowed';
            }
            foreach ($filePaths as $filePath) {
                if (!file_exists($filePath)) {
                    continue;
                }

                $fileName = basename($filePath); // remove extention from file name
                $mine_type = mime_content_type($filePath);

                if ($this->driveType == 'shared') {
                    $file = new Google_Service_Drive_DriveFile(array(
                        'name' => trim($fileName),
                        'mimeType' => $mine_type,
                        'driveId' => $this->driveId,
                        'parents' => array($this->folderId)
                    ));

                    $optParams = array(
                        'fields' => 'id',
                        'data' => file_get_contents($filePath),
                        'supportsAllDrives' => true,
                    );

                    return  $this->service->files->create($file, $optParams);
                } else {

                    $fileMetadata = new Google_Service_Drive_DriveFile(array(
                        'name' => $fileName,    // Set the Filename
                        'parents' => array($this->folderId) // this is the folder id in while file upload
                    ));

                    try {
                        $return_arr[] = $this->service->files->create(
                            $fileMetadata,
                            array(
                                'data' => file_get_contents($filePath),
                                'mimeType' => $mine_type,
                                'uploadType' => 'media'
                            )
                        );
                    } catch (Exception $e) {
                        return 'An error ocurred : ' . $e->getMessage();
                    }
                }
            }
            return $return_arr;
        } else {
            return 'Files missing';
        }
    }

    public function download($folder_path)
    {
        $zip_name = 'gdrive';

        $files = $this->getlist($folder_path);

        if (isset($files['files']) && !empty($files['files'])) {
            $file_list_id = array_column($files['files'], 'download_link');
            $file_list_name = array_column($files['files'], 'name');

            // # create new zip object
            $zip = new ZipArchive();

            # create a temp file & open it
            $tmp_file = tempnam('.', '');
            $zip->open($tmp_file, ZipArchive::CREATE);

            # loop through each file
            foreach ($file_list_id as $key => $fileid) {
                # download file
                $download_file = file_get_contents($fileid);
                #add it to the zip
                $zip->addFromString($file_list_name[$key], $download_file);
            }

            # close zip
            $zip->close();

            # send the file to the browser as a download
            header('Content-disposition: attachment; filename="' . $zip_name . '.zip"');
            header('Content-type: application/zip');
            readfile($tmp_file);
            unlink($tmp_file);
        } else {
            return $files;
        }
    }

    # This function set $this->folderId to last folder where magic happen and return the folder name
    private function lastChildId($foldername, $Isfoldercreate = 1)
    {
        $newSubfolderName = '';
        if (!empty($foldername)) {
            foreach ($foldername as $newSubfolderName) {

                if ($this->driveType == 'shared') {
                    $optParams = array(
                        'corpora' => 'drive',
                        'driveId' => $this->driveId,
                        'includeItemsFromAllDrives' => true,
                        'pageSize' => 100,
                        'supportsAllDrives' => true,
                        'fields' => 'nextPageToken, files(id, name)',
                        'q' => "'" . $this->folderId . "' in parents"
                    );
                } else {
                    $optParams = array(
                        'pageSize' => 100,
                        'fields' => "nextPageToken, files(contentHints/thumbnail,fileExtension,iconLink,id,name,size,thumbnailLink,webContentLink,webViewLink,mimeType,parents)",
                        'q' => "'" . $this->folderId . "' in parents"
                    );
                }

                $Child_folder_list = $this->service->files->listFiles($optParams); // get list of all child Items 

                if (!empty($Child_folder_list['files'])) {

                    # create a child name array
                    $childNameList = array_map(function ($e) {
                        return $e->name;
                    }, $Child_folder_list['files']);

                    $key = array_search($newSubfolderName, $childNameList);
                    if (!empty($key) || $key === 0) {
                        $this->folderId = $Child_folder_list['files'][$key]->id; // if folder already exist then just set folder id
                    } else {
                        if ($Isfoldercreate == 1) {
                            $this->folderId = $this->createsubFolder($this->folderId, $newSubfolderName); // have a  folder list but in the list our folder not found
                        } else {
                            return false;
                        }
                    }
                } else {
                    if ($Isfoldercreate == 1) {
                        $this->folderId = $this->createsubFolder($this->folderId, $newSubfolderName); // not having any child folder inside whole folder
                    } else {
                        return false;
                    }
                }
            }
        } else {
            return true; // return defult folder if path is not mention
        }
        return $newSubfolderName;
    }

    # any were required to create folder callback function to create a folder
    private function createsubFolder($folderId, $folderName)
    {

        if ($this->driveType == 'shared') {
            $file = new Google_Service_Drive_DriveFile(array(
                'name' => trim($folderName),
                'mimeType' => 'application/vnd.google-apps.folder',
                'driveId' => $this->driveId,
                'parents' => array($folderId)
            ));
        } else {
            $file = new Google_Service_Drive_DriveFile(array(
                'name' => trim($folderName),
                'mimeType' => 'application/vnd.google-apps.folder',
                'driveId' => $folderId,
                'parents' => array($folderId)
            ));
        }

        $optParams = array(
            'fields' => 'id',
            'supportsAllDrives' => true,
        );

        $createdFolder = $this->service->files->create($file, $optParams);
        return $createdFolder->id;
    }
}
