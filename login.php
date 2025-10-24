<?php
session_start();
require_once 'config.php';

$info = '';
$success = '';

if ($_SERVER['REQUEST_METHOD'] === 'GET' && isset($_GET['logout']) && $_GET['logout'] === '1') {
	$_SESSION = [];
	if (ini_get('session.use_cookies')) {
		$params = session_get_cookie_params();
		setcookie(session_name(), '', time() - 42000, $params['path'], $params['domain'], $params['secure'], $params['httponly']);
	}
	session_destroy();
	$info = 'You have been logged out.';
}

if ($info === '' && isset($_SESSION['username']) && $_SESSION['username'] !== '') {
	header('Location: resume.php');
	exit();
}

$error = '';
$username = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
	$username = isset($_POST['username']) ? trim($_POST['username']) : '';
	$password = isset($_POST['password']) ? (string)$_POST['password'] : '';

	if ($username === '' || $password === '') {
		$error = 'All fields are required!';
	} else {
		// Authenticate using PostgreSQL database
		$user = authenticateUser($username, $password);
		
		if ($user) {
			session_regenerate_id(true);
			$_SESSION['username'] = $user['username'];
			$_SESSION['user_id'] = $user['id'];
			header('Location: resume.php');
			exit();
		} else {
			$error = 'Invalid Username or Password';
		}
	}
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
	<meta charset="UTF-8">
	<meta name="viewport" content="width=device-width, initial-scale=1">
	<title>Activity 3 - Login</title>
	<style>
		* {
			margin: 0;
			padding: 0;
			box-sizing: border-box;
		}
		
		html, body { 
			height: 100%; 
		}
		
		body {
			font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
			background: linear-gradient(135deg, #2c3e50 0%, #34495e 50%, #3d5a4a 100%);
			position: relative;
			display: flex;
			align-items: center;
			justify-content: center;
			overflow: hidden;
		}

		/* Animated background elements */
		body::before {
			content: '';
			position: absolute;
			width: 500px;
			height: 500px;
			background: rgba(139, 119, 101, 0.15);
			border-radius: 50%;
			top: -200px;
			left: -200px;
			animation: float 20s infinite ease-in-out;
		}

		body::after {
			content: '';
			position: absolute;
			width: 400px;
			height: 400px;
			background: rgba(101, 84, 63, 0.12);
			border-radius: 50%;
			bottom: -150px;
			right: -150px;
			animation: float 15s infinite ease-in-out reverse;
		}

		@keyframes float {
			0%, 100% { transform: translate(0, 0) scale(1); }
			50% { transform: translate(50px, 50px) scale(1.1); }
		}

		.container {
			width: 420px;
			padding: 40px;
			background: rgba(44, 38, 35, 0.92);
			backdrop-filter: blur(10px);
			border-radius: 20px;
			box-shadow: 0 20px 60px rgba(0, 0, 0, 0.5);
			border: 1px solid rgba(139, 119, 101, 0.3);
			position: relative;
			z-index: 1;
			animation: slideIn 0.6s ease-out;
		}

		@keyframes slideIn {
			from {
				opacity: 0;
				transform: translateY(-30px);
			}
			to {
				opacity: 1;
				transform: translateY(0);
			}
		}

		.logo {
			width: 80px;
			height: 80px;
			margin: 0 auto 20px;
			background: linear-gradient(135deg, #8b7765 0%, #65543f 100%);
			border-radius: 50%;
			display: flex;
			align-items: center;
			justify-content: center;
			font-size: 36px;
			color: white;
			font-weight: bold;
			box-shadow: 0 10px 25px rgba(101, 84, 63, 0.4);
		}

		h2 { 
			text-align: center;
			margin: 0 0 30px;
			color: #e8dfd6;
			font-size: 28px;
			font-weight: 600;
		}

		.input-group {
			margin-bottom: 20px;
			position: relative;
		}

		.password-wrapper {
			position: relative;
		}

		.toggle-password {
			position: absolute;
			right: 14px;
			top: 50%;
			transform: translateY(-50%);
			background: none;
			border: none;
			cursor: pointer;
			padding: 4px 8px;
			color: #c9b8a5;
			font-size: 12px;
			transition: all 0.3s ease;
			width: auto;
			margin: 0;
			box-shadow: none;
			font-weight: 600;
			letter-spacing: 0.3px;
		}

		.toggle-password:hover {
			color: #8b7765;
			transform: translateY(-50%);
			box-shadow: none;
		}

		label { 
			display: block;
			margin-bottom: 8px;
			color: #c9b8a5;
			font-weight: 500;
			font-size: 14px;
		}

		input[type="text"], input[type="password"] {
			width: 100%;
			padding: 14px 16px;
			border: 2px solid #5a4a3a;
			border-radius: 10px;
			outline: none;
			font-size: 15px;
			transition: all 0.3s ease;
			background: #3a322c;
			color: #e8dfd6;
		}

		input[type="text"]:focus, input[type="password"]:focus {
			border-color: #8b7765;
			box-shadow: 0 0 0 3px rgba(139, 119, 101, 0.2);
			transform: translateY(-2px);
		}

		button {
			width: 100%;
			padding: 14px;
			margin-top: 10px;
			background: linear-gradient(135deg, #8b7765 0%, #65543f 100%);
			color: #fff;
			border: none;
			border-radius: 10px;
			cursor: pointer;
			font-weight: 600;
			font-size: 16px;
			transition: all 0.3s ease;
			box-shadow: 0 4px 15px rgba(101, 84, 63, 0.5);
		}

		button:hover {
			transform: translateY(-2px);
			box-shadow: 0 6px 20px rgba(139, 119, 101, 0.7);
			background: linear-gradient(135deg, #9a8673 0%, #75644d 100%);
		}

		button:active {
			transform: translateY(0);
		}

		.messages {
			margin-top: 20px;
			min-height: 45px;
		}

		.message {
			padding: 12px 16px;
			border-radius: 8px;
			font-size: 14px;
			font-weight: 500;
			animation: fadeIn 0.3s ease;
		}

		@keyframes fadeIn {
			from { opacity: 0; transform: translateY(-10px); }
			to { opacity: 1; transform: translateY(0); }
		}

		.error {
			background: #fee;
			color: #c53030;
			border-left: 4px solid #fc8181;
		}

		.info {
			background: #e6fffa;
			color: #234e52;
			border-left: 4px solid #38b2ac;
		}

		.footer-text {
			text-align: center;
			margin-top: 20px;
			color: #718096;
			font-size: 13px;
		}
	</style>
</head>
<body>
	<div class="container">
		<div class="logo">🔐</div>
		<h2>Log in Form</h2>
		<form method="POST" action="login.php">
			<div class="input-group">
				<label for="username">Username</label>
				<input id="username" type="text" name="username" value="<?php echo htmlspecialchars($username, ENT_QUOTES, 'UTF-8'); ?>" placeholder="Enter your username">
			</div>

			<div class="input-group">
				<label for="password">Password</label>
				<div class="password-wrapper">
					<input id="password" type="password" name="password" placeholder="Enter your password">
					<button type="button" class="toggle-password" onclick="togglePassword()">👁️</button>
				</div>
			</div>

			<button type="submit">Sign In</button>
		</form>
		<div class="messages">
			<?php if ($error !== ''): ?>
				<p class="message error"><?php echo htmlspecialchars($error, ENT_QUOTES, 'UTF-8'); ?></p>
			<?php elseif ($info !== ''): ?>
				<p class="message info"><?php echo htmlspecialchars($info, ENT_QUOTES, 'UTF-8'); ?></p>
			<?php endif; ?>
		</div>
		<p class="footer-text">Activity 3 - Secure Login System</p>
	</div>
	<script>
		function togglePassword() {
			const passwordInput = document.getElementById('password');
			const toggleBtn = document.querySelector('.toggle-password');
			
			if (passwordInput.type === 'password') {
				passwordInput.type = 'text';
				toggleBtn.textContent = '🙈';
			} else {
				passwordInput.type = 'password';
				toggleBtn.textContent = '👁️';
			}
		}
	</script>
</body>
</html>