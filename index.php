<?php
require_once 'lib/google-api-php-client/src/Google_Client.php';
require_once 'lib/google-api-php-client/src/contrib/Google_DriveService.php';

session_start();

// Your Google Drive credentials
$client_id = '1011317251325-7qo4he0lp6jc4m477lltql1q36e1ohag.apps.googleusercontent.com';
$client_secret = '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';
$redirect_uri = 'http://' . $_SERVER['HTTP_HOST'] . $_SERVER['PHP_SELF'];

// Initialize the Google Client
$client = new Google_Client();
$client->setClientId($client_id);
$client->setClientSecret($client_secret);
$client->setRedirectUri($redirect_uri);
$client->setScopes(['https://www.googleapis.com/auth/drive']);

if (isset($_SESSION['access_token'])) {
    $client->setAccessToken($_SESSION['access_token']);
} else {
    if (empty($_GET['code'])) {
        $authUrl = $client->createAuthUrl();
        header('Location: ' . filter_var($authUrl, FILTER_SANITIZE_URL));
        exit;
    } else {
        $client->authenticate($_GET['code']);
        $_SESSION['access_token'] = $client->getAccessToken();
        header('Location: ' . filter_var($redirect_uri, FILTER_SANITIZE_URL));
        exit;
    }
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!empty($_FILES["fileToUpload"]["name"])) {
        $target_file = $_FILES["fileToUpload"]["tmp_name"];

        // Create the Google Drive service object
        $service = new Google_DriveService($client);

        // Create the file on your Google Drive
        $fileMetadata = new Google_Service_Drive_DriveFile([
            'name' => $_FILES["fileToUpload"]["name"]
        ]);
        $content = file_get_contents($target_file);
        $mimeType = mime_content_type($target_file);
        $file = $service->files->create($fileMetadata, [
            'data' => $content,
            'mimeType' => $mimeType,
            'uploadType' => 'multipart'
        ]);

        echo 'File uploaded to Google Drive. File ID: ' . $file->id;
    }
}
?>
<!DOCTYPE html>
<html>
<body>
    <form action="" method="post" enctype="multipart/form-data">
        Select file to upload:
        <input type="file" name="fileToUpload" id="fileToUpload">
        <input type="submit" value="Upload File" name="submit">
    </form>
</body>
</html>
