<?php
/**
 * 公告列表
 */
include("./Common/Core_brain.php");
include("./Admin/Core_Admin.php");
?>
<!DOCTYPE html>
<html lang="zh">
<head>
    <meta charset="utf-8">
    <title>公告管理 - <?php echo conf('Name');?></title>
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
                        <h4 class="page-title">公告管理</h4>
                    </div>
                </div>
                <div class="row">
                    <div class="col-md-12">
                        <div class="white-box">
                            <div class="table-responsive" id="table-container">
                                <!-- 表格内容由Ajax加载 -->
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <?php include './Admin/foot.php'; ?>
    <script>
        function loadTable(page = 1) {
            $.get('./Admin/announcement-table.php?page=' + page, function(data) {
                $('#table-container').html(data);
            });
        }

        $(document).ready(function() {
            loadTable();
        });

        function deleteAnn(id) {
            if(confirm('確定要刪除這條公告嗎？')) {
                $.post('./Admin/ajax.php?act=delete_announcement', {id: id}, function(data) {
                    if(data.code == 1) {
                        loadTable();
                        layer.msg('刪除成功');
                    } else {
                        layer.msg(data.msg);
                    }
                }, 'json');
            }
        }

        function togglePin(id) {
            $.post('./Admin/ajax.php?act=toggle_announcement_pin', {id: id}, function(data) {
                if(data.code == 1) {
                    loadTable();
                    layer.msg('操作成功');
                } else {
                    layer.msg(data.msg);
                }
            }, 'json');
        }
    </script>
</body>
</html>