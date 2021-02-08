<?php
    $key = get_option('mrs_S3Key');
    $secret = get_option('mrs_S3Secret');
    $bucket = get_option('mrs_S3Bucket');
    $region = get_option('mrs_S3Region');
    $folder = get_option('mrs_S3Folder');

    if(!empty($key) && !empty($secret) && !empty($bucket) && !empty($region) && !empty($folder)){
        return[
            's3' => [
                'key' => $key,
                'secret' => $secret,
                'bucket' => $bucket,
                'region' => $region,
                'folder' => $folder,
                'status' => TRUE
            ]
            ];
    }else{
        return[
            's3' => [
                'key' => '',
                'secret' => '',
                'bucket' => '',
                'region' => '',
                'folder' => '',
                'status' => FALSE
            ]
            ];
    }



?>