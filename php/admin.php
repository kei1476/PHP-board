<?php 
define( 'PASSWORD', 'adminPassword');

date_default_timezone_set('Asia/Tokyo');

$current_date = null;
$message = array();
$message_array = array();
$success_message = null;
$error_message = array();
$pdo = null;
$stmt = null;
$res = null;
$option = null;

session_start();

if(!empty($_GET['btn_logout'])) {
    unset($_SESSION['admin_login']);
}

try {
    $option = array(
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
        PDO::MYSQL_ATTR_MULTI_STATEMENTS => false,
    );
    $pdo = new PDO('mysql:charaset=UTF8;dbname=board;host=localhost','root','',$option);
} catch(PDOExeption $e) {
    $error_message[] = $e->getMessage();
}


if(!empty($_POST['btn_submit'])) {
    if(!empty($_POST['admin_password']) && $_POST['admin_password'] === PASSWORD) {
        $_SESSION['admin_login'] = true;
    } else {
        $error_message[] = 'ログインに失敗しました。';
    }
}

if(empty($error_message)) {
    $sql = "SELECT*  FROM board ORDER BY post_date DESC";
    $message_array = $pdo->query($sql);
}

$pdo = null;
?>

<!DOCTYPE html>
<html lang="ja">
<head>
    <meta charset="UTF-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="../css/style.css">
    <title>ひと言掲示板 管理ページ</title>
</head>
<body>
   <h1>ひと言掲示板 管理ページ</h1>
   <?php if(!empty($error_message)): ?>
    <ul class="error_message">
        <?php foreach($error_message as $value): ?>
            <li><?php echo $value; ?></li>
        <?php endforeach; ?>
    </ul>
    <?php endif; ?>
<section>
    <?php if(!empty($_SESSION['admin_login']) && $_SESSION['admin_login'] === true):?>
    <?php if(!empty($message_array)): ?>
    <?php foreach($message_array as $value): ?>
    <article>
        <div class="info">
            <h2><?php echo $value['view_name']; ?></h2>
            <p><?php echo $value['title']?></p>
            <time><?php echo date('Y年m月d日 H:i', strtotime($value['post_date'])); ?></time>
            <p><a href="edit.php?message_id=<?php echo $value['id']; ?>">編集</a>  
            <a href="delete.php?message_id=<?php echo $value['id']; ?>">削除</a></p>
        </div>
        <p><?php echo nl2br($value['message']); ?></p>
        </article>
        <?php endforeach; ?>
        <?php endif; ?>
        <form method="get" action="">
	       <input type="submit" name="btn_logout" value="ログアウト">
        </form>
    <?php else: ?>
        <form method="post">
            <div>
                <label for="admin_password">ログインパスワード</label>
                <input type="password" id="admin_password" name="admin_password" value="">
            </div>
            <input type="submit" name="btn_submit" value="ログイン">
        </form>
    <?php endif;?>
    </article>
</section> 
</body>
</html>