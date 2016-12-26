	<html>
	<body>
	<form name="form1" method="post" action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]);?>">
		<table width="550" border="0" align="center">
			<th> LOGIN</th>
			<tr><td><p><span>* required field.</span></p>
			</td></tr>
			<tr>
				<td>Username:</td>
				<td><input type="text" name="username" id="username" />*
				</td>
				
			</tr>
			<tr>
				<td>Password</td>
				<td><input type="password" name="password" id="password" />*
				</td>
				
			</tr>
			<tr>
				<td><input type="submit" name="button" id="button" value="Login" /></td>
			</tr>
			<tr>
				
				<td><input type="button" name="Register" value="Register" id="button1" onclick="location='register.php'"  /></td>
			</tr>
		</table>
	</form>
	</body>
	</html>


	<?php
	session_start();


	$username = $password = "";

	if ($_SERVER["REQUEST_METHOD"] == "POST"){
		
	function test_input($data) {
	  $data = trim($data);
	  $data = stripslashes($data);
	  $data = htmlspecialchars($data);
	  return $data;
	}
	if (empty($_POST["username"])) {
			echo "Username required";
			exit;
		} else {
	  $username = test_input($_POST['username']);
	  $_SESSION["username"] = $username;
	  echo "session:".$_SESSION['username'];
	  if (!preg_match("/^[a-zA-Z0-9]*$/",$username)) {
	  echo "Only letters and numbers allowed \n"; 
	}
	   

	  
	  }
	  
	if (empty($_POST["password"])) {
		echo "Password required";
		exit;
	  } else {
	  $password = test_input($_POST['password']);}
	try{

	$dbhost = $_SERVER['RDS_HOSTNAME'];
	$dbport = $_SERVER['RDS_PORT'];
	$dbname = $_SERVER['RDS_DB_NAME'];

	$dsn = "mysql:host={$dbhost};port={$dbport};dbname={$dbname}";
	$usernameRD = $_SERVER['RDS_USERNAME'];
	$passwordRD = $_SERVER['RDS_PASSWORD'];

	$db = new PDO($dsn, $usernameRD, $passwordRD);

	$stmt = $db->prepare("SELECT password  FROM UserDetails WHERE username = '$username'");
	$stmt->execute();
	$result = $stmt->fetch();

		



	$number_of_rows = $stmt->fetchColumn(); 
	if (password_verify($password, $result['password']))
	{ 
		header('Location: /upload.php');

	}
	else{
		header('Location: /index.php');

	}

	}
	catch(PDOException $e)
		{
		
		echo $e->getMessage();
		}


	}


	?>


