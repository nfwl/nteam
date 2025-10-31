<?php
/**
 * 公告列表表格内容
 */
include("../Common/Core_brain.php");
include("./Core_Admin.php");

$page = isset($_GET['page']) ? intval($_GET['page']) : 1;
$limit = 15;
$offset = ($page - 1) * $limit;

$total = $DB->getColumn("SELECT COUNT(*) from nteam_announcements");
$pages = ceil($total / $limit);

$rows = $DB->query("SELECT a.*,u.username FROM nteam_announcements a LEFT JOIN nteam_users u ON a.creator_id=u.id ORDER BY pinned DESC, create_time DESC LIMIT $offset,$limit")->fetchAll();
?>
<div class="d-flex justify-content-between mb-3">
    <div>
        <a href="announcement-edit.php" class="btn btn-success"><i class="fa fa-plus"></i> 新增公告</a>
    </div>
</div>
<table class="table table-striped table-hover">
    <thead>
        <tr>
            <th>ID</th>
            <th width="30%">標題</th>
            <th>類型</th>
            <th>作者</th>
            <th>發布時間</th>
            <th>瀏覽</th>
            <th>狀態</th>
            <th>操作</th>
        </tr>
    </thead>
    <tbody>
    <?php
    foreach($rows as $row) {
        echo '<tr>';
        echo '<td>'.$row['id'].'</td>';
        echo '<td>'.($row['pinned']?'<span class="badge badge-warning">置顶</span> ':'').htmlspecialchars($row['title']).'</td>';
        echo '<td>'.($row['type']==1?'<span class="badge badge-info">普通</span>':'<span class="badge badge-danger">重要</span>').'</td>';
        echo '<td>'.htmlspecialchars($row['username']).'</td>';
        echo '<td>'.$row['create_time'].'</td>';
        echo '<td>'.$row['views'].'</td>';
        echo '<td>'.($row['status']==1?'<span class="badge badge-success">顯示</span>':'<span class="badge badge-secondary">隱藏</span>').'</td>';
        echo '<td>
            <a href="announcement-edit.php?id='.$row['id'].'" class="btn btn-info btn-sm">編輯</a>
            <button onclick="togglePin('.$row['id'].')" class="btn btn-warning btn-sm">'.($row['pinned']?'取消置頂':'置頂').'</button>
            <button onclick="deleteAnn('.$row['id'].')" class="btn btn-danger btn-sm">刪除</button>
        </td>';
        echo '</tr>';
    }
    if(empty($rows)) {
        echo '<tr><td colspan="8" class="text-center">暫無數據</td></tr>';
    }
    ?>
    </tbody>
</table>
<?php if($pages > 1) { ?>
<div class="text-center">
    <ul class="pagination">
        <?php
        $start = max(1, $page - 3);
        $end = min($pages, $page + 3);
        for ($i = $start; $i <= $end; $i++) {
            echo '<li class="page-item '.($i==$page?'active':'').'"><a class="page-link" href="javascript:loadTable('.$i.')">'.$i.'</a></li>';
        }
        ?>
    </ul>
</div>
<?php } ?>