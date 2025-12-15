<?php
session_start();
include_once 'config.php';

if (!isset($_SESSION['student_email'])) {
    header('Location: lol.php'); 
    exit();
}

// Get current user ID
$email = $_SESSION['student_email'];
$userQuery = $conn->prepare("SELECT id FROM tbl_users WHERE email = ?");
$userQuery->bind_param("s", $email);
$userQuery->execute();
$userResult = $userQuery->get_result();
$user = $userResult->fetch_assoc();
$user_id = $user['id'];

$message = '';
$error = '';
$success = '';

// Handle PDF deletion
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['delete_pdf'])) {
    $pdf_id = intval($_POST['pdf_id']);
    
    // Get file path before deleting
    $fileQuery = $conn->prepare("SELECT file_path FROM tbl_pdfs WHERE pdf_id = ? AND user_id = ?");
    $fileQuery->bind_param("ii", $pdf_id, $user_id);
    $fileQuery->execute();
    $fileResult = $fileQuery->get_result();
    
    if ($fileResult->num_rows === 1) {
        $file = $fileResult->fetch_assoc();
        $file_path = $file['file_path'];
        
        // Delete from database
        $deleteQuery = $conn->prepare("DELETE FROM tbl_pdfs WHERE pdf_id = ? AND user_id = ?");
        $deleteQuery->bind_param("ii", $pdf_id, $user_id);
        
        if ($deleteQuery->execute()) {
            // Delete file from server
            if (file_exists($file_path)) {
                unlink($file_path);
            }
            $success = "PDF deleted successfully!";
        } else {
            $error = "Error deleting PDF!";
        }
    } else {
        $error = "PDF not found or you don't have permission to delete it!";
    }
}

// Handle access key regeneration
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['regenerate_key'])) {
    $pdf_id = intval($_POST['pdf_id']);
    $new_key = bin2hex(random_bytes(16)); // 32 character hex string
    
    $updateQuery = $conn->prepare("UPDATE tbl_pdfs SET access_key = ? WHERE pdf_id = ? AND user_id = ?");
    $updateQuery->bind_param("sii", $new_key, $pdf_id, $user_id);
    
    if ($updateQuery->execute()) {
        $success = "Access key regenerated successfully!";
    } else {
        $error = "Error regenerating access key!";
    }
}

// Handle visibility toggle
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['toggle_visibility'])) {
    $pdf_id = intval($_POST['pdf_id']);
    $current_visibility = intval($_POST['current_visibility']);
    $new_visibility = $current_visibility ? 0 : 1; // Toggle
    
    // Generate access key if making private
    $access_key = null;
    if ($new_visibility === 0) {
        $access_key = bin2hex(random_bytes(16));
    }
    
    $updateQuery = $conn->prepare("UPDATE tbl_pdfs SET is_public = ?, access_key = ? WHERE pdf_id = ? AND user_id = ?");
    $updateQuery->bind_param("isii", $new_visibility, $access_key, $pdf_id, $user_id);
    
    if ($updateQuery->execute()) {
        $success = "PDF visibility updated successfully!";
    } else {
        $error = "Error updating PDF visibility!";
    }
}

// Pagination
$page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
$limit = 10;
$offset = ($page - 1) * $limit;

// Filtering
$filter_category = isset($_GET['category']) ? $_GET['category'] : '';
$filter_query = '';
$params = [];
$param_types = '';

// Base query for all PDFs accessible to the user
$query = "SELECT p.*, u.firstname, u.lastname FROM tbl_pdfs p 
          LEFT JOIN tbl_users u ON p.user_id = u.id 
          WHERE (p.is_public = 1 OR p.user_id = ?)";
$params[] = $user_id;
$param_types .= 'i';

// Add category filter if specified
if (!empty($filter_category)) {
    $query .= " AND p.category = ?";
    $params[] = $filter_category;
    $param_types .= 's';
}

// Add search term filter if specified
if (isset($_GET['search']) && !empty($_GET['search'])) {
    $search = '%' . $_GET['search'] . '%';
    $query .= " AND (p.title LIKE ? OR p.description LIKE ? OR p.tags LIKE ?)";
    $params[] = $search;
    $params[] = $search;
    $params[] = $search;
    $param_types .= 'sss';
}

// Add ordering
$query .= " ORDER BY p.upload_date DESC";

// Add limit for pagination
$query .= " LIMIT ? OFFSET ?";
$params[] = $limit;
$params[] = $offset;
$param_types .= 'ii';

// Prepare and execute the query
$stmt = $conn->prepare($query);
$stmt->bind_param($param_types, ...$params);
$stmt->execute();
$result = $stmt->get_result();
$pdfs = [];
while ($row = $result->fetch_assoc()) {
    $pdfs[] = $row;
}

// Get total number of PDFs for pagination
$count_params = array_slice($params, 0, -2); // Remove limit and offset
$count_param_types = substr($param_types, 0, -2); // Remove ii for limit and offset
$count_query = str_replace("SELECT p.*, u.firstname, u.lastname", "SELECT COUNT(*) as total", 
                           substr($query, 0, strrpos($query, " LIMIT")));

$count_stmt = $conn->prepare($count_query);
if (!empty($count_params)) {
    $count_stmt->bind_param($count_param_types, ...$count_params);
}
$count_stmt->execute();
$count_result = $count_stmt->get_result();
$total_rows = $count_result->fetch_assoc()['total'];
$total_pages = ceil($total_rows / $limit);

// Get all categories for filter dropdown
$categoryQuery = $conn->query("SELECT DISTINCT category FROM tbl_pdfs WHERE category IS NOT NULL AND category != ''");
$categories = [];
while($row = $categoryQuery->fetch_assoc()) {
    $categories[] = $row['category'];
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
    <title>PDF Library - Jot Well</title>
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
        
        .card {
            transition: transform 0.3s;
        }
        
        .card:hover {
            transform: translateY(-5px);
            box-shadow: 0 10px 20px rgba(0,0,0,0.1);
        }
        
        .pagination .page-item.active .page-link {
            background-color: #9f6335;
            border-color: #9f6335;
        }
        
        .pagination .page-link {
            color: #9f6335;
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
        
        .btn-primary {
            background-color: #9f6335;
            border-color: #9f6335;
        }
        
        .btn-primary:hover {
            background-color: #8B5429;
            border-color: #8B5429;
        }
        
        .access-key {
            background-color: #f9f9f9;
            padding: 6px;
            border-radius: 4px;
            font-family: monospace;
            border: 1px solid #ddd;
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
            <h1 class="mb-4">PDF Library</h1>
            
            <?php if($error): ?>
                <div class="alert alert-danger"><?php echo $error; ?></div>
            <?php endif; ?>
            
            <?php if($success): ?>
                <div class="alert alert-success"><?php echo $success; ?></div>
            <?php endif; ?>
            
            <!-- Filter and Search Controls -->
            <div class="row mb-4">
                <div class="col-md-6">
                    <form action="" method="get" class="d-flex">
                        <select name="category" class="form-select me-2">
                            <option value="">All Categories</option>
                            <?php foreach($categories as $category): ?>
                                <option value="<?php echo htmlspecialchars($category); ?>" <?php echo $filter_category === $category ? 'selected' : ''; ?>>
                                    <?php echo htmlspecialchars($category); ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                        <button type="submit" class="btn btn-primary">Filter</button>
                    </form>
                </div>
                <div class="col-md-6">
                    <form action="" method="get" class="d-flex">
                        <?php if(!empty($filter_category)): ?>
                            <input type="hidden" name="category" value="<?php echo htmlspecialchars($filter_category); ?>">
                        <?php endif; ?>
                        <input type="text" name="search" class="form-control me-2" placeholder="Search by title, description or tags" value="<?php echo isset($_GET['search']) ? htmlspecialchars($_GET['search']) : ''; ?>">
                        <button type="submit" class="btn btn-primary">Search</button>
                    </form>
                </div>
            </div>
            
            <!-- Add PDF Button -->
            <div class="mb-4">
                <a href="upload_pdf.php" class="btn btn-primary">
                    <i class="bi bi-plus-lg"></i> Upload New PDF
                </a>
            </div>
            
            <!-- PDFs List -->
            <?php if(empty($pdfs)): ?>
                <div class="alert alert-info">No PDFs found. Upload some PDFs to get started!</div>
            <?php else: ?>
                <div class="row row-cols-1 row-cols-md-2 row-cols-lg-3 g-4">
                    <?php foreach($pdfs as $pdf): ?>
                        <div class="col">
                            <div class="card h-100">
                                <div class="card-header d-flex justify-content-between align-items-center">
                                    <span>
                                        <?php if($pdf['is_public']): ?>
                                            <span class="badge badge-public">Public</span>
                                        <?php else: ?>
                                            <span class="badge badge-private">Private</span>
                                        <?php endif; ?>
                                        
                                        <?php if(!empty($pdf['category'])): ?>
                                            <span class="badge badge-category"><?php echo htmlspecialchars($pdf['category']); ?></span>
                                        <?php endif; ?>
                                    </span>
                                    
                                    <?php if($pdf['user_id'] == $user_id): ?>
                                        <div class="dropdown">
                                            <button class="btn btn-sm btn-outline-secondary dropdown-toggle" type="button" id="dropdownMenuButton<?php echo $pdf['pdf_id']; ?>" data-bs-toggle="dropdown" aria-expanded="false">
                                                <i class="bi bi-gear"></i>
                                            </button>
                                            <ul class="dropdown-menu" aria-labelledby="dropdownMenuButton<?php echo $pdf['pdf_id']; ?>">
                                                <li>
                                                    <form action="" method="post">
                                                        <input type="hidden" name="pdf_id" value="<?php echo $pdf['pdf_id']; ?>">
                                                        <input type="hidden" name="current_visibility" value="<?php echo $pdf['is_public']; ?>">
                                                        <button type="submit" name="toggle_visibility" class="dropdown-item">
                                                            <?php if($pdf['is_public']): ?>
                                                                <i class="bi bi-lock-fill"></i> Make Private
                                                            <?php else: ?>
                                                                <i class="bi bi-unlock-fill"></i> Make Public
                                                            <?php endif; ?>
                                                        </button>
                                                    </form>
                                                </li>
                                                <?php if(!$pdf['is_public']): ?>
                                                <li>
                                                    <form action="" method="post">
                                                        <input type="hidden" name="pdf_id" value="<?php echo $pdf['pdf_id']; ?>">
                                                        <button type="submit" name="regenerate_key" class="dropdown-item">
                                                            <i class="bi bi-key-fill"></i> Regenerate Access Key
                                                        </button>
                                                    </form>
                                                </li>
                                                <?php endif; ?>
                                                <li>
                                                    <form action="" method="post" onsubmit="return confirm('Are you sure you want to delete this PDF?');">
                                                        <input type="hidden" name="pdf_id" value="<?php echo $pdf['pdf_id']; ?>">
                                                        <button type="submit" name="delete_pdf" class="dropdown-item text-danger">
                                                            <i class="bi bi-trash-fill"></i> Delete
                                                        </button>
                                                    </form>
                                                </li>
                                            </ul>
                                        </div>
                                    <?php endif; ?>
                                </div>
                                <div class="card-body">
                                    <h5 class="card-title"><?php echo htmlspecialchars($pdf['title']); ?></h5>
                                    <p class="card-text">
                                        <?php if(!empty($pdf['description'])): ?>
                                            <?php echo htmlspecialchars(substr($pdf['description'], 0, 100)); ?>
                                            <?php echo strlen($pdf['description']) > 100 ? '...' : ''; ?>
                                        <?php else: ?>
                                            <em>No description available</em>
                                        <?php endif; ?>
                                    </p>
                                    
                                    <?php if(!empty($pdf['tags'])): ?>
                                        <div class="mb-2">
                                            <?php foreach(explode(',', $pdf['tags']) as $tag): ?>
                                                <?php if(trim($tag) !== ''): ?>
                                                    <span class="badge bg-secondary"><?php echo htmlspecialchars(trim($tag)); ?></span>
                                                <?php endif; ?>
                                            <?php endforeach; ?>
                                        </div>
                                    <?php endif; ?>
                                    
                                    <p class="card-text">
                                        <small class="text-muted">
                                            Uploaded by: <?php echo htmlspecialchars($pdf['firstname'] . ' ' . $pdf['lastname']); ?><br>
                                            Date: <?php echo date('M j, Y', strtotime($pdf['upload_date'])); ?>
                                        </small>
                                    </p>
                                    
                                    <?php if(!$pdf['is_public'] && $pdf['user_id'] == $user_id): ?>
                                        <div class="mb-3">
                                            <small>Access Key:</small>
                                            <div class="access-key"><?php echo htmlspecialchars($pdf['access_key']); ?></div>
                                        </div>
                                    <?php endif; ?>
                                </div>
                                <div class="card-footer">
                                    <a href="view_pdf.php?id=<?php echo $pdf['pdf_id']; ?>" class="btn btn-sm btn-primary">
                                        <i class="bi bi-file-earmark-pdf"></i> View
                                    </a>
                                    <a href="<?php echo htmlspecialchars($pdf['file_path']); ?>" class="btn btn-sm btn-outline-secondary" download>
                                        <i class="bi bi-download"></i> Download
                                    </a>
                                </div>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>
                
                <!-- Pagination -->
                <?php if($total_pages > 1): ?>
                    <div class="d-flex justify-content-center mt-4">
                        <nav aria-label="Page navigation">
                            <ul class="pagination">
                                <?php if($page > 1): ?>
                                    <li class="page-item">
                                        <a class="page-link" href="?page=<?php echo ($page - 1); ?><?php echo !empty($filter_category) ? '&category=' . urlencode($filter_category) : ''; ?><?php echo isset($_GET['search']) ? '&search=' . urlencode($_GET['search']) : ''; ?>" aria-label="Previous">
                                            <span aria-hidden="true">&laquo;</span>
                                        </a>
                                    </li>
                                <?php endif; ?>
                                
                                <?php for($i = 1; $i <= $total_pages; $i++): ?>
                                    <li class="page-item <?php echo $i == $page ? 'active' : ''; ?>">
                                        <a class="page-link" href="?page=<?php echo $i; ?><?php echo !empty($filter_category) ? '&category=' . urlencode($filter_category) : ''; ?><?php echo isset($_GET['search']) ? '&search=' . urlencode($_GET['search']) : ''; ?>">
                                            <?php echo $i; ?>
                                        </a>
                                    </li>
                                <?php endfor; ?>
                                
                                <?php if($page < $total_pages): ?>
                                    <li class="page-item">
                                        <a class="page-link" href="?page=<?php echo ($page + 1); ?><?php echo !empty($filter_category) ? '&category=' . urlencode($filter_category) : ''; ?><?php echo isset($_GET['search']) ? '&search=' . urlencode($_GET['search']) : ''; ?>" aria-label="Next">
                                            <span aria-hidden="true">&raquo;</span>
                                        </a>
                                    </li>
                                <?php endif; ?>
                            </ul>
                        </nav>
                    </div>
                <?php endif; ?>
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
    </script>
</body>
</html>
