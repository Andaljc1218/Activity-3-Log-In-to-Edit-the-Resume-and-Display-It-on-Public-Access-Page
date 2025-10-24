<?php
session_start();
require_once 'config.php';

// Initialize database tables
createUsersTable();
createResumeDataTable();

// Get resume data from database or use default
function getResumeData() {
    $pdo = getDBConnection();
    if (!$pdo) {
        return getDefaultResumeData();
    }
    
    try {
        $stmt = $pdo->query("SELECT * FROM resume_data WHERE id = 1");
        $data = $stmt->fetch(PDO::FETCH_ASSOC);
        
        if ($data) {
            return json_decode($data['data'], true);
        }
    } catch (PDOException $e) {
        error_log("Error fetching resume data: " . $e->getMessage());
    }
    
    return getDefaultResumeData();
}

function getDefaultResumeData() {
    return [
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
}

$resumeData = getResumeData();
$isLoggedIn = isset($_SESSION['username']) && $_SESSION['username'] !== '';

$e = fn($v) => htmlspecialchars((string)$v, ENT_QUOTES, 'UTF-8');
$avatarPath = __DIR__ . '/avatar.jpg';
$avatarUrl = file_exists($avatarPath) ? 'avatar.jpg' : null;
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title><?= htmlspecialchars($resumeData['name']) ?> — Resume</title>
    <style>
        :root {
            --text: #e8dfd6;
            --text-strong: #f5ede3;
            --muted: #a89884;
            --border: #4a3f35;
            --border-strong: #5a4a3a;
            --accent: #c9b8a5;
            --link: #b89968;
            --bg: #1a1612;
            --card: #2c2620;
            --card-hover: #33291f;
        }
        * { box-sizing: border-box; }
        html, body { 
            height: 100%;
            margin: 0;
        }
        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            color: var(--text);
            background: linear-gradient(135deg, #2c3e50 0%, #34495e 50%, #3d5a4a 100%);
            line-height: 1.6;
            font-size: 15px;
            position: relative;
            overflow-x: hidden;
        }

        /* Animated background elements */
        body::before {
            content: '';
            position: fixed;
            width: 500px;
            height: 500px;
            background: rgba(139, 119, 101, 0.08);
            border-radius: 50%;
            top: -200px;
            left: -200px;
            animation: float 20s infinite ease-in-out;
            z-index: 0;
        }

        body::after {
            content: '';
            position: fixed;
            width: 400px;
            height: 400px;
            background: rgba(101, 84, 63, 0.06);
            border-radius: 50%;
            bottom: -150px;
            right: -150px;
            animation: float 15s infinite ease-in-out reverse;
            z-index: 0;
        }

        @keyframes float {
            0%, 100% { transform: translate(0, 0) scale(1); }
            50% { transform: translate(50px, 50px) scale(1.1); }
        }

        .page {
            max-width: 900px;
            margin: 32px auto 56px;
            background: var(--card);
            padding: 40px;
            border-radius: 16px;
            box-shadow: 0 20px 60px rgba(0, 0, 0, 0.5);
            border: 1px solid var(--border-strong);
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
     
        .header { 
            display: grid; 
            grid-template-columns: 110px 1fr auto; 
            gap: 18px; 
            align-items: start;
            padding-bottom: 20px;
        }
        
        .header-info {
            min-width: 0;
        }
        .avatar { 
            width: 110px; 
            height: 110px; 
            border-radius: 50%; 
            background: #3a322c; 
            object-fit: cover; 
            display: block; 
            border: 3px solid var(--border-strong);
            box-shadow: 0 4px 12px rgba(0, 0, 0, 0.4);
        }
        .name { 
            font-size: 28px; 
            letter-spacing: 1.2px; 
            font-weight: 800; 
            margin: 0 0 6px; 
            color: var(--accent);
        }
        .title { 
            margin: 0 0 6px; 
            font-weight: 700; 
            color: var(--text-strong);
        }
        .contact { 
            display: flex; 
            gap: 14px; 
            flex-wrap: wrap; 
            font-size: 13px; 
            color: var(--muted);
        }
        .divider { 
            height: 2px; 
            background: linear-gradient(90deg, var(--border-strong), var(--border), var(--border-strong)); 
            margin: 16px 0 22px;
        }
        .logout { 
            display: inline-block; 
            padding: 10px 16px; 
            border: 2px solid var(--border-strong); 
            border-radius: 8px; 
            text-decoration: none; 
            color: #d97757; 
            font-weight: 700;
            transition: all 0.3s ease;
            background: transparent;
        }
        .logout:hover { 
            background: rgba(217, 119, 87, 0.1);
            border-color: #d97757;
            transform: translateY(-2px);
        }
        .login-btn, .edit-btn {
            display: inline-block; 
            padding: 10px 16px; 
            border: 2px solid var(--border-strong); 
            border-radius: 8px; 
            text-decoration: none; 
            color: var(--accent); 
            font-weight: 700;
            transition: all 0.3s ease;
            background: transparent;
            margin-left: 10px;
        }
        .login-btn:hover, .edit-btn:hover { 
            background: rgba(201, 184, 165, 0.1);
            border-color: var(--accent);
            transform: translateY(-2px);
        }
       
        .section { 
            margin-bottom: 22px;
        }
        .section-title { 
            font-size: 16px; 
            letter-spacing: 1.5px; 
            font-weight: 800; 
            margin: 0 0 14px; 
            color: var(--accent);
            text-transform: uppercase;
        }
        .muted { 
            color: var(--muted); 
            font-size: 14px;
        }
        .section-box { 
            border: 1px solid var(--border); 
            border-radius: 12px; 
            padding: 20px; 
            background: rgba(44, 38, 32, 0.6);
            backdrop-filter: blur(10px);
            transition: all 0.3s ease;
        }
        .section-box:hover {
            border-color: var(--border-strong);
            box-shadow: 0 4px 16px rgba(0, 0, 0, 0.3);
        }
  
        .table { 
            border: 1px solid var(--border); 
            border-radius: 10px; 
            overflow: hidden; 
            background: rgba(58, 50, 44, 0.4);
        }
        .table-row { 
            display: grid; 
            grid-template-columns: 220px 1fr; 
            gap: 10px 16px; 
            padding: 12px 14px; 
            border-top: 1px solid var(--border);
            align-items: start;
            transition: background 0.2s ease;
        }
        .table-row:hover {
            background: rgba(58, 50, 44, 0.6);
        }
        .table-row:first-child { 
            border-top: none;
        }
        .info-label { 
            font-weight: 700; 
            font-size: 12px; 
            color: var(--accent); 
            letter-spacing: 0.8px;
        }
        .align-right { 
            text-align: right; 
            color: var(--muted); 
            white-space: nowrap;
        }
        .table-list { 
            border: 1px solid var(--border); 
            border-radius: 10px; 
            list-style: none; 
            padding: 0; 
            margin: 0; 
            overflow: hidden;
            background: rgba(58, 50, 44, 0.4);
        }
        .table-list li { 
            padding: 14px 16px; 
            border-top: 1px solid var(--border);
            transition: background 0.2s ease;
        }
        .table-list li:hover {
            background: rgba(58, 50, 44, 0.6);
        }
        .table-list li:first-child { 
            border-top: none;
        }
        
        .project-table { 
            border: 1px solid var(--border); 
            border-radius: 10px; 
            overflow: hidden; 
            background: rgba(58, 50, 44, 0.4);
        }
        .project-row { 
            display: grid; 
            grid-template-columns: 1fr auto; 
            gap: 16px; 
            padding: 14px 16px; 
            border-top: 1px solid var(--border); 
            align-items: center;
            transition: background 0.2s ease;
        }
        .project-row:hover {
            background: rgba(58, 50, 44, 0.6);
        }
        .project-row:first-child { 
            border-top: none;
        }
        .project-link { 
            display: inline-block; 
            padding: 8px 14px; 
            border: 2px solid var(--border-strong); 
            border-radius: 8px; 
            color: var(--link); 
            text-decoration: none; 
            font-weight: 600; 
            font-size: 13px;
            transition: all 0.3s ease;
            background: transparent;
        }
        .project-link:hover { 
            background: rgba(184, 153, 104, 0.15);
            border-color: var(--link);
            transform: translateY(-2px);
            text-decoration: none;
        }

        @media (max-width: 720px) { 
            .header { grid-template-columns: 80px 1fr auto; } 
            .name { font-size: 24px; } 
            .table-row { grid-template-columns: 1fr; } 
            .align-right { text-align: left; margin-top: 6px; } 
            .project-row { grid-template-columns: 1fr; }
            .page { padding: 24px; margin: 16px; }
        }
    </style>
</head>
<body>
    <div class="page">
        <div class="header">
            <?php if ($avatarUrl): ?>
                <img src="<?= $e($avatarUrl) ?>" alt="Avatar" class="avatar">
            <?php else: ?>
                <div class="avatar"></div>
            <?php endif; ?>
            <div>
                <h1 class="name"><?= htmlspecialchars(strtoupper($resumeData['name'])) ?></h1>
                <p class="title"><?= htmlspecialchars($resumeData['title']) ?></p>
                <div class="contact">
                    <span><?= htmlspecialchars($resumeData['phone']) ?></span>
                    <span><?= htmlspecialchars($resumeData['email']) ?></span>
                    <span><strong>Address:</strong> <?= htmlspecialchars($resumeData['address']) ?></span>
                </div>
            </div>
            <div>
                <?php if ($isLoggedIn): ?>
                    <a class="edit-btn" href="edit_resume.php">Edit Resume</a>
                          <a class="logout" href="login.php?logout=1">Logout</a>
                <?php else: ?>
                    <a class="login-btn" href="login.php">Login</a>
                <?php endif; ?>
            </div>
        </div>

        <div class="divider"></div>

        <section class="section section-box">
            <h3 class="section-title">Profile</h3>
            <p class="muted"><?= htmlspecialchars($resumeData['profile']) ?></p>
        </section>

        <section class="section section-box">
            <h3 class="section-title">Personal Information</h3>
            <div class="table">
                <div class="table-row"><div class="info-label">AGE</div><div><?= $e($resumeData['age']) ?></div></div>
                <div class="table-row"><div class="info-label">DATE OF BIRTH</div><div><?= $e($resumeData['dob']) ?></div></div>
                <div class="table-row"><div class="info-label">PLACE OF BIRTH</div><div><?= $e($resumeData['pob']) ?></div></div>
                <div class="table-row"><div class="info-label">CITIZENSHIP</div><div><?= $e($resumeData['citizenship']) ?></div></div>
                <div class="table-row"><div class="info-label">RELIGION</div><div><?= $e($resumeData['religion']) ?></div></div>
                <div class="table-row"><div class="info-label">LANGUAGES</div><div><?= $e($resumeData['languages']) ?></div></div>
                <div class="table-row"><div class="info-label">ADDRESS</div><div><?= htmlspecialchars($resumeData['address']) ?></div></div>
            </div>
        </section>

        <section class="section section-box">
            <h3 class="section-title">Skills</h3>
            <ul class="table-list">
                <?php foreach ($resumeData['skills'] as $skill): ?>
                    <li><?= $e($skill) ?></li>
                <?php endforeach; ?>
            </ul>
        </section>

        <section class="section section-box">
            <h3 class="section-title">Education</h3>
            <div class="table">
                <?php foreach ($resumeData['education'] as $edu): ?>
                    <div class="table-row">
                        <div>
                            <strong><?= $e($edu['degree']) ?></strong><br>
                            <span class="muted"><?= $e($edu['school']) ?></span>
                        </div>
                        <div class="align-right"><?= $e($edu['start']) ?> – <?= $e($edu['end']) ?></div>
                    </div>
                <?php endforeach; ?>
            </div>
        </section>

        <section class="section section-box">
            <h3 class="section-title">Projects</h3>
            <div class="project-table">
                <?php foreach ($resumeData['projects'] as $proj): ?>
                    <div class="project-row">
                        <div>
                            <strong><?= $e($proj['name']) ?></strong><br>
                            <span class="muted"><?= $e($proj['description']) ?></span><br>
                            <span><strong>Languages:</strong> <?= $e($proj['languages']) ?></span>
                        </div>
                        <div>
                            <a class="project-link" href="<?= $e($proj['link']) ?>" target="_blank" rel="noopener noreferrer">View Project</a>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
        </section>
    </div>
</body>
</html>
