<?php
include("./Common/Core_brain.php");

$id = isset($_GET['id']) ? intval($_GET['id']) : 0;
if(!$id) {
    header('Location: blogs.php');
    exit;
}

// 更新瀏覽次數
$DB->query("UPDATE nteam_blogs SET views=views+1 WHERE id=:id", [':id'=>$id]);

// 獲取博客詳情
$blog = $DB->query("SELECT b.*,c.name as category_name,u.username,u.avatar 
    FROM nteam_blogs b 
    LEFT JOIN nteam_blog_categories c ON b.category_id=c.id 
    LEFT JOIN nteam_users u ON b.author_id=u.id 
    WHERE b.id=:id AND b.status=1", [':id'=>$id])->fetch();
if(!$blog) {
    header('Location: blogs.php');
    exit;
}

// 獲取評論
$comments = $DB->query("SELECT c.*,u.username,u.avatar 
    FROM nteam_blog_comments c 
    LEFT JOIN nteam_users u ON c.user_id=u.id 
    WHERE c.blog_id=:id AND c.status=1 
    ORDER BY c.create_time ASC", [':id'=>$id])->fetchAll();

// 獲取相關文章（同分類）
$related = $DB->query("SELECT id,title,create_time,views 
    FROM nteam_blogs 
    WHERE status=1 AND category_id={$blog['category_id']} AND id!={$id} 
    ORDER BY create_time DESC LIMIT 5")->fetchAll();

// 獲取上一篇和下一篇
$prev = $DB->query("SELECT id,title FROM nteam_blogs WHERE id<:id AND status=1 ORDER BY id DESC LIMIT 1", [':id'=>$id])->fetch();
$next = $DB->query("SELECT id,title FROM nteam_blogs WHERE id>:id AND status=1 ORDER BY id ASC LIMIT 1", [':id'=>$id])->fetch();
?>
<!DOCTYPE html>
<html lang="zh">
<head>
    <meta charset="utf-8">
    <meta content="width=device-width, initial-scale=1.0" name="viewport">
    <title><?php echo htmlspecialchars($blog['title']); ?> - <?php echo conf('Name');?></title>
    <meta content="<?php echo $blog['summary'] ? htmlspecialchars($blog['summary']) : mb_substr(strip_tags($blog['content']), 0, 200); ?>" name="descriptison">
    <meta content="<?php echo $blog['tags'].','.conf('Keywords');?>" name="keywords">
    <link href="assets/vendor/bootstrap/css/bootstrap.min.css" rel="stylesheet">
    <link href="assets/vendor/icofont/icofont.min.css" rel="stylesheet">
    <link href="assets/vendor/boxicons/css/boxicons.min.css" rel="stylesheet">
    <link href="assets/vendor/animate.css/animate.min.css" rel="stylesheet">
    <link href="assets/css/style.css" rel="stylesheet">
    <style>
        .blog-detail {
            padding: 60px 0;
            background: #f8f9fa;
        }
        .blog-header {
            background: #fff;
            border-radius: 8px;
            box-shadow: 0 2px 15px rgba(0,0,0,0.05);
            padding: 25px;
            margin-bottom: 25px;
        }
        .blog-title {
            font-size: 28px;
            margin-bottom: 20px;
            color: #333;
        }
        .blog-meta {
            display: flex;
            align-items: center;
            color: #6c757d;
            font-size: 14px;
        }
        .blog-meta img {
            width: 40px;
            height: 40px;
            border-radius: 50%;
            margin-right: 10px;
        }
        .blog-meta span {
            margin-right: 20px;
        }
        .blog-meta i {
            margin-right: 5px;
        }
        .blog-content {
            background: #fff;
            border-radius: 8px;
            box-shadow: 0 2px 15px rgba(0,0,0,0.05);
            padding: 25px;
            margin-bottom: 25px;
            line-height: 1.8;
        }
        .blog-content img {
            max-width: 100%;
            height: auto;
        }
        .blog-tags {
            background: #fff;
            border-radius: 8px;
            box-shadow: 0 2px 15px rgba(0,0,0,0.05);
            padding: 20px 25px;
            margin-bottom: 25px;
        }
        .blog-tag {
            display: inline-block;
            padding: 3px 10px;
            margin: 0 5px 5px 0;
            background: #f0f2f5;
            color: #666;
            border-radius: 3px;
            font-size: 13px;
            text-decoration: none;
        }
        .blog-tag:hover {
            background: #e3e6ea;
            color: #333;
            text-decoration: none;
        }
        .blog-nav {
            background: #fff;
            border-radius: 8px;
            box-shadow: 0 2px 15px rgba(0,0,0,0.05);
            padding: 15px 25px;
            margin-bottom: 25px;
        }
        .blog-nav p {
            margin: 10px 0;
        }
        .blog-nav a {
            color: #5c8af7;
            text-decoration: none;
        }
        .blog-nav a:hover {
            text-decoration: underline;
        }
        .comments-section {
            background: #fff;
            border-radius: 8px;
            box-shadow: 0 2px 15px rgba(0,0,0,0.05);
            padding: 25px;
            margin-bottom: 25px;
        }
        .comments-title {
            font-size: 20px;
            color: #333;
            margin-bottom: 20px;
        }
        .comment-list {
            list-style: none;
            padding: 0;
            margin: 0;
        }
        .comment-item {
            padding: 20px 0;
            border-bottom: 1px solid #eee;
        }
        .comment-item:last-child {
            border-bottom: none;
        }
        .comment-meta {
            display: flex;
            align-items: center;
            margin-bottom: 10px;
        }
        .comment-meta img {
            width: 40px;
            height: 40px;
            border-radius: 50%;
            margin-right: 10px;
        }
        .comment-meta .name {
            font-weight: 500;
            color: #333;
        }
        .comment-meta .date {
            color: #6c757d;
            font-size: 13px;
            margin-left: 10px;
        }
        .comment-content {
            color: #666;
            line-height: 1.6;
        }
        .comment-form {
            margin-top: 30px;
        }
        .widget {
            background: #fff;
            border-radius: 8px;
            box-shadow: 0 2px 15px rgba(0,0,0,0.05);
            padding: 20px;
            margin-bottom: 25px;
        }
        .widget-title {
            font-size: 18px;
            color: #333;
            margin-bottom: 15px;
        }
        .related-list {
            list-style: none;
            padding: 0;
            margin: 0;
        }
        .related-item {
            padding: 10px 0;
            border-bottom: 1px solid #eee;
        }
        .related-item:last-child {
            border-bottom: none;
        }
        .related-item a {
            color: #666;
            text-decoration: none;
        }
        .related-item a:hover {
            color: #5c8af7;
        }
        .related-item .date {
            color: #6c757d;
            font-size: 12px;
        }
        .related-item .views {
            float: right;
            color: #6c757d;
            font-size: 12px;
        }
    </style>
</head>
<body>
    <?php include 'header.php'; ?>

    <main id="main">
        <section class="blog-detail">
            <div class="container">
                <div class="row">
                    <div class="col-lg-8">
                        <article class="blog-post">
                            <div class="blog-header wow fadeInUp">
                                <h1 class="blog-title"><?php echo htmlspecialchars($blog['title']); ?></h1>
                                <div class="blog-meta">
                                    <img src="<?php echo $blog['avatar'] ? $blog['avatar'] : 'assets/img/default-avatar.png'; ?>" alt="<?php echo htmlspecialchars($blog['username']); ?>">
                                    <div>
                                        <span><i class="bx bx-user"></i><?php echo htmlspecialchars($blog['username']); ?></span>
                                        <span><i class="bx bx-folder"></i><?php echo htmlspecialchars($blog['category_name']); ?></span>
                                        <span><i class="bx bx-time"></i><?php echo date('Y-m-d H:i', strtotime($blog['create_time'])); ?></span>
                                        <span><i class="bx bx-show"></i><?php echo $blog['views']; ?> 次瀏覽</span>
                                    </div>
                                </div>
                            </div>

                            <div class="blog-content wow fadeInUp">
                                <?php echo htmlspecialchars_decode($blog['content']); ?>
                            </div>

                            <?php if($blog['tags']) { ?>
                            <div class="blog-tags wow fadeInUp">
                                <i class="bx bx-tag"></i> 標籤：
                                <?php foreach(explode(',', $blog['tags']) as $tag) { ?>
                                <a href="blogs.php?tag=<?php echo urlencode(trim($tag)); ?>" class="blog-tag"><?php echo htmlspecialchars(trim($tag)); ?></a>
                                <?php } ?>
                            </div>
                            <?php } ?>

                            <div class="blog-nav wow fadeInUp">
                                <p>
                                    <strong>上一篇：</strong>
                                    <?php if($prev) { ?>
                                    <a href="blog.php?id=<?php echo $prev['id']; ?>"><?php echo htmlspecialchars($prev['title']); ?></a>
                                    <?php } else { ?>
                                    沒有了
                                    <?php } ?>
                                </p>
                                <p>
                                    <strong>下一篇：</strong>
                                    <?php if($next) { ?>
                                    <a href="blog.php?id=<?php echo $next['id']; ?>"><?php echo htmlspecialchars($next['title']); ?></a>
                                    <?php } else { ?>
                                    沒有了
                                    <?php } ?>
                                </p>
                            </div>

                            <div class="comments-section wow fadeInUp">
                                <h3 class="comments-title">評論（<?php echo count($comments); ?>）</h3>
                                <?php if($comments) { ?>
                                <ul class="comment-list">
                                    <?php foreach($comments as $comment) { ?>
                                    <li class="comment-item" id="comment-<?php echo $comment['id']; ?>">
                                        <div class="comment-meta">
                                            <img src="<?php echo $comment['avatar'] ? $comment['avatar'] : 'assets/img/default-avatar.png'; ?>" alt="<?php echo htmlspecialchars($comment['username']); ?>">
                                            <div>
                                                <span class="name"><?php echo htmlspecialchars($comment['username']); ?></span>
                                                <span class="date"><?php echo date('Y-m-d H:i', strtotime($comment['create_time'])); ?></span>
                                            </div>
                                        </div>
                                        <div class="comment-content">
                                            <?php echo nl2br(htmlspecialchars($comment['content'])); ?>
                                        </div>
                                    </li>
                                    <?php } ?>
                                </ul>
                                <?php } else { ?>
                                <div class="text-center text-muted py-5">
                                    <i class="bx bx-message-square-detail" style="font-size: 48px;"></i>
                                    <p class="mt-3">暫無評論</p>
                                </div>
                                <?php } ?>

                                <?php if($isLogin) { ?>
                                <div class="comment-form">
                                    <h4>發表評論</h4>
                                    <form id="commentForm">
                                        <input type="hidden" name="blog_id" value="<?php echo $id; ?>">
                                        <div class="form-group">
                                            <textarea class="form-control" name="content" rows="4" required></textarea>
                                        </div>
                                        <button type="submit" class="btn btn-primary">提交評論</button>
                                    </form>
                                </div>
                                <?php } else { ?>
                                <div class="text-center mt-4">
                                    <p>請<a href="user/login.php">登錄</a>後發表評論</p>
                                </div>
                                <?php } ?>
                            </div>
                        </article>
                    </div>

                    <div class="col-lg-4">
                        <?php if($related) { ?>
                        <div class="widget wow fadeInUp">
                            <h3 class="widget-title">相關文章</h3>
                            <ul class="related-list">
                                <?php foreach($related as $item) { ?>
                                <li class="related-item">
                                    <a href="blog.php?id=<?php echo $item['id']; ?>"><?php echo htmlspecialchars($item['title']); ?></a>
                                    <div>
                                        <span class="date"><?php echo date('Y-m-d', strtotime($item['create_time'])); ?></span>
                                        <span class="views"><i class="bx bx-show"></i> <?php echo $item['views']; ?></span>
                                    </div>
                                </li>
                                <?php } ?>
                            </ul>
                        </div>
                        <?php } ?>

                        <div class="widget wow fadeInUp">
                            <h3 class="widget-title">分享文章</h3>
                            <div class="social-share">
                                <!-- 這裡可以添加社交分享按鈕 -->
                            </div>
                        </div>
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

        <?php if($isLogin) { ?>
        $(document).ready(function() {
            $("#commentForm").submit(function(e) {
                e.preventDefault();
                var form = $(this);
                var btn = form.find('button[type="submit"]');
                btn.prop('disabled', true);
                
                $.ajax({
                    url: 'ajax.php?act=add_blog_comment',
                    type: 'POST',
                    data: form.serialize(),
                    dataType: 'json',
                    success: function(data) {
                        if(data.code == 1) {
                            layer.msg('評論成功');
                            setTimeout(function() {
                                location.reload();
                            }, 1000);
                        } else {
                            layer.msg(data.msg);
                            btn.prop('disabled', false);
                        }
                    },
                    error: function() {
                        layer.msg('伺服器錯誤');
                        btn.prop('disabled', false);
                    }
                });
            });
        });
        <?php } ?>
    </script>
</body>
</html>