<?php
session_start();
require_once 'config.php';

// Initialize database tables
createUsersTable();
createResumeDataTable();

// Check if user is logged in
if (!isset($_SESSION['username']) || $_SESSION['username'] === '') {
    header('Location: login.php');
    exit();
}

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $pdo = getDBConnection();
    if ($pdo) {
        try {
            $resumeData = [
                'name' => $_POST['name'] ?? '',
                'title' => $_POST['title'] ?? '',
                'email' => $_POST['email'] ?? '',
                'phone' => $_POST['phone'] ?? '',
                'address' => $_POST['address'] ?? '',
                'age' => $_POST['age'] ?? '',
                'dob' => $_POST['dob'] ?? '',
                'pob' => $_POST['pob'] ?? '',
                'citizenship' => $_POST['citizenship'] ?? '',
                'religion' => $_POST['religion'] ?? '',
                'languages' => $_POST['languages'] ?? '',
                'objectives' => $_POST['objectives'] ?? '',
                'education' => json_decode($_POST['education_json'] ?? '[]', true),
                'skills' => array_filter(explode("\n", $_POST['skills'] ?? '')),
                'projects' => json_decode($_POST['projects_json'] ?? '[]', true),
                'profile' => $_POST['profile'] ?? ''
            ];
            
            $stmt = $pdo->prepare("UPDATE resume_data SET data = ?, updated_at = CURRENT_TIMESTAMP WHERE id = 1");
            $stmt->execute([json_encode($resumeData)]);
            
            $success = "Resume updated successfully!";
        } catch (PDOException $e) {
            $error = "Error updating resume: " . $e->getMessage();
        }
    }
}

// Get current resume data
function getResumeData() {
    $pdo = getDBConnection();
    if (!$pdo) return [];
    
    try {
        $stmt = $pdo->query("SELECT data FROM resume_data WHERE id = 1");
        $data = $stmt->fetchColumn();
        return $data ? json_decode($data, true) : [];
    } catch (PDOException $e) {
        return [];
    }
}

$resumeData = getResumeData();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Edit Resume</title>
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
            padding: 20px;
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

        .container {
            max-width: 1000px;
            margin: 0 auto;
            background: var(--card);
            border-radius: 16px;
            box-shadow: 0 20px 60px rgba(0, 0, 0, 0.5);
            border: 1px solid var(--border-strong);
            position: relative;
            z-index: 1;
            animation: slideIn 0.6s ease-out;
            overflow: hidden;
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
            background: var(--border-strong);
            color: var(--text-strong);
            padding: 20px;
            text-align: center;
        }
        
        .header h1 {
            margin-bottom: 10px;
            color: var(--accent);
        }
        
        .nav-buttons {
            margin-top: 15px;
        }
        
        .nav-buttons a {
            color: var(--text);
            text-decoration: none;
            padding: 8px 16px;
            margin: 0 5px;
            border-radius: 8px;
            background: rgba(201, 184, 165, 0.1);
            border: 1px solid var(--border);
            transition: all 0.3s ease;
        }
        
        .nav-buttons a:hover {
            background: rgba(201, 184, 165, 0.2);
            border-color: var(--accent);
        }
        
        .form-container {
            padding: 30px;
        }
        
        .form-section {
            margin-bottom: 30px;
            padding: 20px;
            border: 1px solid var(--border);
            border-radius: 12px;
            background: rgba(44, 38, 32, 0.6);
            backdrop-filter: blur(10px);
            transition: all 0.3s ease;
        }
        
        .form-section:hover {
            border-color: var(--border-strong);
            box-shadow: 0 4px 16px rgba(0, 0, 0, 0.3);
        }
        
        .form-section h3 {
            color: var(--accent);
            margin-bottom: 15px;
            padding-bottom: 5px;
            border-bottom: 2px solid var(--accent);
            font-size: 16px;
            letter-spacing: 1.5px;
            font-weight: 800;
            text-transform: uppercase;
        }
        
        .form-group {
            margin-bottom: 15px;
        }
        
        .form-group label {
            display: block;
            margin-bottom: 5px;
            font-weight: 500;
            color: var(--text-strong);
        }
        
        .form-group input,
        .form-group textarea {
            width: 100%;
            padding: 10px;
            border: 1px solid var(--border);
            border-radius: 8px;
            font-size: 14px;
            background: rgba(58, 50, 44, 0.4);
            color: var(--text);
            transition: all 0.3s ease;
        }
        
        .form-group input:focus,
        .form-group textarea:focus {
            outline: none;
            border-color: var(--accent);
            box-shadow: 0 0 0 2px rgba(201, 184, 165, 0.2);
        }
        
        .form-group textarea {
            height: 100px;
            resize: vertical;
        }
        
        .form-row {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 15px;
        }
        
        .dynamic-section {
            border: 1px solid var(--border);
            padding: 15px;
            margin: 10px 0;
            border-radius: 8px;
            background: rgba(58, 50, 44, 0.4);
            transition: all 0.3s ease;
        }
        
        .dynamic-section:hover {
            border-color: var(--border-strong);
            background: rgba(58, 50, 44, 0.6);
        }
        
        .dynamic-section h4 {
            color: var(--accent);
            margin-bottom: 10px;
            font-weight: 600;
        }
        
        .add-btn, .remove-btn {
            background: var(--accent);
            color: var(--bg);
            border: none;
            padding: 8px 16px;
            border-radius: 6px;
            cursor: pointer;
            margin: 5px;
            font-weight: 600;
            transition: all 0.3s ease;
        }
        
        .remove-btn {
            background: #d97757;
            color: white;
        }
        
        .add-btn:hover {
            background: var(--link);
            transform: translateY(-2px);
        }
        
        .remove-btn:hover {
            background: #c65d3d;
            transform: translateY(-2px);
        }
        
        .submit-btn {
            background: var(--accent);
            color: var(--bg);
            padding: 12px 30px;
            border: none;
            border-radius: 8px;
            cursor: pointer;
            font-size: 16px;
            font-weight: 600;
            transition: all 0.3s ease;
        }
        
        .submit-btn:hover {
            background: var(--link);
            transform: translateY(-2px);
        }
        
        .message {
            padding: 10px;
            margin: 10px 0;
            border-radius: 8px;
            border: 1px solid;
        }
        
        .success {
            background: rgba(40, 167, 69, 0.1);
            color: #28a745;
            border-color: #28a745;
        }
        
        .error {
            background: rgba(220, 53, 69, 0.1);
            color: #dc3545;
            border-color: #dc3545;
        }
        
        @media (max-width: 768px) {
            .form-row {
                grid-template-columns: 1fr;
            }
            .container {
                margin: 10px;
                padding: 15px;
            }
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="header">
            <h1>✏️ Edit Resume</h1>
            <p>Update your resume information</p>
            <div class="nav-buttons">
                <a href="index.php">👁️ View Public Resume</a>
                <a href="login.php?logout=1">🚪 Logout</a>
            </div>
        </div>
        
        <div class="form-container">
            <?php if (isset($success)): ?>
                <div class="message success"><?php echo htmlspecialchars($success); ?></div>
            <?php endif; ?>
            
            <?php if (isset($error)): ?>
                <div class="message error"><?php echo htmlspecialchars($error); ?></div>
            <?php endif; ?>
            
            <form method="POST" action="edit_resume.php">
                <!-- Personal Information -->
                <div class="form-section">
                    <h3>👤 Personal Information</h3>
                    <div class="form-row">
                        <div class="form-group">
                            <label for="name">Full Name</label>
                            <input type="text" id="name" name="name" value="<?php echo htmlspecialchars($resumeData['name'] ?? ''); ?>" required>
                        </div>
                        <div class="form-group">
                            <label for="title">Title/Position</label>
                            <input type="text" id="title" name="title" value="<?php echo htmlspecialchars($resumeData['title'] ?? ''); ?>" required>
                        </div>
                    </div>
                    
                    <div class="form-row">
                        <div class="form-group">
                            <label for="email">Email</label>
                            <input type="email" id="email" name="email" value="<?php echo htmlspecialchars($resumeData['email'] ?? ''); ?>" required>
                        </div>
                        <div class="form-group">
                            <label for="phone">Phone</label>
                            <input type="text" id="phone" name="phone" value="<?php echo htmlspecialchars($resumeData['phone'] ?? ''); ?>" required>
                        </div>
                    </div>
                    
                    <div class="form-group">
                        <label for="address">Address</label>
                        <input type="text" id="address" name="address" value="<?php echo htmlspecialchars($resumeData['address'] ?? ''); ?>" required>
                    </div>
                    
                    <div class="form-row">
                        <div class="form-group">
                            <label for="age">Age</label>
                            <input type="text" id="age" name="age" value="<?php echo htmlspecialchars($resumeData['age'] ?? ''); ?>">
                        </div>
                        <div class="form-group">
                            <label for="dob">Date of Birth</label>
                            <input type="text" id="dob" name="dob" value="<?php echo htmlspecialchars($resumeData['dob'] ?? ''); ?>">
                        </div>
                    </div>
                    
                    <div class="form-row">
                        <div class="form-group">
                            <label for="pob">Place of Birth</label>
                            <input type="text" id="pob" name="pob" value="<?php echo htmlspecialchars($resumeData['pob'] ?? ''); ?>">
                        </div>
                        <div class="form-group">
                            <label for="citizenship">Citizenship</label>
                            <input type="text" id="citizenship" name="citizenship" value="<?php echo htmlspecialchars($resumeData['citizenship'] ?? ''); ?>">
                        </div>
                    </div>
                    
                    <div class="form-row">
                        <div class="form-group">
                            <label for="religion">Religion</label>
                            <input type="text" id="religion" name="religion" value="<?php echo htmlspecialchars($resumeData['religion'] ?? ''); ?>">
                        </div>
                        <div class="form-group">
                            <label for="languages">Languages</label>
                            <input type="text" id="languages" name="languages" value="<?php echo htmlspecialchars($resumeData['languages'] ?? ''); ?>">
                        </div>
                    </div>
                </div>
                
                <!-- Profile -->
                <div class="form-section">
                    <h3>👤 Profile</h3>
                    <div class="form-group">
                        <label for="profile">Profile Description</label>
                        <textarea id="profile" name="profile" required><?php echo htmlspecialchars($resumeData['profile'] ?? ''); ?></textarea>
                    </div>
                </div>
                
                <!-- Education -->
                <div class="form-section">
                    <h3>🎓 Education</h3>
                    <div id="education-container">
                        <?php foreach (($resumeData['education'] ?? []) as $index => $edu): ?>
                            <div class="dynamic-section">
                                <h4>Education <?php echo $index + 1; ?></h4>
                                <div class="form-row">
                                    <div class="form-group">
                                        <label>School/Institution</label>
                                        <input type="text" name="education[<?php echo $index; ?>][school]" value="<?php echo htmlspecialchars($edu['school'] ?? ''); ?>" required>
                                    </div>
                                    <div class="form-group">
                                        <label>Degree/Program</label>
                                        <input type="text" name="education[<?php echo $index; ?>][degree]" value="<?php echo htmlspecialchars($edu['degree'] ?? ''); ?>" required>
                                    </div>
                                </div>
                                <div class="form-row">
                                    <div class="form-group">
                                        <label>Start Year</label>
                                        <input type="text" name="education[<?php echo $index; ?>][start]" value="<?php echo htmlspecialchars($edu['start'] ?? ''); ?>">
                                    </div>
                                    <div class="form-group">
                                        <label>End Year</label>
                                        <input type="text" name="education[<?php echo $index; ?>][end]" value="<?php echo htmlspecialchars($edu['end'] ?? ''); ?>">
                                    </div>
                                </div>
                                <div class="form-group">
                                    <label>Description</label>
                                    <textarea name="education[<?php echo $index; ?>][description]"><?php echo htmlspecialchars($edu['description'] ?? ''); ?></textarea>
                                </div>
                                <button type="button" class="remove-btn" onclick="removeEducation(this)">Remove</button>
                            </div>
                        <?php endforeach; ?>
                    </div>
                    <button type="button" class="add-btn" onclick="addEducation()">+ Add Education</button>
                </div>
                
                <!-- Work Experience -->
                <div class="form-section">
                    <h3>💼 Work Experience</h3>
                    <div id="experience-container">
                        <?php foreach (($resumeData['experience'] ?? []) as $index => $exp): ?>
                            <div class="dynamic-section">
                                <h4>Experience <?php echo $index + 1; ?></h4>
                                <div class="form-row">
                                    <div class="form-group">
                                        <label>Company</label>
                                        <input type="text" name="experience[<?php echo $index; ?>][company]" value="<?php echo htmlspecialchars($exp['company'] ?? ''); ?>" required>
                                    </div>
                                    <div class="form-group">
                                        <label>Position</label>
                                        <input type="text" name="experience[<?php echo $index; ?>][position]" value="<?php echo htmlspecialchars($exp['position'] ?? ''); ?>">
                                    </div>
                                </div>
                                <div class="form-row">
                                    <div class="form-group">
                                        <label>Start Date</label>
                                        <input type="text" name="experience[<?php echo $index; ?>][start]" value="<?php echo htmlspecialchars($exp['start'] ?? ''); ?>">
                                    </div>
                                    <div class="form-group">
                                        <label>End Date</label>
                                        <input type="text" name="experience[<?php echo $index; ?>][end]" value="<?php echo htmlspecialchars($exp['end'] ?? ''); ?>">
                                    </div>
                                </div>
                                <div class="form-group">
                                    <label>Description</label>
                                    <textarea name="experience[<?php echo $index; ?>][description]"><?php echo htmlspecialchars($exp['description'] ?? ''); ?></textarea>
                                </div>
                                <button type="button" class="remove-btn" onclick="removeExperience(this)">Remove</button>
                            </div>
                        <?php endforeach; ?>
                    </div>
                    <button type="button" class="add-btn" onclick="addExperience()">+ Add Experience</button>
                </div>
                
                <!-- Projects -->
                <div class="form-section">
                    <h3>🚀 Projects</h3>
                    <div id="projects-container">
                        <?php foreach (($resumeData['projects'] ?? []) as $index => $proj): ?>
                            <div class="dynamic-section">
                                <h4>Project <?php echo $index + 1; ?></h4>
                                <div class="form-group">
                                    <label>Project Name</label>
                                    <input type="text" name="projects[<?php echo $index; ?>][name]" value="<?php echo htmlspecialchars($proj['name'] ?? ''); ?>" required>
                                </div>
                                <div class="form-group">
                                    <label>Description</label>
                                    <textarea name="projects[<?php echo $index; ?>][description]"><?php echo htmlspecialchars($proj['description'] ?? ''); ?></textarea>
                                </div>
                                <div class="form-group">
                                    <label>Languages/Technologies</label>
                                    <input type="text" name="projects[<?php echo $index; ?>][languages]" value="<?php echo htmlspecialchars($proj['languages'] ?? ''); ?>">
                                </div>
                                <div class="form-group">
                                    <label>Project Link</label>
                                    <input type="url" name="projects[<?php echo $index; ?>][link]" value="<?php echo htmlspecialchars($proj['link'] ?? ''); ?>">
                                </div>
                                <button type="button" class="remove-btn" onclick="removeProject(this)">Remove</button>
                            </div>
                        <?php endforeach; ?>
                    </div>
                    <button type="button" class="add-btn" onclick="addProject()">+ Add Project</button>
                </div>

                <!-- Skills -->
                <div class="form-section">
                    <h3>🛠️ Skills</h3>
                    <div class="form-group">
                        <label for="skills">Skills (one per line)</label>
                        <textarea id="skills" name="skills" rows="6"><?php echo htmlspecialchars(implode("\n", $resumeData['skills'] ?? [])); ?></textarea>
                    </div>
                </div>
                
                <button type="submit" class="submit-btn">💾 Save Resume</button>
            </form>
        </div>
    </div>

    <script>
        function addEducation() {
            const container = document.getElementById('education-container');
            const index = container.children.length;
            
            const section = document.createElement('div');
            section.className = 'dynamic-section';
            section.innerHTML = `
                <h4>Education ${index + 1}</h4>
                <div class="form-row">
                    <div class="form-group">
                        <label>School/Institution</label>
                        <input type="text" name="education[${index}][school]" required>
                    </div>
                    <div class="form-group">
                        <label>Degree/Program</label>
                        <input type="text" name="education[${index}][degree]" required>
                    </div>
                </div>
                <div class="form-row">
                    <div class="form-group">
                        <label>Start Year</label>
                        <input type="text" name="education[${index}][start]">
                    </div>
                    <div class="form-group">
                        <label>End Year</label>
                        <input type="text" name="education[${index}][end]">
                    </div>
                </div>
                <div class="form-group">
                    <label>Description</label>
                    <textarea name="education[${index}][description]"></textarea>
                </div>
                <button type="button" class="remove-btn" onclick="removeEducation(this)">Remove</button>
            `;
            
            container.appendChild(section);
        }
        
        function removeEducation(button) {
            button.parentElement.remove();
        }
        
        function addExperience() {
            const container = document.getElementById('experience-container');
            const index = container.children.length;
            
            const section = document.createElement('div');
            section.className = 'dynamic-section';
            section.innerHTML = `
                <h4>Experience ${index + 1}</h4>
                <div class="form-row">
                    <div class="form-group">
                        <label>Company</label>
                        <input type="text" name="experience[${index}][company]" required>
                    </div>
                    <div class="form-group">
                        <label>Position</label>
                        <input type="text" name="experience[${index}][position]">
                    </div>
                </div>
                <div class="form-row">
                    <div class="form-group">
                        <label>Start Date</label>
                        <input type="text" name="experience[${index}][start]">
                    </div>
                    <div class="form-group">
                        <label>End Date</label>
                        <input type="text" name="experience[${index}][end]">
                    </div>
                </div>
                <div class="form-group">
                    <label>Description</label>
                    <textarea name="experience[${index}][description]"></textarea>
                </div>
                <button type="button" class="remove-btn" onclick="removeExperience(this)">Remove</button>
            `;
            
            container.appendChild(section);
        }
        
        function removeExperience(button) {
            button.parentElement.remove();
        }
        
        function addProject() {
            const container = document.getElementById('projects-container');
            const index = container.children.length;
            
            const section = document.createElement('div');
            section.className = 'dynamic-section';
            section.innerHTML = `
                <h4>Project ${index + 1}</h4>
                <div class="form-group">
                    <label>Project Name</label>
                    <input type="text" name="projects[${index}][name]" required>
                </div>
                <div class="form-group">
                    <label>Description</label>
                    <textarea name="projects[${index}][description]"></textarea>
                </div>
                <div class="form-group">
                    <label>Languages/Technologies</label>
                    <input type="text" name="projects[${index}][languages]">
                </div>
                <div class="form-group">
                    <label>Project Link</label>
                    <input type="url" name="projects[${index}][link]">
                </div>
                <button type="button" class="remove-btn" onclick="removeProject(this)">Remove</button>
            `;
            
            container.appendChild(section);
        }
        
        function removeProject(button) {
            button.parentElement.remove();
        }
        
        // Convert form data to JSON before submission
        document.querySelector('form').addEventListener('submit', function(e) {
            const educationData = [];
            const experienceData = [];
            const projectsData = [];
            
            // Collect education data
            document.querySelectorAll('#education-container .dynamic-section').forEach((section, index) => {
                const inputs = section.querySelectorAll('input, textarea');
                educationData.push({
                    school: inputs[0].value,
                    degree: inputs[1].value,
                    start: inputs[2].value,
                    end: inputs[3].value,
                    description: inputs[4].value
                });
            });
            
            // Collect experience data
            document.querySelectorAll('#experience-container .dynamic-section').forEach((section, index) => {
                const inputs = section.querySelectorAll('input, textarea');
                experienceData.push({
                    company: inputs[0].value,
                    position: inputs[1].value,
                    start: inputs[2].value,
                    end: inputs[3].value,
                    description: inputs[4].value
                });
            });
            
            // Collect projects data
            document.querySelectorAll('#projects-container .dynamic-section').forEach((section, index) => {
                const inputs = section.querySelectorAll('input, textarea');
                projectsData.push({
                    name: inputs[0].value,
                    description: inputs[1].value,
                    languages: inputs[2].value,
                    link: inputs[3].value
                });
            });
            
            // Add hidden inputs for JSON data
            const educationInput = document.createElement('input');
            educationInput.type = 'hidden';
            educationInput.name = 'education_json';
            educationInput.value = JSON.stringify(educationData);
            this.appendChild(educationInput);
            
            const experienceInput = document.createElement('input');
            experienceInput.type = 'hidden';
            experienceInput.name = 'experience_json';
            experienceInput.value = JSON.stringify(experienceData);
            this.appendChild(experienceInput);
            
            const projectsInput = document.createElement('input');
            projectsInput.type = 'hidden';
            projectsInput.name = 'projects_json';
            projectsInput.value = JSON.stringify(projectsData);
            this.appendChild(projectsInput);
        });
    </script>
</body>
</html>
