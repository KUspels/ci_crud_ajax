<!DOCTYPE html>
<html>
  <head>
    <meta charset="utf-8" />
    <title>JSON Form</title>
    <link
      rel="stylesheet"
      href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css"
    />
  </head>
  <body>
    <h1>Getting started with JSON Form</h1>
    <form></form>
    <div id="res" class="alert"></div>
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script src="https://underscorejs.org/underscore-min.js"></script>
    <script src="https://cdn.jsdelivr.net/gh/jsonform/jsonform/lib/jsonform.js"></script>
    <script>
      function handleFileUpload(event) {
        const file = event.target.files[0];
        console.log("File selected:", file.name); // Example: Log file name
      }

      $("form").jsonForm({
        schema: {
          title: {
            title: "Post Title",
            type: "string",
            required: true,
          },
          category: {
            title: "Post Category",
            type: "string",
            required: true,
          },
          file_content: {
            title: "Post Body",
            type: "string",
            required: true,
          },
          upload: {
            title: "Post Image",
            type: "string",
            required: true,
          },
        },
        form: [
          {
            key: "title",
            type: "text",
          },
          {
            key: "category",
            type: "text",
          },
          {
            key: "file_content",
            type: "textarea",
          },
          {
            key: "upload",
            type: "file",
            accept: ".jpg,.jpeg,.png",
            onChange: "handleFileUpload",
          },
          {
            type: "submit",
            title: "Add Post",
          },
        ],
        onSubmit: function (errors, values) {
          if (errors) {
            $("#res").html(
              "<p>There are errors in the form. Please fix them.</p>"
            );
          } else {
            $("#res").html(
              "<p>Post added successfully!<br/>" +
                "Title: " +
                values.title +
                "<br/>" +
                "Category: " +
                values.category +
                "<br/>" +
                "Body: " +
                values.file_content +
                "<br/>" +
                "Image: " +
                values.upload +
                "</p>"
            );
          }
        },
      });
    </script>
  </body>
</html>
