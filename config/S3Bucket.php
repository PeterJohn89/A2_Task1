<?php
require '../vendor/autoload.php'; 

use Aws\S3\S3Client;

$s3Client = new S3Client([
    'region'  => 'us-east-1', 
    'version' => 'latest'
]);


function uploadImages($imageUrl){
    global $s3Client;
    try {
        // Get the image content from the URL
        set_time_limit(200);

        $imageContent = file_get_contents($imageUrl);
        if ($imageContent === false) {
            throw new Exception("Failed to download image from: " . $imageUrl);
        }

        // Extract the image file name from the URL
        $imageFileName = basename(parse_url($imageUrl, PHP_URL_PATH));

        // Upload the image to S3
        $s3Client->putObject([
            'Bucket' => 'artistimagesurls',
            'Key' => 'images/' . $imageFileName,
            'Body' => $imageContent
        ]);

        // Return the public S3 URL of the uploaded image
        return $s3Client->getObjectUrl('artistimagesurls', 'images/' . $imageFileName);

    } catch (Aws\S3\Exception\S3Exception $e) {
        error_log("There was an error uploading the image: " . $e->getMessage());
        return null;
    } catch (Exception $e) {
        error_log("Error processing image: " . $e->getMessage());
        return null;
    }
}

function getMusicImage($artist){
    global $s3Client;
    $artist = str_replace(' ', '', $artist) . '.jpg';
    $s3Client->getObjectUrl('artistimagesurls', 'images/' . $artist);
}