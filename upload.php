<?php

session_start();
//echo 'session'.$_SESSION['username'];
$username = $_SESSION["username"];
if (!class_exists('S3'))require_once('S3.php');
 $ini = parse_ini_file('config.ini');
 //echo $ini['awsAccessKey'];
//AWS access info
if (!defined('awsAccessKey')) define('awsAccessKey', $ini['awsAccessKey']);
if (!defined('awsSecretKey')) define('awsSecretKey', $ini['awsSecretKey']);
 
//instantiate the class
$s3 = new S3(awsAccessKey, awsSecretKey);
 
//we'll continue our script from here in step 4!

if ( isset( $_POST['send'] ) ) { 
if ($_SERVER["REQUEST_METHOD"] == "POST"){
	$filename1 = uniqid(rand(), true) ;
  $actual_file_name =  $_FILES['userfile']['name'] ;
  $fileInfo = pathinfo($actual_file_name);
$fileExt=  $fileInfo['extension'];
if($fileExt != "cpp" AND $fileExt != "c" AND $fileExt != "cpp" AND $fileExt != "php" AND $fileExt != "java" AND $fileExt != "py" ){
	echo "Sorry wrong file extension!!!";
	exit;
}
if ($_FILES["userfile"]["size"] > 500000) {
    echo "Sorry, your file is too large.";
    exit;
}
if (empty($_FILES['userfile']['name'])) {
    echo "File required";
	exit;
  }

 $fileName = $filename1.".".$_FILES['userfile']['name'];
 $fileTempName = $_FILES['userfile']['tmp_name'];
$s3->putBucket("tempsecupload", S3::ACL_PUBLIC_READ); //creat bucket

 
//move the file
if ($s3->putObjectFile($fileTempName, "tempsecupload", $fileName, S3::ACL_PUBLIC_READ)) {
    echo "We successfully uploaded your file.";
}else{
    echo "Something went wrong while uploading your file... sorry.";
}

$Public_url = "http://tempsecupload.s3.amazonaws.com/".$fileName;
date_default_timezone_set('America/chicago');
//echo date("Y-m-d h:i:s A");
$FileDate = date("Y-m-d h:i:s A");
$dbhost = $_SERVER['RDS_HOSTNAME'];
$dbport = $_SERVER['RDS_PORT'];
$dbname = $_SERVER['RDS_DB_NAME'];

$dsn = "mysql:host={$dbhost};port={$dbport};dbname={$dbname}";
$usernameRD = $_SERVER['RDS_USERNAME'];
$passwordRD = $_SERVER['RDS_PASSWORD'];

$db = new PDO($dsn, $usernameRD, $passwordRD);

$createTable = "CREATE TABLE IF NOT EXISTS FileDetails (id INT(6)  NOT NULL AUTO_INCREMENT PRIMARY KEY, username VARCHAR(150) NOT NULL  , ActualFilename VARCHAR(120),filename VARCHAR(300), fileUrl VARCHAR(300) NOT NULL UNIQUE, FileDate VARCHAR(30) )";
$db->exec($createTable);

$qry = $db->prepare('INSERT INTO FileDetails (username,ActualFilename, filename, fileUrl, FileDate) VALUES (?, ?, ?, ?, ?)');
$qry->EXECUTE(array($username, $actual_file_name,$fileName, $Public_url, $FileDate));




	
	
}
}

?>

<html>
<body>



<form action="" enctype="multipart/form-data" method="post">
    Files to upload: <br>
   <input type="file" name="userfile" size="40">
   <input type="submit" value="Send" name = "send">
   <input type="button" name="Logout" value="Logout" id="Logout" onclick="location='logout.php'" >
  
</form>    



</body>
</html>

<?php
//show user's files
$dbhost1 = $_SERVER['RDS_HOSTNAME'];
$dbport1 = $_SERVER['RDS_PORT'];
$dbname1 = $_SERVER['RDS_DB_NAME'];

$dsn1 = "mysql:host={$dbhost1};port={$dbport1};dbname={$dbname1}";
$usernameRD1 = $_SERVER['RDS_USERNAME'];
$passwordRD1 = $_SERVER['RDS_PASSWORD'];

$db1 = new PDO($dsn1, $usernameRD1, $passwordRD1);

$stmt = $db1->prepare("SELECT id,ActualFilename,filename,fileUrl,FileDate FROM FileDetails WHERE username = '$username'");
$stmt->execute();

//$result = $stmt->fetch();
echo "<table align='center'>";
echo '<tr>';
echo '<th>Files Uploaded</th>';
echo '</tr>';
echo '<tr><td>Filename</td><td>Date and Time</td><td>File</td><td>Run</td></tr>';
while($result = $stmt->fetch()){
	$filename = $result['ActualFilename'];
	$fileUrl = $result['fileUrl'];
	$id = $result['id'];
	$file = $result['filename'];
	$FDate = $result['FileDate'];
	
	
	 echo '<tr>';
	
    echo '<td>'.$filename.'</td>';
    echo '<td>'.$FDate.'</td>';
	$fileInfo = pathinfo($filename);
	$fileExt=  $fileInfo['extension'];
	//if($fileInfo['extension'] == "py"){ echo "python";}
    echo "<td><a href=\"$fileUrl\">$filename</a><br /></td>";
	if($fileInfo['extension'] == "py"){
	echo "<td><form action = run.php?fileu=$file method=post >
                    <input type=submit  value=Pylint name = ids  >
	</form></td>";}
	if($fileInfo['extension'] == "c" OR $fileInfo['extension'] == "cpp" ){
		echo "<td><form action = runC.php?fileu=$file method=post >
                    <input type=submit  value=Flawfinder name = ids  >
	</form></td>";
			
	}
	if($fileInfo['extension'] == "java"){
	echo "<td><form action = runJava.php?fileu=$file method=post >
                    <input type=submit  value=PMD name = ids  >
	</form></td>";}
	
    //echo "<td><input type=\"button\" name=\"run\" value=\"run\" id=\"$id\" onclick=\"location=\'run.php\'\" ></td>";
	echo '</tr>';
	
}
echo '</table>';

//show result files

$stmt1 = $db1->prepare("SELECT id,ActualFilename,filename,fileUrl FROM RunDetails WHERE username = '$username'");
$stmt1->execute();

//$result = $stmt->fetch();
echo "<table align='center' id = 'res'>";
echo '<tr>';
echo '<th>Results (only the latest runs exists.. the result files get overwritten)</th>';
echo '</tr>';
while($result1 = $stmt1->fetch()){
	$filenameActual = $result1['ActualFilename'];
	$fileUrlresult = $result1['fileUrl'];
	
	 echo '<tr>';
	
    //echo '<td>'.$filename.'</td>';
    echo "<td><a href=\"$fileUrlresult\">$filenameActual</a><br /></td>";
	
					
    //echo "<td><input type=\"button\" name=\"run\" value=\"run\" id=\"$id\" onclick=\"location=\'run.php\'\" ></td>";
	echo '</tr>';
	
}
echo '</table>';





?>