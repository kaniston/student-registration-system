<form action="add_student.php" method="POST">
    <input type="hidden" name="csrf_token"
           value="<?php echo htmlspecialchars($_SESSION['csrf_token']); ?>">

    <input type="text" name="name" placeholder="Student Name" required>
    <input type="email" name="email" placeholder="Email" required>
    <input type="text" name="course" placeholder="Course" required>
    <input type="number" name="age" min="16" max="21" required>

    <button type="submit">Register Student</button>
</form>