<?php

namespace App\Controllers;

use App\Controllers\BaseController;
use App\Controllers\PostValidation;
use App\Models\PostModel;
use App\Models\CategoryModel;

class PostController extends BaseController
{
    public function index()
    {
        return view('index');
    }

    // New method to fetch categories
    public function getCategories()
    {
        $categoryModel = new CategoryModel();
        $categories = $categoryModel->findAll();

        // Map database fields to expected structure
        $formattedCategories = array_map(function ($category) {
            return [
                'id' => $category['id'], // Ensure 'id' exists in your table
                'category_title' => $category['category_title'], // Map 'name' (or other DB column) to 'category_title'
            ];
        }, $categories);

        return $this->response->setJSON([
            'error' => false,
            'data' => $formattedCategories,
        ]);
    }

    public function add()
    {
            // Load validation rules and messages
        $rules = PostValidation::getValidationRules('add');
        $messages = PostValidation::getValidationMessages();

        // Validate the request
        if (!$this->validate($rules, $messages)) {
            log_message('error', 'Validation failed: ' . print_r($this->validator->getErrors(), true));
            return $this->response->setJSON([
                'error' => true,
                'message' => $this->validator->getErrors(), // Return validation errors
            ]);
        }

        // Handling multiple image files
        $files = $this->request->getFiles();
        log_message('debug', 'Files received: ' . print_r($files, true));

        $imagesData = $this->request->getPost('imagesData'); // Get the JSON string for image metadata

        $uploadedImages = []; // Array to store images metadata

        if ($imagesData) {
            $decodedImagesData = json_decode($imagesData, true);

            if (isset($files['images']) && is_array($files['images'])) {
                log_message('debug', 'Total images received: ' . count($files['images'])); // Log the count of images

                $validFiles = [];
                foreach ($files['images'] as $index => $file) {
                    if ($file->isValid() && !$file->hasMoved()) {
                        $validFiles[] = $file; // Keep track of valid files only
                    } else {
                        log_message('debug', 'Invalid or already moved file at index ' . $index);
                    }
                }

                foreach ($validFiles as $validIndex => $file) {
                    if (!isset($decodedImagesData[$validIndex])) {
                        log_message('debug', 'Image metadata missing for index: ' . $validIndex);
                        continue; // Skip if metadata is missing
                    }

                    // Save the file
                    $fileName = $file->getRandomName();
                    $file->move('uploads/avatar', $fileName);

                    // Append the current file data to the uploadedImages array
                    $uploadedImages[] = [
                        'fileName' => $fileName,
                        'title' => $decodedImagesData[$validIndex]['title'] ?? '', // Use the title from metadata
                    ];

                    // Debugging log to verify the image data is added correctly
                    log_message('debug', 'Current Uploaded Images Array: ' . print_r($uploadedImages, true));
                }
            } else {
                log_message('debug', 'No valid images array found in request.');
            }
        }

        // Debugging log after processing all files
        log_message('debug', 'Final Uploaded Images Array: ' . print_r($uploadedImages, true));

        // Decode and validate categories
        $categories = $this->request->getPost('category') ?? '[]';
        $decodedCategories = is_array($categories) ? $categories : json_decode($categories, true);

        // Decode tags field
        $tags = $this->request->getPost('tags');
        $decodedTags = is_array($tags) ? $tags : json_decode($tags, true);
        log_message('debug', 'Tags received and decoded: ' . print_r($decodedTags, true)); // Debugging log

        // Prepare data for insertion
        $data = [
            'title' => $this->request->getPost('title'),
            'category' => json_encode($decodedCategories), // Save as JSONB
            'tags' => json_encode($decodedTags),            // Save as JSONB
            'body' => $this->request->getPost('body'),
            'image' => json_encode($uploadedImages),        // Save as JSONB
        ];

        // Insert data into the database
        $postModel = new PostModel();
        $postModel->save($data);

        return $this->response->setJSON([
            'error' => false,
            'message' => 'Successfully added new post!',
        ]);
    }


    public function edit($id = null)
    {
        $postModel = new PostModel();
        $categoryModel = new CategoryModel();

        $post = $postModel->find($id);

        if ($post) {
            // Decode JSON fields
            $post['category'] = json_decode($post['category'], true);
            $post['tags'] = json_decode($post['tags'], true);
            $post['image'] = json_decode($post['image'], true);

            // Get category titles for selected categories
            if (is_array($post['category'])) {
                $categories = $categoryModel->whereIn('id', $post['category'])->findAll();
                $categoryTitles = array_map(function ($category) {
                    return [
                        'id' => $category['id'],
                        'category_title' => $category['category_title']
                    ];
                }, $categories);
                $post['category_titles'] = $categoryTitles;
            } else {
                $post['category_titles'] = [];
            }

            return $this->response->setJSON([
                'error' => false,
                'message' => $post,
            ]);
        } else {
            return $this->response->setJSON([
                'error' => true,
                'message' => 'Post not found',
            ]);
        }
    }

    public function fetch()
    {
        $postModel = new PostModel();
        $categoryModel = new CategoryModel();
        $posts = $postModel->findAll();

        foreach ($posts as &$post) {
            // Decode categories from JSON and map category IDs to titles
            $categoryIds = json_decode($post['category'], true);
            if (is_array($categoryIds) && !empty($categoryIds)) {
                $categories = $categoryModel->whereIn('id', $categoryIds)->findAll();
                $post['category'] = array_map(function ($category) {
                    return $category['category_title'];
                }, $categories);
            } else {
                $post['category'] = [];
            }

            // Decode tags and images from JSON
            $post['tags'] = json_decode($post['tags'], true);
            $post['image'] = json_decode($post['image'], true);
        }

        $data = view('posts_list', ['posts' => $posts]);

        return $this->response->setJSON([
            'error' => false,
            'message' => $data,
        ]);
    }

    public function update()
    {

         // Load validation rules and messages
         $rules = PostValidation::getValidationRules('edit');
         $messages = PostValidation::getValidationMessages();
 
         // Validate the request
         if (!$this->validate($rules, $messages)) {
             log_message('error', 'Validation failed: ' . print_r($this->validator->getErrors(), true));
             return $this->response->setJSON([
                 'error' => true,
                 'message' => $this->validator->getErrors(), // Return validation errors
             ]);
         }

        $id = $this->request->getPost('id');
        if (!$id) {
            log_message('error', 'Post ID is missing.');
            return $this->response->setJSON([
                'error' => true,
                'message' => 'Post ID is required.',
            ]);
        }

        log_message('debug', "Updating post images for ID: {$id}");

        $files = $this->request->getFiles();
        $removedOldFiles = json_decode($this->request->getPost('removedOldFiles'), true);
        $existingImages = json_decode($this->request->getPost('existingImages'), true);
        // Ensure existingImages is an array
        if (!is_array($existingImages)) {
            $existingImages = [];
        }
        $finalImages = [];

        // Process existing images
        foreach ($existingImages as $image) {
            if (!in_array($image['fileName'], $removedOldFiles)) {
                $finalImages[] = [
                    'fileName' => $image['fileName'],
                    'title' => $image['title']
                ];
            }
        }

        $titles = $this->request->getPost('new_image_titles'); 

        if (isset($files['newImages']) && is_array($files['newImages'])) {
            foreach ($files['newImages'] as $uniqueId => $file) {
                if ($file->isValid() && !$file->hasMoved()) {
                    $fileName = $file->getRandomName();
                    $file->move('uploads/avatar', $fileName);
                    
                    $title = $titles[$uniqueId] ?? '';
                    
                    $finalImages[] = [
                        'fileName' => $fileName,
                        'title' => $title,
                    ];
                }
            }
        }

        // Remove old files marked for deletion
        if (!empty($removedOldFiles)) {
            foreach ($removedOldFiles as $fileId) {
                // Map fileId to file path (in case of existing images, fileId is the fileName)
                $filePath = FCPATH . 'uploads/avatar/' . $fileId;
        
                // Check for conflicts (e.g., if the file is also being added)
                if (in_array($fileId, array_column($finalImages, 'fileName'))) {
                    log_message('error', "Conflict detected: {$fileId} marked for deletion and addition.");
                    continue; // Skip conflicting file
                }
        
                // Attempt to delete the file
                if (file_exists($filePath)) {
                    if (!unlink($filePath)) {
                        log_message('error', "Failed to delete file: {$filePath}");
                    } else {
                        log_message('info', "Successfully deleted file: {$filePath}");
                    }
                } else {
                    log_message('error', "File not found for deletion: {$filePath}");
                }
            }
        }
        
        log_message('debug', 'Received image_titles: ' . json_encode($this->request->getPost('image_titles')));

        // Decode and validate categories
        $categories = $this->request->getPost('category') ?? '[]';
        $decodedCategories = is_array($categories) ? $categories : json_decode($categories, true);

        // Decode tags field
        $tags = $this->request->getPost('tags');
        $decodedTags = is_array($tags) ? $tags : json_decode($tags, true);
        log_message('debug', 'Tags received and decoded: ' . print_r($decodedTags, true)); // Debugging log

        // Prepare data for updating only images
        $data = [
            'title' => $this->request->getPost('title'),
            'category' => json_encode($decodedCategories), // Save as JSONB
            'tags' => json_encode($decodedTags),         // Save as JSON
            'body' => $this->request->getPost('body'),
            'image' => json_encode($finalImages), // Save as JSON
        ];
        log_message('debug', 'Final Images Array: ' . json_encode($finalImages));

        log_message('debug', 'Data prepared for update: ' . print_r($data, true));

        $postModel = new PostModel();
        try {
            $postModel->update($id, $data);
            log_message('debug', "Successfully updated post images for ID: {$id}");
            return $this->response->setJSON([
                'error' => false,
                'message' => 'Successfully updated post images!',
            ]);
        } catch (\Exception $e) {
            log_message('error', 'Failed to update post images: ' . $e->getMessage());
            return $this->response->setJSON([
                'error' => true,
                'message' => 'Failed to update post images: ' . $e->getMessage(),
            ]);
        }
    }

    public function delete($id = null)
    {
        $postModel = new PostModel();
        $post = $postModel->find($id);

        // Decode the image field
        $images = is_string($post['image']) ? json_decode($post['image'], true) : $post['image'];

        // Delete each image if it exists
        if ($images && is_array($images)) {
            foreach ($images as $file) {
                $filePath = 'uploads/avatar/' . $file['fileName'];
                if (file_exists(FCPATH . $filePath)) {
                    unlink(FCPATH . $filePath);
                }
            }
        }

        // Delete the post from the database
        $postModel->delete($id);

        return $this->response->setJSON([
            'error' => false,
            'message' => 'Successfully deleted post!',
        ]);
    }

    public function detail($id = null)
    {
        $postModel = new PostModel();
        $categoryModel = new CategoryModel();
        $post = $postModel->find($id);

        if ($post) {
            // Decode JSON fields
            $categoryIds = json_decode($post['category'], true);
            if (is_array($categoryIds)) {
                $categories = $categoryModel->whereIn('id', $categoryIds)->findAll();
                $post['category'] = array_map(function ($category) {
                    return $category['category_title'];
                }, $categories);
            } else {
                $post['category'] = [];
            }

            $post['tags'] = json_decode($post['tags'], true);
            $post['image'] = json_decode($post['image'], true);

            return $this->response->setJSON([
                'error' => false,
                'message' => $post, // Use 'message' so it matches your JavaScript
            ]);
        } else {
            return $this->response->setJSON([
                'error' => true,
                'message' => 'Post not found',
            ]);
        }
    }
}
