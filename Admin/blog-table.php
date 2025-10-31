<?php
/**
 * 博客列表表格内容
 */
include("../Common/Core_brain.php");
include("./Core_Admin.php");

$page = isset($_GET['page']) ? intval($_GET['page']) : 1;
$limit = 15;
$offset = ($page - 1) * $limit;

$total = $DB->getColumn("SELECT COUNT(*) from nteam_blogs");
$pages = ceil($total / $limit);

$rows = $DB->query("SELECT b.*,c.name as category_name,u.username FROM nteam_blogs b 
    LEFT JOIN nteam_blog_categories c ON b.category_id=c.id 
    LEFT JOIN nteam_users u ON b.author_id=u.id 
    ORDER BY create_time DESC LIMIT $offset,$limit")->fetchAll();
?>
<div class="d-flex justify-content-between mb-3">
    <div>
        <a href="blog-edit.php" class="btn btn-success"><i class="fa fa-plus"></i> 新增博客</a>
        <a href="blog-category.php" class="btn btn-info"><i class="fa fa-list"></i> 分類管理</a>
    </div>
</div>
<table class="table table-striped table-hover">
    <thead>
        <tr>
            <th>ID</th>
            <th width="25%">標題</th>
            <th>分類</th>
            <th>作者</th>
            <th>發布時間</th>
            <th>瀏覽</th>
            <th>評論</th>
            <th>狀態</th>
            <th>操作</th>
        </tr>
    </thead>
    <tbody>
    <?php
    foreach($rows as $row) {
        // 獲取評論數
        $comment_count = $DB->getColumn("SELECT COUNT(*) FROM nteam_blog_comments WHERE blog_id='{$row['id']}'");
        
        echo '<tr>';
        echo '<td>'.$row['id'].'</td>';
        echo '<td>'.htmlspecialchars($row['title']).'</td>';
        echo '<td>'.htmlspecialchars($row['category_name']).'</td>';
        echo '<td>'.htmlspecialchars($row['username']).'</td>';
        echo '<td>'.$row['create_time'].'</td>';
        echo '<td>'.$row['views'].'</td>';
        echo '<td>'.$comment_count.'</td>';
        echo '<td>'.($row['status']==1?'<span class="badge badge-success">顯示</span>':'<span class="badge badge-secondary">隱藏</span>').'</td>';
        echo '<td>
            <a href="blog-edit.php?id='.$row['id'].'" class="btn btn-info btn-sm">編輯</a>
            <button onclick="toggleStatus('.$row['id'].')" class="btn btn-warning btn-sm">'.($row['status']==1?'隱藏':'顯示').'</button>
            <button onclick="deleteBlog('.$row['id'].')" class="btn btn-danger btn-sm">刪除</button>
        </td>';
        echo '</tr>';
    }
    if(empty($rows)) {
        echo '<tr><td colspan="9" class="text-center">暫無數據</td></tr>';
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