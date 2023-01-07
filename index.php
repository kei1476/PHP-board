<?php
define( 'FILENAME', './message.txt');

date_default_timezone_set('Asia/Tokyo');

$current_date = null;
$data = null;
$file_handle = null;
$split_data = null;
$message = array();
$message_array = array();


if( !empty($_POST['btn_submit']) ) {
	
	if( $file_handle = fopen( FILENAME, "a") ) {

		$current_date = date("Y-m-d H:i:s");

		$data = "'".$_POST['view_name']."','".$_POST['message']."','".$current_date."'\n";
	

		fwrite( $file_handle, $data);
	
		fclose( $file_handle);
	}		
}

if( $file_handle = fopen( FILENAME,'r') ) {
	while( $data = fgets($file_handle) ){
	
		$split_data = preg_split( '/\'/', $data);
		
		$message = array(
			'view_name' => $split_data[1],
			'message' => $split_data[3],
			'post_date' => $split_data[5]
		);
		array_unshift( $message_array, $message);
	}
	
	fclose( $file_handle);
}

?>

<!DOCTYPE html>
<html lang="ja">
<head>
    <meta charset="UTF-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Document</title>
    <link rel="stylesheet" href="css/reset.css">
    <link rel="stylesheet" href="css/style.css">
</head>
<body>
    <form method="post">
       <div>
        <label for="view_name">表示名</label>
        <input type="text" name="view_name" id="view_name">
       </div> 
       <div>
        <label for="message">ひと言メッセージ</label>
        <textarea name="message" id="message" cols="30" rows="10"></textarea>
       </div>
       <input type="submit" name="btn_submit" value="書き込む">
    </form>
    <hr>
    <section>
        <?php if( !empty($message_array) ): ?>
        <?php foreach( $message_array as $value ): ?>
        <article>
            <div class="info">
                <h2><?php echo $value['view_name']; ?></h2>
                <time><?php echo date('Y年m月d日 H:i', strtotime($value['post_date'])); ?></time>
            </div>
            <p><?php echo $value['message']; ?></p>
        </article>
        <?php endforeach; ?>
        <?php endif; ?>   
    </section>
</body>
</html>