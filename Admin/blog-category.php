<?php
/**
 * 博客分類管理
 */
include("./Common/Core_brain.php");
include("./Admin/Core_Admin.php");
?>
<!DOCTYPE html>
<html lang="zh">
<head>
    <meta charset="utf-8">
    <title>博客分類管理 - <?php echo conf('Name');?></title>
    <?php include './Admin/head.php'; ?>
</head>

<body class="fix-header">
    <div id="wrapper">
        <?php include './Admin/top.php'; ?>
        <?php include './Admin/sidebar.php'; ?>
        <div id="page-wrapper">
            <div class="container-fluid">
                <div class="row bg-title">
                    <div class="col-lg-3 col-md-4 col-sm-4 col-xs-12">
                        <h4 class="page-title">博客分類管理</h4>
                    </div>
                </div>
                <div class="row">
                    <div class="col-md-12">
                        <div class="white-box">
                            <div class="mb-3">
                                <button class="btn btn-success" onclick="addCategory()"><i class="fa fa-plus"></i> 新增分類</button>
                                <a href="blog.php" class="btn btn-default">返回博客列表</a>
                            </div>
                            <div class="table-responsive">
                                <table class="table table-striped">
                                    <thead>
                                        <tr>
                                            <th>ID</th>
                                            <th>分類名稱</th>
                                            <th>描述</th>
                                            <th>文章數量</th>
                                            <th>排序</th>
                                            <th>操作</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php
                                        $categories = $DB->query("SELECT c.*,(SELECT COUNT(*) FROM nteam_blogs WHERE category_id=c.id) as blog_count FROM nteam_blog_categories c ORDER BY sort DESC")->fetchAll();
                                        foreach($categories as $row) {
                                            echo '<tr>';
                                            echo '<td>'.$row['id'].'</td>';
                                            echo '<td>'.htmlspecialchars($row['name']).'</td>';
                                            echo '<td>'.htmlspecialchars($row['description']).'</td>';
                                            echo '<td>'.$row['blog_count'].'</td>';
                                            echo '<td>'.$row['sort'].'</td>';
                                            echo '<td>
                                                <button class="btn btn-info btn-sm" onclick="editCategory('.$row['id'].',\''.htmlspecialchars($row['name']).'\',\''.htmlspecialchars($row['description']).'\','.$row['sort'].')">編輯</button>
                                                <button class="btn btn-danger btn-sm" onclick="deleteCategory('.$row['id'].')">刪除</button>
                                            </td>';
                                            echo '</tr>';
                                        }
                                        if(empty($categories)) {
                                            echo '<tr><td colspan="6" class="text-center">暫無數據</td></tr>';
                                        }
                                        ?>
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <?php include './Admin/foot.php'; ?>
    <script>
        function addCategory() {
            layer.open({
                type: 1,
                title: '新增分類',
                area: ['400px', '300px'],
                content: `
                    <div class="p-3">
                        <div class="form-group">
                            <label>分類名稱</label>
                            <input type="text" class="form-control" id="name">
                        </div>
                        <div class="form-group">
                            <label>描述</label>
                            <textarea class="form-control" id="description" rows="3"></textarea>
                        </div>
                        <div class="form-group">
                            <label>排序</label>
                            <input type="number" class="form-control" id="sort" value="0">
                        </div>
                        <button class="btn btn-success" onclick="submitCategory()">提交</button>
                    </div>
                `
            });
        }

        function editCategory(id, name, description, sort) {
            layer.open({
                type: 1,
                title: '編輯分類',
                area: ['400px', '300px'],
                content: `
                    <div class="p-3">
                        <input type="hidden" id="category_id" value="${id}">
                        <div class="form-group">
                            <label>分類名稱</label>
                            <input type="text" class="form-control" id="name" value="${name}">
                        </div>
                        <div class="form-group">
                            <label>描述</label>
                            <textarea class="form-control" id="description" rows="3">${description}</textarea>
                        </div>
                        <div class="form-group">
                            <label>排序</label>
                            <input type="number" class="form-control" id="sort" value="${sort}">
                        </div>
                        <button class="btn btn-success" onclick="submitCategory()">提交</button>
                    </div>
                `
            });
        }

        function submitCategory() {
            var id = $('#category_id').val();
            var name = $('#name').val();
            var description = $('#description').val();
            var sort = $('#sort').val();

            if(!name) {
                layer.msg('請輸入分類名稱');
                return;
            }

            $.post('./Admin/ajax.php?act=save_blog_category', {
                id: id,
                name: name,
                description: description,
                sort: sort
            }, function(data) {
                if(data.code == 1) {
                    layer.closeAll();
                    location.reload();
                } else {
                    layer.msg(data.msg);
                }
            }, 'json');
        }

        function deleteCategory(id) {
            if(confirm('確定要刪除這個分類嗎？如果分類下有文章，需要先移動或刪除這些文章。')) {
                $.post('./Admin/ajax.php?act=delete_blog_category', {id: id}, function(data) {
                    if(data.code == 1) {
                        location.reload();
                    } else {
                        layer.msg(data.msg);
                    }
                }, 'json');
            }
        }
    </script>
</body>
</html>