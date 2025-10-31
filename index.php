<?php
include("./Common/Core_brain.php");
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="utf-8">
<meta content="<?php echo conf('Descriptison');?>" name="description">
<meta content="width=device-width, initial-scale=1.0" name="viewport">
<title><?php echo conf('Name');?> - <?php echo conf('SiteName');?>
</title>
<meta content="<?php echo conf('Keywords');?>" name="keywords">
<!-- Favicons -->
<link href="assets/img/favicon.png" rel="icon">
<link href="assets/img/apple-touch-icon.png" rel="apple-touch-icon">
<!-- Google Fonts -->
<link href="https://fonts.googleapis.com/css?family=Open+Sans:300,300i,400,400i,600,600i,700,700i|Roboto:300,300i,400,400i,500,500i,700,700i|Poppins:300,300i,400,400i,500,500i,600,600i,700,700i" rel="stylesheet">
<!-- Vendor CSS Files -->
<link href="assets/vendor/bootstrap/css/bootstrap.min.css" rel="stylesheet">
<link href="assets/vendor/icofont/icofont.min.css" rel="stylesheet">
<link href="assets/vendor/boxicons/css/boxicons.min.css" rel="stylesheet">
<link href="//at.alicdn.com/t/font_1886590_v6zxjghcwli.css" rel="stylesheet">
<link href="assets/vendor/animate.css/animate.min.css" rel="stylesheet">
<link href="assets/vendor/owl.carousel/assets/owl.carousel.min.css" rel="stylesheet">
<link href="assets/vendor/venobox/venobox.css" rel="stylesheet">
<!-- Template Main CSS File -->
<link href="assets/css/style.css" rel="stylesheet">
</head>
<body>
<!-- ======= Hero Section ======= --><section id="hero">
<div class="hero-container">
  <div id="heroCarousel" class="carousel slide carousel-fade" data-ride="carousel">
    <ol class="carousel-indicators" id="hero-carousel-indicators">
    </ol>
    <div class="carousel-inner" role="listbox">
      <!-- Slide 1 -->
      <div class="carousel-item active">
        <div class="carousel-background">
          <img src="assets/img/background.png" alt="background">
        </div>
        <div class="carousel-container">
          <div class="carousel-content">
            <h2 class="animate__animated animate__fadeInDown"><span><?php echo conf('Name') ?>
            </span></h2>
            <p class="animate__animated animate__fadeInUp">
              <?php echo conf_index('Index_Slide1') ?>
            </p>
            <a href="#about" class="btn-get-started animate__animated animate__fadeInUp scrollto">了解更多</a>
          </div>
        </div>
      </div>
      <!-- Slide 2 -->
      <div class="carousel-item">
        <div class="carousel-background">
          <img src="assets/img/background.png" alt="background">
        </div>
        <div class="carousel-container">
          <div class="carousel-content">
            <h2 class="animate__animated animate__fadeInDown">成員查詢</h2>
            <p class="animate__animated animate__fadeInUp">
              <?php echo conf_index('Index_Slide2') ?>
            </p>
            <a href="javascript:;" id="Query" class="btn-get-started animate__animated animate__fadeInUp scrollto">成員查詢</a>
          </div>
        </div>
      </div>
      <!-- Slide 3 -->
      <div class="carousel-item">
        <div class="carousel-background">
          <img src="assets/img/background.png" alt="background">
        </div>
        <div class="carousel-container">
          <div class="carousel-content">
            <h2 class="animate__animated animate__fadeInDown">加入我們</h2>
            <p class="animate__animated animate__fadeInUp">
              <?php echo conf_index('Index_Slide3') ?>
            </p>
            <a href="javascript:;" id="Join" class="btn-get-started animate__animated animate__fadeInUp scrollto">申請加入</a>
          </div>
        </div>
      </div>
    </div>
    <a class="carousel-control-prev" href="#heroCarousel" role="button" data-slide="prev">
    <span class="carousel-control-prev-icon icofont-thin-double-left" aria-hidden="true"></span>
    <span class="sr-only">Previous</span>
    </a>
    <a class="carousel-control-next" href="#heroCarousel" role="button" data-slide="next">
    <span class="carousel-control-next-icon icofont-thin-double-right" aria-hidden="true"></span>
    <span class="sr-only">Next</span>
    </a>
  </div>
</div>
</section><!-- End Hero --><!-- ======= Header ======= --><header id="header">
<div class="container d-flex">
  <div class="logo mr-auto">
    <h1 class="text-light"><a href="index.php"><span><?php echo conf('Name') ?></span></a></h1>
    <!-- Uncomment below if you prefer to use an image logo -->
    <!-- <a href="index.php"><img src="assets/img/logo.png" alt="" class="img-fluid"></a>-->
  </div>

  <nav class="nav-menu d-none d-lg-block">
    <ul>
      <li class="active"><a href="index.php">首頁</a></li>
      <?php if(isset($_SESSION['userid'])) { ?>
        <li class="drop-down">
          <a href="javascript:void(0);">
            <?php 
              $user = $DB->getRow("SELECT * FROM nteam_users WHERE id=:id", [':id'=>$_SESSION['userid']]);
              echo htmlspecialchars($user['username']);
            ?>
          </a>
          <ul>
            <li><a href="user/profile.php">個人資料</a></li>
            <li><a href="user/settings.php">帳號設定</a></li>
            <?php if($user['is_admin'] == 1) { ?>
              <li><a href="Admin/">管理後台</a></li>
            <?php } ?>
            <li><a href="javascript:void(0);" onclick="logout()">登出</a></li>
          </ul>
        </li>
      <?php } else { ?>
        <li><a href="user/login.php">登入</a></li>
        <li><a href="user/register.php">註冊</a></li>
      <?php } ?>
    </ul>
  </nav>
  <nav class="nav-menu d-none d-lg-block">
  <ul>
    <li class="active"><a href="#header">首頁</a></li>
    <li><a href="#about">關於</a></li>
    <li><a href="#services">服務</a></li>
    <li><a href="#latest-news">最新動態</a></li>
    <li><a href="announcements.php">公告</a></li>
    <li><a href="blogs.php">博客</a></li>
    <li><a href="#portfolio">項目</a></li>
    <li><a href="#team">成員</a></li>
    <li><a href="#contact">聯繫</a></li>
  </ul>
  </nav><!-- .nav-menu -->
</div>
</header><!-- End Header --><main id="main"><!-- ======= About Us Section ======= --><section id="about" class="about">
<div class="container">
  <div class="section-title">
    <h2>About Us</h2>
    <p>
      <?php echo conf_index('Index_About') ?>
    </p>
  </div>
</div>
</section><!-- End About Us Section --><!-- ======= Our Services Section ======= --><section id="services" class="services">
<div class="container">
  <div class="section-title">
    <h2>Services</h2>
  </div>
  <div class="row">
    <div class="col-md-6 col-lg-3 d-flex align-items-stretch mb-5 mb-lg-0">
      <div class="icon-box">
        <div class="icon">
          <i class="bx bxl-dribbble"></i>
        </div>
        <h4 class="title"><a href=""><?php echo conf_index('Index_Services_t1') ?>
        </a></h4>
        <p class="description">
          <?php echo conf_index('Index_Services_d1') ?>
        </p>
      </div>
    </div>
    <div class="col-md-6 col-lg-3 d-flex align-items-stretch mb-5 mb-lg-0">
      <div class="icon-box">
        <div class="icon">
          <i class="bx bx-file"></i>
        </div>
        <h4 class="title"><a href=""><?php echo conf_index('Index_Services_t2') ?>
        </a></h4>
        <p class="description">
          <?php echo conf_index('Index_Services_d2') ?>
        </p>
      </div>
    </div>
    <div class="col-md-6 col-lg-3 d-flex align-items-stretch mb-5 mb-lg-0">
      <div class="icon-box">
        <div class="icon">
          <i class="bx bx-tachometer"></i>
        </div>
        <h4 class="title"><a href=""><?php echo conf_index('Index_Services_t3') ?>
        </a></h4>
        <p class="description">
          <?php echo conf_index('Index_Services_d3') ?>
        </p>
      </div>
    </div>
    <div class="col-md-6 col-lg-3 d-flex align-items-stretch mb-5 mb-lg-0">
      <div class="icon-box">
        <div class="icon">
          <i class="bx bx-world"></i>
        </div>
        <h4 class="title"><a href=""><?php echo conf_index('Index_Services_t4') ?>
        </a></h4>
        <p class="description">
          <?php echo conf_index('Index_Services_d4') ?>
        </p>
      </div>
    </div>
  </div>
</div>
</section><!-- End Our Services Section --><!-- ======= Cta Section ======= --><section class="cta">
<div class="container">
  <div class="text-center">
    <h3>Hitokoto</h3>
    <p id="hitokoto">
      :D 获取中...
    </p>
    <script src="https://v1.hitokoto.cn/?encode=js&amp;select=%23hitokoto" defer=""></script>
  </div>
</div>
</section><!-- End Cta Section -->

<!-- ======= Latest News Section ======= -->
<section id="latest-news" class="latest-news section-bg">
  <div class="container">
    <div class="section-title">
      <h2>最新動態</h2>
    </div>
    
    <div class="row">
      <!-- 公告部分 -->
      <div class="col-lg-6">
        <h3>最新公告</h3>
        <div class="announcements-list">
          <?php
          $announcements = $DB->query("SELECT * FROM nteam_announcement WHERE status=1 ORDER BY id DESC LIMIT 5");
          while($announcement = $announcements->fetch()){
          ?>
            <div class="announcement-item">
              <h4><a href="announcement.php?id=<?php echo $announcement['id'];?>"><?php echo $announcement['title'];?></a></h4>
              <p class="date"><i class="bx bx-calendar"></i> <?php echo date('Y-m-d', strtotime($announcement['addtime']));?></p>
            </div>
          <?php } ?>
          <div class="text-center mt-4">
            <a href="announcements.php" class="btn-read-more">查看更多公告</a>
          </div>
        </div>
      </div>
      
      <!-- 博客部分 -->
      <div class="col-lg-6">
        <h3>最新文章</h3>
        <div class="blogs-list">
          <?php
          $blogs = $DB->query("SELECT * FROM nteam_blog WHERE status=1 ORDER BY id DESC LIMIT 5");
          while($blog = $blogs->fetch()){
          ?>
            <div class="blog-item">
              <h4><a href="blog.php?id=<?php echo $blog['id'];?>"><?php echo $blog['title'];?></a></h4>
              <p class="meta">
                <i class="bx bx-calendar"></i> <?php echo date('Y-m-d', strtotime($blog['addtime']));?>
                <i class="bx bx-folder ml-3"></i> <?php echo $blog['category'];?>
              </p>
            </div>
          <?php } ?>
          <div class="text-center mt-4">
            <a href="blogs.php" class="btn-read-more">查看更多文章</a>
          </div>
        </div>
      </div>
    </div>
  </div>
</section>
<!-- End Latest News Section -->

<!-- ======= Our Portfolio Section ======= --><section id="portfolio" class="portfolio section-bg">
<div class="container">
  <div class="section-title">
    <h2>Portfolio</h2>
  </div>
  <div class="row">
    <div class="col-lg-12 d-flex justify-content-center">
      <ul id="portfolio-flters">
        <li data-filter="*" class="filter-active">所有</li>
        <li data-filter=".filter-web">网页</li>
        <li data-filter=".filter-app">程序</li>
      </ul>
    </div>
  </div>
  <div class="row portfolio-container">
  <?php
  $projects=$DB->query("SELECT * FROM nteam_project_list WHERE status=1 and is_show=1 and Audit_status=1 ORDER BY id");
  while($project = $projects->fetch()){
  ?>
    <div class="col-lg-4 col-md-6 portfolio-item filter-<?php echo $project['type'];?>
      ">
      <div class="portfolio-wrap">
        <img src="<?php echo $project['img'];?>" class="img-fluid" alt="background">
        <div class="portfolio-info">
          <h4><?php echo $project['name'];?>
          </h4>
          <p>
            <?php echo $project['sketch'];?>
          </p>
        </div>
        <div class="portfolio-links">
          <a href="<?php echo $project['img'];?>" data-gall="portfolioGallery" class="venobox" title="Web 1"><i class="bx bx-plus"></i></a>
          <a href="project.php?id=<?php echo $project['id'];?>" title="More Details"><i class="bx bx-link"></i></a>
        </div>
      </div>
    </div>
    <?php
  }
  ?>
  </div>
</div>
</section><!-- End Our Portfolio Section --><!-- ======= Our Team Section ======= --><section id="team" class="team">
<div class="container">
  <div class="section-title">
    <h2>Team</h2>
    <p>
      一群充满活力の少年.
    </p>
  </div>
  <div class="row">
  <?php
  $teams=$DB->query("SELECT * FROM nteam_team_member WHERE Audit_status=1 and is_show=1 ORDER BY id");
  while($team = $teams->fetch()){
  ?>
    <div class="col-xl-3 col-lg-4 col-md-6">
      <div class="member">
        <img src="https://q1.qlogo.cn/g?b=qq&nk=<?php echo $team['qq'];?>&s=640" class="img-fluid" alt="member
		">
        <div class="member-info">
          <div class="member-info-content">
            <h4><?php echo $team['name'];?>
            </h4>
            <span>QQ：<?php echo $team['qq'];?>
            <br>
            <?php echo $team['describe'];?>
            </span>
          </div>
          <div class="social">
            <a href="https://wpa.qq.com/msgrd?v=3&uin=<?php echo $team['qq'];?>&site=qq&menu=yes" class="QQ"><i class="NanFeng Icon-QQ" style="font-size: 24px;"></i></a>
            <a href="<?php echo $team['Url'];?>" class="weixin"><i class="NanFeng Icon-weixin" style="font-size: 24px;"></i></a>
            <a href="http://mail.qq.com/cgi-bin/qm_share?t=qm_mailme&email=<?php echo $team['qq'];?>@qq.com" class="youxiang"><i class="NanFeng Icon-youxiang" style="font-size: 24px;"></i></a>
          </div>
        </div>
      </div>
    </div>
    <?php
  }
  ?>
  </div>
  </section><!-- End Our Team Section --><!-- ======= Contact Us Section ======= --><section id="contact" class="contact section-bg">
  <div class="container">
    <div class="section-title">
      <h2>联系</h2>
    </div>
  </div>
  <div class="container-fluid">
    <div class="row">
      <div class="col-lg-6 d-flex align-items-stretch infos">
        <div class="row">
          <div class="col-lg-6 info d-flex flex-column align-items-stretch">
            <i class="bx bx-map"></i>
            <h4>地点</h4>
            <p>
              <?php echo conf_index('Index_Place') ?>
            </p>
          </div>
          <div class="col-lg-6 info info-bg d-flex flex-column align-items-stretch">
            <i class="bx bx-phone"></i>
            <h4>电话</h4>
            <p>
            +86 
              <?php echo conf_index('Index_Phone') ?>
            </p>
          </div>
          <div class="col-lg-6 info info-bg d-flex flex-column align-items-stretch">
            <i class="bx bx-envelope"></i>
            <h4>电子邮件</h4>
            <p>
              <?php echo conf_index('Index_Email') ?>
            </p>
          </div>
          <div class="col-lg-6 info d-flex flex-column align-items-stretch">
            <i class="bx bx-time-five"></i>
            <h4>工作时间</h4>
            <p>
            周一至周五：上午9点至下午5点
              <br>
            星期日：上午9点至下午1点
            </p>
          </div>
        </div>
      </div>
      <div class="col-lg-6 d-flex align-items-stretch contact-form-wrap">
        <form action="Ajax.php?act=contact" method="post" role="form" class="php-email-form">
          <div class="form-row">
            <div class="col-md-6 form-group">
              <label for="name">您的名字</label>
              <input type="text" name="name" class="form-control" id="name" data-rule="minlen:4" data-msg="Please enter at least 4 chars"/>
              <div class="validate">
              </div>
            </div>
            <div class="col-md-6 form-group">
              <label for="email">您的邮箱</label>
              <input type="email" class="form-control" name="email" id="email" data-rule="email" data-msg="Please enter a valid email"/>
              <div class="validate">
              </div>
            </div>
          </div>
          <div class="form-group">
            <label for="subject">邮件主题</label>
            <input type="text" class="form-control" name="subject" id="subject" data-rule="minlen:4" data-msg="Please enter at least 8 chars of subject"/>
            <div class="validate">
            </div>
          </div>
          <div class="form-group">
            <label for="message">邮件内容</label>
            <textarea class="form-control" name="message" rows="8" data-rule="required" data-msg="Please write something for us"></textarea>
            <div class="validate">
            </div>
          </div>
          <div class="mb-3">
            <div class="loading">
            Loading
            </div>
            <div class="error-message">
            </div>
            <div class="sent-message">
            您的留言已发送。谢谢！
            </div>
          </div>
          <div class="text-center">
            <button type="submit" id="submit">发送</button>
          </div>
        </form>
      </div>
    </div>
  </div>
  </section><!-- End Contact Us Section --></main><!-- End #main -->
  <?php require 'foot.php';?>