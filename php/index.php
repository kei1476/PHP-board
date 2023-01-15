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

    $view_name = preg_replace( '/\A[\p{C}\p{Z}]++|[\p{C}\p{Z}]++\z/u', '', $_POST['view_name']);
    $title = preg_replace( '/\A[\p{C}\p{Z}]++|[\p{C}\p{Z}]++\z/u', '', $_POST['title']);
	$message = preg_replace( '/\A[\p{C}\p{Z}]++|[\p{C}\p{Z}]++\z/u', '', $_POST['message']);

    if(empty($view_name)) {
        $error_message[] = '表示名を入力してください。';
    } else {
        $_SESSION['view_name'] = $view_name;
    }


    if(empty($title)) {
        $error_message[] = 'タイトルを入力してください。';
    } elseif(40<mb_strlen($title,'UTF-8')) {
        $error_message[] = 'タイトルは40文字以内で入力してください。';
    }

    if(empty($message)) {
        $error_message[] = 'ひと言メッセージを入力してください。';
    } elseif(100<mb_strlen($message,'UTF-8')) {
        $error_message[] = 'ひと言メッセージは100文字以内で入力してください。';
    }


    if(empty($error_message)) {
        $current_date = date("Y-m-d H:i:s");

          $pdo->beginTransaction();

        try {

            // SQL作成
            $stmt = $pdo->prepare("INSERT INTO board (view_name, message, post_date,title) VALUES ( :view_name, :message, :current_date,:title)");

            // 値をセット
            $stmt->bindParam( ':view_name', $view_name, PDO::PARAM_STR);
            $stmt->bindParam( ':message', $message, PDO::PARAM_STR);
            $stmt->bindParam( ':current_date', $current_date, PDO::PARAM_STR);
            $stmt->bindParam(':title',$title,PDO::PARAM_STR);

            // SQLクエリの実行
            $res = $stmt->execute();

            // コミット
            $res = $pdo->commit();

        } catch(Exception $e) {

            // エラーが発生した時はロールバック
            $pdo->rollBack();
        }

        if( $res ) {
            $_SESSION['success_message'] = 'メッセージを書き込みました。';
        } else {
            $error_message[] = '書き込みに失敗しました。';
        }

        // プリペアドステートメントを削除
        $stmt = null;

        header('Location: ./');
        exit;
    }
}

if(empty($error_message)) {
    $sql = "SELECT view_name,message,post_date,title FROM board ORDER BY post_date DESC";
    $message_array = $pdo->query($sql);
}

if(!empty($_POST['search_btn'])) {
    header('Location: ./search.php');
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
    <title>Document</title>
</head>
<body>
   <h1>ひと言掲示板</h1>
   <?php  if( empty($_POST['btn_submit']) && !empty($_SESSION    ['success_message']) ):?>
     <p class="success_message"><?php echo  htmlspecialchars( $_SESSION['success_message'], ENT_QUOTES, 'UTF-8');?></p>
     <?php unset($_SESSION['success_message']); ?>
   <?php endif; ?>
   <?php if(!empty($error_message)): ?>
    <ul class="error_message">
        <?php foreach($error_message as $value): ?>
            <li><?php echo $value; ?></li>
        <?php endforeach; ?>
    </ul>
    <?php endif; ?>


<!-- 入力欄 -->
<form method="post">
	<div>
		<label for="view_name">表示名</label>
		<input id="view_name" type="text" name="view_name" value="<?php if(!empty($_SESSION['view_name'])){echo htmlspecialchars($_SESSION['view_name'],ENT_QUOTES,'UTF-8');}?>">
	</div>
    <div>
        <label for="title">タイトル</label>
        <input type="text" name="title" value="">
    </div>
	<div>
		<label for="message">ひと言メッセージ</label>
		<textarea id="message" name="message"><?php if( !empty($message) ){ echo htmlspecialchars( $message, ENT_QUOTES, 'UTF-8'); } ?></textarea>
	</div>
	<input type="submit" name="btn_submit" value="書き込む">
</form>

<hr>

<!-- 検索欄 -->
<form method="post" name="search_form" action="./search.php">
    <label for="search">投稿内容を検索</label>
    <input type="text" name="search">
    <input type="submit" name="search_btn" value="検索">
</form>

<!-- 表示欄 -->
<section>
    <?php if(!empty($message_array)): ?>
    <?php foreach($message_array as $value): ?>
    <article>
        <div class="info">
            <h2><?php echo $value['view_name']; ?></h2>
            <p><?php echo $value['title']?></p>
            <time><?php echo date('Y年m月d日 H:i', strtotime($value['post_date'])); ?></time>
        </div>
        <p><?php echo nl2br($value['message']); ?></p>
    </article>
        <?php endforeach; ?>
        <?php endif; ?>
</section> 
</body>
</html>