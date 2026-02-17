<?php
/**
 * Fox Lab – Blog Page
 * Featured hero post, 3-column grid, pagination, category filter, FB embed
 */
require_once __DIR__ . '/../config/db.php';

$pageTitle = 'Blogs';
$pdo = getDBConnection();

// --- Category filter ---
$category = isset($_GET['category']) ? trim($_GET['category']) : '';
$page = isset($_GET['page']) ? max(1, (int)$_GET['page']) : 1;
$perPage = 6;
$offset = ($page - 1) * $perPage;

// --- Get featured post ---
$stmtFeatured = $pdo->prepare("SELECT * FROM blogs WHERE is_featured = 1 ORDER BY published_at DESC LIMIT 1");
$stmtFeatured->execute();
$featured = $stmtFeatured->fetch();

// --- Count & fetch posts ---
$whereClause = "WHERE is_featured = 0";
$params = [];

if ($category !== '' && $category !== 'All Categories') {
    $whereClause .= " AND category = :category";
    $params[':category'] = $category;
}

$stmtCount = $pdo->prepare("SELECT COUNT(*) FROM blogs $whereClause");
$stmtCount->execute($params);
$totalPosts = $stmtCount->fetchColumn();
$totalPages = max(1, ceil($totalPosts / $perPage));

$stmtPosts = $pdo->prepare("SELECT * FROM blogs $whereClause ORDER BY published_at DESC LIMIT :limit OFFSET :offset");
foreach ($params as $k => $v) {
    $stmtPosts->bindValue($k, $v);
}
$stmtPosts->bindValue(':limit', $perPage, PDO::PARAM_INT);
$stmtPosts->bindValue(':offset', $offset, PDO::PARAM_INT);
$stmtPosts->execute();
$posts = $stmtPosts->fetchAll();

// --- Get categories for filter ---
$stmtCats = $pdo->query("SELECT DISTINCT category FROM blogs ORDER BY category ASC");
$categories = $stmtCats->fetchAll(PDO::FETCH_COLUMN);

// --- Handle single blog view ---
$viewSingle = false;
$singlePost = null;
if (isset($_GET['id'])) {
    $stmtSingle = $pdo->prepare("SELECT * FROM blogs WHERE id = :id LIMIT 1");
    $stmtSingle->execute([':id' => (int)$_GET['id']]);
    $singlePost = $stmtSingle->fetch();
    if ($singlePost) {
        $viewSingle = true;
        $pageTitle = $singlePost['title'];
        // Increment views
        $pdo->prepare("UPDATE blogs SET views = views + 1 WHERE id = :id")->execute([':id' => $singlePost['id']]);
    }
}

include __DIR__ . '/../includes/header.php';

if ($viewSingle && $singlePost):
?>

<!-- ===== SINGLE BLOG VIEW ===== -->
<section class="blog-detail">
    <div class="container">
        <div class="blog-detail-inner">
            <div class="blog-detail-header">
                <a href="blog.php" style="display:inline-flex;align-items:center;gap:6px;margin-bottom:15px;color:var(--accent);font-weight:600;"><i class="fas fa-arrow-left"></i> Back to Blogs</a>
                <div class="blog-meta">
                    <span class="category-badge"><?php echo e($singlePost['category']); ?></span>
                    <span class="date"><?php echo date('F j, Y', strtotime($singlePost['published_at'])); ?></span>
                    <span class="read-time"><?php echo e($singlePost['read_time']); ?> min read</span>
                </div>
                <h1 style="font-size:2rem;font-weight:700;margin-top:10px;"><?php echo e($singlePost['title']); ?></h1>
                <div class="blog-author" style="margin-top:15px;">
                    <div class="blog-author-icon"><i class="fas fa-envelope"></i></div>
                    <div class="blog-author-info">
                        <span><?php echo e($singlePost['author']); ?></span>
                        <?php if ($singlePost['author_org']): ?>
                        <small><?php echo e($singlePost['author_org']); ?></small>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
            <div class="blog-detail-content">
                <?php echo $singlePost['content']; ?>
            </div>

            <?php if (!empty($singlePost['fb_link'])): ?>
            <div class="fb-embed-wrap">
                <h3 style="margin-bottom:10px;font-size:1rem;">Facebook Post</h3>
                <div id="fb-embed-container">
                    <iframe src="https://www.facebook.com/plugins/post.php?href=<?php echo urlencode($singlePost['fb_link']); ?>&show_text=true&width=500" 
                            width="100%" height="300" style="border:none;overflow:hidden" scrolling="no" frameborder="0" 
                            allowfullscreen="true" allow="autoplay; clipboard-write; encrypted-media; picture-in-picture; web-share">
                    </iframe>
                </div>
            </div>
            <?php endif; ?>
        </div>
    </div>
</section>

<?php else: ?>

<!-- ===== FEATURED BLOG HERO ===== -->
<?php if ($featured): ?>
<section class="blog-hero">
    <div class="container">
        <div class="blog-hero-inner">
            <div class="blog-hero-image">
                <?php if (!empty($featured['image_url'])): ?>
                    <img src="../<?php echo e($featured['image_url']); ?>" alt="<?php echo e($featured['title']); ?>">
                <?php else: ?>
                    <div class="blog-hero-image-placeholder">
                        <i class="fas fa-newspaper"></i>
                    </div>
                <?php endif; ?>
            </div>
            <div class="blog-hero-content">
                <div class="blog-meta">
                    <span class="category-badge"><?php echo e($featured['category']); ?></span>
                    <span class="date"><?php echo date('F, Y', strtotime($featured['published_at'])); ?></span>
                    <span class="read-time"><?php echo e($featured['read_time']); ?> min read</span>
                </div>
                <h1><?php echo e($featured['title']); ?></h1>
                <p><?php echo e($featured['excerpt']); ?></p>
                <div class="blog-author">
                    <div class="blog-author-icon"><i class="fas fa-envelope"></i></div>
                    <div class="blog-author-info">
                        <span><?php echo e($featured['author']); ?></span>
                        <?php if ($featured['author_org']): ?>
                        <small><?php echo e($featured['author_org']); ?></small>
                        <?php endif; ?>
                    </div>
                </div>
                <a href="blog.php?id=<?php echo $featured['id']; ?>" class="btn btn-primary">Read Full Article</a>
            </div>
        </div>
    </div>
</section>
<?php endif; ?>

<!-- ===== BLOG GRID ===== -->
<section class="blog-section">
    <div class="container">
        <div class="blog-section-header">
            <h2>Latest Cybersecurity News</h2>
            <div class="blog-filters">
                <form method="GET" action="blog.php" id="blogFilterForm">
                    <select name="category" onchange="document.getElementById('blogFilterForm').submit();">
                        <option value="All Categories">All Categories</option>
                        <?php foreach ($categories as $cat): ?>
                        <option value="<?php echo e($cat); ?>" <?php echo ($category === $cat) ? 'selected' : ''; ?>>
                            <?php echo e($cat); ?>
                        </option>
                        <?php endforeach; ?>
                    </select>
                </form>
            </div>
        </div>

        <div class="blog-grid">
            <?php foreach ($posts as $post): ?>
            <a href="blog.php?id=<?php echo $post['id']; ?>" class="blog-card" style="text-decoration:none;color:inherit;">
                <?php if (!empty($post['image_url'])): ?>
                    <img src="../<?php echo e($post['image_url']); ?>" alt="<?php echo e($post['title']); ?>" class="blog-card-img">
                <?php else: ?>
                    <div class="blog-card-img-placeholder">
                        <i class="fas fa-newspaper"></i>
                    </div>
                <?php endif; ?>
                <div class="blog-card-body">
                    <div class="blog-card-meta">
                        <span class="category-badge"><?php echo e($post['category']); ?></span>
                        <span class="date"><?php echo date('M j, Y', strtotime($post['published_at'])); ?></span>
                    </div>
                    <h3><?php echo e($post['title']); ?></h3>
                    <p><?php echo e($post['excerpt']); ?></p>
                    <div class="blog-card-footer">
                        <span class="author-info">
                            <i class="fas fa-envelope"></i>
                            <?php echo e($post['author']); ?>
                        </span>
                        <span class="read-time"><?php echo e($post['read_time']); ?> min read</span>
                    </div>
                </div>
            </a>
            <?php endforeach; ?>
        </div>

        <?php if (empty($posts)): ?>
        <div style="text-align:center;padding:40px;color:var(--text-muted);">
            <i class="fas fa-inbox" style="font-size:2rem;margin-bottom:10px;display:block;"></i>
            <p>No blog posts found for this category.</p>
        </div>
        <?php endif; ?>

        <!-- Pagination -->
        <?php if ($totalPages > 1): ?>
        <div class="pagination">
            <?php if ($page > 1): ?>
            <a href="blog.php?page=<?php echo $page - 1; ?>&category=<?php echo urlencode($category); ?>">Previous</a>
            <?php endif; ?>
            
            <?php for ($i = 1; $i <= min($totalPages, 5); $i++): ?>
            <a href="blog.php?page=<?php echo $i; ?>&category=<?php echo urlencode($category); ?>" class="<?php echo $i === $page ? 'active' : ''; ?>"><?php echo $i; ?></a>
            <?php endfor; ?>
            
            <?php if ($totalPages > 5): ?>
            <span>...</span>
            <?php endif; ?>
            
            <?php if ($page < $totalPages): ?>
            <a href="blog.php?page=<?php echo $page + 1; ?>&category=<?php echo urlencode($category); ?>">Next</a>
            <?php endif; ?>
        </div>
        <?php endif; ?>
    </div>
</section>

<?php endif; ?>

<?php include __DIR__ . '/../includes/footer.php'; ?>
