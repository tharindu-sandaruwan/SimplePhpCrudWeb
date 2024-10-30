<?php
// config.php
$host = 'localhost';
$dbname = 'stdmanagement';
$username = 'root';
$password = '';

try {
    $pdo = new PDO("mysql:host=$host;dbname=$dbname", $username, $password);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch(PDOException $e) {
    echo "Connection failed: " . $e->getMessage();
}

// Create students table if not exists
$pdo->exec("CREATE TABLE IF NOT EXISTS students (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(100) NOT NULL,
    email VARCHAR(100) NOT NULL,
    phone VARCHAR(20),
    course VARCHAR(50),
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
)");

// Get student data for editing
$edit_student = null;
if(isset($_GET['edit'])) {
    $stmt = $pdo->prepare("SELECT * FROM students WHERE id = ?");
    $stmt->execute([$_GET['edit']]);
    $edit_student = $stmt->fetch();
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Student Management System Example</title>
    <link href="https://cdnjs.cloudflare.com/ajax/libs/tailwindcss/2.2.19/tailwind.min.css" rel="stylesheet">
</head>
<body class="bg-gray-100">
    <div class="container mx-auto px-4 py-8">
        <h1 class="text-3xl font-bold mb-8 text-center text-gray-800">Student Management System</h1>
        
        <!-- Add/Edit Student Form -->
        <div class="bg-white rounded-lg shadow-md p-6 mb-8">
            <h2 class="text-xl font-semibold mb-4">
                <?php echo $edit_student ? 'Edit Student' : 'Add New Student'; ?>
            </h2>
            <form method="POST" action="">
                <?php if($edit_student) : ?>
                    <input type="hidden" name="id" value="<?php echo $edit_student['id']; ?>">
                <?php endif; ?>
                
                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <div>
                        <label class="block text-gray-700 mb-2">Name:</label>
                        <input type="text" name="name" required 
                               value="<?php echo $edit_student ? htmlspecialchars($edit_student['name']) : ''; ?>"
                               class="w-full px-3 py-2 border rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-400">
                    </div>
                    <div>
                        <label class="block text-gray-700 mb-2">Email:</label>
                        <input type="email" name="email" required 
                               value="<?php echo $edit_student ? htmlspecialchars($edit_student['email']) : ''; ?>"
                               class="w-full px-3 py-2 border rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-400">
                    </div>
                    <div>
                        <label class="block text-gray-700 mb-2">Phone:</label>
                        <input type="tel" name="phone" 
                               value="<?php echo $edit_student ? htmlspecialchars($edit_student['phone']) : ''; ?>"
                               class="w-full px-3 py-2 border rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-400">
                    </div>
                    <div>
                        <label class="block text-gray-700 mb-2">Course:</label>
                        <input type="text" name="course" 
                               value="<?php echo $edit_student ? htmlspecialchars($edit_student['course']) : ''; ?>"
                               class="w-full px-3 py-2 border rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-400">
                    </div>
                </div>
                <div class="mt-4 flex gap-2">
                    <button type="submit" name="<?php echo $edit_student ? 'update' : 'submit'; ?>" 
                            class="bg-blue-500 text-white px-4 py-2 rounded-lg hover:bg-blue-600 transition duration-200">
                        <?php echo $edit_student ? 'Update Student' : 'Add Student'; ?>
                    </button>
                    <?php if($edit_student) : ?>
                        <a href="index.php" 
                           class="bg-gray-500 text-white px-4 py-2 rounded-lg hover:bg-gray-600 transition duration-200">
                            Cancel
                        </a>
                    <?php endif; ?>
                </div>
            </form>
        </div>

        <?php
        // Handle form submission for adding new student
        if(isset($_POST['submit'])) {
            $name = $_POST['name'];
            $email = $_POST['email'];
            $phone = $_POST['phone'];
            $course = $_POST['course'];

            $stmt = $pdo->prepare("INSERT INTO students (name, email, phone, course) VALUES (?, ?, ?, ?)");
            $stmt->execute([$name, $email, $phone, $course]);

            echo "<div class='bg-green-100 border-l-4 border-green-500 text-green-700 p-4 mb-4'>Student added successfully!</div>";
        }

        // Handle form submission for updating student
        if(isset($_POST['update'])) {
            $id = $_POST['id'];
            $name = $_POST['name'];
            $email = $_POST['email'];
            $phone = $_POST['phone'];
            $course = $_POST['course'];

            $stmt = $pdo->prepare("UPDATE students SET name = ?, email = ?, phone = ?, course = ? WHERE id = ?");
            $stmt->execute([$name, $email, $phone, $course, $id]);

            echo "<div class='bg-green-100 border-l-4 border-green-500 text-green-700 p-4 mb-4'>
                    Student updated successfully! <a href='index.php' class='underline'>Return to list</a>
                  </div>";
        }

        // Delete student
        if(isset($_GET['delete'])) {
            $id = $_GET['delete'];
            $stmt = $pdo->prepare("DELETE FROM students WHERE id = ?");
            $stmt->execute([$id]);
            echo "<div class='bg-red-100 border-l-4 border-red-500 text-red-700 p-4 mb-4'>Student deleted successfully!</div>";
        }
        ?>

        <!-- Students List -->
        <div class="bg-white rounded-lg shadow-md p-6">
            <h2 class="text-xl font-semibold mb-4">Students List</h2>
            <div class="overflow-x-auto">
                <table class="w-full table-auto">
                    <thead>
                        <tr class="bg-gray-100">
                            <th class="px-4 py-2 text-left">Name</th>
                            <th class="px-4 py-2 text-left">Email</th>
                            <th class="px-4 py-2 text-left">Phone</th>
                            <th class="px-4 py-2 text-left">Course</th>
                            <th class="px-4 py-2 text-left">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php
                        $stmt = $pdo->query("SELECT * FROM students ORDER BY created_at DESC");
                        while($row = $stmt->fetch()) {
                            echo "<tr class='border-b hover:bg-gray-50'>";
                            echo "<td class='px-4 py-2'>" . htmlspecialchars($row['name']) . "</td>";
                            echo "<td class='px-4 py-2'>" . htmlspecialchars($row['email']) . "</td>";
                            echo "<td class='px-4 py-2'>" . htmlspecialchars($row['phone']) . "</td>";
                            echo "<td class='px-4 py-2'>" . htmlspecialchars($row['course']) . "</td>";
                            echo "<td class='px-4 py-2 flex gap-2'>
                                    <a href='?edit=" . $row['id'] . "' 
                                       class='text-blue-500 hover:text-blue-700'>
                                        Edit
                                    </a>
                                    <a href='?delete=" . $row['id'] . "' 
                                       class='text-red-500 hover:text-red-700'
                                       onclick='return confirm(\"Are you sure you want to delete this student?\")'>
                                        Delete
                                    </a>
                                  </td>";
                            echo "</tr>";
                        }
                        ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</body>
</html>