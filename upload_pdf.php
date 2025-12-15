<?php
session_start();
include_once 'config.php';

if (!isset($_SESSION['student_email'])) {
    header('Location: lol.php'); 
    exit();
}

$message = '';
$error = '';
$success = '';

// Handle PDF upload
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['upload_pdf'])) {
    $title = htmlspecialchars($_POST['title'], ENT_QUOTES, 'UTF-8');
    $description = htmlspecialchars($_POST['description'], ENT_QUOTES, 'UTF-8');
    $category = htmlspecialchars($_POST['category'], ENT_QUOTES, 'UTF-8');
    $tags = htmlspecialchars($_POST['tags'], ENT_QUOTES, 'UTF-8');
    $is_public = isset($_POST['is_public']) ? 1 : 0;
    
    // Get user ID
    $email = $_SESSION['student_email'];
    $userQuery = $conn->prepare("SELECT id FROM tbl_users WHERE email = ?");
    $userQuery->bind_param("s", $email);
    $userQuery->execute();
    $userResult = $userQuery->get_result();
    $user = $userResult->fetch_assoc();
    $user_id = $user['id'];
    
    // Process file upload
    if(isset($_FILES['pdf_file']) && $_FILES['pdf_file']['error'] == 0) {
        $file = $_FILES['pdf_file'];
        $filename = $file['name'];
        $file_tmp = $file['tmp_name'];
        $file_size = $file['size'];
        $file_ext = strtolower(pathinfo($filename, PATHINFO_EXTENSION));
        
        // Check if the file is a PDF
        if($file_ext !== "pdf") {
            $error = "Only PDF files are allowed!";
        } else {
            // Create a unique filename
            $new_filename = uniqid() . '_' . $filename;
            $upload_dir = "uploads/";
            
            // Create directory if it doesn't exist
            if (!is_dir($upload_dir)) {
                mkdir($upload_dir, 0755, true);
            }
            
            $file_path = $upload_dir . $new_filename;
            
            // Generate access key for private PDFs
            $access_key = null;
            if (!$is_public) {
                $access_key = bin2hex(random_bytes(16)); // 32 character hex string
            }
            
            // Move uploaded file
            if(move_uploaded_file($file_tmp, $file_path)) {
                // Insert into database
                $stmt = $conn->prepare("INSERT INTO tbl_pdfs (title, description, filename, file_path, file_size, user_id, category, tags, is_public, access_key) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?)");
                $stmt->bind_param("sssssissis", $title, $description, $new_filename, $file_path, $file_size, $user_id, $category, $tags, $is_public, $access_key);
                
                if($stmt->execute()) {
                    $success = "PDF uploaded successfully!";
                } else {
                    $error = "Error uploading to database: " . $stmt->error;
                }
                $stmt->close();
            } else {
                $error = "Error uploading file.";
            }
        }
    } else {
        $error = "Please select a PDF file.";
    }
}

// Get categories from existing PDFs
$categories = [];
$categoryQuery = $conn->query("SELECT DISTINCT category FROM tbl_pdfs WHERE category IS NOT NULL");
while($row = $categoryQuery->fetch_assoc()) {
    if(!empty($row['category'])) {
        $categories[] = $row['category'];
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons/font/bootstrap-icons.css" rel="stylesheet">
    <link rel="stylesheet" href="bootstrap5/css/bootstrap.min.css">
    <link rel="stylesheet" href="css/uploads.css">
    <title>Upload PDF - Jot Well</title>
    <style>
        .sidebar {
            width: 250px;
            position: fixed;
            top: 0;
            left: 0;
            height: 100%;
            background-color: #c4bdf8;
            color: #162144;
            padding-top: 20px;
            padding-left: 20px;
        }
        .sidebar h2 {
            text-align: center;
            margin-bottom: 20px;
        }
        .sidebar a {
            display: block;
            padding: 10px;
            text-decoration: none;
            color: white;
            margin-bottom: 10px;
            border-bottom: 1px solid #444;
        }
        .sidebar a:hover {
            background-color: #575757;
        }

        /* Content Styling */
        .content {
            margin-left: 250px;
            padding: 20px;
        }
        
        .alert {
            padding: 15px;
            margin-bottom: 20px;
            border-radius: 4px;
        }
        
        .alert-success {
            background-color: #d4edda;
            color: #155724;
            border: 1px solid #c3e6cb;
        }
        
        .alert-danger {
            background-color: #f8d7da;
            color: #721c24;
            border: 1px solid #f5c6cb;
        }
        
        .upload-form {
            background-color: #f9f9f9;
            padding: 20px;
            border-radius: 8px;
            box-shadow: 0 0 15px rgba(0, 0, 0, 0.1);
        }
        
        .form-control:focus {
            border-color: #c4bdf8;
            box-shadow: 0 0 0 0.25rem rgba(196, 189, 248, 0.25);
        }
        
        .btn-primary {
            background-color: #9f6335;
            border-color: #9f6335;
        }
        
        .btn-primary:hover {
            background-color: #8B5429;
            border-color: #8B5429;
        }
    </style>
</head>
<body>
    <!-- Sidebar -->
    <div class="sidebar">
        <h2><img src="pics/sdaa.png" alt="Logo" style="width: 130px;"></h2>
        <div class="container px-4">
            <a class="navbar-brand" href="/"></a>
            <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#collapsibleNavbar" aria-controls="collapsibleNavbar">
                <span class="navbar-toggler-icon"></span>
            </button>
            <div class="navbar-collapse" id="collapsibleNavbar">
                <ul class="navbar-nav">
                    <li class="nav-item pt-2"><a class="nav-link text-black" href="pdf_library.php">PDF LIBRARY</a></li>
                    <li class="nav-item pt-2"><a class="nav-link text-black" href="upload_pdf.php">UPLOAD PDF</a></li>
                    <li class="nav-item pt-2"><a class="nav-link text-black" href="uploads.php">UPLOADS</a></li>
                    <li class="nav-item pt-2"><a class="nav-link text-black" href="techniques.php">TECHNIQUES</a></li>
                    <li class="nav-item pt-2"><a class="nav-link text-black" href="quiz.php">QUIZ</a></li>
                </ul>
                <div class="navbar-nav ms-auto mb-2 mb-lg-0">
                    <li class="nav-item dropdown pe-5">
                        <a class="nav-link text-black dropdown-toggle" href="#" id="navbarDropdown" role="button" data-bs-toggle="dropdown" aria-expanded="false">
                            <i class="bi bi-person-circle ps-3" style="font-size: 1.5rem; color: #e8ff32;"></i>
                        </a>
                        <ul class="dropdown-menu">
                            <li><a class="dropdown-item text-black" href="#">My Account</a></li>
                            <li><hr class="dropdown-divider"></li>
                            <li><button type="button" id="confirmLogoutBtn" class="btn bg-transparent border-0">Logout</button></li>
                        </ul>
                    </li>
                </div>
            </div>
        </div>
    </div>

    <!-- Page Content -->
    <div class="content">
        <div class="container">
            <h1 class="mb-4">Upload PDF to Library</h1>
            
            <?php if($error): ?>
                <div class="alert alert-danger"><?php echo $error; ?></div>
            <?php endif; ?>
            
            <?php if($success): ?>
                <div class="alert alert-success"><?php echo $success; ?></div>
            <?php endif; ?>
            
            <div class="upload-form">
                <form action="" method="post" enctype="multipart/form-data">
                    <div class="mb-3">
                        <label for="title" class="form-label">Title</label>
                        <input type="text" class="form-control" id="title" name="title" required>
                    </div>
                    
                    <div class="mb-3">
                        <label for="description" class="form-label">Description</label>
                        <textarea class="form-control" id="description" name="description" rows="3"></textarea>
                    </div>
                    
                    <div class="mb-3">
                        <label for="category" class="form-label">Category</label>
                        <input type="text" class="form-control" id="category" name="category" list="categoryList">
                        <datalist id="categoryList">
                            <?php foreach($categories as $category): ?>
                                <option value="<?php echo htmlspecialchars($category); ?>">
                            <?php endforeach; ?>
                        </datalist>
                    </div>
                    
                    <div class="mb-3">
                        <label for="tags" class="form-label">Tags (comma separated)</label>
                        <input type="text" class="form-control" id="tags" name="tags">
                    </div>
                    
                    <div class="mb-3">
                        <label for="pdf_file" class="form-label">Select PDF</label>
                        <input type="file" class="form-control" id="pdf_file" name="pdf_file" accept=".pdf" required>
                    </div>
                    
                    <div class="mb-3 form-check">
                        <input type="checkbox" class="form-check-input" id="is_public" name="is_public" checked>
                        <label class="form-check-label" for="is_public">Make this PDF public</label>
                        <small class="form-text text-muted d-block">If unchecked, the PDF will be accessible only with a unique access key.</small>
                    </div>
                    
                    <button type="submit" name="upload_pdf" class="btn btn-primary">Upload PDF</button>
                </form>
            </div>
        </div>
    </div>

    <!-- Logout Confirmation Modal -->
    <div class="modal fade" id="logoutModal" tabindex="-1" aria-labelledby="logoutModalLabel" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="logoutModalLabel">Confirm Logout</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    Are you sure you want to logout?
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <a href="logout.php" class="btn btn-primary">Logout</a>
                </div>
            </div>
        </div>
    </div>

    <script src="jquery-3.7.1.min.js"></script>
    <script src="bootstrap5/js/popper.min.js"></script>
    <script src="bootstrap5/js/bootstrap.bundle.min.js"></script>
    <script>
        document.getElementById('confirmLogoutBtn').addEventListener('click', function() {
            var logoutModal = new bootstrap.Modal(document.getElementById('logoutModal'));
            logoutModal.show();
        });
    </script>
</body>
</html>