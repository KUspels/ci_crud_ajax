const postSchema = {
  type: "object",
  properties: {
    title: {
      title: "Post Title",
      type: "string",
      required: true,
    },
    category: {
      title: "Post Category",
      type: "array",
      items: {
        type: "string",
        enum: [],
      },
      required: true,
    },
    body: {
      title: "Post Body",
      type: "string",
      required: true,
    },
    tags: {
      title: "Tags",
      type: "array",
      items: {
        type: "string",
        title: "Tag",
      },
    },
  },
};

function generateDynamicIdsForTabs(prefix) {
  return {
    type: "fieldset",
    title: "Sections",
    items: [
      {
        type: "tabs",
        id: `${prefix}-navtabs`,
        items: [
          {
            title: "Main",
            type: "tab",
            id: `${prefix}-Main`,
            items: [
              {
                key: "title",
                type: "text",
                name: "title",
              },
              {
                key: "category",
                type: "select",
                name: "category",
                options: [],
              },
              {
                key: "body",
                type: "textarea",
                name: "body",
              },
            ],
          },
          {
            title: "Tags",
            type: "tab",
            id: `${prefix}-Tags`,
            items: ["tags"],
            add: "<a href='#' class='btn btn-outline-dark _jsonform-array-addmore'><b>+ Add Tag</b></a>",
            remove:
              "<a href='#' class='btn btn-outline-danger _jsonform-array-delete'><b>- Remove Tag</b></a>",
          },
          {
            title: "Pictures",
            type: "tab",
            id: `${prefix}-Pictures`,
            items: [
              {
                type: "help",
                helpvalue: `
                  <div id="${prefix}-image-upload-section" class="mt-4">
                    <label for="${prefix}-image-input" class="form-label">Upload Images:</label>
                    <input
                      type="file"
                      id="${prefix}-image-input"
                      name="images[]"
                      multiple
                      accept="image/*"
                      class="form-control" />
                    <div id="${prefix}-image-preview-container" class="mt-3"></div>
                  </div>
                `,
              },
            ],
          },
        ],
      },
    ],
  };
}
