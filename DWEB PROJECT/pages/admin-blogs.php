<?php
/**
 * Fox Lab – Admin Blog Management
 * Create, edit, delete blogs with image uploads
 */
require_once __DIR__ . '/../config/db.php';
requireAdmin();

$pageTitle = 'Manage Blogs';
$pdo = getDBConnection();

// Upload directory (relative to project root)
$uploadDir = __DIR__ . '/../uploads/blog/';
if (!is_dir($uploadDir)) {
    mkdir($uploadDir, 0755, true);
}

// ===== HANDLE ACTIONS =====
$message = '';
$messageType = '';

// --- DELETE ---
if (isset($_GET['delete']) && is_numeric($_GET['delete'])) {
    $delId = (int)$_GET['delete'];
    // Get image path to delete file
    $stmt = $pdo->prepare("SELECT image_url FROM blogs WHERE id = :id");
    $stmt->execute([':id' => $delId]);
    $row = $stmt->fetch();
    if ($row && !empty($row['image_url']) && strpos($row['image_url'], 'uploads/') === 0) {
        $filePath = __DIR__ . '/../' . $row['image_url'];
        if (file_exists($filePath)) unlink($filePath);
    }
    $pdo->prepare("DELETE FROM blogs WHERE id = :id")->execute([':id' => $delId]);
    $message = 'Blog post deleted successfully.';
    $messageType = 'success';
}

// --- CREATE / UPDATE ---
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['save_blog'])) {
    $editId      = (int)($_POST['edit_id'] ?? 0);
    $title       = trim($_POST['title'] ?? '');
    $excerpt     = trim($_POST['excerpt'] ?? '');
    $content     = $_POST['content'] ?? '';
    $category    = trim($_POST['category'] ?? 'Technology');
    $author      = trim($_POST['author'] ?? 'Fox Lab');
    $authorOrg   = trim($_POST['author_org'] ?? '');
    $fbLink      = trim($_POST['fb_link'] ?? '');
    $readTime    = max(1, (int)($_POST['read_time'] ?? 5));
    $isFeatured  = isset($_POST['is_featured']) ? 1 : 0;
    $publishedAt = $_POST['published_at'] ?? date('Y-m-d');

    if ($title === '' || $content === '') {
        $message = 'Title and content are required.';
        $messageType = 'error';
    } else {
        // Handle thumbnail upload
        $imageUrl = $_POST['existing_image'] ?? '';
        if (!empty($_FILES['thumbnail']['name']) && $_FILES['thumbnail']['error'] === UPLOAD_ERR_OK) {
            $allowed = ['image/jpeg', 'image/png', 'image/gif', 'image/webp', 'image/svg+xml'];
            $finfo = finfo_open(FILEINFO_MIME_TYPE);
            $mimeType = finfo_file($finfo, $_FILES['thumbnail']['tmp_name']);
            finfo_close($finfo);

            if (in_array($mimeType, $allowed)) {
                $ext = pathinfo($_FILES['thumbnail']['name'], PATHINFO_EXTENSION);
                $filename = 'blog_' . time() . '_' . bin2hex(random_bytes(4)) . '.' . $ext;
                $destPath = $uploadDir . $filename;
                if (move_uploaded_file($_FILES['thumbnail']['tmp_name'], $destPath)) {
                    // Delete old uploaded image if exists
                    if (!empty($imageUrl) && strpos($imageUrl, 'uploads/') === 0) {
                        $oldPath = __DIR__ . '/../' . $imageUrl;
                        if (file_exists($oldPath)) unlink($oldPath);
                    }
                    $imageUrl = 'uploads/blog/' . $filename;
                }
            } else {
                $message = 'Invalid image type. Allowed: JPG, PNG, GIF, WebP, SVG.';
                $messageType = 'error';
            }
        }

        // Handle inline content images
        if (!empty($_FILES['content_images']['name'][0])) {
            foreach ($_FILES['content_images']['name'] as $idx => $name) {
                if ($_FILES['content_images']['error'][$idx] === UPLOAD_ERR_OK && $name !== '') {
                    $allowed = ['image/jpeg', 'image/png', 'image/gif', 'image/webp', 'image/svg+xml'];
                    $finfo = finfo_open(FILEINFO_MIME_TYPE);
                    $mimeType = finfo_file($finfo, $_FILES['content_images']['tmp_name'][$idx]);
                    finfo_close($finfo);
                    if (in_array($mimeType, $allowed)) {
                        $ext = pathinfo($name, PATHINFO_EXTENSION);
                        $filename = 'content_' . time() . '_' . bin2hex(random_bytes(4)) . '.' . $ext;
                        move_uploaded_file($_FILES['content_images']['tmp_name'][$idx], $uploadDir . $filename);
                    }
                }
            }
        }

        if ($messageType !== 'error') {
            // If marking as featured, unmark others
            if ($isFeatured) {
                $pdo->exec("UPDATE blogs SET is_featured = 0");
            }

            // Resolve category_id from category name
            $stmtCatId = $pdo->prepare("SELECT id FROM categories WHERE name = :name LIMIT 1");
            $stmtCatId->execute([':name' => $category]);
            $categoryId = $stmtCatId->fetchColumn() ?: null;

            if ($editId > 0) {
                $stmt = $pdo->prepare("UPDATE blogs SET title = :title, excerpt = :excerpt, content = :content, category = :category, category_id = :cat_id, author = :author, author_org = :author_org, image_url = :img, fb_link = :fb, read_time = :rt, is_featured = :feat, published_at = :pub WHERE id = :id");
                $stmt->execute([
                    ':title' => $title, ':excerpt' => $excerpt, ':content' => $content,
                    ':category' => $category, ':cat_id' => $categoryId, ':author' => $author, ':author_org' => $authorOrg,
                    ':img' => $imageUrl, ':fb' => $fbLink, ':rt' => $readTime,
                    ':feat' => $isFeatured, ':pub' => $publishedAt, ':id' => $editId
                ]);
                $message = 'Blog post updated successfully.';
            } else {
                $stmt = $pdo->prepare("INSERT INTO blogs (title, excerpt, content, category, category_id, author, author_org, image_url, fb_link, read_time, is_featured, published_at) VALUES (:title, :excerpt, :content, :category, :cat_id, :author, :author_org, :img, :fb, :rt, :feat, :pub)");
                $stmt->execute([
                    ':title' => $title, ':excerpt' => $excerpt, ':content' => $content,
                    ':category' => $category, ':cat_id' => $categoryId, ':author' => $author, ':author_org' => $authorOrg,
                    ':img' => $imageUrl, ':fb' => $fbLink, ':rt' => $readTime,
                    ':feat' => $isFeatured, ':pub' => $publishedAt
                ]);
                $message = 'Blog post created successfully.';
            }
            $messageType = 'success';
        }
    }
}

// --- LOAD DATA ---
$editBlog = null;
if (isset($_GET['edit']) && is_numeric($_GET['edit'])) {
    $stmt = $pdo->prepare("SELECT * FROM blogs WHERE id = :id LIMIT 1");
    $stmt->execute([':id' => (int)$_GET['edit']]);
    $editBlog = $stmt->fetch();
}

$allBlogs = $pdo->query("SELECT id, title, category, author, image_url, is_featured, views, published_at FROM blogs ORDER BY published_at DESC")->fetchAll();

// Categories for dropdown (from DB)
$catOptions = $pdo->query("SELECT name FROM categories ORDER BY display_order ASC")->fetchAll(PDO::FETCH_COLUMN);

include __DIR__ . '/../includes/header.php';
?>

<!-- ===== ADMIN BLOG DASHBOARD ===== -->
<section class="admin-blogs">
    <div class="container">
        <div class="admin-header">
            <div class="admin-header-left">
                <h1><i class="fas fa-pen-to-square"></i> Blog Management</h1>
                <p>Create, edit, and manage all blog posts</p>
            </div>
            <div class="admin-header-actions">
                <a href="blog.php" class="admin-btn admin-btn-outline"><i class="fas fa-eye"></i> View Blog</a>
                <button class="admin-btn admin-btn-primary" onclick="toggleEditor(true)"><i class="fas fa-plus"></i> New Post</button>
            </div>
        </div>

        <?php if ($message): ?>
        <div class="admin-alert admin-alert-<?php echo $messageType; ?>">
            <i class="fas fa-<?php echo $messageType === 'success' ? 'check-circle' : 'exclamation-circle'; ?>"></i>
            <?php echo e($message); ?>
        </div>
        <?php endif; ?>

        <!-- ===== EDITOR PANEL ===== -->
        <div class="admin-editor-panel" id="editorPanel" style="<?php echo ($editBlog || (isset($_POST['save_blog']) && $messageType === 'error')) ? '' : 'display:none;'; ?>">
            <div class="admin-editor-header">
                <h2><?php echo $editBlog ? 'Edit Post' : 'Create New Post'; ?></h2>
                <button class="admin-btn-close" onclick="toggleEditor(false)" title="Close"><i class="fas fa-times"></i></button>
            </div>
            <form method="POST" enctype="multipart/form-data" class="admin-editor-form">
                <input type="hidden" name="save_blog" value="1">
                <input type="hidden" name="edit_id" value="<?php echo $editBlog['id'] ?? 0; ?>">
                <input type="hidden" name="existing_image" value="<?php echo e($editBlog['image_url'] ?? ''); ?>">

                <div class="admin-form-grid">
                    <!-- Left column -->
                    <div class="admin-form-main">
                        <div class="admin-field">
                            <label for="blogTitle">Title <span class="required">*</span></label>
                            <input type="text" id="blogTitle" name="title" value="<?php echo e($editBlog['title'] ?? ''); ?>" required placeholder="Enter blog title...">
                        </div>

                        <div class="admin-field">
                            <label for="blogExcerpt">Excerpt</label>
                            <textarea id="blogExcerpt" name="excerpt" rows="3" placeholder="Brief summary shown on blog cards..."><?php echo e($editBlog['excerpt'] ?? ''); ?></textarea>
                        </div>

                        <div class="admin-field">
                            <label for="blogContent">Content (HTML) <span class="required">*</span></label>
                            <div class="admin-toolbar">
                                <button type="button" onclick="insertTag('h3')" title="Heading"><i class="fas fa-heading"></i></button>
                                <button type="button" onclick="insertTag('p')" title="Paragraph"><i class="fas fa-paragraph"></i></button>
                                <button type="button" onclick="insertTag('strong')" title="Bold"><i class="fas fa-bold"></i></button>
                                <button type="button" onclick="insertTag('em')" title="Italic"><i class="fas fa-italic"></i></button>
                                <button type="button" onclick="insertTag('ul')" title="List"><i class="fas fa-list-ul"></i></button>
                                <button type="button" onclick="insertTag('li')" title="List Item"><i class="fas fa-list"></i></button>
                                <button type="button" onclick="insertTag('a')" title="Link"><i class="fas fa-link"></i></button>
                                <button type="button" onclick="insertImg()" title="Insert Image Tag"><i class="fas fa-image"></i></button>
                                <button type="button" onclick="insertTag('blockquote')" title="Quote"><i class="fas fa-quote-left"></i></button>
                                <button type="button" onclick="insertTag('code')" title="Code"><i class="fas fa-code"></i></button>
                                <button type="button" onclick="togglePreview()" title="Preview" class="admin-toolbar-preview"><i class="fas fa-eye"></i> Preview</button>
                            </div>
                            <textarea id="blogContent" name="content" rows="16" required placeholder="Write your blog content here (HTML supported)..."><?php echo e($editBlog['content'] ?? ''); ?></textarea>
                            <div class="admin-content-preview" id="contentPreview" style="display:none;"></div>
                        </div>
                    </div>

                    <!-- Right column – meta -->
                    <div class="admin-form-sidebar">
                        <div class="admin-sidebar-card">
                            <h3><i class="fas fa-image"></i> Thumbnail</h3>
                            <div class="admin-thumbnail-area" id="thumbnailArea">
                                <?php if (!empty($editBlog['image_url'])): ?>
                                <img src="../<?php echo e($editBlog['image_url']); ?>" alt="Current thumbnail" id="thumbnailPreview">
                                <?php else: ?>
                                <div class="admin-thumbnail-placeholder" id="thumbnailPreview">
                                    <i class="fas fa-cloud-upload-alt"></i>
                                    <span>Click or drag to upload</span>
                                </div>
                                <?php endif; ?>
                                <input type="file" name="thumbnail" id="thumbnailInput" accept="image/*" onchange="previewThumbnail(this)">
                            </div>
                            <small class="admin-hint">JPG, PNG, GIF, WebP, or SVG. Max 5MB.</small>
                        </div>

                        <div class="admin-sidebar-card">
                            <h3><i class="fas fa-images"></i> Content Images</h3>
                            <div class="admin-content-images-area">
                                <label class="admin-upload-label">
                                    <i class="fas fa-plus"></i> Add Images
                                    <input type="file" name="content_images[]" multiple accept="image/*" onchange="showUploadedFiles(this)" style="display:none;">
                                </label>
                                <div id="contentImagesList" class="admin-uploaded-list"></div>
                            </div>
                            <small class="admin-hint">Upload images to use in content. Copy the path after upload.</small>
                        </div>

                        <div class="admin-sidebar-card">
                            <h3><i class="fas fa-cog"></i> Post Settings</h3>

                            <div class="admin-field-sm">
                                <label>Category</label>
                                <select name="category">
                                    <?php foreach ($catOptions as $cat): ?>
                                    <option value="<?php echo e($cat); ?>" <?php echo ($editBlog['category'] ?? '') === $cat ? 'selected' : ''; ?>><?php echo e($cat); ?></option>
                                    <?php endforeach; ?>
                                </select>
                            </div>

                            <div class="admin-field-sm">
                                <label>Author</label>
                                <input type="text" name="author" value="<?php echo e($editBlog['author'] ?? 'Fox Lab'); ?>" placeholder="Author name">
                            </div>

                            <div class="admin-field-sm">
                                <label>Author Organization</label>
                                <input type="text" name="author_org" value="<?php echo e($editBlog['author_org'] ?? 'Fox Lab'); ?>" placeholder="Organization">
                            </div>

                            <div class="admin-field-sm">
                                <label>Read Time (min)</label>
                                <input type="number" name="read_time" value="<?php echo e($editBlog['read_time'] ?? '5'); ?>" min="1" max="60">
                            </div>

                            <div class="admin-field-sm">
                                <label>Publish Date</label>
                                <input type="date" name="published_at" value="<?php echo e($editBlog['published_at'] ?? date('Y-m-d')); ?>">
                            </div>

                            <div class="admin-field-sm">
                                <label>Facebook Post Link</label>
                                <input type="url" name="fb_link" value="<?php echo e($editBlog['fb_link'] ?? ''); ?>" placeholder="https://facebook.com/...">
                            </div>

                            <div class="admin-field-checkbox">
                                <label>
                                    <input type="checkbox" name="is_featured" value="1" <?php echo ($editBlog['is_featured'] ?? 0) ? 'checked' : ''; ?>>
                                    <span>Featured Post</span>
                                </label>
                                <small>Only one post can be featured at a time.</small>
                            </div>
                        </div>

                        <button type="submit" class="admin-btn admin-btn-primary admin-btn-full">
                            <i class="fas fa-save"></i> <?php echo $editBlog ? 'Update Post' : 'Publish Post'; ?>
                        </button>
                    </div>
                </div>
            </form>
        </div>

        <!-- ===== BLOG LIST TABLE ===== -->
        <div class="admin-table-wrap">
            <div class="admin-table-header">
                <h2><i class="fas fa-newspaper"></i> All Posts (<?php echo count($allBlogs); ?>)</h2>
            </div>
            <div class="admin-table-scroll">
                <table class="admin-table">
                    <thead>
                        <tr>
                            <th style="width:50px;">Thumb</th>
                            <th>Title</th>
                            <th>Category</th>
                            <th>Author</th>
                            <th>Views</th>
                            <th>Date</th>
                            <th>Featured</th>
                            <th style="width:120px;">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (empty($allBlogs)): ?>
                        <tr><td colspan="8" style="text-align:center;padding:40px;color:var(--text-muted);">No blog posts yet. Create your first post!</td></tr>
                        <?php else: ?>
                        <?php foreach ($allBlogs as $blog): ?>
                        <tr>
                            <td class="admin-table-thumb">
                                <?php if (!empty($blog['image_url'])): ?>
                                <img src="../<?php echo e($blog['image_url']); ?>" alt="">
                                <?php else: ?>
                                <div class="admin-table-thumb-placeholder"><i class="fas fa-image"></i></div>
                                <?php endif; ?>
                            </td>
                            <td class="admin-table-title">
                                <a href="blog.php?id=<?php echo $blog['id']; ?>" target="_blank"><?php echo e($blog['title']); ?></a>
                            </td>
                            <td><span class="admin-badge"><?php echo e($blog['category']); ?></span></td>
                            <td><?php echo e($blog['author']); ?></td>
                            <td><?php echo number_format($blog['views']); ?></td>
                            <td><?php echo $blog['published_at'] ? date('M j, Y', strtotime($blog['published_at'])) : '—'; ?></td>
                            <td>
                                <?php if ($blog['is_featured']): ?>
                                <span class="admin-badge admin-badge-accent"><i class="fas fa-star"></i> Yes</span>
                                <?php else: ?>
                                <span style="color:var(--text-muted);">No</span>
                                <?php endif; ?>
                            </td>
                            <td class="admin-table-actions">
                                <a href="admin-blogs.php?edit=<?php echo $blog['id']; ?>" class="admin-action-btn edit" title="Edit"><i class="fas fa-pen"></i></a>
                                <a href="admin-blogs.php?delete=<?php echo $blog['id']; ?>" class="admin-action-btn delete" title="Delete" onclick="return confirm('Are you sure you want to delete this post?')"><i class="fas fa-trash"></i></a>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>

    </div>
</section>

<script>
// Toggle editor panel
function toggleEditor(show) {
    const panel = document.getElementById('editorPanel');
    if (show) {
        // Reset form for new post
        if (!panel.querySelector('input[name="edit_id"]').value || panel.querySelector('input[name="edit_id"]').value === '0') {
            panel.querySelector('form').reset();
            panel.querySelector('input[name="edit_id"]').value = '0';
            panel.querySelector('input[name="existing_image"]').value = '';
            const prev = document.getElementById('thumbnailPreview');
            if (prev && prev.tagName === 'IMG') {
                prev.outerHTML = '<div class="admin-thumbnail-placeholder" id="thumbnailPreview"><i class="fas fa-cloud-upload-alt"></i><span>Click or drag to upload</span></div>';
            }
        }
        panel.style.display = '';
        panel.scrollIntoView({ behavior: 'smooth', block: 'start' });
    } else {
        panel.style.display = 'none';
        // Reset URL if editing
        if (window.location.search.includes('edit=')) {
            history.replaceState(null, '', 'admin-blogs.php');
        }
    }
}

// Thumbnail preview
function previewThumbnail(input) {
    if (input.files && input.files[0]) {
        const reader = new FileReader();
        reader.onload = function(e) {
            let prev = document.getElementById('thumbnailPreview');
            if (prev.tagName !== 'IMG') {
                const img = document.createElement('img');
                img.id = 'thumbnailPreview';
                prev.replaceWith(img);
                prev = img;
            }
            prev.src = e.target.result;
            prev.alt = 'Thumbnail preview';
        };
        reader.readAsDataURL(input.files[0]);
    }
}

// Show uploaded content image filenames
function showUploadedFiles(input) {
    const list = document.getElementById('contentImagesList');
    list.innerHTML = '';
    if (input.files) {
        for (const file of input.files) {
            const div = document.createElement('div');
            div.className = 'admin-uploaded-item';
            div.innerHTML = '<i class="fas fa-image"></i> ' + file.name;
            list.appendChild(div);
        }
    }
}

// Toolbar: insert HTML tag
function insertTag(tag) {
    const ta = document.getElementById('blogContent');
    const start = ta.selectionStart;
    const end = ta.selectionEnd;
    const selected = ta.value.substring(start, end);

    let insert = '';
    if (tag === 'ul') {
        insert = '<ul>\n<li>' + (selected || 'Item') + '</li>\n</ul>';
    } else if (tag === 'a') {
        const url = prompt('Enter URL:', 'https://');
        if (!url) return;
        insert = '<a href="' + url + '">' + (selected || 'Link text') + '</a>';
    } else {
        insert = '<' + tag + '>' + (selected || '') + '</' + tag + '>';
    }

    ta.value = ta.value.substring(0, start) + insert + ta.value.substring(end);
    ta.focus();
    ta.selectionStart = ta.selectionEnd = start + insert.length;
}

// Toolbar: insert image tag
function insertImg() {
    const url = prompt('Enter image URL (or relative path like uploads/blog/filename.jpg):');
    if (!url) return;
    const alt = prompt('Alt text:', 'Image');
    const ta = document.getElementById('blogContent');
    const pos = ta.selectionStart;
    const tag = '<img src="' + url + '" alt="' + (alt || '') + '" style="max-width:100%;border-radius:8px;margin:15px 0;">';
    ta.value = ta.value.substring(0, pos) + tag + ta.value.substring(pos);
    ta.focus();
}

// Toggle content preview
let previewVisible = false;
function togglePreview() {
    const ta = document.getElementById('blogContent');
    const preview = document.getElementById('contentPreview');
    previewVisible = !previewVisible;
    if (previewVisible) {
        preview.innerHTML = ta.value;
        preview.style.display = 'block';
        ta.style.display = 'none';
    } else {
        preview.style.display = 'none';
        ta.style.display = '';
    }
}

// Click thumbnail area to trigger file input
document.getElementById('thumbnailArea')?.addEventListener('click', function(e) {
    if (e.target.tagName !== 'INPUT') {
        document.getElementById('thumbnailInput').click();
    }
});

// Drag & drop for thumbnail
const thumbArea = document.getElementById('thumbnailArea');
if (thumbArea) {
    thumbArea.addEventListener('dragover', e => { e.preventDefault(); thumbArea.classList.add('dragover'); });
    thumbArea.addEventListener('dragleave', () => thumbArea.classList.remove('dragover'));
    thumbArea.addEventListener('drop', e => {
        e.preventDefault();
        thumbArea.classList.remove('dragover');
        const input = document.getElementById('thumbnailInput');
        input.files = e.dataTransfer.files;
        previewThumbnail(input);
    });
}
</script>

<?php include __DIR__ . '/../includes/footer.php'; ?>
