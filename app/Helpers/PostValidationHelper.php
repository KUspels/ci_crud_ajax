<?php

namespace App\Helpers;

use CodeIgniter\HTTP\Files\UploadedFile;

class PostValidationHelper
{
    /**
     * Validate `imagesData` structure and the uploaded image files.
     */
    public static function checkValidImages($value): bool
    {
        $images = json_decode($value, true); // Decode JSON string
    
        
    
        foreach ($images as $image) {
            if (empty($image['fileName']) || !isset($image['title']) || $image['title'] === '') {
                log_message('error', 'Image data missing fileName or title: ' . json_encode($image));
                return false; // Missing fileName or title
            }
    
            $uploadedFiles = $_FILES['images'] ?? [];
            foreach ($uploadedFiles['name'] as $index => $fileName) {
                if ($fileName === $image['fileName']) {
                    $fileSize = $uploadedFiles['size'][$index];
                    $tmpName = $uploadedFiles['tmp_name'][$index];
                    $fileType = mime_content_type($tmpName);
    
                    if ($fileSize > 1024 * 1024) { // 1 MB limit
                        log_message('error', "File $fileName exceeds size limit.");
                        return false;
                    }
    
                    if (!in_array($fileType, ['image/jpeg', 'image/png', 'image/jpg'])) {
                        log_message('error', "File $fileName has an invalid MIME type: $fileType.");
                        return false;
                    }
                }
            }
        }
    
        return true; // All images are valid
    }

    /**
     * Validate `existingImages` structure for edit form.
     */
    public static function checkValidExistingImages($value): bool
    {
        $images = json_decode($value, true); // Decode JSON string
    
        if (!is_array($images)) {
            return true; // No existing images to validate
        }
    
        foreach ($images as $image) {
            if (empty($image['title']) || !is_string($image['title'])) {
                log_message('error', 'Existing image missing a valid title: ' . json_encode($image));
                return false; // Missing or invalid title
            }
        }
    
        return true; // All existing images have valid titles
    }

    /**
     * Validate `newImages` for uploaded files in edit form.
     */
    public static function checkValidNewImages(): bool
{
    $uploadedFiles = $_FILES['newImages'] ?? [];

    if (empty($uploadedFiles)) {
        return true; // No new images to validate
    }

    foreach ($uploadedFiles['name'] as $index => $fileName) {
        $fileSize = $uploadedFiles['size'][$index];
        $tmpName = $uploadedFiles['tmp_name'][$index];
        $fileType = mime_content_type($tmpName);

        if ($fileSize > 1024 * 1024) { // 1 MB limit
            log_message('error', "File $fileName exceeds size limit.");
            return false;
        }

        if (!in_array($fileType, ['image/jpeg', 'image/png', 'image/jpg'])) {
            log_message('error', "File $fileName has an invalid MIME type: $fileType.");
            return false;
        }
    }

    // Ensure new image titles are valid
    $newImageTitles = $_POST['new_image_titles'] ?? [];
    if (!is_array($newImageTitles)) {
        return false; // Titles must be an array
    }

    foreach ($newImageTitles as $title) {
        if (empty($title) || !is_string($title)) {
            log_message('error', 'New image title is missing or invalid.');
            return false;
        }
    }

    return true; // All new images and titles are valid
}





    /**
     * Validate `new_image_titles` structure.
     */
    public static function checkValidImageTitles($value): bool
    {
        if (!is_array($value)) {
            return false; // Must be an array
        }

        foreach ($value as $title) {
            if (empty($title) || !is_string($title)) {
                return false; // Title must be a non-empty string
            }
        }

        return true; // All titles are valid
    }

    /**
     * Custom validation rule to check if an array is not empty.
     */
    public static function checkNotEmptyArray($value): bool
    {
        log_message('debug', 'Value received in checkNotEmptyArray: ' . print_r($value, true));

        // Handle cases where the value is null, empty string, or empty array
        if (empty($value)) {
            return false;
        }

        // Check if the value is a JSON string and decode it
        if (is_string($value)) {
            $decoded = json_decode($value, true);
            log_message('debug', 'Decoded value: ' . print_r($decoded, true));
            return is_array($decoded) && !empty($decoded);
        }

        // Check if the value is already an array and not empty
        return is_array($value) && !empty($value);
    }
}
