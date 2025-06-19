<?php

namespace Arpanpatoliya\DBBackup\Contracts;

/**
 * UploaderInterface Contract
 * 
 * Defines the contract for file upload operations.
 * Any class implementing this interface must provide a method
 * to upload files to a specified destination and return the result.
 * This allows for different upload strategies (Google Drive, S3, etc.).
 */
interface UploaderInterface
{
    /**
     * Upload File
     * 
     * Uploads a file to the destination specified by the implementing class.
     * The method should handle the complete upload process and return
     * a JSON encoded string with status and result information.
     * 
     * @param string $filePath Path to the file that needs to be uploaded
     * @return string JSON encoded response with upload status and details
     */
    public function upload(string $filePath): string;
}

