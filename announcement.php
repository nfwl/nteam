<?php
include("./Common/Core_brain.php");

$id = isset($_GET['id']) ? intval($_GET['id']) : 0;
if(!$id) {
    header('Location: announcements.php');
    exit;
}

// 更新瀏覽次數
$DB->query("UPDATE nteam_announcements SET views=views+1 WHERE id=:id", [':id'=>$id]);

// 獲取公告詳情
$announcement = $DB->query("SELECT a.*,u.username,u.avatar FROM nteam_announcements a LEFT JOIN nteam_users u ON a.creator_id=u.id WHERE a.id=:id AND a.status=1", [':id'=>$id])->fetch();
if(!$announcement) {
    header('Location: announcements.php');
    exit;
}

// 獲取上一篇和下一篇
$prev = $DB->query("SELECT id,title FROM nteam_announcements WHERE id<:id AND status=1 ORDER BY id DESC LIMIT 1", [':id'=>$id])->fetch();
$next = $DB->query("SELECT id,title FROM nteam_announcements WHERE id>:id AND status=1 ORDER BY id ASC LIMIT 1", [':id'=>$id])->fetch();
?>
<!DOCTYPE html>
<html lang="zh">
<head>
    <meta charset="utf-8">
    <meta content="width=device-width, initial-scale=1.0" name="viewport">
    <title><?php echo htmlspecialchars($announcement['title']); ?> - <?php echo conf('Name');?></title>
    <meta content="<?php echo conf('Descriptison');?>" name="descriptison">
    <meta content="<?php echo conf('Keywords');?>" name="keywords">
    <link href="assets/vendor/bootstrap/css/bootstrap.min.css" rel="stylesheet">
    <link href="assets/vendor/icofont/icofont.min.css" rel="stylesheet">
    <link href="assets/vendor/boxicons/css/boxicons.min.css" rel="stylesheet">
    <link href="assets/vendor/animate.css/animate.min.css" rel="stylesheet">
    <link href="assets/css/style.css" rel="stylesheet">
    <style>
        .announcement-detail {
            padding: 60px 0;
            background: #f8f9fa;
        }
        .announcement-header {
            background: #fff;
            border-radius: 8px;
            box-shadow: 0 2px 15px rgba(0,0,0,0.05);
            padding: 25px;
            margin-bottom: 25px;
        }
        .announcement-title {
            font-size: 24px;
            margin-bottom: 20px;
            color: #333;
        }
        .announcement-meta {
            display: flex;
            align-items: center;
            color: #6c757d;
            font-size: 14px;
        }
        .announcement-meta img {
            width: 40px;
            height: 40px;
            border-radius: 50%;
            margin-right: 10px;
        }
        .announcement-meta span {
            margin-right: 20px;
        }
        .announcement-meta i {
            margin-right: 5px;
        }
        .announcement-content {
            background: #fff;
            border-radius: 8px;
            box-shadow: 0 2px 15px rgba(0,0,0,0.05);
            padding: 25px;
            margin-bottom: 25px;
            line-height: 1.8;
        }
        .announcement-nav {
            background: #fff;
            border-radius: 8px;
            box-shadow: 0 2px 15px rgba(0,0,0,0.05);
            padding: 15px 25px;
        }
        .announcement-nav p {
            margin: 10px 0;
        }
        .announcement-nav a {
            color: #5c8af7;
            text-decoration: none;
        }
        .announcement-nav a:hover {
            text-decoration: underline;
        }
        .announcement-type {
            display: inline-block;
            padding: 2px 8px;
            border-radius: 3px;
            font-size: 12px;
            margin-right: 10px;
        }
        .type-1 {
            background: #e3f2fd;
            color: #1976d2;
        }
        .type-2 {
            background: #fbe9e7;
            color: #d84315;
        }
        .pinned-badge {
            background: #fff3e0;
            color: #ef6c00;
            padding: 2px 8px;
            border-radius: 3px;
            font-size: 12px;
            margin-right: 10px;
        }
    </style>
</head>
<body>
    <?php include 'header.php'; ?>

    <main id="main">
        <section class="announcement-detail">
            <div class="container">
                <div class="row">
                    <div class="col-lg-9">
                        <div class="announcement-header wow fadeInUp">
                            <h1 class="announcement-title">
                                <?php if($announcement['pinned']) { ?>
                                <span class="pinned-badge"><i class="bx bx-pin"></i>置顶</span>
                                <?php } ?>
                                <span class="announcement-type type-<?php echo $announcement['type']; ?>">
                                    <?php echo $announcement['type']==1 ? '普通公告' : '重要公告'; ?>
                                </span>
                                <?php echo htmlspecialchars($announcement['title']); ?>
                            </h1>
                            <div class="announcement-meta">
                                <img src="<?php echo $announcement['avatar'] ? $announcement['avatar'] : 'assets/img/default-avatar.png'; ?>" alt="<?php echo htmlspecialchars($announcement['username']); ?>">
                                <div>
                                    <span><i class="bx bx-user"></i><?php echo htmlspecialchars($announcement['username']); ?></span>
                                    <span><i class="bx bx-time"></i><?php echo date('Y-m-d H:i', strtotime($announcement['create_time'])); ?></span>
                                    <span><i class="bx bx-show"></i><?php echo $announcement['views']; ?> 次查看</span>
                                </div>
                            </div>
                        </div>

                        <div class="announcement-content wow fadeInUp">
                            <?php echo htmlspecialchars_decode($announcement['content']); ?>
                        </div>

                        <div class="announcement-nav wow fadeInUp">
                            <p>
                                <strong>上一篇：</strong>
                                <?php if($prev) { ?>
                                <a href="announcement.php?id=<?php echo $prev['id']; ?>"><?php echo htmlspecialchars($prev['title']); ?></a>
                                <?php } else { ?>
                                没有了
                                <?php } ?>
                            </p>
                            <p>
                                <strong>下一篇：</strong>
                                <?php if($next) { ?>
                                <a href="announcement.php?id=<?php echo $next['id']; ?>"><?php echo htmlspecialchars($next['title']); ?></a>
                                <?php } else { ?>
                                没有了
                                <?php } ?>
                            </p>
                        </div>
                    </div>

                    <div class="col-lg-3">
                        <div class="card mb-4">
                            <div class="card-header">
                                <h5 class="m-0">公告分类</h5>
                            </div>
                            <div class="list-group list-group-flush">
                                <a href="announcements.php" class="list-group-item list-group-item-action">
                                    全部公告
                                </a>
                                <a href="announcements.php?type=1" class="list-group-item list-group-item-action">
                                    普通公告
                                </a>
                                <a href="announcements.php?type=2" class="list-group-item list-group-item-action">
                                    重要公告
                                </a>
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
    </script>
</body>
</html>