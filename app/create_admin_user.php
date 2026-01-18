<?php
/**
 * Create Admin User - One-time script
 * Creates a new admin user: syedmuzzameer@upnm.edu.my
 * Password: sam@2026
 */

require_once __DIR__ . '/config/database.php';

// User details
$username = 'syedmuzzameer@upnm.edu.my';
$email = 'syedmuzzameer@upnm.edu.my';
$password = 'sam@2026';
$full_name = 'Syed Muzzameer';
$role = 'ADMIN';
$status = 'active';

?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Create Admin User</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            max-width: 800px;
            margin: 50px auto;
            padding: 20px;
            background-color: #f5f5f5;
        }
        .container {
            background: white;
            padding: 30px;
            border-radius: 8px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
        }
        h1 {
            color: #333;
            border-bottom: 2px solid #007bff;
            padding-bottom: 10px;
        }
        .info {
            background: #e7f3ff;
            padding: 15px;
            border-left: 4px solid #007bff;
            margin: 20px 0;
        }
        .success {
            background: #d4edda;
            padding: 15px;
            border-left: 4px solid #28a745;
            margin: 20px 0;
            color: #155724;
        }
        .error {
            background: #f8d7da;
            padding: 15px;
            border-left: 4px solid #dc3545;
            margin: 20px 0;
            color: #721c24;
        }
        .warning {
            background: #fff3cd;
            padding: 15px;
            border-left: 4px solid #ffc107;
            margin: 20px 0;
            color: #856404;
        }
        table {
            width: 100%;
            border-collapse: collapse;
            margin: 20px 0;
        }
        table td {
            padding: 10px;
            border-bottom: 1px solid #ddd;
        }
        table td:first-child {
            font-weight: bold;
            width: 150px;
        }
        .btn {
            display: inline-block;
            padding: 10px 20px;
            background: #007bff;
            color: white;
            text-decoration: none;
            border-radius: 4px;
            margin-top: 20px;
        }
        .btn:hover {
            background: #0056b3;
        }
    </style>
</head>
<body>
    <div class="container">
        <h1>Create Admin User</h1>
        
        <?php
        try {
            $pdo = getDB();
            
            // Check if user already exists
            $checkStmt = $pdo->prepare('SELECT id, username, email, role, status FROM users WHERE (username = :username OR email = :email) AND deleted_at IS NULL');
            $checkStmt->execute([':username' => $username, ':email' => $email]);
            $existingUser = $checkStmt->fetch();
            
            if ($existingUser) {
                echo '<div class="warning">';
                echo '<h3>⚠️ User Already Exists</h3>';
                echo '<p>The user with this email/username already exists in the database.</p>';
                echo '<table>';
                echo '<tr><td>ID:</td><td>' . htmlspecialchars($existingUser['id']) . '</td></tr>';
                echo '<tr><td>Username:</td><td>' . htmlspecialchars($existingUser['username']) . '</td></tr>';
                echo '<tr><td>Email:</td><td>' . htmlspecialchars($existingUser['email']) . '</td></tr>';
                echo '<tr><td>Role:</td><td>' . htmlspecialchars($existingUser['role']) . '</td></tr>';
                echo '<tr><td>Status:</td><td>' . htmlspecialchars($existingUser['status']) . '</td></tr>';
                echo '</table>';
                echo '</div>';
            } else {
                // Generate password hash
                $passwordHash = password_hash($password, PASSWORD_DEFAULT);
                
                // Insert new user
                $sql = 'INSERT INTO users (username, email, password_hash, full_name, role, status, password_changed_at, created_at) 
                        VALUES (:username, :email, :password_hash, :full_name, :role, :status, NOW(), NOW())';
                
                $stmt = $pdo->prepare($sql);
                $stmt->execute([
                    ':username' => $username,
                    ':email' => $email,
                    ':password_hash' => $passwordHash,
                    ':full_name' => $full_name,
                    ':role' => $role,
                    ':status' => $status
                ]);
                
                $newUserId = $pdo->lastInsertId();
                
                echo '<div class="success">';
                echo '<h3>✅ User Created Successfully!</h3>';
                echo '<p>The admin user has been created in the database.</p>';
                echo '<table>';
                echo '<tr><td>User ID:</td><td>' . htmlspecialchars($newUserId) . '</td></tr>';
                echo '<tr><td>Username:</td><td>' . htmlspecialchars($username) . '</td></tr>';
                echo '<tr><td>Email:</td><td>' . htmlspecialchars($email) . '</td></tr>';
                echo '<tr><td>Full Name:</td><td>' . htmlspecialchars($full_name) . '</td></tr>';
                echo '<tr><td>Role:</td><td>' . htmlspecialchars($role) . '</td></tr>';
                echo '<tr><td>Status:</td><td>' . htmlspecialchars($status) . '</td></tr>';
                echo '<tr><td>Password:</td><td>sam@2026</td></tr>';
                echo '</table>';
                echo '</div>';
            }
            
            // Display user information
            echo '<div class="info">';
            echo '<h3>User Information</h3>';
            echo '<table>';
            echo '<tr><td>Username:</td><td>' . htmlspecialchars($username) . '</td></tr>';
            echo '<tr><td>Email:</td><td>' . htmlspecialchars($email) . '</td></tr>';
            echo '<tr><td>Password:</td><td>sam@2026</td></tr>';
            echo '<tr><td>Role:</td><td>ADMIN (Pentadbir)</td></tr>';
            echo '<tr><td>Status:</td><td>Active</td></tr>';
            echo '</table>';
            echo '</div>';
            
        } catch (PDOException $e) {
            echo '<div class="error">';
            echo '<h3>❌ Database Error</h3>';
            echo '<p>Error: ' . htmlspecialchars($e->getMessage()) . '</p>';
            echo '</div>';
        } catch (Exception $e) {
            echo '<div class="error">';
            echo '<h3>❌ Error</h3>';
            echo '<p>Error: ' . htmlspecialchars($e->getMessage()) . '</p>';
            echo '</div>';
        }
        ?>
        
        <p><strong>Note:</strong> This is a one-time script. You can delete this file after creating the user for security purposes.</p>
        <a href="index.php" class="btn">Go to Homepage</a>
    </div>
</body>
</html>

