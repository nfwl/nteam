<?php
include("./Common/Core_brain.php");

$page = isset($_GET['page']) ? intval($_GET['page']) : 1;
$category_id = isset($_GET['category']) ? intval($_GET['category']) : 0;
$tag = isset($_GET['tag']) ? trim($_GET['tag']) : '';

$limit = 10;
$offset = ($page - 1) * $limit;

$where = "WHERE b.status=1";
if($category_id) {
    $where .= " AND b.category_id=".$category_id;
}
if($tag) {
    $where .= " AND b.tags LIKE '%".addslashes($tag)."%'";
}

$total = $DB->getColumn("SELECT COUNT(*) FROM nteam_blogs b {$where}");
$pages = ceil($total / $limit);

// 獲取文章列表
$blogs = $DB->query("SELECT b.*,c.name as category_name,u.username,u.avatar 
    FROM nteam_blogs b 
    LEFT JOIN nteam_blog_categories c ON b.category_id=c.id 
    LEFT JOIN nteam_users u ON b.author_id=u.id 
    {$where} ORDER BY b.create_time DESC LIMIT {$offset},{$limit}")->fetchAll();

// 獲取分類列表
$categories = $DB->query("SELECT c.*,(SELECT COUNT(*) FROM nteam_blogs WHERE category_id=c.id AND status=1) as blog_count 
    FROM nteam_blog_categories c 
    HAVING blog_count > 0 
    ORDER BY c.sort DESC")->fetchAll();

// 獲取標籤雲
$tags = [];
$tag_results = $DB->query("SELECT tags FROM nteam_blogs WHERE status=1 AND tags<>''")->fetchAll();
foreach($tag_results as $row) {
    $blog_tags = explode(',', $row['tags']);
    foreach($blog_tags as $t) {
        $t = trim($t);
        if($t) {
            if(isset($tags[$t])) {
                $tags[$t]++;
            } else {
                $tags[$t] = 1;
            }
        }
    }
}
arsort($tags);
$tags = array_slice($tags, 0, 20, true);

// 獲取最新評論
$recent_comments = $DB->query("SELECT c.*,b.title as blog_title,u.username,u.avatar 
    FROM nteam_blog_comments c 
    LEFT JOIN nteam_blogs b ON c.blog_id=b.id 
    LEFT JOIN nteam_users u ON c.user_id=u.id 
    WHERE c.status=1 
    ORDER BY c.create_time DESC LIMIT 5")->fetchAll();
?>
<!DOCTYPE html>
<html lang="zh">
<head>
    <meta charset="utf-8">
    <meta content="width=device-width, initial-scale=1.0" name="viewport">
    <title><?php echo $category_id ? htmlspecialchars($categories[array_search($category_id, array_column($categories, 'id'))]['name']).' - ' : '';?>技術博客 - <?php echo conf('Name');?></title>
    <meta content="<?php echo conf('Descriptison');?>" name="descriptison">
    <meta content="<?php echo conf('Keywords');?>" name="keywords">
    <link href="assets/vendor/bootstrap/css/bootstrap.min.css" rel="stylesheet">
    <link href="assets/vendor/icofont/icofont.min.css" rel="stylesheet">
    <link href="assets/vendor/boxicons/css/boxicons.min.css" rel="stylesheet">
    <link href="assets/vendor/animate.css/animate.min.css" rel="stylesheet">
    <link href="assets/css/style.css" rel="stylesheet">
    <style>
        .blog-section {
            padding: 60px 0;
            background: #f8f9fa;
        }
        .blog-card {
            background: #fff;
            border-radius: 8px;
            box-shadow: 0 2px 15px rgba(0,0,0,0.05);
            margin-bottom: 30px;
            transition: all 0.3s;
        }
        .blog-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 5px 20px rgba(0,0,0,0.1);
        }
        .blog-header {
            padding: 20px;
            border-bottom: 1px solid #eee;
        }
        .blog-title {
            font-size: 20px;
            margin: 0 0 10px;
        }
        .blog-title a {
            color: #333;
            text-decoration: none;
        }
        .blog-title a:hover {
            color: #5c8af7;
        }
        .blog-meta {
            color: #6c757d;
            font-size: 14px;
            display: flex;
            align-items: center;
        }
        .blog-meta img {
            width: 30px;
            height: 30px;
            border-radius: 50%;
            margin-right: 10px;
        }
        .blog-meta span {
            margin-right: 15px;
        }
        .blog-meta i {
            margin-right: 5px;
        }
        .blog-content {
            padding: 20px;
        }
        .blog-summary {
            color: #666;
            line-height: 1.6;
            margin-bottom: 15px;
        }
        .blog-tags {
            margin-top: 15px;
        }
        .blog-tag {
            display: inline-block;
            padding: 2px 8px;
            margin: 0 5px 5px 0;
            background: #f0f2f5;
            color: #666;
            border-radius: 3px;
            font-size: 12px;
            text-decoration: none;
        }
        .blog-tag:hover {
            background: #e3e6ea;
            color: #333;
            text-decoration: none;
        }
        .widget {
            background: #fff;
            border-radius: 8px;
            box-shadow: 0 2px 15px rgba(0,0,0,0.05);
            padding: 20px;
            margin-bottom: 30px;
        }
        .widget-title {
            font-size: 18px;
            margin-bottom: 15px;
            color: #333;
        }
        .category-list {
            list-style: none;
            padding: 0;
            margin: 0;
        }
        .category-list li {
            padding: 8px 0;
            border-bottom: 1px solid #eee;
        }
        .category-list li:last-child {
            border-bottom: none;
        }
        .category-list a {
            color: #666;
            text-decoration: none;
            display: flex;
            justify-content: space-between;
            align-items: center;
        }
        .category-list a:hover {
            color: #5c8af7;
        }
        .tag-cloud {
            display: flex;
            flex-wrap: wrap;
            gap: 8px;
        }
        .comment-list {
            list-style: none;
            padding: 0;
            margin: 0;
        }
        .comment-item {
            padding: 10px 0;
            border-bottom: 1px solid #eee;
        }
        .comment-item:last-child {
            border-bottom: none;
        }
        .comment-meta {
            display: flex;
            align-items: center;
            margin-bottom: 5px;
        }
        .comment-meta img {
            width: 24px;
            height: 24px;
            border-radius: 50%;
            margin-right: 8px;
        }
        .comment-meta .name {
            color: #333;
            font-weight: 500;
        }
        .comment-meta .date {
            color: #6c757d;
            font-size: 12px;
            margin-left: 10px;
        }
        .comment-text {
            color: #666;
            font-size: 14px;
            line-height: 1.5;
        }
        .pagination {
            margin-top: 30px;
            justify-content: center;
        }
    </style>
</head>
<body>
    <?php include 'header.php'; ?>

    <main id="main">
        <section class="blog-section">
            <div class="container">
                <div class="section-title">
                    <h2>技術博客</h2>
                    <p>分享技術經驗，交流學習心得</p>
                </div>

                <div class="row">
                    <div class="col-lg-8">
                        <?php if($blogs) { foreach($blogs as $blog) { 
                            // 獲取評論數
                            $comment_count = $DB->getColumn("SELECT COUNT(*) FROM nteam_blog_comments WHERE blog_id='{$blog['id']}' AND status=1");
                        ?>
                        <div class="blog-card wow fadeInUp">
                            <div class="blog-header">
                                <h2 class="blog-title">
                                    <a href="blog.php?id=<?php echo $blog['id']; ?>"><?php echo htmlspecialchars($blog['title']); ?></a>
                                </h2>
                                <div class="blog-meta">
                                    <img src="<?php echo $blog['avatar'] ? $blog['avatar'] : 'assets/img/default-avatar.png'; ?>" alt="<?php echo htmlspecialchars($blog['username']); ?>">
                                    <div>
                                        <span><i class="bx bx-user"></i><?php echo htmlspecialchars($blog['username']); ?></span>
                                        <span><i class="bx bx-folder"></i><?php echo htmlspecialchars($blog['category_name']); ?></span>
                                        <span><i class="bx bx-time"></i><?php echo date('Y-m-d', strtotime($blog['create_time'])); ?></span>
                                        <span><i class="bx bx-show"></i><?php echo $blog['views']; ?> 次瀏覽</span>
                                        <span><i class="bx bx-comment"></i><?php echo $comment_count; ?> 條評論</span>
                                    </div>
                                </div>
                            </div>
                            <div class="blog-content">
                                <div class="blog-summary">
                                    <?php echo $blog['summary'] ? htmlspecialchars($blog['summary']) : mb_substr(strip_tags($blog['content']), 0, 200).'...'; ?>
                                </div>
                                <a href="blog.php?id=<?php echo $blog['id']; ?>" class="btn btn-primary">閱讀更多</a>
                                <?php if($blog['tags']) { ?>
                                <div class="blog-tags">
                                    <?php foreach(explode(',', $blog['tags']) as $tag) { ?>
                                    <a href="blogs.php?tag=<?php echo urlencode(trim($tag)); ?>" class="blog-tag"><?php echo htmlspecialchars(trim($tag)); ?></a>
                                    <?php } ?>
                                </div>
                                <?php } ?>
                            </div>
                        </div>
                        <?php } } else { ?>
                        <div class="text-center text-muted py-5">
                            <i class="bx bx-book-content" style="font-size: 48px;"></i>
                            <p class="mt-3">暫無文章</p>
                        </div>
                        <?php } ?>

                        <?php if($pages > 1) { ?>
                        <ul class="pagination">
                            <?php if($page > 1) { ?>
                            <li class="page-item">
                                <a class="page-link" href="?page=<?php echo $page-1; ?><?php echo $category_id?'&category='.$category_id:''; ?><?php echo $tag?'&tag='.urlencode($tag):''; ?>">上一頁</a>
                            </li>
                            <?php }
                            $start = max(1, $page - 2);
                            $end = min($pages, $page + 2);
                            for($i=$start; $i<=$end; $i++) { ?>
                            <li class="page-item <?php echo $i==$page?'active':''; ?>">
                                <a class="page-link" href="?page=<?php echo $i; ?><?php echo $category_id?'&category='.$category_id:''; ?><?php echo $tag?'&tag='.urlencode($tag):''; ?>"><?php echo $i; ?></a>
                            </li>
                            <?php }
                            if($page < $pages) { ?>
                            <li class="page-item">
                                <a class="page-link" href="?page=<?php echo $page+1; ?><?php echo $category_id?'&category='.$category_id:''; ?><?php echo $tag?'&tag='.urlencode($tag):''; ?>">下一頁</a>
                            </li>
                            <?php } ?>
                        </ul>
                        <?php } ?>
                    </div>

                    <div class="col-lg-4">
                        <div class="widget">
                            <h3 class="widget-title">文章分類</h3>
                            <ul class="category-list">
                                <li>
                                    <a href="blogs.php">
                                        全部分類
                                        <span class="badge bg-secondary"><?php echo $total; ?></span>
                                    </a>
                                </li>
                                <?php foreach($categories as $cat) { ?>
                                <li>
                                    <a href="blogs.php?category=<?php echo $cat['id']; ?>">
                                        <?php echo htmlspecialchars($cat['name']); ?>
                                        <span class="badge bg-secondary"><?php echo $cat['blog_count']; ?></span>
                                    </a>
                                </li>
                                <?php } ?>
                            </ul>
                        </div>

                        <?php if($tags) { ?>
                        <div class="widget">
                            <h3 class="widget-title">標籤雲</h3>
                            <div class="tag-cloud">
                                <?php foreach($tags as $t => $count) { ?>
                                <a href="blogs.php?tag=<?php echo urlencode($t); ?>" class="blog-tag" title="<?php echo $count; ?>篇文章"><?php echo htmlspecialchars($t); ?></a>
                                <?php } ?>
                            </div>
                        </div>
                        <?php } ?>

                        <?php if($recent_comments) { ?>
                        <div class="widget">
                            <h3 class="widget-title">最新評論</h3>
                            <ul class="comment-list">
                                <?php foreach($recent_comments as $comment) { ?>
                                <li class="comment-item">
                                    <div class="comment-meta">
                                        <img src="<?php echo $comment['avatar'] ? $comment['avatar'] : 'assets/img/default-avatar.png'; ?>" alt="<?php echo htmlspecialchars($comment['username']); ?>">
                                        <span class="name"><?php echo htmlspecialchars($comment['username']); ?></span>
                                        <span class="date"><?php echo date('Y-m-d', strtotime($comment['create_time'])); ?></span>
                                    </div>
                                    <div class="comment-text">
                                        <a href="blog.php?id=<?php echo $comment['blog_id']; ?>#comment-<?php echo $comment['id']; ?>" class="text-muted">
                                            <?php echo mb_substr(strip_tags($comment['content']), 0, 50).'...'; ?>
                                        </a>
                                    </div>
                                </li>
                                <?php } ?>
                            </ul>
                        </div>
                        <?php } ?>
                    </div>
                </div>
            </div>
        </section>
    </main>

    <?php include 'footer.php'; ?>

    <a href="#" class="back-to-top"><i class="icofont-simple-up"></i></a>

    <script src="assets/vendor/jquery/jquery.min.js"></script>
    <script src="assets/vendor/bootstrap/js/bootstrap.bundle.min.js"></script>
    <script src="assets/vendor/jquery.easing/jquery.easing.min.js"></script>
    <script src="assets/vendor/wow/wow.min.js"></script>
    <script src="assets/js/main.js"></script>
    <script>
        new WOW().init();
    </script>
</body>
</html>