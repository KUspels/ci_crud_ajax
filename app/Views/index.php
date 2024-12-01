<!DOCTYPE html>
<html lang="en">

<head>
  <meta charset="UTF-8">
  <meta http-equiv="X-UA-Compatible" content="IE=edge">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>CRUD App Using CI 4 and Ajax</title>
  <!-- Style -->
  <link rel="stylesheet" href="<?= base_url('CSS/style.css') ?>">

  <!-- jQuery -->
  <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>

  <!-- Bootstrap 5 CSS -->
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">

  <!-- Font Awesome -->
  <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.3/css/all.min.css" rel="stylesheet">

  <!-- Select2 -->
  <link href="https://cdnjs.cloudflare.com/ajax/libs/select2/4.0.13/css/select2.min.css" rel="stylesheet">
  <script src="https://cdnjs.cloudflare.com/ajax/libs/select2/4.0.13/js/select2.min.js"></script>

  <!-- Underscore.js -->
  <script src="https://underscorejs.org/underscore-min.js"></script>

  <!-- JSONForm -->
  <script src="https://cdn.jsdelivr.net/gh/jsonform/jsonform/lib/jsonform.js"></script>

  <!-- SweetAlert2 -->
  <script src="//cdn.jsdelivr.net/npm/sweetalert2@11"></script>

  <!-- Bootstrap 5 JS -->
  <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>




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

      // JSONform SCHEMA

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
        tags: {
          title: "Tags",
          type: "array",
          items: {
            type: "string",
            title: "Tag"
          }
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
          key: "tags",
          type: "array",
          items: [{
            key: "tags[]",
            type: "text",
            title: "Tag"
          }],
          add: "<a href='#' class='btn btn-outline-dark _jsonform-array-addmore'><b>+ Add Tag</b></a>",
          remove: "<a href='#' class='btn btn-outline-danger _jsonform-array-delete'><b>- Remove Tag</b></a>"


        },
        {
          key: "image",
          type: "file",
          accept: ".jpg,.jpeg,.png",
        },
      ];

      //ADD POST

      // Load form dynamically for Add Post
      $("#add_post_modal").off('shown.bs.modal').on('shown.bs.modal', async function() {
        // Reset the form
        $(this).find('form')[0].reset();
        $('#categoryDropdown').empty();
        console.log("Modal initialized and form should render");
        await populateCategoryDropdownForForm([]);
        $('#jsonform-1-elt-category').select2({
          placeholder: "Select categories",
          allowClear: true,
          multiple: true,
          dropdownParent: $('#add_post_modal')
        }).trigger('change');


        console.log("Modal initialized and form should render");

        // Initialize form using jsonForm for add post
        $("form#add_post_form").empty().jsonForm({
          schema: postSchema,
          form: postForm,
          onSubmit: function(errors, values) {
            if (errors) {
              alert("Form has errors!");
            } else {
              let formData = new FormData();
              formData.append('title', values.title);
              formData.append('category', JSON.stringify($('#jsonform-1-elt-category').val() || [])); // Always encode as JSON
              formData.append('body', values.body);
              formData.append('tags', JSON.stringify(values.tags));
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

      //EDIT POST

      $(document).on('click', '.post_edit_btn', function() {
        const id = $(this).attr('id'); // Get post ID

        // Fetch the post data based on the ID
        $.ajax({
          url: '<?= base_url('post/edit/') ?>/' + id,
          method: 'get',
          dataType: 'json',
          success: function(response) {
            const postData = response.message; // Data for the selected post
            console.log("Fetched post data:", postData);

            // Populate form dynamically
            $("#edit_post_modal").off('shown.bs.modal').on('shown.bs.modal', function() {
              $('#jsonform-1-elt-category').select2({
                placeholder: "Select categories",
                allowClear: true,
                dropdownParent: $('#edit_post_modal')
              });
              const categoryDropdown = $('#jsonform-1-elt-category');
              populateCategoryDropdownForForm(postData.category || []); // Initialize with post data categories
              // Clear and initialize the form
              $("form#edit_post_form").empty();

              // Render the form with the post data
              $("form#edit_post_form").jsonForm({
                schema: postSchema,
                form: postForm,
                value: {
                  title: postData.title,
                  category: (() => {
                    if (Array.isArray(postData.category)) {
                      return postData.category;
                    }
                    try {
                      return JSON.parse(postData.category || "[]"); // Parse valid JSON or default to an empty array
                    } catch (error) {
                      console.error("Invalid JSON for categories:", postData.category);
                      return []; // Default to an empty array on error
                    }
                  })(),

                  body: postData.body,
                  tags: Array.isArray(postData.tags) ?
                    postData.tags : JSON.parse(postData.tags || "[]"),
                  image: postData.image
                },
                onSubmit: function(errors, values) {
                  if (errors) {
                    alert("Form has errors!");
                  } else {
                    let formData = new FormData();
                    formData.append('id', id); // Add post ID
                    formData.append('title', values.title);
                    const category = Array.isArray(values.category) ? values.category : [];
                    formData.append('category', JSON.stringify($('#jsonform-1-elt-category').val() || [])); // Send as JSON array
                    formData.append('body', values.body);
                    const tags = Array.isArray(values.tags) ? values.category : [];
                    formData.append('tags', JSON.stringify(tags));

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
                      success: function(response) {
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
          error: function() {
            alert("Error fetching post data.");
          }
        });
      });

      // Trigger form submit manually when Update Post button is clicked
      $("#edit_post_btn").on('click', function() {
        // Trigger the form submit by calling jsonForm's onSubmit method
        $("form#edit_post_form").submit();
      });

      // FETCH ALL POSTS
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

      // DELETE POST AJAX REQUEST

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

      // POST DETAIL AJAX REQUEST

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

    // POPULATE CATEGORY DROPDOWN

    function populateCategoryDropdownForForm(selectedCategories = []) {
      console.log("populateCategoryDropdownForForm called");

      // Clear and parse selected categories, ensuring it's always an array
      if (!Array.isArray(selectedCategories)) {
        try {
          selectedCategories = JSON.parse(selectedCategories) || [];
        } catch (error) {
          console.error("Error parsing selected categories:", error);
          selectedCategories = [];
        }
      }

      // AJAX call to fetch categories
      $.ajax({
        url: "<?= base_url('post/getCategories') ?>", // Correct endpoint
        type: "GET",
        dataType: "json",
        success: function(data) {
          let categoryDropdown = $('#jsonform-1-elt-category');

          // Prepare dropdown for multiple selections and clear existing options
          categoryDropdown.attr('multiple', 'multiple').empty();

          // Add a placeholder option for Select2
          categoryDropdown.append('<option></option>');

          // Populate dropdown with fetched categories
          if (Array.isArray(data.data)) {
            data.data.forEach(function(category) {
              let option = $('<option>', {
                value: category.id.toString(), // Ensure value is a string
                text: category.category_title
              });

              // Pre-select categories if necessary
              if (selectedCategories.includes(category.id.toString())) {
                option.prop('selected', true);
              }

              categoryDropdown.append(option);
            });
          } else {
            console.error("Error: `data.data` is not an array.");
            alert("Failed to load categories.");
          }

          // Reinitialize Select2 with options
          categoryDropdown.select2({
            placeholder: "Select categories", // Placeholder text
            allowClear: true // Enable clearing selections
          }).trigger('change');

          // Ensure the Select2 container adjusts to full width
          categoryDropdown.next(".select2-container").css("width", "100%");

          console.log("Dropdown initialized with options:", categoryDropdown.html());
        },
        error: function() {
          console.error("Error fetching categories from server.");
          alert("Error fetching categories. Please try again later.");
        }
      });
    }
  </script>

</body>

</html>