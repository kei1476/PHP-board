<?php 
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

if(empty($_SESSION['admin_login']) || $_SESSION['admin_login'] !== true) {
    header("Location: ./admin.php");
    exit;
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

if(!empty($_GET['message_id']) && empty($_POST['message_id'])) {
    $stmt = $pdo->prepare("SELECT* FROM board WHERE id=:id");
    $stmt -> bindValue(':id',$_GET['message_id'],PDO::PARAM_INT);
    $stmt->execute();
    $message_data = $stmt->fetch();

    if(empty($message_data)) {
        header("Location: ./admin.php");
        exit;
    } 
  } elseif(!empty($_POST['message_id'])) {
       $pdo->beginTransaction();
       
       try {
        $stmt = $pdo->prepare("DELETE FROM board WHERE id = :id" );
        $stmt->bindValue(':id',$_POST['message_id'],PDO::PARAM_INT);
        $stmt->execute();
        $res = $pdo->commit();
       } catch(Exeption $e) {
        $pdo->rollBack();
       }

       if($res) {
        header("Location: ./admin.php");
       }
}

$stmt = null;
$pdo = null;
?>

<!DOCTYPE html>
<html lang="ja">
<head>
    <meta charset="UTF-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="../css/style.css">
    <title>編集ページ</title>
</head>
<body>
   <h1>ひと言掲示板　削除ページ</h1>
   <?php if(!empty($error_message)): ?>
    <ul class="error_message">
        <?php foreach($error_message as $value): ?>
            <li><?php echo $value; ?></li>
        <?php endforeach; ?>
    </ul>
    <?php endif; ?>
    <p class="text-confirm">以下の投稿を削除します。<br>よろしければ「削除」ボタンを押してください。</p>
<form method="post">
	<div>
		<label for="view_name">表示名</label>
		<input id="view_name" type="text" name="view_name" value="<?php if( !empty($message_data['view_name']) ){ echo $message_data['view_name']; } elseif(!empty($view_name)){echo htmlspecialchars( $view_name, ENT_QUOTES, 'UTF-8');}?>" disabled>
	</div>
	<div>
		<label for="title">タイトル</label>
		<input id="title" type="text" name="title" value="<?php if( !empty($message_data['title']) ){ echo $message_data['title']; } elseif(!empty($title)){echo htmlspecialchars( $title, ENT_QUOTES, 'UTF-8');}?>" disabled>
	</div>
	<div>
		<label for="message">ひと言メッセージ</label>
		<textarea id="message" name="message" disabled><?php if( !empty($message_data['message']) ){ echo $message_data['message']; } elseif( !empty($message) ){ echo htmlspecialchars( $message, ENT_QUOTES, 'UTF-8'); }?></textarea>
	</div>
    <a class="btn_cancel" href="admin.php">キャンセル</a>
	<input type="submit" name="btn_submit" value="削除">
    <input type="hidden" name="message_id" value="<?php if( !empty($message_data['id']) ){ echo $message_data['id']; }elseif( !empty($_POST['message_id']) ){ echo htmlspecialchars( $_POST['message_id'], ENT_QUOTES, 'UTF-8'); } ?>">
</form>
</body>
</html>