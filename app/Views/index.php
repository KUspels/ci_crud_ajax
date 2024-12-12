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

  <!-- Include Utils.js -->
  <script src="<?= base_url('JS/utils.js') ?>"></script>

  <!-- Include Schema.js -->
  <script src="<?= base_url('JS/schema.js') ?>"></script>

  <script>
    const BASE_URL = "<?= base_url() ?>";
  </script>

</head>

<body>
  <!-- ADD POST MODAL -->
  <div class="modal fade" id="add_post_modal" data-bs-backdrop="static" data-bs-keyboard="false" aria-labelledby="staticBackdropLabel" aria-hidden="inert">
    <div class="modal-dialog modal-dialog-centered modal-lg">
      <div class="modal-content">
        <div class="modal-header">
          <h5 class="modal-title">Add New Post</h5>
          <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
        </div>
        <div class="modal-body p-5">
          <form id="add_post_form" enctype="multipart/form-data">
            <div id="add-jsonform-render-container"></div>
          </form>
        </div>
        <div class="modal-footer">
          <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
          <button type="button" class="btn btn-primary" id="add_post_btn">Add Post</button>
        </div>
      </div>
    </div>
  </div>

  <!-- EDIT POST MODAL -->
  <div class="modal fade" id="edit_post_modal" data-bs-backdrop="static" data-bs-keyboard="false" tabindex="-1" aria-labelledby="staticBackdropLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-lg">
      <div class="modal-content">
        <div class="modal-header">
          <h5 class="modal-title">Edit Post</h5>
          <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
        </div>
        <div class="modal-body">
          <form id="edit_post_form" enctype="multipart/form-data">
            <div id="edit-jsonform-render-container"></div>
          </form>
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
          <h3 id="detail_post_title" class="mt-3"></h3>
          <div id="detail_post_category" class="mb-3"></div>
          <div id="detail_post_tags" class="mb-3"></div>
          <div id="detail_post_images" class="mb-3"></div>
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

    // FETCH ALL POSTS
    // Fetches all posts when the page is loaded
    fetchAllPosts('<?= base_url('post/fetch') ?>');

    // ADD POST
    // Array to store all files to be uploaded for a new post
    let allFiles = [];

    // Initialize the form for adding a new post when the modal is shown
    $("#add_post_modal").on('shown.bs.modal', function() {
      initializeForm("add", allFiles);
    });

    // Handle the submission of the "Add Post" form
    $("#add_post_btn").off('click').on('click', function(e) {
      e.preventDefault();

      let formData = new FormData(document.getElementById('add_post_form'));

      // Collect input values for the post
      const title = $('[name="title"]').val();
      const body = $('[name="body"]').val();
      const categories = $('[name="category"]').val() || [];
      const tags = collectTags('input[name^="tags"]');

      // Ensure title is added
      if (!title.trim()) {
        alert("Please provide a title for the post.");
        return; 
      }

      // Ensure body is added
      if (!body.trim()) {
        alert("Please provide a body for the post.");
        return; 
      }

      // Ensure at least one category is uploaded
      if (categories.length === 0) {
        alert("Please select at least one category.");
        return; 
      }

      // Ensure at least one tag is uploaded
      if (tags.length === 0) {
        alert("Please add at least one tag.");
        return;
      }

      formData.append('title', title);
      formData.append('body', body);
      formData.append('category', JSON.stringify(categories));
      formData.append('tags', JSON.stringify(tags));

      // Ensure at least one image is uploaded
      if (allFiles.length === 0) {
        alert("Please upload at least one image.");
        return;
      }

      // Add files and their details to FormData
      allFiles.forEach(fileObj => formData.append('images[]', fileObj.file));

      let images = [];
      allFiles.forEach(fileObj => {
        const previewItem = $(`.add-image-preview-item[data-file-id='${fileObj.id}']`);
        const titleValue = previewItem.find('input[name^="new_image_titles"]').val() || "";
        images.push({
          fileName: fileObj.file.name,
          title: titleValue
        });
      });
      formData.append('imagesData', JSON.stringify(images));

      // Send the form data via AJAX
      sendAjaxRequest(
        '<?= base_url("post/add") ?>',
        'POST',
        formData,
        (response) => {
          Swal.fire('Success', response.message, 'success');
          $("#add_post_modal").modal('hide');
          fetchAllPosts('<?= base_url('post/fetch') ?>'); // Refresh post list
        },
        (xhr, status, error) => {
          console.error("AJAX error:", status, error);
        }
      );
    });

    // ------------------------------------------------------------------------------------------------------------------------------
    // EDIT POST
    // Arrays to manage files for editing posts
    let editAllFiles = [];
    let removedOldFiles = [];

    // Fetch post data and initialize the "Edit Post" modal
    $(document).on("click", ".post_edit_btn", function(e) {
      e.preventDefault();
      removedOldFiles.length = 0; // Reset removed files list

      const postId = $(this).attr("id");

      // Fetch post details via AJAX
      $.ajax({
        url: `<?= base_url('post/edit/') ?>/${postId}`,
        method: "GET",
        dataType: "json",
        success: function(response) {
          if (!response.error) {
            $("#edit_post_modal").data("post-id", response.message.id);
            initializeForm("edit", editAllFiles, response.message); // Initialize edit form
            $("#edit_post_modal").modal("show");
          } else {
            console.error("Failed to fetch post data:", response.message);
            Swal.fire("Error", "Failed to fetch post data", "error");
          }
        },
        error: function(xhr, status, error) {
          console.error("Error fetching post data:", status, error);
          Swal.fire("Error", "An error occurred while fetching post data", "error");
        },
      });

      // Handle "Mark for Deletion" checkbox for existing images
      $(document).on("change", ".mark-for-deletion", function() {
        const fileId = $(this).data("file-id");
        if ($(this).is(":checked")) {
          if (!removedOldFiles.includes(fileId)) {
            removedOldFiles.push(fileId); // Add to removed files list
          }
        } else {
          removedOldFiles = removedOldFiles.filter((id) => id !== fileId); // Remove from removed files list
        }
      });
    });

    // Handle the submission of the "Edit Post" form
    $(document).on('click', '#edit_post_btn', function(e) {
      e.preventDefault();

      const postId = $('#edit_post_modal').data('post-id');
      if (!postId) return;

      let formData = new FormData();

      // Collect updated values for the post
      const title = $('#edit_post_modal').find('[name="title"]').val();
      const body = $('#edit_post_modal').find('[name="body"]').val();
      const categories = $('#edit_post_modal').find('[name="category"]').val() || [];
      const tags = collectTags('input[name^="tags"]');

      // Ensure title is added
      if (!title.trim()) {
        alert("Please provide a title for the post.");
        return; 
      }

      // Ensure body is added
      if (!body.trim()) {
        alert("Please provide a body for the post.");
        return; 
      }

      // Ensure at least one category is uploaded
      if (categories.length === 0) {
        alert("Please select at least one category.");
        return; 
      }

      // Ensure at least one tag is uploaded
      if (tags.length === 0) {
        alert("Please add at least one tag.");
        return;
      }

      formData.append('id', postId);
      formData.append('title', title);
      formData.append('body', body);
      formData.append('category', JSON.stringify(categories));
      formData.append('tags', JSON.stringify(tags));

      // Process existing images that are not marked for deletion
      let existingImages = [];
      $('.edit-image-preview-item[data-file-id]').each(function() {
        if ($(this).hasClass('new-image-preview-item')) {
          return; // Skip new images
        }

        const fileId = $(this).data('file-id');
        const title = $(this).find('input[type="text"]').val();

        if (fileId && !removedOldFiles.includes(fileId)) {
          existingImages.push({
            fileName: fileId,
            title: title
          });
        }
      });

      formData.append('existingImages', JSON.stringify(existingImages));
      formData.append('removedOldFiles', JSON.stringify(removedOldFiles));

      // Add new images and their titles
      editAllFiles.forEach(({ id, file }) => {
        formData.append(`newImages[${id}]`, file);
        const titleInput = $(`input[name="new_image_titles[${id}]"]`).val() || '';
        formData.append(`new_image_titles[${id}]`, titleInput);
      });

      // Submit the updated post data via AJAX
      sendAjaxRequest(
        '<?= base_url("post/update") ?>',
        'POST',
        formData,
        (response) => {
          Swal.fire('Updated', response.message, 'success');
          $("#edit_post_modal").modal('hide');
          fetchAllPosts('<?= base_url('post/fetch') ?>'); // Refresh post list
        },
        (xhr, status, error) => {
          console.error("AJAX error:", status, error);
        }
      );
    });

    // ------------------------------------------------------------------------------------------------------------------------------
    // DELETE POST AJAX REQUEST
    // Handle post deletion with confirmation
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
              Swal.fire('Deleted!', response.message, 'success');
              fetchAllPosts('<?= base_url('post/fetch') ?>'); // Refresh post list
            }
          });
        }
      });
    });

    // ------------------------------------------------------------------------------------------------------------------------------
    // POST DETAIL AJAX REQUEST
    // Handle displaying post details in a modal
    $(document).delegate('.post_detail_btn', 'click', function(e) {
      e.preventDefault();
      const id = $(this).attr('id');

      $.ajax({
        url: '<?= base_url('post/detail/') ?>/' + id,
        method: 'get',
        dataType: 'json',
        success: function(response) {
          if (!response.error) {
            const post = response.message;

            // Update modal with post details
            $("#detail_post_title").text(post.title);
            $("#detail_post_body").text(post.body);
            $("#detail_post_created").text(post.created_at);

            // Display categories and tags
            let categoriesHtml = '';
            if (post.category && Array.isArray(post.category)) {
              post.category.forEach(category => {
                categoriesHtml += `<span class="badge bg-primary me-1">${category}</span>`;
              });
            } else {
              categoriesHtml = '<p class="text-muted">No categories available.</p>';
            }
            $("#detail_post_category").html(categoriesHtml);

            let tagsHtml = '';
            if (post.tags && Array.isArray(post.tags)) {
              post.tags.forEach(tag => {
                tagsHtml += `<span class="badge bg-secondary me-1">${tag}</span>`;
              });
            } else {
              tagsHtml = '<p class="text-muted">No tags available.</p>';
            }
            $("#detail_post_tags").html(tagsHtml);

            // Display images
            const imagesContainer = $("#detail_post_images");
            imagesContainer.empty();

            if (post.image && Array.isArray(post.image)) {
              post.image.forEach(file => {
                const baseUrl = '<?= base_url() ?>';
                const imageElement = `
                  <div class="mb-2">
                    <img src="${baseUrl}/uploads/avatar/${file.fileName}" style="width: 100px; height: 100px; object-fit: cover;" alt="${file.title}" />
                    <p>${file.title}</p>
                  </div>
                `;
                imagesContainer.append(imageElement);
              });
            } else {
              imagesContainer.html('<p class="text-muted">No images available.</p>');
            }
          } else {
            alert('Failed to load post details!');
          }
        },
        error: function() {
          alert('An error occurred while fetching the post details.');
        }
      });
    });
  });
</script>

</body>
</html>