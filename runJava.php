<?php
include ('upload.php');
$username = $_SESSION["username"];
$dbhost2 = $_SERVER['RDS_HOSTNAME'];
$dbport2 = $_SERVER['RDS_PORT'];
$dbname2 = $_SERVER['RDS_DB_NAME'];

$dsn2 = "mysql:host={$dbhost2};port={$dbport2};dbname={$dbname2}";
$usernameRD2 = $_SERVER['RDS_USERNAME'];
$passwordRD2 = $_SERVER['RDS_PASSWORD'];

$db2 = new PDO($dsn2, $usernameRD2, $passwordRD2);

$filename2 = $_GET['fileu'];

$stmt = $db2->prepare("SELECT id,ActualFilename,filename,fileUrl FROM FileDetails WHERE filename = '$filename2'");
$stmt->execute();
$result = $stmt->fetch();

$fileUrl = $result['fileUrl'];

$actualFile = $result['ActualFilename'];
if($s3->getObject("tempsecupload", $filename2,'temp.java')){
	echo "got object". "\n";
	$result1 = $s3->getObject("tempsecupload", $filename2,'temp.java');
	
	$homepage = file_get_contents('temp.java');
	
	$absolute_path = realpath("temp.java");

	
	
	//chdir('/home/ec2-user/pmd-bin-5.1.0/bin');
	exec('wget "https://sourceforge.net/projects/pmd/files/pmd/5.5.2/pmd-bin-5.5.2.zip"');
	exec("unzip -q pmd-bin-5.5.2.zip");
	//exec("cd /home/ec2-user/pmd-bin-5.5.2/bin");
	chdir('pmd-bin-5.5.2/bin');
	exec(" ./run.sh pmd -d /var/app/current/temp.java -f xml -R rulesets/internal/all-java.xml", $out, $val);
	//print_r($out);
	$resultFile = $filename2.'_resultFile.txt';
	
	$string_data =  implode(PHP_EOL, $out);

	
	file_put_contents($resultFile,$string_data);
	$absolute_path1 = realpath($resultFile);

	if ($s3->putObjectFile($resultFile, "tempsecupload", $resultFile, S3::ACL_PUBLIC_READ)) {
    echo "We successfully uploaded your file.";
	$createTable = "CREATE TABLE IF NOT EXISTS RunDetails (id INT(6)  NOT NULL AUTO_INCREMENT PRIMARY KEY, username VARCHAR(150) NOT NULL  , ActualFilename VARCHAR(120),filename VARCHAR(300), fileUrl VARCHAR(300) NOT NULL UNIQUE)";
	$db2->exec($createTable);
	
	$FileUrl = "http://tempsecupload.s3.amazonaws.com/".$resultFile;

	$qry = $db2->prepare('INSERT INTO RunDetails (username,ActualFilename, filename, fileUrl) VALUES (?, ?, ?, ?)');
	$qry->EXECUTE(array($username, $actualFile,$resultFile, $FileUrl));
	header('Location: /upload.php');

}else{
    echo "Something went wrong while uploading your file... sorry.";
}



	
}
	
	

else {echo "no object";}

?>