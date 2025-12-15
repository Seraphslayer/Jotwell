<?php
session_start();
include_once 'config.php';

if (!isset($_SESSION['student_email'])) {
    header('Location: lol.php'); 
    exit();
}

$error = '';
$pdf_data = null;

// Get current user ID
$email = $_SESSION['student_email'];
$userQuery = $conn->prepare("SELECT id FROM tbl_users WHERE email = ?");
$userQuery->bind_param("s", $email);
$userQuery->execute();
$userResult = $userQuery->get_result();
$user = $userResult->fetch_assoc();
$user_id = $user['id'];

// Check if PDF ID is provided
if (isset($_GET['id'])) {
    $pdf_id = intval($_GET['id']);
    
    // Fetch PDF data
    $pdfQuery = $conn->prepare("SELECT p.*, u.firstname, u.lastname FROM tbl_pdfs p 
                                LEFT JOIN tbl_users u ON p.user_id = u.id
                                WHERE p.pdf_id = ?");
    $pdfQuery->bind_param("i", $pdf_id);
    $pdfQuery->execute();
    $pdfResult = $pdfQuery->get_result();
    
    if ($pdfResult->num_rows === 1) {
        $pdf_data = $pdfResult->fetch_assoc();
        
        // Check access permissions
        if (!$pdf_data['is_public'] && $pdf_data['user_id'] != $user_id) {
            // Check if access key is provided for private PDFs
            if (!isset($_GET['key']) || $_GET['key'] !== $pdf_data['access_key']) {
                $error = "This PDF is private and requires a valid access key.";
                $pdf_data = null;
            }
        }
    } else {
        $error = "PDF not found!";
    }
} else {
    $error = "No PDF selected.";
}

// Handle access key submission
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['submit_key'])) {
    $provided_key = trim($_POST['access_key']);
    $pdf_id = intval($_POST['pdf_id']);
    
    // Redirect to the same page with key parameter
    header("Location: view_pdf.php?id=$pdf_id&key=$provided_key");
    exit();
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
    <title><?php echo $pdf_data ? htmlspecialchars($pdf_data['title']) : 'View PDF'; ?> - Jot Well</title>
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
        
        .alert-danger {
            background-color: #f8d7da;
            color: #721c24;
            border: 1px solid #f5c6cb;
        }
        
        .pdf-viewer {
            width: 100%;
            height: 800px;
            border: 1px solid #ddd;
            border-radius: 4px;
        }
        
        .pdf-info {
            background-color: #f9f9f9;
            padding: 20px;
            border-radius: 8px;
            margin-bottom: 20px;
            box-shadow: 0 0 15px rgba(0, 0, 0, 0.1);
        }
        
        .badge-category {
            background-color: #c4bdf8;
            color: #162144;
        }
        
        .badge-private {
            background-color: #f8c5b9;
            color: #7b2d20;
        }
        
        .badge-public {
            background-color: #b9f8c5;
            color: #207b2d;
        }
        
        .key-form {
            max-width: 500px;
            margin: 50px auto;
            padding: 20px;
            background-color: #f9f9f9;
            border-radius: 8px;
            box-shadow: 0 0 15px rgba(0, 0, 0, 0.1);
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
            <?php if($error): ?>
                <div class="alert alert-danger"><?php echo $error; ?></div>
                
                <?php if(isset($_GET['id']) && !isset($pdf_data)): ?>
                    <!-- Access Key Form for Private PDF -->
                    <div class="key-form">
                        <h4>This PDF requires an access key</h4>
                        <form action="" method="post">
                            <div class="mb-3">
                                <label for="access_key" class="form-label">Enter Access Key</label>
                                <input type="text" class="form-control" id="access_key" name="access_key" required>
                                <input type="hidden" name="pdf_id" value="<?php echo intval($_GET['id']); ?>">
                            </div>
                            <button type="submit" name="submit_key" class="btn btn-primary">Access PDF</button>
                        </form>
                    </div>
                <?php endif; ?>
                
            <?php elseif($pdf_data): ?>
                <!-- PDF Information -->
                <div class="pdf-info">
                    <div class="d-flex justify-content-between align-items-center mb-3">
                        <h1><?php echo htmlspecialchars($pdf_data['title']); ?></h1>
                        <div>
                            <?php if($pdf_data['is_public']): ?>
                                <span class="badge badge-public">Public</span>
                            <?php else: ?>
                                <span class="badge badge-private">Private</span>
                            <?php endif; ?>
                            
                            <?php if(!empty($pdf_data['category'])): ?>
                                <span class="badge badge-category"><?php echo htmlspecialchars($pdf_data['category']); ?></span>
                            <?php endif; ?>
                        </div>
                    </div>
                    
                    <div class="row mb-3">
                        <div class="col-md-8">
                            <?php if(!empty($pdf_data['description'])): ?>
                                <p><?php echo nl2br(htmlspecialchars($pdf_data['description'])); ?></p>
                            <?php else: ?>
                                <p><em>No description available</em></p>
                            <?php endif; ?>
                        </div>
                        <div class="col-md-4">
                            <p>
                                <strong>Uploaded by:</strong> <?php echo htmlspecialchars($pdf_data['firstname'] . ' ' . $pdf_data['lastname']); ?><br>
                                <strong>Date:</strong> <?php echo date('M j, Y', strtotime($pdf_data['upload_date'])); ?><br>
                                <strong>File size:</strong> <?php echo round($pdf_data['file_size'] / 1024, 2); ?> KB
                            </p>
                            
                            <?php if(!empty($pdf_data['tags'])): ?>
                                <div class="mb-2">
                                    <strong>Tags:</strong><br>
                                    <?php foreach(explode(',', $pdf_data['tags']) as $tag): ?>
                                        <?php if(trim($tag) !== ''): ?>
                                            <span class="badge bg-secondary"><?php echo htmlspecialchars(trim($tag)); ?></span>
                                        <?php endif; ?>
                                    <?php endforeach; ?>
                                </div>
                            <?php endif; ?>
                            
                            <!-- Share Link (for private PDFs) -->
                            <?php if(!$pdf_data['is_public'] && $pdf_data['user_id'] == $user_id): ?>
                                <div class="mt-3">
                                    <p><strong>Share Link:</strong></p>
                                    <div class="input-group mb-3">
                                        <input type="text" class="form-control" value="<?php echo htmlspecialchars($_SERVER['HTTP_HOST'] . $_SERVER['PHP_SELF'] . '?id=' . $pdf_data['pdf_id'] . '&key=' . $pdf_data['access_key']); ?>" id="shareLink" readonly>
                                        <button class="btn btn-outline-secondary" type="button" onclick="copyShareLink()">
                                            <i class="bi bi-clipboard"></i>
                                        </button>
                                    </div>
                                </div>
                            <?php endif; ?>
                        </div>
                    </div>
                    
                    <div class="d-flex">
                        <a href="pdf_library.php" class="btn btn-secondary me-2">
                            <i class="bi bi-arrow-left"></i> Back to Library
                        </a>
                        <a href="<?php echo htmlspecialchars($pdf_data['file_path']); ?>" class="btn btn-primary" download>
                            <i class="bi bi-download"></i> Download PDF
                        </a>
                    </div>
                </div>
                
                <!-- PDF Viewer -->
                <div class="card">
                    <div class="card-body p-0">
                        <iframe src="<?php echo htmlspecialchars($pdf_data['file_path']); ?>" class="pdf-viewer"></iframe>
                    </div>
                </div>
            <?php endif; ?>
        </div>
    </div>

    <!-- Logout Confirmation Modal -->
    <div class="modal fade" id="logoutModal" tabindex="-1" aria-labelledby="logoutModalLabel" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="logoutModalLabel">Logout Confirmation</h5>
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

    <script src="bootstrap5/js/bootstrap.bundle.min.js"></script>
    <script>
        // Show logout confirmation modal
        document.getElementById('confirmLogoutBtn').addEventListener('click', function() {
            var logoutModal = new bootstrap.Modal(document.getElementById('logoutModal'));
            logoutModal.show();
        });
        
        // Copy share link function
        function copyShareLink() {
            var copyText = document.getElementById("shareLink");
            copyText.select();
            copyText.setSelectionRange(0, 99999);
            navigator.clipboard.writeText(copyText.value);
            
            // Alert user that the link was copied
            alert("Share link copied to clipboard!");
        }
    </script>
</body>
</html>