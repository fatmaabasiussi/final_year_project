<?php
// Database connection
$conn = new mysqli('localhost', 'root', '1234', 'islamic_db');
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Get form data
    $name = $_POST['name'];
    $email = $_POST['email'];
    $phone = $_POST['phone'];
    $course_interest = $_POST['course_interest'];
    $message = $_POST['message'];
    
    // Create contacts table if it doesn't exist
    $sql = "CREATE TABLE IF NOT EXISTS contacts (
        id INT PRIMARY KEY AUTO_INCREMENT,
        name VARCHAR(100) NOT NULL,
        email VARCHAR(100) NOT NULL,
        phone VARCHAR(15) NOT NULL,
        course_interest VARCHAR(50) NOT NULL,
        message TEXT NOT NULL,
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
    )";
    
    $conn->query($sql);
    
    // Insert contact form data
    $sql = "INSERT INTO contacts (name, email, phone, course_interest, message) VALUES (?, ?, ?, ?, ?)";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("sssss", $name, $email, $phone, $course_interest, $message);
    
    if ($stmt->execute()) {
        // Send email notification (you'll need to configure your email settings)
        $to = "info@islamicedu.com";
        $subject = "New Contact Form Submission";
        $email_message = "Name: $name\n";
        $email_message .= "Email: $email\n";
        $email_message .= "Phone: $phone\n";
        $email_message .= "Course Interest: $course_interest\n";
        $email_message .= "Message: $message\n";
        
        mail($to, $subject, $email_message);
        
        // Set success message
        session_start();
        $_SESSION['success_message'] = "Asante! Tutawasiliana nawe hivi karibuni.";
    } else {
        $_SESSION['error_message'] = "Samahani, kulikuwa na hitilafu. Tafadhali jaribu tena.";
    }
    
    // Redirect back to the homepage
    header("Location: index.php#div_4");
    exit();
}
?> 