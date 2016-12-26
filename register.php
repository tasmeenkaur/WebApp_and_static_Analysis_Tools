<html>
<title>Register</title>
<h4>Register</H4>
</head>
 
<body>
<form name="register"  method="post" action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]);?>">
	<table width="510" border="0" align="center">
		<tr>
			<td colspan="2"><p><strong>Registration Form</strong></p></td>
		</tr>
		<tr><td><p><span>* required field.</span></p>
		</td></tr>
		<tr>
			<td>Username:</td>
			<td><input type="text" name="username" maxlength="20" />*</td>
		</tr>
		<tr>
			<td>Password:</td>
			<td><input type="password" name="password1" />*</td>
		</tr>
		<tr>
			<td>Confirm Password:</td>
			<td><input type="password" name="password2" />*</td>
		</tr>
		<tr>
			<td>Email:</td>
			<td><input type="text" name="email" id="email" />*</td>
		</tr>
		<tr>
			
			<td><input type="submit" value="Register" /></td>
		</tr>
	</table>
</form>
</body>
</html>



<?php
$username == $password1 =$password2 = $email = "";
if ($_SERVER["REQUEST_METHOD"] == "POST"){
	
function test_input($data) {
  $data = trim($data);
  $data = stripslashes($data);
  $data = htmlspecialchars($data);
  return $data;
}
	
//retrieve our DATA FROM POST
if (empty($_POST["username"])) {
		echo "Username required";
		exit;
	} else {
  $username = test_input($_POST['username']);
  if (!preg_match("/^[a-zA-Z0-9]*$/",$username)) {
  echo "Only letters and numbers allowed \n"; 
}
  
  }
  
 if (empty($_POST["password1"])) {
    echo "Password required";
	exit;
  } else {
  $password1 = test_input($_POST['password1']);}

  if (empty($_POST["password2"])) {
    echo "Please Re-enter the password";
	exit;
  } else {
  $password2 = test_input($_POST['password2']);}

  
  
if (empty($_POST["email"])) {
    echo "email required";
	exit;
  } else {
  $email = test_input($_POST['email']);
  if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
  echo "Invalid email";
  exit;
}
  
  }

try{

$dbhost = $_SERVER['RDS_HOSTNAME'];
$dbport = $_SERVER['RDS_PORT'];
$dbname = $_SERVER['RDS_DB_NAME'];

$dsn = "mysql:host={$dbhost};port={$dbport};dbname={$dbname}";
$usernameRD = $_SERVER['RDS_USERNAME'];
$password = $_SERVER['RDS_PASSWORD'];

$db = new PDO($dsn, $usernameRD, $password);
$createTable = "CREATE TABLE IF NOT EXISTS UserDetails (id INT(6)  NOT NULL AUTO_INCREMENT PRIMARY KEY, username VARCHAR(150) NOT NULL UNIQUE , password VARCHAR(120), email VARCHAR(50) NOT NULL UNIQUE)";
$db->exec($createTable);


 
if($password1 != $password2)
{
	echo "Passwords do not match";
	exit;
	
}
 
if(strlen($username) > 30){
    echo "Username cannot be more than 30 characters of length";
	exit;
}

$options = [
    'cost' => 11,
    'salt' => mcrypt_create_iv(22, MCRYPT_DEV_URANDOM),
];

$password  = password_hash($password1, PASSWORD_BCRYPT, $options);
$hash_email = hash('sha256', $email);
$stmtq = $db->prepare("SELECT COUNT(*) FROM UserDetails WHERE email = :email ");
$stmtq->execute(array(':email' => $hash_email));
$result = $stmtq->fetchObject();

if($result -> total > 0){
	echo "Email already exists..!!!";
	exit;
	
}
else{
 
$qry = $db->prepare('INSERT INTO UserDetails (username, password, email) VALUES (?, ?, ?)');
$qry->EXECUTE(array($username, $password, $hash_email));
 
header('Location: /index.php');

}
}
catch(PDOException $e)
    {
	
    echo $e->getMessage();
    }
}

?>