<!DOCTYPE html>
<html lang="en">

<head>
  <meta charset="UTF-8">
  <meta http-equiv="X-UA-Compatible" content="IE=edge">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>CRUD App Using CI 4 and Ajax</title>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-1BmE4kWBq78iYhFldvKuhfTAU6auU8tT94WrHftjDbrCEXSU1oBoqyl2QvZ6jIW3" crossorigin="anonymous">
  <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
  <script src="https://underscorejs.org/underscore-min.js"></script>
  <script src="https://cdn.jsdelivr.net/gh/jsonform/jsonform/lib/jsonform.js"></script>
</head>

<body>
  <!-- Modal Form: Add Post -->
  <div class="modal fade" id="add_post_modal" data-bs-backdrop="static" data-bs-keyboard="false" tabindex="-1" aria-labelledby="staticBackdropLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-lg">
      <div class="modal-content">
        <div class="modal-header">
          <h5 class="modal-title" id="staticBackdropLabel">Add New Post</h5>
          <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
        </div>
        <div class="modal-body p-5">
          <!-- JSONForm will be rendered here -->
          <form id="add_post_form"></form>
        </div>
        <div class="modal-footer">
          <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
          <button type="" class="btn btn-primary" id="add_post_btn">Add Post</button>
        </div>
      </div>
    </div>
  </div>

  <!-- Modal Form: Edit Post -->
  <div class="modal fade" id="edit_post_modal" data-bs-backdrop="static" data-bs-keyboard="false" tabindex="-1" aria-labelledby="staticBackdropLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-lg">
      <div class="modal-content">
        <div class="modal-header">
          <h5 class="modal-title" id="staticBackdropLabel">Edit Post</h5>
          <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
        </div>
        <div class="modal-body p-5">
          <!-- JSONForm will be rendered here -->
          <form id="edit_post_form"></form>
        </div>
        <div class="modal-footer">
          <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
          <button type="button" class="btn btn-primary" id="edit_post_btn">Update Post</button>
        </div>
      </div>
    </div>
  </div>

  <!-- detail post modal start -->
  <div class="modal fade" id="detail_post_modal" data-bs-backdrop="static" data-bs-keyboard="false" tabindex="-1" aria-labelledby="staticBackdropLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-lg">
      <div class="modal-content">
        <div class="modal-header">
          <h5 class="modal-title" id="staticBackdropLabel">Details of Post</h5>
          <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
        </div>
        <div class="modal-body">
          <img src="" id="detail_post_image" class="img-fluid">
          <h3 id="detail_post_title" class="mt-3"></h3>
          <h5 id="detail_post_category"></h5>
          <p id="detail_post_body"></p>
          <p id="detail_post_created" class="fst-italic"></p>
        </div>
        <div class="modal-footer">
          <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
        </div>
      </div>
    </div>
  </div>
  <!-- detail post modal end -->

  <!-- Posts Listing -->
  <div class="container">
    <div class="row my-4">
      <div class="col-lg-12">
        <div class="card shadow">
          <div class="card-header d-flex justify-content-between align-items-center">
            <div class="text-secondary fw-bold fs-3">All Posts</div>
            <button class="btn btn-dark" data-bs-toggle="modal" data-bs-target="#add_post_modal">Add New Post</button>
          </div>
          <div class="card-body">
            <div class="row" id="show_posts">
              <h1 class="text-center text-secondary my-5">Posts Loading..</h1>
            </div>
          </div>
        </div>
      </div>
    </div>
  </div>

  <!-- Scripts -->
  <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js" integrity="sha384-ka7Sk0Gln4gmtz2MlQnikT1wXgYsOg+OMhuP+IlRH9sENBO0LRn5q+8nbTov4+1p" crossorigin="anonymous"></script>
  <script src="//cdn.jsdelivr.net/npm/sweetalert2@11"></script>

  <script>
  $(document).ready(function() {
    const postSchema = {
      title: {
        title: "Post Title",
        type: "string",
        required: true
      },
      category: {
        title: "Post Category",
        type: "string",
        required: true
      },
      body: {
        title: "Post Body",
        type: "string",
        required: true
      },
      image: {
        title: "Post Image",
        type: "string",
        required: true,
        validate: false
      }
    };

    const postForm = [
      {
        key: "title",
        type: "text",
      },
      {
        key: "category",
        type: "text",
      },
      {
        key: "body",
        type: "textarea",
      },
      {
        key: "image",
        type: "file",
        accept: ".jpg,.jpeg,.png",
      },
    ];

    // Load form dynamically for Add Post
    $("#add_post_modal").on('shown.bs.modal', function() {
      console.log("Modal initialized and form should render");
      $("form#add_post_form").empty();

      // Initialize form using jsonForm for add post
      $("form#add_post_form").jsonForm({
        schema: postSchema,
        form: postForm,
        onSubmit: function(errors, values) {
          if (errors) {
            alert("Form has errors!");
          } else {
            let formData = new FormData();
            formData.append('title', values.title);
            formData.append('category', values.category);
            formData.append('body', values.body);
            formData.append('image', $('#add_post_form input[type="file"]')[0].files[0]);

            $.ajax({
              url: '<?= base_url('post/add') ?>',
              method: 'post',
              data: formData,
              contentType: false,
              processData: false,
              dataType: 'json',
              success: function(response) {
                console.log("AJAX success:", response);
                if (response.error) {
                  alert(response.message);
                } else {
                  $("#add_post_modal").modal('hide');
                  Swal.fire('Added', response.message, 'success');
                  fetchAllPosts();
                }
              }
            });
          }
        }
      });
      console.log("JSONForm rendered for Add Post");
    });

    // Trigger form submit manually when Add Post button is clicked
    $("#add_post_btn").on('click', function() {
    // Trigger the form submit by calling jsonForm's onSubmit method
    $("form#add_post_form").submit();
    });

    // Load form dynamically for Edit Post
    $(document).on('click', '.post_edit_btn', function() {
  const id = $(this).attr('id');
  $.ajax({
    url: '<?= base_url('post/edit/') ?>/' + id,
    method: 'get',
    success: function(response) {
      const postData = response.message;

      // Reset schema with the existing post data for editing
      postSchema.title.default = postData.title;
      postSchema.category.default = postData.category;
      postSchema.body.default = postData.body;
      postSchema.image.default = postData.image;

      // Open the edit modal and render the form
      $("#edit_post_modal").on('shown.bs.modal', function() {
        $("form#edit_post_form").empty(); // Clear any existing form content

        // Initialize form using jsonForm for edit post
        $("form#edit_post_form").jsonForm({
          schema: postSchema,
          form: postForm,
          onSubmit: function(errors, values) {
            if (errors) {
              alert("Form has errors!");
            } else {
              // Create a new FormData object for the form submission
              let formData = new FormData();

              // Append the form fields data to FormData
              formData.append('id', id);  // Add the post ID
              formData.append('title', values.title); // Add the title field
              formData.append('category', values.category); // Add the category field
              formData.append('body', values.body); // Add the body field

              // Handle image upload (if any)
              let imageFile = $("input[name='image']")[0].files[0]; // Get the file input
              if (imageFile) {
                formData.append('image', imageFile); // Add the image file
              } else {
                // If no new image is selected, append the old image value
                formData.append('old_image', postData.image);
              }

              // Perform the AJAX request to update the post
              $.ajax({
                url: '<?= base_url('post/update') ?>',
                method: 'post',
                data: formData,
                dataType: 'json',
                processData: false,  // Don't process the data
                contentType: false,  // Let jQuery set content-type to multipart/form-data
                success: function(response) {
                  if (response.error) {
                    alert(response.message);
                  } else {
                    $("#edit_post_modal").modal('hide');
                    Swal.fire('Updated', response.message, 'success');
                    fetchAllPosts();
                  }
                }
              });
            }
          }
        });
      });
    }
  });
});

// Trigger form submit manually when Update Post button is clicked
$("#edit_post_btn").on('click', function() {
  // Trigger the form submit by calling jsonForm's onSubmit method
  $("form#edit_post_form").submit();
});

    // Fetch all posts
    function fetchAllPosts() {
      $.ajax({
        url: '<?= base_url('post/fetch') ?>',
        method: 'get',
        success: function(response) {
          $("#show_posts").html(response.message);
        }
      });
    }

    fetchAllPosts();

    // Delete post ajax request
    $(document).delegate('.post_delete_btn', 'click', function(e) {
      e.preventDefault();
      const id = $(this).attr('id');
      Swal.fire({
        title: 'Are you sure?',
        text: "You won't be able to revert this!",
        icon: 'warning',
        showCancelButton: true,
        confirmButtonColor: '#3085d6',
        cancelButtonColor: '#d33',
        confirmButtonText: 'Yes, delete it!'
      }).then((result) => {
        if (result.isConfirmed) {
          $.ajax({
            url: '<?= base_url('post/delete/') ?>/' + id,
            method: 'get',
            success: function(response) {
              Swal.fire(
                'Deleted!',
                response.message,
                'success'
              )
              fetchAllPosts();
            }
          });
        }
      })
    });

    // Post detail ajax request
    $(document).delegate('.post_detail_btn', 'click', function(e) {
      e.preventDefault();
      const id = $(this).attr('id');
      $.ajax({
        url: '<?= base_url('post/detail/') ?>/' + id,
        method: 'get',
        dataType: 'json',
        success: function(response) {
          $("#detail_post_image").attr('src', '<?= base_url('uploads/avatar/') ?>/' + response.message.image);
          $("#detail_post_title").text(response.message.title);
          $("#detail_post_category").text(response.message.category);
          $("#detail_post_body").text(response.message.body);
          $("#detail_post_created").text(response.message.created_at);
        }
      });
    });
  });
</script>

</body>

</html>
