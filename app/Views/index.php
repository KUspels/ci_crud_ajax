<!DOCTYPE html>
<html lang="en">

<head>
  <meta charset="UTF-8">
  <meta http-equiv="X-UA-Compatible" content="IE=edge">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>CRUD App Using CI 4 and Ajax</title>
  <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js" integrity="sha384-ka7Sk0Gln4gmtz2MlQnikT1wXgYsOg+OMhuP+IlRH9sENBO0LRn5q+8nbTov4+1p" crossorigin="anonymous"></script>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-1BmE4kWBq78iYhFldvKuhfTAU6auU8tT94WrHftjDbrCEXSU1oBoqyl2QvZ6jIW3" crossorigin="anonymous">
  <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
  <script src="https://underscorejs.org/underscore-min.js"></script>
  <script src="//cdn.jsdelivr.net/npm/sweetalert2@11"></script>
  <script src="https://cdn.jsdelivr.net/gh/jsonform/jsonform/lib/jsonform.js"></script>
  <script src="https://cdnjs.cloudflare.com/ajax/libs/select2/4.0.13/js/select2.min.js"></script>
  <link href="https://cdnjs.cloudflare.com/ajax/libs/select2/4.0.13/css/select2.min.css" rel="stylesheet" />

  <style>
    .select2-container {
      z-index: 1055 !important;
      /* Higher than Bootstrap's modal z-index */
    }
  </style>
</head>

<body>
  <!-- Modal Form: Add Post -->
  <div class="modal fade" id="add_post_modal" data-bs-backdrop="static" data-bs-keyboard="false" aria-labelledby="staticBackdropLabel" aria-hidden="inert">
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
  <div class="modal fade" id="edit_post_modal" data-bs-backdrop="static" data-bs-keyboard="false" aria-labelledby="staticBackdropLabel" aria-hidden="inert">
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
          type: "array",
          items: {
            type: "string",
            enum: [],
          },
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
          required: true
        }
      };

      const postForm = [{
          key: "title",
          type: "text",
        },
        {
          key: "category",
          type: "select",
          options: [],
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
        $('#jsonform-1-elt-category').select2({
          placeholder: "Select categories",
          allowClear: true,
          dropdownParent: $('#add_post_modal')
        });
        populateCategoryDropdownForForm([]);
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
              formData.append('category', JSON.stringify(values.category));
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

      $(document).on('click', '.post_edit_btn', function () {
    const id = $(this).attr('id'); // Get post ID

    // Fetch the post data based on the ID
    $.ajax({
        url: '<?= base_url('post/edit/') ?>/' + id,
        method: 'get',
        dataType: 'json',
        success: function (response) {
            const postData = response.message; // Data for the selected post
            console.log("Fetched post data:", postData);

            // Populate form dynamically
            $("#edit_post_modal").off('shown.bs.modal').on('shown.bs.modal', function () {
                $('#jsonform-1-elt-category').select2({
                    placeholder: "Select categories",
                    allowClear: true,
                    dropdownParent: $('#edit_post_modal')
                });

                // Clear and initialize the form
                $("form#edit_post_form").empty();

                // Render the form with the post data
                $("form#edit_post_form").jsonForm({
                    schema: postSchema,
                    form: postForm,
                    value: {
                        title: postData.title,
                        category: JSON.parse(postData.category), // Selected categories
                        body: postData.body,
                        image: postData.image
                    },
                    onSubmit: function (errors, values) {
                        if (errors) {
                            alert("Form has errors!");
                        } else {
                            let formData = new FormData();
                            formData.append('id', id); // Add post ID
                            formData.append('title', values.title);
                            formData.append('category', JSON.stringify(values.category));
                            formData.append('body', values.body);

                            // Handle image upload
                            const imageFile = $('#edit_post_form input[type="file"]')[0].files[0];
                            if (imageFile) {
                                formData.append('image', imageFile); // New image
                            } else {
                                formData.append('old_image', postData.image); // Keep old image
                            }

                            // Submit updated post data
                            $.ajax({
                                url: '<?= base_url('post/update') ?>',
                                method: 'post',
                                data: formData,
                                contentType: false,
                                processData: false,
                                dataType: 'json',
                                success: function (response) {
                                    if (response.error) {
                                        alert(response.message);
                                    } else {
                                        $("#edit_post_modal").modal('hide');
                                        Swal.fire('Updated', response.message, 'success');
                                        fetchAllPosts(); // Reload the post list
                                    }
                                }
                            });
                        }
                    }
                });

                // Populate the dropdown after rendering the form
                populateCategoryDropdownForForm(JSON.parse(postData.category));
            });

            // Open the modal
            $("#edit_post_modal").modal('show');
        },
        error: function () {
            alert("Error fetching post data.");
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
    $(document).ready(function() {
      populateCategoryDropdownForForm([]);
    });

    function populateCategoryDropdownForForm(selectedCategories = []) {
      $.ajax({
        url: "<?= base_url('post/getCategories') ?>",
        type: "GET",
        dataType: "json",
        success: function(data) {
          var categoryDropdown = $('[name="category"]');
          categoryDropdown.attr('multiple', 'multiple'); // Add multiple attribute
          categoryDropdown.empty(); // Clear existing options

          // Add placeholder option for Select2
          categoryDropdown.append('<option></option>');

          // Populate dropdown with categories
          if (Array.isArray(data.data)) {
            data.data.forEach(function(category) {
              var option = $('<option>', {
                value: category.id,
                text: category.category_title
              });

              // Pre-select categories if necessary
              if (selectedCategories.includes(category.id)) {
                option.attr('selected', 'selected');
              }

              categoryDropdown.append(option);
            });
          } else {
            console.error("Error: `data.data` is not an array.");
            alert("Failed to load categories.");
          }
          categoryDropdown.trigger('change');
          categoryDropdown.select2().next(".select2-container").css("width", "100%");


          categoryDropdown.select2({
            placeholder: "Select categories", // Placeholder text
            allowClear: true // Enable clearing selections
          });

          console.log("Dropdown initialized with options:", categoryDropdown.html());
        },
        error: function() {
          alert("Error fetching categories from server.");
        }
      });
    }
  </script>

</body>

</html>