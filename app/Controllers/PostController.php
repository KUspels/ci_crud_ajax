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
        $file = $this->request->getFile('file');
        $fileName = $file->getRandomName();

        $data = [
            'title' => $this->request->getPost('title'),
            'category' => $this->request->getPost('category'),
            'body' => $this->request->getPost('body'),
            'image' => $fileName,
            'created_at' => date('Y-m-d H:i:s'),
        ];

        $validation = \Config\Services::validation();
        $validation->setRules([
            'image' => 'uploaded[file]|max_size[file,1024]|is_image[file]|mime_in[file,image/jpg,image/jpeg,image/png]',
        ]);

        if (!$validation->withRequest($this->request)->run()) {
            return $this->response->setJSON([
                'error' => true,
                'message' => $validation->getErrors(),
            ]);
        } else {
            $file->move('uploads/avatar', $fileName);
            $postModel = new PostModel(); 
            $postModel->save($data);
            return $this->response->setJSON([
                'error' => false,
                'message' => 'Successfully added new post!',
            ]);
        }
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
    

    // Other methods (edit, update, delete, detail) follow the same pattern
    public function edit($id = null) {
        $postModel = new PostModel(); 
        $post = $postModel->find($id);
        return $this->response->setJSON([
            'error' => false,
            'message' => $post,
        ]);
    }

    public function update() {
        $id = $this->request->getPost('id');
        $file = $this->request->getFile('file');
        $fileName = $file->getFilename();

        if ($fileName != '') {
            $fileName = $file->getRandomName();
            $file->move('uploads/avatar', $fileName);
            if ($this->request->getPost('old_image') != '') {
                unlink('uploads/avatar/' . $this->request->getPost('old_image'));
            }
        } else {
            $fileName = $this->request->getPost('old_image');
        }

        $data = [
            'title' => $this->request->getPost('title'),
            'category' => $this->request->getPost('category'),
            'body' => $this->request->getPost('body'),
            'image' => $fileName,
            'updated_at' => date('Y-m-d H:i:s'),
        ];

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
