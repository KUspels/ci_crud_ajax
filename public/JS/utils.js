// utils.js

// MODAL INIT
// Function to initialize the form, reset inputs, set up JSONform configuration, and populate categories and images.
function initializeForm(prefix, fileArray, postData = null) {
  // Selectors for form and preview containers
  const formContainerSelector = `#${prefix}-jsonform-render-container`;
  const previewContainerSelector = `#${prefix}-image-preview-container`;
  const formSelector = `#${prefix}_post_form`;
  const imageInputSelector = `#${prefix}-image-input`;

  // Reset form inputs and file tracking arrays
  $('input[name^="tags"]').remove();
  $(formContainerSelector).empty();
  $(formSelector)[0].reset();
  fileArray.length = 0;

  // JSONform config setup
  const jsonFormConfig = {
    schema: postSchema,
    form: generateDynamicIdsForTabs(prefix),
    onSubmit: function (errors, values) {
      if (errors) {
        console.error("Form submission errors:", errors);
        alert("Form has errors!");
      } else {
        console.log("Form values submitted:", values);
      }
    },
  };

  // Populate form values for editing
  if (prefix === "edit" && postData) {
    jsonFormConfig.value = {
      title: postData.title || "",
      category: postData.category || [],
      body: postData.body || "",
      tags: postData.tags || [],
    };
  }

  // Initialize JSONform
  $(formContainerSelector).jsonForm(jsonFormConfig);

  // Populate category dropdown
  populateCategoryDropdownForForm(
    postData?.category || [],
    '[name="category"]',
    `${BASE_URL}/post/getCategories`
  );

  // Initialize select2 for category selection
  $('[name="category"]')
    .select2({
      placeholder: "Select categories",
      allowClear: true,
      multiple: true,
      dropdownParent: $(`#${prefix}_post_modal`),
    })
    .val(postData?.category || [])
    .trigger("change");

  // Add image previews for editing
  if (prefix === "edit" && postData?.image && Array.isArray(postData.image)) {
    postData.image.forEach((file) => {
      const fileTitle = file.title || "";
      const imageHtml = `
        <div class="edit-image-preview-item d-flex align-items-center mb-2" data-file-id="${file.fileName}">
          <img src="${BASE_URL}/uploads/avatar/${file.fileName}" 
               style="width: 100px; height: 100px; object-fit: cover;" class="me-2">
          <input type="text" name="edit_image_titles[${file.fileName}]" value="${fileTitle}" 
                 placeholder="Enter image title" class="form-control me-2" />
          <label class="form-check-label me-2">
            <input type="checkbox" class="form-check-input mark-for-deletion" data-file-id="${file.fileName}">
            Mark for deletion
          </label>
        </div>`;
      $(previewContainerSelector).append(imageHtml);
    });
  }

  // Set up image input handler for adding new images
  setupImageInputHandler(prefix, fileArray, previewContainerSelector);
}

// IMAGE INPUT HANDLING
// Function to handle image input changes, previews, and adding/removing files.
function setupImageInputHandler(prefix, fileArray, previewContainerSelector) {
  const imageInputSelector = `#${prefix}-image-input`;

  // Reset the change handler to avoid duplicate bindings
  $(imageInputSelector)
    .off("change")
    .on("change", function (event) {
      const files = event.target.files;

      Array.from(files).forEach((file) => {
        const uniqueId = `file_${Date.now()}_${Math.random()
          .toString(36)
          .substr(2, 9)}`;
        fileArray.push({ id: uniqueId, file });

        const reader = new FileReader();
        reader.onload = function (e) {
          // Create a preview item for the uploaded image
          const previewItem = $("<div>")
            .addClass(`${prefix}-image-preview-item new-image-preview-item`)
            .attr("data-file-id", uniqueId)
            .css({
              display: "flex",
              alignItems: "center",
              marginBottom: "10px",
            });

          const imgName = $("<div>").text(file.name).css({
            marginRight: "10px",
            maxWidth: "140px",
            overflow: "hidden",
            textOverflow: "ellipsis",
            whiteSpace: "nowrap",
          });

          const imgElement = $("<img>").attr("src", e.target.result).css({
            width: "100px",
            height: "100px",
            marginRight: "10px",
          });

          const titleInput = $("<input>")
            .attr({
              type: "text",
              name: `new_image_titles[${uniqueId}]`,
              placeholder: "Enter image title",
              class: "form-control",
            })
            .css({ flex: "1", marginRight: "10px" });

          const removeButton = $("<button>")
            .addClass("btn btn-danger")
            .text("Remove")
            .css({ marginLeft: "10px" })
            .on("click", function () {
              fileArray.splice(
                fileArray.findIndex((f) => f.id === uniqueId),
                1
              );
              $(this).closest(`.${prefix}-image-preview-item`).remove();
            });

          previewItem.append(imgName, imgElement, titleInput, removeButton);
          $(previewContainerSelector).append(previewItem);
        };
        reader.readAsDataURL(file);
      });

      // Clear the input after handling files
      $(imageInputSelector).val("");
    });
}

// TAGS
// Collects tags from input fields into an array.
function collectTags(inputSelector) {
  let tags = [];
  $(inputSelector).each(function () {
    const tagValue = $(this).val();
    if (tagValue) {
      tags.push(tagValue);
    }
  });
  return tags;
}

// AJAX
function sendAjaxRequest(url, method, data, onSuccess, onError) {
  $.ajax({
    url: url,
    method: method,
    data: data,
    contentType: false,
    processData: false,
    dataType: "json",
    success: onSuccess,
    error: onError,
  });
}

// FETCH ALL POSTS
// Fetches all posts from the server and updates the UI with the response.
function fetchAllPosts(base_url) {
  sendAjaxRequest(
    base_url,
    "GET",
    null,
    function (response) {
      $("#show_posts").html(response.message);
    },
    function (xhr, status, error) {
      console.error("AJAX error:", status, error);
    }
  );
}

// POPULATE CATEGORY DROPDOWN
// Populates a category dropdown with data fetched from the server.
function populateCategoryDropdownForForm(
  selectedCategories = [],
  dropdownSelector,
  base_url,
  callback
) {
  if (!Array.isArray(selectedCategories)) {
    try {
      selectedCategories = JSON.parse(selectedCategories) || [];
    } catch (error) {
      console.error("Error parsing selected categories:", error);
      selectedCategories = [];
    }
  }

  // Fetch categories via AJAX
  $.ajax({
    url: base_url,
    type: "GET",
    dataType: "json",
    success: function (data) {
      const categoryDropdown = $(dropdownSelector);
      categoryDropdown.attr("multiple", "multiple").empty();

      // Populate dropdown with categories
      if (Array.isArray(data.data)) {
        data.data.forEach((category) => {
          let option = $("<option>", {
            value: category.id.toString(),
            text: category.category_title,
          });

          if (selectedCategories.map(String).includes(category.id.toString())) {
            option.prop("selected", true);
          }

          categoryDropdown.append(option);
        });
      } else {
        console.error("Error: `data.data` is not an array.");
        alert("Failed to load categories.");
      }

      // Initialize select2 and trigger changes
      categoryDropdown
        .select2({
          placeholder: "Select categories",
          allowClear: true,
        })
        .trigger("change");
      categoryDropdown
        .select2()
        .next(".select2-container")
        .css("width", "100%");

      if (callback) callback();
    },
    error: function () {
      console.error("Error fetching categories from server.");
      alert("Error fetching categories. Please try again later.");
    },
  });
}
