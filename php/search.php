<?php 
date_default_timezone_set('Asia/Tokyo');

$current_date = null;
$message_data = array();
$error_message = array();
$pdo = null;
$stmt = null;
$option = null;


try {
    $option = array(
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
        PDO::MYSQL_ATTR_MULTI_STATEMENTS => false,
    );
    $pdo = new PDO('mysql:charaset=UTF8;dbname=board;host=localhost','root','',$option);

} catch(PDOExeption $e) {
    $error_message[] = $e->getMessage();
}

if(!empty($_POST['search_btn'])) {

    $search = preg_replace( '/\A[\p{C}\p{Z}]++|[\p{C}\p{Z}]++\z/u', '', $_POST['search']);

    $search_word = '%'.$search.'%';

    if(empty($search)) {
        $error_message[] = '検索内容を入力してください。';
    }

    if(empty($error_message)) {
        $pdo->beginTransaction();
        try {
            $stmt = $pdo->prepare("SELECT* FROM board WHERE message LIKE :search");

            $stmt->bindParam(':search',$search_word,PDO::PARAM_STR);

            // $stmt = $pdo->prepare("SELECT* FROM board WHERE message LIKE ?");

            // $stmt->execute(['%'.$search.'%']);

            $stmt->execute();

            $message_data = $stmt->fetchAll();

            $pdo->commit();

        } catch(Exception $e) {
            $pdo->rollBack();
        }

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
    <title>結果ページ</title>
</head>
<body>
   <h1>検索結果</h1>
   <?php if(!empty($error_message)): ?>
    <ul class="error_message">
        <?php foreach($error_message as $value): ?>
            <li><?php echo $value; ?></li>
        <?php endforeach; ?>
    </ul>
    <?php endif; ?>
<section>
    <?php if(!empty($message_data)): ?>
    <?php foreach($message_data as $value): ?>
    <article>
        <div class="info">
            <h2><?php echo $value['view_name']; ?></h2>
            <p><?php echo $value['title']?></p>
            <time><?php echo date('Y年m月d日 H:i', strtotime($value['post_date'])); ?></time>
        </div>
        <p><?php echo nl2br($value['message']); ?></p>
    </article>
    <?php endforeach; ?>
    <?php elseif(empty($message_data)):?>
        <p><?php echo $_POST['search'].'に該当する書き込みはありません。';?></p>
    <?php endif; ?>
    <a href="./index.php">戻る</a>
</section> 
</body>
</html>