<?php

namespace App\Controllers;

use CodeIgniter\Controller;

class PostValidation extends Controller
{
    public static function getValidationRules($type = 'add')
{
    $rules = [
        'title' => 'required|max_length[255]',
        'body' => 'required|max_length[1000]',
        'category' => 'required|checkNotEmptyArray',
        'tags' => 'required|checkNotEmptyArray',
    ];

    if ($type === 'add') {
        $rules['imagesData'] = 'required|checkNotEmptyArray|checkValidImages'; 

    } elseif ($type === 'edit') {
        $rules['existingImages'] = 'checkValidExistingImages'; 
        $rules['newImages'] = 'checkValidNewImages'; 
    }

    return $rules;
}



public static function getValidationMessages()
{
    return [
        'title' => [
            'required' => 'The title is required.',
            'max_length' => 'The title must not exceed 255 characters.',
        ],
        'body' => [
            'required' => 'The body is required.',
            'max_length' => 'The body must not exceed 1000 characters.',
        ],
        'category' => [
            'required' => 'At least one category must be selected.',
            'checkNotEmptyArray' => 'At least one category must be selected.',
        ],
        'tags' => [
            'required' => 'At least one tag is required.',
            'checkNotEmptyArray' => 'At least one tag must be provided.', 
        ],
        'imagesData' => [
            'required' => 'At least one image is required.',
            'checkValidImages' => 'Each image must have a valid file name, title, and comply with size and format constraints.',
            'checkNotEmptyArray' => 'Atleast one image is required',
        ],
        'existingImages' => [
            'checkValidExistingImages' => 'Each existing image must have a valid title.',
        ],
        'newImages' => [
            'checkValidNewImages' => 'New images must have a valid size, valid format and title.',
        ],
    ];
}



}
