<?php
require_once 'config.php';

$search = $_GET['search'] ?? '';
$topic_filter = $_GET['topic'] ?? '';

try {
    $conn = getDBConnection();
    
    if ($search || $topic_filter) {
        $sql = "SELECT * FROM registrations WHERE 1=1";
        $types = "";
        $params = [];
        
        if ($search) {
            $sql .= " AND (full_name LIKE ? OR email LIKE ? OR company LIKE ?)";
            $types .= "sss";
            $searchTerm = "%$search%";
            $params[] = $searchTerm;
            $params[] = $searchTerm;
            $params[] = $searchTerm;
        }
        
        if ($topic_filter) {
            $sql .= " AND seminar_topic = ?";
            $types .= "s";
            $params[] = $topic_filter;
        }
        
        $sql .= " ORDER BY registration_date DESC";
        $stmt = $conn->prepare($sql);
        
        if ($params) {
            $stmt->bind_param($types, ...$params);
        }
    } else {
        $stmt = $conn->prepare("SELECT * FROM registrations ORDER BY registration_date DESC");
    }
    
    $stmt->execute();
    $result = $stmt->get_result();
    $registrations = $result->fetch_all(MYSQLI_ASSOC);
    $stmt->close();
    
    // Get unique topics for filter
    $topics_result = $conn->query("SELECT DISTINCT seminar_topic FROM registrations ORDER BY seminar_topic");
    $topics = [];
    while ($row = $topics_result->fetch_assoc()) {
        $topics[] = $row['seminar_topic'];
    }
    
    $conn->close();
} catch (Exception $e) {
    $error = $e->getMessage();
}

// Count registrations with photos
$with_photos = 0;
foreach ($registrations as $reg) {
    if (!empty($reg['profile_photo'])) $with_photos++;
}
?>

<!DOCTYPE html>
<html>
<head>
    <title>Admin - View Registrations</title>
    <style>
        body { 
            font-family: Arial, sans-serif; 
            margin: 0;
            padding: 20px;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            min-height: 100vh;
        }
        .container {
            max-width: 1800px;
            margin: 0 auto;
            background: white;
            border-radius: 15px;
            padding: 30px;
            box-shadow: 0 10px 30px rgba(0, 0, 0, 0.3);
        }
        h1 {
            text-align: center;
            color: #333;
            margin-bottom: 30px;
            font-size: 2.5em;
        }
        .search-box { 
            margin-bottom: 30px; 
            padding: 25px; 
            background: #f8f9fa; 
            border-radius: 10px;
            border-left: 5px solid #007bff;
        }
        .search-box input, .search-box select { 
            padding: 12px; 
            margin: 5px;
            border: 2px solid #ddd;
            border-radius: 8px;
            font-size: 16px;
        }
        .search-box input { 
            width: 300px; 
        }
        .search-box button { 
            padding: 12px 25px; 
            background: #007bff;
            color: white;
            border: none;
            border-radius: 8px;
            cursor: pointer;
            font-size: 16px;
        }
        .search-box button:hover {
            background: #0056b3;
        }
        table { 
            border-collapse: collapse; 
            width: 100%; 
            margin-top: 20px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
        }
        th, td { 
            border: 1px solid #ddd; 
            padding: 15px; 
            text-align: left; 
        }
        th { 
            background-color: #2c3e50; 
            color: white;
            font-weight: bold;
            position: sticky;
            top: 0;
        }
        tr:nth-child(even) {
            background-color: #f9f9f9;
        }
        tr:hover {
            background-color: #f1f8ff;
        }
        .photo-cell { 
            text-align: center; 
        }
        .photo-img { 
            max-width: 80px; 
            max-height: 80px; 
            border-radius: 5px; 
            border: 2px solid #ddd;
            cursor: pointer;
            transition: transform 0.3s ease;
        }
        .photo-img:hover {
            transform: scale(2.5);
            z-index: 1000;
            position: relative;
            border-color: #007bff;
        }
        .no-photo { 
            color: #999; 
            font-style: italic;
        }
        .stats { 
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            padding: 25px; 
            border-radius: 10px; 
            margin-bottom: 30px;
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
            gap: 20px;
            text-align: center;
        }
        .stat-item {
            background: rgba(255,255,255,0.1);
            padding: 20px;
            border-radius: 10px;
            backdrop-filter: blur(10px);
        }
        .stat-number {
            font-size: 2.5em;
            font-weight: bold;
            display: block;
        }
        .stat-label {
            font-size: 1.1em;
            opacity: 0.9;
        }
        .action-buttons {
            text-align: center;
            margin: 30px 0;
        }
        .btn {
            padding: 12px 24px;
            margin: 0 10px;
            border: none;
            border-radius: 25px;
            font-size: 16px;
            font-weight: bold;
            cursor: pointer;
            transition: all 0.3s ease;
            text-decoration: none;
            display: inline-block;
        }
        .btn-primary {
            background: #007bff;
            color: white;
        }
        .btn-primary:hover {
            background: #0056b3;
            transform: translateY(-2px);
        }
        .btn-success {
            background: #28a745;
            color: white;
        }
        .btn-success:hover {
            background: #1e7e34;
            transform: translateY(-2px);
        }
        .btn-danger {
            background: #dc3545;
            color: white;
        }
        .btn-danger:hover {
            background: #c82333;
            transform: translateY(-2px);
        }
        .topic-badge {
            background: #e3f2fd;
            padding: 5px 12px;
            border-radius: 15px;
            font-weight: bold;
            font-size: 0.9em;
            display: inline-block;
            margin: 2px;
        }
    </style>
</head>
<body>
    <div class="container">
        <h1>🔧 Admin Dashboard - Seminar Registrations</h1>
        
        <div class="stats">
            <div class="stat-item">
                <span class="stat-number"><?php echo count($registrations); ?></span>
                <span class="stat-label">Total Registrations</span>
            </div>
            <div class="stat-item">
                <span class="stat-number"><?php echo $with_photos; ?></span>
                <span class="stat-label">With Photos</span>
            </div>
            <div class="stat-item">
                <span class="stat-number"><?php echo count($topics); ?></span>
                <span class="stat-label">Seminar Topics</span>
            </div>
            <div class="stat-item">
                <span class="stat-number"><?php echo count($registrations) - $with_photos; ?></span>
                <span class="stat-label">Without Photos</span>
            </div>
        </div>

        <div class="search-box">
            <form method="GET">
                <input type="text" name="search" placeholder="🔍 Search by name, email, or company..." value="<?php echo htmlspecialchars($search); ?>">
                <select name="topic">
                    <option value="">All Topics</option>
                    <?php foreach ($topics as $topic): ?>
                        <option value="<?php echo htmlspecialchars($topic); ?>" <?php echo $topic_filter == $topic ? 'selected' : ''; ?>>
                            <?php echo htmlspecialchars($topic); ?>
                        </option>
                    <?php endforeach; ?>
                </select>
                <button type="submit">Search</button>
                <?php if ($search || $topic_filter): ?>
                    <a href="admin_view.php" style="margin-left: 15px; color: #007bff;">Clear Filters</a>
                <?php endif; ?>
            </form>
        </div>

        <div class="action-buttons">
            <a href="index.php" class="btn btn-primary">🏠 Back to Registration</a>
            <a href="view_registrations.php" class="btn btn-success">📋 Simple View</a>
            <button onclick="exportToExcel()" class="btn btn-danger">📊 Export to Excel</button>
        </div>
        
        <?php if (isset($error)): ?>
            <div style="color: red; padding: 15px; background: #ffe6e6; border-radius: 8px; margin: 20px 0;">
                <strong>Error:</strong> <?php echo $error; ?>
            </div>
        <?php endif; ?>
        
        <?php if (empty($registrations)): ?>
            <div style="text-align: center; padding: 40px; color: #666;">
                <h3>No registrations found</h3>
                <p>Try adjusting your search criteria.</p>
            </div>
        <?php else: ?>
            <table>
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>Name</th>
                        <th>Email</th>
                        <th>Phone</th>
                        <th>Company</th>
                        <th>Position</th>
                        <th>Seminar Topic</th>
                        <th>Dietary</th>
                        <th>Comments</th>
                        <th>Profile Photo</th>
                        <th>Registration Date</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($registrations as $reg): ?>
                    <tr>
                        <td><strong>#<?php echo $reg['id']; ?></strong></td>
                        <td><strong><?php echo htmlspecialchars($reg['full_name']); ?></strong></td>
                        <td><?php echo htmlspecialchars($reg['email']); ?></td>
                        <td><?php echo htmlspecialchars($reg['phone']); ?></td>
                        <td><?php echo htmlspecialchars($reg['company'] ?: 'N/A'); ?></td>
                        <td><?php echo htmlspecialchars($reg['position'] ?: 'N/A'); ?></td>
                        <td>
                            <span class="topic-badge"><?php echo htmlspecialchars($reg['seminar_topic']); ?></span>
                        </td>
                        <td><?php echo htmlspecialchars($reg['dietary_requirements']); ?></td>
                        <td><?php echo htmlspecialchars($reg['comments'] ?: 'No comments'); ?></td>
                        <td class="photo-cell">
                            <?php if (!empty($reg['profile_photo']) && file_exists("uploads/" . $reg['profile_photo'])): ?>
                                <img src="uploads/<?php echo htmlspecialchars($reg['profile_photo']); ?>" 
                                     alt="Profile Photo" 
                                     class="photo-img"
                                     title="Click to enlarge">
                            <?php else: ?>
                                <span class="no-photo">❌ No Photo</span>
                            <?php endif; ?>
                        </td>
                        <td><?php echo date('M j, Y g:i A', strtotime($reg['registration_date'])); ?></td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
            
            <div style="text-align: center; margin-top: 30px; color: #666;">
                <p>Showing <?php echo count($registrations); ?> registration(s)</p>
            </div>
        <?php endif; ?>
    </div>

    <script>
        function exportToExcel() {
            // Simple table export
            const table = document.querySelector('table');
            let csv = [];
            
            // Add headers
            let headers = [];
            for (let i = 0; i < table.rows[0].cells.length - 1; i++) { // -1 to exclude photo column
                headers.push(table.rows[0].cells[i].innerText);
            }
            csv.push(headers.join(','));
            
            // Add rows
            for (let i = 1; i < table.rows.length; i++) {
                let row = [];
                for (let j = 0; j < table.rows[i].cells.length - 1; j++) { // -1 to exclude photo column
                    row.push('"' + table.rows[i].cells[j].innerText.replace(/"/g, '""') + '"');
                }
                csv.push(row.join(','));
            }
            
            // Download
            const csvString = csv.join('\n');
            const blob = new Blob([csvString], { type: 'text/csv' });
            const url = window.URL.createObjectURL(blob);
            const a = document.createElement('a');
            a.setAttribute('hidden', '');
            a.setAttribute('href', url);
            a.setAttribute('download', 'seminar_registrations.csv');
            document.body.appendChild(a);
            a.click();
            document.body.removeChild(a);
        }
        
        // Add image modal functionality
        document.addEventListener('DOMContentLoaded', function() {
            const images = document.querySelectorAll('.photo-img');
            images.forEach(img => {
                img.addEventListener('click', function(e) {
                    e.stopPropagation();
                    const modal = document.createElement('div');
                    modal.style.cssText = `
                        position: fixed;
                        top: 0;
                        left: 0;
                        width: 100%;
                        height: 100%;
                        background: rgba(0,0,0,0.9);
                        display: flex;
                        justify-content: center;
                        align-items: center;
                        z-index: 10000;
                        cursor: zoom-out;
                    `;
                    
                    const modalImg = document.createElement('img');
                    modalImg.src = this.src;
                    modalImg.style.cssText = `
                        max-width: 90%;
                        max-height: 90%;
                        border-radius: 10px;
                        box-shadow: 0 20px 60px rgba(0,0,0,0.5);
                    `;
                    
                    modal.appendChild(modalImg);
                    document.body.appendChild(modal);
                    
                    modal.addEventListener('click', function() {
                        document.body.removeChild(modal);
                    });
                });
            });
        });
    </script>
</body>
</html>