<?php

namespace App\Controllers;

use App\Controllers\BaseController;
use App\Models\PostModel; 

class PostController extends BaseController {
    public function index() {
        return view('index');
    }

    // Handle add new post ajax request
    public function add() {
        // Get the uploaded image file
        $file = $this->request->getFile('image');
        
        // Ensure the file is valid and handle errors
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

        // Generate a random filename for the uploaded image
        $fileName = $file->getRandomName();
        
        // Move the file to the 'uploads/avatar' directory
        $file->move('uploads/avatar', $fileName);

        // Prepare post data
        $data = [
            'title' => $this->request->getPost('title'),
            'category' => $this->request->getPost('category'),
            'body' => $this->request->getPost('body'),
            'image' => $fileName, // Store the filename of the uploaded image
            'created_at' => date('Y-m-d H:i:s'),
        ];

        // Save the post data to the database
        $postModel = new PostModel(); 
        $postModel->save($data);

        return $this->response->setJSON([
            'error' => false,
            'message' => 'Successfully added new post!',
        ]);
    }


    // Other methods (edit, update, delete, detail) follow the same pattern
    public function edit($id = null) {
        $postModel = new PostModel(); 
        $post = $postModel->find($id);
        return $this->response->setJSON([
            'error' => false,
            'message' => $post,
        ]);
    }


    // Handle fetch all posts ajax request
    public function fetch() {
        $postModel = new PostModel();
        $posts = $postModel->findAll();
    
        $data = view('posts_list', ['posts' => $posts]);
    
        return $this->response->setJSON([
            'error' => false,
            'message' => $data,
        ]);
    }

    public function update() {
        $id = $this->request->getPost('id');
        $file = $this->request->getFile('image');
        $fileName = $file->getFilename();

        // If a new image is uploaded
        if ($fileName != '') {
            $fileName = $file->getRandomName();
            $file->move('uploads/avatar', $fileName);

            // Remove the old image if exists
            if ($this->request->getPost('old_image') != '') {
                unlink('uploads/avatar/' . $this->request->getPost('old_image'));
            }
        } else {
            // Use the old image if none is uploaded
            $fileName = $this->request->getPost('old_image');
        }

        // Prepare post data
        $data = [
            'title' => $this->request->getPost('title'),
            'category' => $this->request->getPost('category'),
            'body' => $this->request->getPost('body'),
            'image' => $fileName, // Save the new or old image filename
            'updated_at' => date('Y-m-d H:i:s'),
        ];

        // Update the post in the database
        $postModel = new PostModel(); 
        $postModel->update($id, $data);

        return $this->response->setJSON([
            'error' => false,
            'message' => 'Successfully updated post!',
        ]);
    }

    public function delete($id = null) {
        $postModel = new PostModel(); 
        $post = $postModel->find($id);
        $postModel->delete($id);
        unlink('uploads/avatar/' . $post['image']);
        return $this->response->setJSON([
            'error' => false,
            'message' => 'Successfully deleted post!',
        ]);
    }

    public function detail($id = null) {
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
