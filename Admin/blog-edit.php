<?php
/**
 * 新增/編輯博客
 */
include("./Common/Core_brain.php");
include("./Admin/Core_Admin.php");

$id = isset($_GET['id']) ? intval($_GET['id']) : 0;
if($id) {
    $row = $DB->query("SELECT * FROM nteam_blogs WHERE id=:id", [':id'=>$id])->fetch();
    if(!$row) {
        exit('博客不存在');
    }
}

// 獲取所有分類
$categories = $DB->query("SELECT * FROM nteam_blog_categories ORDER BY sort DESC")->fetchAll();
?>
<!DOCTYPE html>
<html lang="zh">
<head>
    <meta charset="utf-8">
    <title><?php echo $id?'編輯':'新增';?>博客 - <?php echo conf('Name');?></title>
    <?php include './Admin/head.php'; ?>
    <link href="./assets/bower_components/html5-editor/bootstrap-wysihtml5.css" rel="stylesheet">
    <link href="./assets/bower_components/bootstrap-tagsinput/dist/bootstrap-tagsinput.css" rel="stylesheet">
</head>

<body class="fix-header">
    <div id="wrapper">
        <?php include './Admin/top.php'; ?>
        <?php include './Admin/sidebar.php'; ?>
        <div id="page-wrapper">
            <div class="container-fluid">
                <div class="row bg-title">
                    <div class="col-lg-3 col-md-4 col-sm-4 col-xs-12">
                        <h4 class="page-title"><?php echo $id?'編輯':'新增';?>博客</h4>
                    </div>
                </div>
                <div class="row">
                    <div class="col-md-12">
                        <div class="white-box">
                            <form method="post" id="form">
                                <input type="hidden" name="id" value="<?php echo $id; ?>">
                                <div class="form-group">
                                    <label>標題</label>
                                    <input type="text" class="form-control" name="title" value="<?php echo $row['title']??''; ?>" required>
                                </div>
                                <div class="form-group">
                                    <label>分類</label>
                                    <select class="form-control" name="category_id" required>
                                        <option value="">請選擇分類</option>
                                        <?php foreach($categories as $cat) { ?>
                                        <option value="<?php echo $cat['id']; ?>" <?php echo isset($row)&&$row['category_id']==$cat['id']?'selected':''; ?>>
                                            <?php echo htmlspecialchars($cat['name']); ?>
                                        </option>
                                        <?php } ?>
                                    </select>
                                </div>
                                <div class="form-group">
                                    <label>摘要</label>
                                    <textarea class="form-control" name="summary" rows="3"><?php echo $row['summary']??''; ?></textarea>
                                </div>
                                <div class="form-group">
                                    <label>內容</label>
                                    <textarea class="form-control" name="content" rows="20" id="content"><?php echo $row['content']??''; ?></textarea>
                                </div>
                                <div class="form-group">
                                    <label>標籤</label>
                                    <input type="text" class="form-control" name="tags" value="<?php echo $row['tags']??''; ?>" data-role="tagsinput">
                                    <small class="text-muted">用逗號分隔多個標籤</small>
                                </div>
                                <div class="form-group">
                                    <label>狀態</label>
                                    <select class="form-control" name="status">
                                        <option value="1" <?php echo isset($row)&&$row['status']==1?'selected':''; ?>>顯示</option>
                                        <option value="0" <?php echo isset($row)&&$row['status']==0?'selected':''; ?>>隱藏</option>
                                    </select>
                                </div>
                                <button type="submit" class="btn btn-success">提交</button>
                                <a href="javascript:history.back(-1);" class="btn btn-default">返回</a>
                            </form>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <?php include './Admin/foot.php'; ?>
    <script src="./assets/bower_components/html5-editor/wysihtml5-0.3.0.js"></script>
    <script src="./assets/bower_components/html5-editor/bootstrap-wysihtml5.js"></script>
    <script src="./assets/bower_components/bootstrap-tagsinput/dist/bootstrap-tagsinput.min.js"></script>
    <script>
        $(document).ready(function() {
            $('#content').wysihtml5();

            $("#form").submit(function(e) {
                e.preventDefault();
                var form = $(this);
                var btn = form.find('button[type="submit"]');
                btn.prop('disabled', true);
                $.ajax({
                    url: './Admin/ajax.php?act=save_blog',
                    type: 'POST',
                    data: form.serialize(),
                    dataType: 'json',
                    success: function(data) {
                        if(data.code == 1) {
                            layer.msg('保存成功');
                            setTimeout(function() {
                                window.location.href = './Admin/blog.php';
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
    </script>
</body>
</html>