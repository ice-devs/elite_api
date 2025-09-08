<?php

namespace App;

use Cloudinary\Cloudinary;
use Cloudinary\Configuration\Configuration;
use Cloudinary\Api\Upload\UploadApi;
use Exception;

class CloudinaryUploader {
    private $cloudinary;

    public function __construct() {
        $this->cloudinary = new Cloudinary(Configuration::instance(getenv('CLOUDINARY_URL')));
    }

    public function uploadFile($file) {
        try {
            $options = [];
            $options['folder'] = "EliteProducts";
            $result = (new UploadApi())->upload($file['tmp_name'], $options);
            return $result['secure_url'];
        } catch (Exception $e) {
            echo 'File upload error: ' . $e->getMessage();
            return false;
        }
    }
}
?>
