# How setup Google Drive 
There are some below steps to setup google drive api with your php projects.

##### Step 1 : Setup console account

- Go to the [Google cloud console](https://console.cloud.google.com/)
- Click on create a new project [here](https://i.ibb.co/fxSnBKB/image.png)
    - Add project name and click on create.(You can see the notification when project being create)


- Now create a client creadential that we use in our site to upload file,create folders etc.
    - Go to cloud navigation menu > APIs & Services > Credentials
    - Click on add new Credentials and select Service account.
    - Fill the service account name and ID then click on create and continue.
    - Then click on done step 2 and 3 are option.
    - After successfully generating of service account click on edit account option.
    - go to KEY tab under key tab click on "ADD KEY >  Create new key " 
    - Select Key type as json and click on create after that 1 json download in your Computer.


##### Step 2 : Let's integrate with our porject

- Copy the content of file from your downloaded file of the service account creadential from cloud console.
- add this content to service-account.json

# How To use function
There are some below functions and know how to use it (We add all of this functions to our example.php file)

##### Step 1 : Setup google drive folder
- got to your [google drive](https://drive.google.com/drive/)
- create a Folder in which store data, retrive data or create folders.
- in my case i create a folder with name demo drive.
- now make this folder plublic.
  - right click on folder and click on share.
  - click on change to anyone with the link [here](https://i.ibb.co/YfVrxNF/image.png).
  - then click on Done.
- Right Click on newly created folder an click on share button.
- share with creted email address of service account.
- now go inside folder and copy the Folder id [here](https://i.ibb.co/VHn6F7t/image.png)

## Now use the functions
now define the folder id and required the action.php to run all function [Like this](https://i.ibb.co/kMHXR2x/image.png)

##### How to create a folder in google drive
- in my example i wants to create a folder main demo and child folder child demo 1 with another child child demo 2
    see the result [here](https://i.ibb.co/S3txzNt/image.png)
     ```
    $googleDrive = new googleDrive();  // define the google class
    $folderPath = 'maindemo/child demo 1/child demo 2'; // this is the folder path in which you wants to upoad
    $googleDrive->createFolder($folderPath);
    ```
    
##### How to upload a file in google drive
- in my example i wants to upload file in above created folder
    see the result [here](https://i.ibb.co/XjBF5Ty/image.png)
     ```
    $googleDrive = new googleDrive();  // define the google class
    $folderPath = 'maindemo/child demo 1/child demo 2'; // this is the folder path in which you wants to upoad
    $filePath = array(
        'D:/xampp/htdocs/drive2/cover.jpg',
        'D:/xampp/htdocs/drive2/images.png',
    ); // file path that you wants to upload in google drive please use in array
    $googleDrive->uploadFile($folderPath, $filePath);  
    ```

##### How to get all file details of particular folder
- in my example i wants to fet list of file in above created folder ( for demo purpose i add 2 file )
    see the result [here](https://i.ibb.co/5hWpTjN/image.png)
     ```
    $googleDrive = new googleDrive();  // define the google class
    $folderPath = 'maindemo/child demo 1/child demo 2'; // this is the folder path in which you wants to upoad
    $list_of_files = $googleDrive->getlist($folderPath); 
    ```
    
##### How to Downalod all file from particular folder
- in my example i wants to download all the file that uploded in above created folder.
    see the result [here](https://i.ibb.co/r2TkWQ2/image.png)
     ```
    $googleDrive = new googleDrive();  // define the google class
    $folderPath = 'maindemo/child demo 1/child demo 2'; // this is the folder path in which you wants to upoad
    $googleDrive->download($folderPath);
    ```
