<?php
require_once 'config.php';

try {
    $conn = getDBConnection();
    $result = $conn->query("SELECT * FROM registrations ORDER BY registration_date DESC");
    $registrations = [];
    
    if ($result) {
        while ($row = $result->fetch_assoc()) {
            $registrations[] = $row;
        }
        $result->free();
    }
    
    $conn->close();
} catch (Exception $e) {
    $error = $e->getMessage();
}
?>

<!DOCTYPE html>
<html>
<head>
    <title>View Registrations</title>
    <style>
        body { font-family: Arial, sans-serif; margin: 20px; }
        table { border-collapse: collapse; width: 100%; margin-top: 20px; }
        th, td { border: 1px solid #ddd; padding: 12px; text-align: left; }
        th { background-color: #f2f2f2; }
        .photo-cell { text-align: center; }
        .photo-img { max-width: 150px; max-height: 150px; border-radius: 5px; border: 2px solid #ddd; }
        .no-photo { color: #999; font-style: italic; }
    </style>
</head>
<body>
    <h1>Seminar Registrations</h1>
    
    <?php if (isset($error)): ?>
        <p style="color: red;">Error: <?php echo $error; ?></p>
    <?php endif; ?>
    
    <table>
        <tr>
            <th>ID</th>
            <th>Name</th>
            <th>Email</th>
            <th>Phone</th>
            <th>Company</th>
            <th>Seminar Topic</th>
            <th>Profile Photo</th>
            <th>Registration Date</th>
        </tr>
        <?php foreach ($registrations as $reg): ?>
        <tr>
            <td><?php echo $reg['id']; ?></td>
            <td><?php echo htmlspecialchars($reg['full_name']); ?></td>
            <td><?php echo htmlspecialchars($reg['email']); ?></td>
            <td><?php echo htmlspecialchars($reg['phone']); ?></td>
            <td><?php echo htmlspecialchars($reg['company']); ?></td>
            <td><?php echo htmlspecialchars($reg['seminar_topic']); ?></td>
            <td class="photo-cell">
                <?php if (!empty($reg['profile_photo'])): ?>
                    <img src="uploads/<?php echo htmlspecialchars($reg['profile_photo']); ?>" 
                         alt="Profile Photo" 
                         class="photo-img"
                         onerror="this.style.display='none'; this.nextElementSibling.style.display='block';">
                    <div style="display: none;" class="no-photo">Image missing</div>
                <?php else: ?>
                    <span class="no-photo">No photo</span>
                <?php endif; ?>
            </td>
            <td><?php echo $reg['registration_date']; ?></td>
        </tr>
        <?php endforeach; ?>
    </table>
    
    <p><a href="index.php">Back to Registration Form</a></p>
</body>
</html>