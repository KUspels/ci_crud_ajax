// EDIT POST
let editAllFiles = []; // To hold the files for editing

$("#edit_post_modal").off('shown.bs.modal').on('shown.bs.modal', async function () {
    console.log("Edit Post Modal opened");

    // Clear form and image previews
    $("form#edit_post_form")[0].reset();
    $('#edit-image-preview-container').empty();
    editAllFiles = []; // Reset previously uploaded files

    try {
        // Get the post ID from modal data
        const postId = $(this).data('post-id');
        if (!postId) {
            console.error("Post ID is not defined.");
            return;
        }

        // Fetch the post data using the new fetchPostData function
        const postData = await fetchPostData(postId);
        if (!postData) {
            console.error("Post data could not be fetched.");
            return;
        }
        console.log("Fetched post data:", postData);

        // Render JSONForm with fetched data
        $("#edit-jsonform-render-container").jsonForm({
            schema: postSchema,
            form: postForm,
            value: {
                title: postData.title,
                category: postData.category,
                body: postData.body,
                tags: postData.tags
            },
            onSubmit: function (errors, values) {
                if (errors) {
                    console.error("Form submission errors:", errors);
                    alert("Form has errors!");
                } else {
                    console.log("Form values:", values);
                }
            }
        });

        // Populate categories and initialize Select2 AFTER JSONForm is rendered
        await populateCategoryDropdownForForm([]);
        $('#jsonform-1-elt-category').select2({
            placeholder: "Select categories",
            allowClear: true,
            multiple: true,
            dropdownParent: $('#edit_post_modal')
        }).val(postData.category).trigger('change'); // Set the categories after dropdown initialization

        // Populate existing images (if any)
        if (postData.image && Array.isArray(postData.image)) {
            postData.image.forEach((file) => {
                const fileTitle = file.title || '';
                const imageHtml = `
                    <div class="image-preview-item d-flex align-items-center mb-2">
                        <img src="<?= base_url('uploads/avatar/') ?>/${file.fileName}" style="width: 100px; height: 100px; object-fit: cover;" class="me-2">
                        <input type="text" name="edit_image_titles[${file.fileName}]" value="${fileTitle}" placeholder="Enter image title" class="form-control me-2" />
                        <button type="button" class="btn btn-danger remove-image-btn" data-file-name="${file.fileName}">Remove</button>
                    </div>
                `;
                $('#edit-image-preview-container').append(imageHtml);
            });
        }

        console.log("JSONForm rendered for Edit Post");
    } catch (error) {
        console.error("Error initializing Edit Post modal:", error);
    }
});

// Edit Image Input Change Event
$('#edit-image-input').on('change', function (event) {
    const files = event.target.files;

    // Add newly selected files to the editAllFiles array
    Array.from(files).forEach(file => {
        editAllFiles.push(file);
    });

    // Generate the preview for each image
    const previewContainer = $('#edit-image-preview-container');
    previewContainer.empty(); // Clear existing previews

    editAllFiles.forEach((file, index) => {
        const reader = new FileReader();
        reader.onload = function (e) {
            const previewItem = $('<div>')
                .addClass('edit-image-preview-item')
                .attr('data-file-index', index)
                .css({
                    display: "flex",
                    alignItems: "center",
                    marginBottom: "10px"
                });

            // Add img name
            const imgName = $('<div>').text(file.name).css({
                marginRight: '10px',
                maxWidth: '140px',
                overflow: 'hidden',
                textOverflow: 'ellipsis',
                whiteSpace: 'nowrap'
            });

            // Add image preview
            const imgElement = $('<img>').attr('src', e.target.result).css({
                width: "100px",
                height: "100px",
                marginRight: "10px"
            });

            // Add image title input
            const titleInput = $('<input>').attr({
                type: "text",
                name: `edit_image_titles[${index}]`,
                placeholder: "Enter image title",
                class: "form-control"
            }).css({
                flex: "1",
                marginRight: "10px"
            });

            // Add remove button
            const removeButton = $('<button>').addClass('btn btn-danger').text('Remove').css({
                marginLeft: "10px"
            });

            // Remove the preview item on click
            removeButton.on('click', function () {
                const itemIndex = $(this).closest('.edit-image-preview-item').data('file-index');
                editAllFiles.splice(itemIndex, 1); // Remove from files array
                $(this).closest('.edit-image-preview-item').remove(); // Remove from DOM
            });

            // Append elements to the preview container
            previewItem.append(imgName, imgElement, titleInput, removeButton);
            previewContainer.append(previewItem);
        };
        reader.readAsDataURL(file);
    });

    // Clear the file input to prevent duplicates
    $('#edit-image-input').val('');
});

// Edit Post Form Submit Handler
$(document).delegate('.post_edit_btn', 'click', function (e) {
    e.preventDefault();
    console.log("Edit Post button clicked, collecting JSONForm and image data...");

    const postId = $(this).attr('id'); // Assuming the button has the post ID as its `id` attribute
    $('#edit_post_modal').data('post-id', postId).modal('show'); // Set the post ID and open modal

    // Collect data from JSONForm
    let jsonFormData = {};
    $("form#edit_post_form").jsonForm({
        schema: postSchema,
        form: postForm,
        onSubmit: function (errors, values) {
            if (errors) {
                console.error("Form submission errors:", errors);
                alert("Form has errors!");
                return;
            } else {
                jsonFormData = values;
                console.log("JSONForm data collected:", jsonFormData);
            }
        },
        onSubmitValid: function (values) {
            jsonFormData = values;
        }
    });

    // Create FormData object for form data
    let formData = new FormData();

    // Append JSONForm data
    formData.append('title', jsonFormData.title);
    formData.append('body', jsonFormData.body);
    const categories = $('#jsonform-1-elt-category').val() || [];
    formData.append('category', JSON.stringify(jsonFormData.category));

    // Collect tags
    let tags = [];
    $('input[name^="tags"]').each(function() {
        let tagValue = $(this).val();
        if (tagValue) {
            tags.push(tagValue);
        }
    });
    console.log("Tags collected:", tags); // Debugging log to check tags
    formData.append('tags', JSON.stringify(tags)); // Store tags as a JSON array

    
        // Validation using allFiles
        if (allFiles.length === 0) {
          alert("Please upload at least one image.");
          return;
        }
        allFiles.forEach((file) => {
          formData.append('images[]', file);
        });


    // Append Images and Metadata
    let images = [];
    editAllFiles.forEach((file, index) => {
        const previewItem = $(`.image-preview-item[data-file-name='${file.name}']`);
        const titleInput = $(`input[name="edit_image_titles[${index}]"]`);
        const titleValue = titleInput.val() || "";

        images.push({
            'fileName': file.name,
            'title': titleValue
        });

        // Append each file to FormData
        formData.append('images[]', file);
    });

    // Append image metadata
    formData.append('imagesData', JSON.stringify(images));

    // AJAX request to submit the form
    $.ajax({
        url: '<?= base_url('post/update') ?>',
        method: 'post',
        data: formData,
        contentType: false,
        processData: false,
        dataType: 'json',
        success: function (response) {
            console.log("AJAX success:", response);
            if (response.error) {
                console.error("Server-side error:", response.message);
                alert(response.message);
            } else {
                console.log("Post updated successfully:", response.message);
                $("#edit_post_modal").modal('hide');
                Swal.fire('Updated', response.message, 'success');
                fetchAllPosts();
            }
        },
        error: function (xhr, status, error) {
            console.error("AJAX error:", status, error);
            alert("Failed to update post. Please try again.");
        }
    });
});