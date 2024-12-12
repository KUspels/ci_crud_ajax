<!-- app/Views/posts_list.php -->
<?php if ($posts): ?>
    <?php foreach ($posts as $post): ?>
        <div class="col-md-4">
            <div class="card shadow-sm style=cursor: pointer;">
                <div class="card-body post_detail_btn" id="<?= $post['id']; ?>" data-bs-toggle="modal" data-bs-target="#detail_post_modal">
                    <!-- Post Title -->
                    <h5 class="card-title"><?= esc($post['title']); ?></h5>

                    <!-- Display Images and Titles -->
                    <div class="mb-3">
                        <?php
                        $fileData = is_string($post['image']) ? json_decode($post['image'], true) : $post['image'];
                        if ($fileData && is_array($fileData)):
                            foreach ($fileData as $file):
                                $imagePath = base_url('uploads/avatar/' . esc($file['fileName'])); ?>
                                <div class="d-flex align-items-center mb-2">
                                    <img src="<?= $imagePath; ?>"
                                         alt="<?= esc($file['title']); ?>"
                                         style="width: 50px; height: 50px; object-fit: cover;"
                                         class="me-2">
                                    <span><?= esc($file['title']); ?></span>
                                </div>
                            <?php endforeach;
                        else: ?>
                            <p class="text-muted">No images available.</p>
                        <?php endif; ?>
                    </div>
                    <!-- Display Categories -->
                    <?php if ($post['category'] && is_array($post['category'])): ?>
                        <div class="mb-2">
                            <strong>Categories:</strong>
                            <?php foreach ($post['category'] as $category): ?>
                                <span class="badge bg-primary"><?= esc($category); ?></span>
                            <?php endforeach; ?>
                        </div>
                    <?php endif; ?>

                    <!-- Display Tags -->
                    <?php if ($post['tags'] && is_array($post['tags'])): ?>
                        <div class="mb-2">
                            <strong>Tags:</strong>
                            <?php foreach ($post['tags'] as $tag): ?>
                                <span class="badge bg-secondary"><?= esc($tag); ?></span>
                            <?php endforeach; ?>
                        </div>
                    <?php endif; ?>
                    <!-- Post Body -->
                    <p><?= esc(substr($post['body'], 0, 80)); ?>...</p>
                </div>

                <!-- Footer with Metadata -->
                <div class="card-footer d-flex justify-content-between align-items-center">
                    <div class="fst-italic"><?= date('d F Y', strtotime($post['created_at'])); ?></div>
                    <div>
                        <!-- Edit Button -->
                        <a href="#" id="<?= $post['id']; ?>" data-bs-toggle="modal" data-bs-target="#edit_post_modal" class="btn btn-success btn-sm post_edit_btn">Edit</a>
                        <!-- Delete Button -->
                        <a href="#" id="<?= $post['id']; ?>" class="btn btn-danger btn-sm post_delete_btn">Delete</a>
                    </div>
                </div>
            </div>
        </div>
    <?php endforeach; ?>
<?php else: ?>
    <div class="text-secondary text-center fw-bold my-5">No posts found in the database!</div>
<?php endif; ?>


