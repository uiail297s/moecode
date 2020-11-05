<!DOCTYPE html>
<html lang="ja">
    <head>
        <meta charset="UTF-8">
        <title>mission5-1.php</title>
    </head>
    <body>
<?php
//ミッション3では、投稿をテキストファイルに保存しました。
//ミッション4では、データをデータベースに登録し、また抽出する方法を学びました。
//ここでは上記の2つを組み合わせ、テキストファイルではなくMySQLでデータベースに保存する仕組みを作りましょう。

//DB接続設定
$dsn = 'データベース名'; //データベース名
$user = 'ユーザー名'; //ユーザー名
$password = 'パスワード'; //パスワード
    $pdo = new PDO($dsn,$user,$password,array(PDO::ATTR_ERRMODE => PDO::ERRMODE_WARNING)); //

//テーブル作成
$sql = "CREATE TABLE IF NOT EXISTS mission5_1"
."("
."id INT AUTO_INCREMENT PRIMARY KEY,"
."name char(32),"
."comment varchar(256),"
."date DATETIME,"
."pass INT"　//INTなのでパスワードは数字で入力
.");";
$stmt = $pdo -> query($sql);

//エラー
error_reporting(E_ALL & ~E_NOTICE);

//投稿
if(!empty("submit")){
    //変数定義
    $checkNo = $_POST["checkNo"];
    $name = $_POST["name1"];
    $comment = $_POST["comment1"];
    $pass = $_POST["pass1"];
    $date = new DATETIME();
    $date = $date -> format('Y-m-d H:i:s');
    
    //空欄じゃない　かつ　編集申請されていないとき
    if(!empty($name) && !empty($comment) && empty($checkNo)){
        //sqlのテーブルにデータを書き込み
        $sql = $pdo -> prepare("INSERT INTO mission5_1 (name, comment, date, pass) VALUES(:name, :comment, :date, :pass)");
        $sql -> bindParam(':name', $name, PDO::PARAM_STR); //
        $sql -> bindParam(':comment', $comment, PDO::PARAM_STR);
        $sql -> bindParam(':date', $date, PDO::PARAM_STR);
        $sql -> bindParam(':pass', $pass, PDO::PARAM_INT);
        $sql -> execute();
    }
    
    //空欄じゃない　かつ　編集申請されているとき
    if(!empty($name) && !empty($comment) && !empty($checkNo)){
        //sqlテーブルのデータの編集
        $sql = 'UPDATE mission5_1 SET name=:name, comment=:comment, date=:date, pass=:pass WHERE id=:id';
        $stmt = $pdo -> prepare($sql);
        $stmt -> bindParam(':name', $name, PDO::PARAM_STR);
        $stmt -> bindParam(':comment', $comment, PDO::PARAM_STR);
        $stmt -> bindParam(':date', $date, PDO::PARAM_STR);
        $stmt -> bindParam(':pass', $pass, PDO::PARAM_INT);
        $stmt -> bindParam(':id', $checkNo, PDO::PARAM_INT);
        $stmt -> execute();
    }
}



//削除
if(!empty("delete")){ //削除ボタンが押されたとき
    //変数設定
    $deleteNo = $_POST["deleteNo"]; //削除する投稿番号
    $deletePass = $_POST["pass2"]; //削除フォームのパスワード
    //削除番号、パスワードが入力されたとき
    if(!empty($deleteNo) && !empty($deletePass)){ 
        //パスワードを抽出するためデータへアクセス
        $sql = 'SELECT * FROM mission5_1 WHERE id=:id'; //SELECT文WHEREで選んで抽出（今回はid=投稿番号）
        $stmt = $pdo -> prepare($sql); //差し替えるデータを含むsqlを準備
        $stmt -> bindParam(':id',$deleteNo, PDO::PARAM_INT); //その差し替えるデータの値を指定
        $stmt -> execute(); //SQL実行
        $results = $stmt -> fetchAll(); //結果セットに残っている全ての行を含む配列を返す
        foreach($results as $row){
            $passCheck = $row['pass']; //パスワード取得
        }
         //パスワードが一致したとき
        if($passCheck == $deletePass){ 
            $sql = "delete from mission5_1 where id=:id"; //削除処理
            $stmt = $pdo -> prepare($sql); //sqlを準備
            $stmt -> bindParam(':id', $deleteNo, PDO::PARAM_INT); //deleteNoに差し替える
            $stmt -> execute(); //sqlを実行
        }
    }
}



//編集
if(!empty("edit")){
    $editNo = $_POST["editNo"];
    $editPass = $_POST["pass3"];
    //データの中身を取得
    if(!empty($editNo) && !empty($editPass)){
        $sql = 'SELECT * FROM mission5_1 WHERE id=:id';
        $stmt = $pdo -> prepare($sql);
        $stmt -> bindParam(':id', $editNo, PDO::PARAM_INT); //idが編集したい番号と一致するとき限定
        $stmt -> execute();
        $results = $stmt -> fetchAll();
        foreach($results as $row);{
            $passCheck = $row['pass']; //パスワードのみ取得
        }
        //パスワードが一致したとき
        if($passCheck == $editPass){
            //編集したい内容を新しい変数に代入
            foreach($results as $row){
                $editName = $row['name'];
                $editComment = $row['comment'];
                $newPass = $editPass;
                $sentNo = $editNo;
            }
        }
    }
}

?>

<!-- 投稿フォーム -->
<form action="" method="post">
    <input type="text" name="name1" placeholder="名前" value="<?php echo $editName;?>"><br>
    <input type="text" name="comment1" placeholder="コメント" value="<?php echo $editComment;?>"><br>
    <input type="password" name="pass1" placeholder="パスワード" value="<?php echo $editPass;?>">
    <input type="submit" name="submit" value="送信"><br>
    <input type="hidden" name="checkNo" value="<?php echo $sentNo;?>">
    <br>
</form>
<br>
<!-- 削除フォーム -->
<form action="" method="post">
    <input type="number" name="deleteNo" placeholder="削除対象番号"><br>
    <input type="password" name="pass2" placeholder="パスワード">
    <input type="submit" name="delete" value="削除"><br>
</form>
<br>
<!-- 編集フォーム -->
<form action="" method="post">
    <input type="number" name="editNo" placeholder="編集対象番号"><br>
    <input type="password" name="pass3" placeholder="パスワード">
    <input type="submit" name="edit" value="編集"><br>
</form>

<?php
// $sql = 'DROP TABLE mission5_1';
// $stmt = $pdo->query($sql);

//表示
$sql = 'SELECT * FROM mission5_1';
$stmt = $pdo -> query($sql);
$results = $stmt -> fetchAll();
foreach($results as $row){
    echo $row['id']." ".$row['name']." ".$row['pass']." ".$row['comment']." ".$row['date']."<br>";
}

?>
    </body>
</html>
