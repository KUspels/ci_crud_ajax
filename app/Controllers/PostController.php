<?php

namespace App\Controllers;

use App\Controllers\BaseController;
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


    // Handle add new post ajax request
    public function add()
{
    $file = $this->request->getFile('image');

    // Validate image upload
    $validation = \Config\Services::validation();
    $validation->setRules([
        'image' => 'uploaded[image]|max_size[image,1024]|is_image[image]|mime_in[image,image/jpg,image/jpeg,image/png]',
    ]);

    if (!$validation->withRequest($this->request)->run()) {
        return $this->response->setJSON([
            'error' => true,
            'message' => $validation->getErrors(),
        ]);
    }

    $fileName = $file->getRandomName();
    $file->move('uploads/avatar', $fileName);

    // Decode tags field
    $tags = $this->request->getPost('tags');
    $decodedTags = is_array($tags) ? $tags : json_decode($tags, true);

    if (!$decodedTags || !is_array($decodedTags)) {
        return $this->response->setJSON([
            'error' => true,
            'message' => 'Invalid tags data.',
        ]);
    }

    // Prepare data for insertion
    $data = [
        'title' => $this->request->getPost('title'),
        'category' => json_encode($this->request->getPost('category')), // Save as JSON
        'tags' => json_encode($this->request->getPost('tags')), // Save as JSON
        'body' => $this->request->getPost('body'),
        'image' => $fileName,
        'created_at' => date('Y-m-d H:i:s'),
    ];
    

    $postModel = new PostModel();
    $postModel->save($data);

    return $this->response->setJSON([
        'error' => false,
        'message' => 'Successfully added new post!',
    ]);
}

    

    // Other methods (edit, update, delete, detail) follow the same pattern
    public function edit($id = null)
    {
        $postModel = new PostModel();
        $post = $postModel->find($id);
        $post['category'] = json_decode($post['category'], true); // Decode category
        $post['tags'] = json_decode($post['tags'], true);         // Decode tags
        return $this->response->setJSON([
            'error' => false,
            'message' => $post,
        ]);
    }


    // Handle fetch all posts ajax request
    public function fetch()
    {
        $postModel = new PostModel();
        $posts = $postModel->findAll();

        foreach ($posts as &$post) {
            $post['category'] = json_decode($post['category'], true); // Decode category
            $post['tags'] = json_decode($post['tags'], true);         // Decode tags
        }

        $data = view('posts_list', ['posts' => $posts]);

        return $this->response->setJSON([
            'error' => false,
            'message' => $data,
        ]);
    }

    public function update()
    {
        $id = $this->request->getPost('id');
        $file = $this->request->getFile('image');
    
        // Initialize the filename with the old image
        $fileName = $this->request->getPost('old_image');
    
        // If a new image is uploaded
        if ($file && $file->isValid() && !$file->hasMoved()) {
            $fileName = $file->getRandomName();
            $file->move('uploads/avatar', $fileName);
    
            // Remove the old image if it exists
            $oldImage = $this->request->getPost('old_image');
            if (!empty($oldImage) && file_exists('uploads/avatar/' . $oldImage)) {
                unlink('uploads/avatar/' . $oldImage);
            }
        }
    
        // Decode tags field
        $tags = $this->request->getPost('tags');
        $decodedTags = is_array($tags) ? $tags : json_decode($tags, true);
    
        if (!$decodedTags || !is_array($decodedTags)) {
            return $this->response->setJSON([
                'error' => true,
                'message' => 'Invalid tags data.',
            ]);
        }
    
        // Prepare data for update
        $data = [
            'title' => $this->request->getPost('title'),
            'category' => json_encode($this->request->getPost('category')), // Save as JSON
            'tags' => json_encode($this->request->getPost('tags')), // Save as JSON
            'body' => $this->request->getPost('body'),
            'image' => $fileName,
            'created_at' => date('Y-m-d H:i:s'),
        ];
        
    
        $postModel = new PostModel();
        try {
            $postModel->update($id, $data);
            return $this->response->setJSON([
                'error' => false,
                'message' => 'Successfully updated post!',
            ]);
        } catch (\Exception $e) {
            return $this->response->setJSON([
                'error' => true,
                'message' => 'Failed to update post: ' . $e->getMessage(),
            ]);
        }
    }
    



public function delete($id = null)
{
    $postModel = new PostModel();
    $post = $postModel->find($id);
    $postModel->delete($id);
    unlink('uploads/avatar/' . $post['image']);
    return $this->response->setJSON([
        'error' => false,
        'message' => 'Successfully deleted post!',
    ]);
}

public function detail($id = null)
{
    $postModel = new PostModel();
    $post = $postModel->find($id);

    if ($post) {
        return $this->response->setJSON([
            'error' => false,
            'data' => $post,
        ]);
    } else {
        return $this->response->setJSON([
            'error' => true,
            'message' => 'Post not found',
        ]);
    }
}
}
