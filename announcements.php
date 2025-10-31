<?php
include("./Common/Core_brain.php");

$page = isset($_GET['page']) ? intval($_GET['page']) : 1;
$limit = 10;
$offset = ($page - 1) * $limit;

$where = "WHERE status=1";
if(isset($_GET['type'])) {
    $type = intval($_GET['type']);
    $where .= " AND type=".$type;
}

$total = $DB->getColumn("SELECT COUNT(*) FROM nteam_announcements {$where}");
$pages = ceil($total / $limit);

$announcements = $DB->query("SELECT a.*,u.username FROM nteam_announcements a LEFT JOIN nteam_users u ON a.creator_id=u.id {$where} ORDER BY pinned DESC, create_time DESC LIMIT {$offset},{$limit}")->fetchAll();
?>
<!DOCTYPE html>
<html lang="zh">
<head>
    <meta charset="utf-8">
    <meta content="width=device-width, initial-scale=1.0" name="viewport">
    <title>公告中心 - <?php echo conf('Name');?></title>
    <meta content="<?php echo conf('Descriptison');?>" name="descriptison">
    <meta content="<?php echo conf('Keywords');?>" name="keywords">
    <link href="assets/vendor/bootstrap/css/bootstrap.min.css" rel="stylesheet">
    <link href="assets/vendor/icofont/icofont.min.css" rel="stylesheet">
    <link href="assets/vendor/boxicons/css/boxicons.min.css" rel="stylesheet">
    <link href="assets/vendor/animate.css/animate.min.css" rel="stylesheet">
    <link href="assets/css/style.css" rel="stylesheet">
    <style>
        .announcements-section {
            padding: 60px 0;
            background: #f8f9fa;
        }
        .announcement-card {
            background: #fff;
            border-radius: 8px;
            box-shadow: 0 2px 15px rgba(0,0,0,0.05);
            margin-bottom: 20px;
            transition: all 0.3s;
        }
        .announcement-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 5px 20px rgba(0,0,0,0.1);
        }
        .announcement-card .card-body {
            padding: 20px;
        }
        .announcement-meta {
            font-size: 14px;
            color: #6c757d;
            margin-bottom: 10px;
        }
        .announcement-meta i {
            margin-right: 5px;
        }
        .announcement-title {
            font-size: 18px;
            margin-bottom: 10px;
            color: #333;
        }
        .announcement-title a {
            color: inherit;
            text-decoration: none;
        }
        .announcement-title a:hover {
            color: #5c8af7;
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
        .pagination {
            margin-top: 30px;
            justify-content: center;
        }
    </style>
</head>
<body>
    <?php include 'header.php'; ?>

    <main id="main">
        <section class="announcements-section">
            <div class="container">
                <div class="section-title">
                    <h2>公告中心</h2>
                    <p>团队最新公告与重要通知</p>
                </div>

                <div class="row">
                    <div class="col-lg-9">
                        <?php if($announcements) { foreach($announcements as $row) { ?>
                        <div class="announcement-card wow fadeInUp">
                            <div class="card-body">
                                <h3 class="announcement-title">
                                    <?php if($row['pinned']) { ?>
                                    <span class="pinned-badge"><i class="bx bx-pin"></i>置顶</span>
                                    <?php } ?>
                                    <span class="announcement-type type-<?php echo $row['type']; ?>">
                                        <?php echo $row['type']==1 ? '普通公告' : '重要公告'; ?>
                                    </span>
                                    <a href="announcement.php?id=<?php echo $row['id']; ?>"><?php echo htmlspecialchars($row['title']); ?></a>
                                </h3>
                                <div class="announcement-meta">
                                    <span><i class="bx bx-user"></i><?php echo htmlspecialchars($row['username']); ?></span>
                                    <span><i class="bx bx-time"></i><?php echo date('Y-m-d H:i', strtotime($row['create_time'])); ?></span>
                                    <span><i class="bx bx-show"></i><?php echo $row['views']; ?> 次查看</span>
                                </div>
                                <div class="announcement-preview">
                                    <?php echo mb_substr(strip_tags($row['content']), 0, 150).'...'; ?>
                                </div>
                            </div>
                        </div>
                        <?php } } else { ?>
                        <div class="text-center text-muted py-5">
                            <i class="bx bx-news" style="font-size: 48px;"></i>
                            <p class="mt-3">暂无公告</p>
                        </div>
                        <?php } ?>

                        <?php if($pages > 1) { ?>
                        <ul class="pagination">
                            <?php if($page > 1) { ?>
                            <li class="page-item">
                                <a class="page-link" href="?page=<?php echo $page-1; ?><?php echo isset($type)?'&type='.$type:''; ?>">上一页</a>
                            </li>
                            <?php } ?>
                            <?php
                            $start = max(1, $page - 2);
                            $end = min($pages, $page + 2);
                            for($i=$start; $i<=$end; $i++) {
                            ?>
                            <li class="page-item <?php echo $i==$page?'active':''; ?>">
                                <a class="page-link" href="?page=<?php echo $i; ?><?php echo isset($type)?'&type='.$type:''; ?>"><?php echo $i; ?></a>
                            </li>
                            <?php } ?>
                            <?php if($page < $pages) { ?>
                            <li class="page-item">
                                <a class="page-link" href="?page=<?php echo $page+1; ?><?php echo isset($type)?'&type='.$type:''; ?>">下一页</a>
                            </li>
                            <?php } ?>
                        </ul>
                        <?php } ?>
                    </div>

                    <div class="col-lg-3">
                        <div class="card mb-4">
                            <div class="card-header">
                                <h5 class="m-0">公告分类</h5>
                            </div>
                            <div class="list-group list-group-flush">
                                <a href="announcements.php" class="list-group-item list-group-item-action <?php echo !isset($type)?'active':''; ?>">
                                    全部公告
                                </a>
                                <a href="?type=1" class="list-group-item list-group-item-action <?php echo isset($type)&&$type==1?'active':''; ?>">
                                    普通公告
                                </a>
                                <a href="?type=2" class="list-group-item list-group-item-action <?php echo isset($type)&&$type==2?'active':''; ?>">
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