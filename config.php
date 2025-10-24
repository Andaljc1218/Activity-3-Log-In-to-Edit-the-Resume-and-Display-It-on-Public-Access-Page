<?php
// PostgreSQL Database configuration
define('DB_HOST', 'localhost');
define('DB_PORT', '5433');
define('DB_NAME', 'web3_auth');
define('DB_USER', 'postgres');
define('DB_PASS', '1234'); // Update this with your PostgreSQL password

// Database connection function
function getDBConnection() {
    try {
        $dsn = "pgsql:host=" . DB_HOST . ";port=" . DB_PORT . ";dbname=" . DB_NAME;
        $pdo = new PDO($dsn, DB_USER, DB_PASS);
        $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        return $pdo;
    } catch (PDOException $e) {
        error_log("Database connection failed: " . $e->getMessage());
        return false;
    }
}

// Function to create users table if it doesn't exist
function createUsersTable() {
    $pdo = getDBConnection();
    if (!$pdo) return false;
    
    try {
        $sql = "CREATE TABLE IF NOT EXISTS users (
            id SERIAL PRIMARY KEY,
            username VARCHAR(50) UNIQUE NOT NULL,
            password_hash VARCHAR(255) NOT NULL,
            email VARCHAR(100),
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            last_login TIMESTAMP
        )";
        $pdo->exec($sql);
        return true;
    } catch (PDOException $e) {
        error_log("Error creating users table: " . $e->getMessage());
        return false;
    }
}

// Function to authenticate user
function authenticateUser($username, $password) {
    $pdo = getDBConnection();
    if (!$pdo) return false;
    
    try {
        $stmt = $pdo->prepare("SELECT id, username, password_hash FROM users WHERE username = ?");
        $stmt->execute([$username]);
        $user = $stmt->fetch(PDO::FETCH_ASSOC);
        
        if ($user && password_verify($password, $user['password_hash'])) {
            // Update last login
            $updateStmt = $pdo->prepare("UPDATE users SET last_login = CURRENT_TIMESTAMP WHERE id = ?");
            $updateStmt->execute([$user['id']]);
            return $user;
        }
        return false;
    } catch (PDOException $e) {
        error_log("Authentication error: " . $e->getMessage());
        return false;
    }
}

// Function to create a new user (for setup)
function createUser($username, $password, $email = null) {
    $pdo = getDBConnection();
    if (!$pdo) return false;
    
    try {
        $passwordHash = password_hash($password, PASSWORD_DEFAULT);
        $stmt = $pdo->prepare("INSERT INTO users (username, password_hash, email) VALUES (?, ?, ?)");
        return $stmt->execute([$username, $passwordHash, $email]);
    } catch (PDOException $e) {
        error_log("Error creating user: " . $e->getMessage());
        return false;
    }
}

// Function to create resume_data table and initialize with default data
function createResumeDataTable() {
    $pdo = getDBConnection();
    if (!$pdo) return false;
    
    try {
        // Create resume_data table
        $sql = "CREATE TABLE IF NOT EXISTS resume_data (
            id SERIAL PRIMARY KEY,
            data JSONB NOT NULL,
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
        )";
        $pdo->exec($sql);
        
        // Check if data exists, if not insert default data
        $stmt = $pdo->query("SELECT COUNT(*) FROM resume_data WHERE id = 1");
        $count = $stmt->fetchColumn();
        
        if ($count == 0) {
            $defaultData = [
                'name' => 'JAYCEE F. ANDAL',
                'title' => 'Computer Science Student',
                'email' => 'jayceeja12@gmail.com',
                'phone' => '0970-865-3711',
                'address' => 'San Felipe, Padre Garcia, Batangas',
                'age' => '20 y.o.',
                'dob' => 'September 12, 2005',
                'pob' => 'Padre Garcia',
                'citizenship' => 'Filipino',
                'religion' => 'Roman Catholic',
                'languages' => 'English / Tagalog',
                'education' => [
                    [
                        'school' => 'San Felipe Elementary School',
                        'degree' => 'Primary School',
                        'start' => '2016',
                        'end' => '2017',
                        'description' => ''
                    ],
                    [
                        'school' => 'Padre Garcia Integrated National High School',
                        'degree' => 'Secondary School',
                        'start' => '2022',
                        'end' => '2023',
                        'description' => ''
                    ],
                    [
                        'school' => 'Batangas State University – Alangilan Campus',
                        'degree' => 'Tertiary School',
                        'start' => '2023',
                        'end' => 'Present',
                        'description' => ''
                    ]
                ],
                'skills' => [
                    'Computer Literate',
                    'Basic Computer Skills',
                    'Basic Arithmetic Skills',
                    'Customer Service Basic Skills',
                    'Ability to Work Under Pressure',
                    'Teamwork and Adaptability'
                ],
                'projects' => [
                    [
                        'name' => 'FLASH-Q: Flashcard Quiz System',
                        'description' => 'The Flashcard Quiz System is a console-based Java application designed to aid users in learning and self-assessment by creating, managing, and taking quizzes with flashcards.',
                        'languages' => 'Java, SQL',
                        'link' => 'https://github.com/Andaljc1218/FLASH-Q.git'
                    ],
                    [
                        'name' => 'EcoMap - Smart Waste Management System',
                        'description' => 'EcoMap is a web-based platform designed to make waste management smarter and more efficient for communities.',
                        'languages' => 'HTML5, CSS3, JavaScript, PHP, MySQL',
                        'link' => 'https://github.com/Andaljc1218/ECO-MAP.git'
                    ]
                ],
                'profile' => 'A student of Batangas State University pursuing a degree in Bachelor of Science Major in Computer Science, seeking practical experiences and application opportunities. To enhance my learnings and skills at a stable workplace. To gain new experiences. To learn new skills and practical knowledge.'
            ];
            
            $stmt = $pdo->prepare("INSERT INTO resume_data (id, data) VALUES (1, ?)");
            $stmt->execute([json_encode($defaultData)]);
        }
        
        return true;
    } catch (PDOException $e) {
        error_log("Error creating resume_data table: " . $e->getMessage());
        return false;
    }
}
?>
