<?php
// Start the session to store cart items
session_start();

// Handle file upload
$uploadSuccess = false;
$uploadError = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_FILES['receipt'])) {
    $uploadDir = __DIR__ . '/assets/uploads/';
    if (!is_dir($uploadDir)) {
        mkdir($uploadDir, 0777, true);
    }
    $uploadFile = $uploadDir . basename($_FILES['receipt']['name']);
    $fileType = pathinfo($uploadFile, PATHINFO_EXTENSION);
    $allowedTypes = ['jpg', 'jpeg', 'png', 'pdf'];
    $maxFileSize = 2 * 1024 * 1024; // 2MB

    // Validate file type and size
    if (in_array($fileType, $allowedTypes) && $_FILES['receipt']['size'] <= $maxFileSize) {
        if (move_uploaded_file($_FILES['receipt']['tmp_name'], $uploadFile)) {
            $uploadSuccess = true;
        } else {
            $uploadError = "There was an error uploading your file.";
            error_log($uploadError); // Log error for debugging
        }
    } else {
        $uploadError = "Invalid file type or size exceeds 2MB.";
        error_log($uploadError); // Log error for debugging
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="https://fonts.googleapis.com/css2?family=Inter:wght@400;700&display=swap">
    <title>Siti Cookies Online Banking</title>
    <style>
        body {
            font-family: 'Inter', sans-serif;
            margin: 0;
            padding: 0;
            background-color: #FCF7F0;
            color: #333;
        }

        header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            padding: 20px;
            background-color: #F1E8DA;
        }

        header .logo {
            display: flex;
            align-items: center;
        }

        header .logo img {
            height: 40px;
            margin-right: 10px;
            margin-left: 25px;
        }

        header nav ul {
            list-style: none;
            display: flex;
            margin: 0;
            padding: 0;
        }

        header nav ul li {
            margin-left: 40px;
        }

        header nav ul li a {
            text-decoration: none;
            color: #333;
            font-weight: 600;
            font-size: 14px;
        }

        header nav ul li a.login-signup {
            background-color: #000;
            color: #fff;
            padding: 11px 25px;
            border-radius: 5px;
            font-size: 10px;
        }

        .instructions {
            margin: 20px;
            background-color: white;
            padding: 20px;
            border-radius: 10px;
            box-shadow: 0 0 5px rgba(0, 0, 0, 0.1);
        }

        .instructions h2 {
            margin-top: 0;
        }

        .instructions p {
            margin: 10px 0;
        }

        .upload-form {
            margin: 20px;
            background-color: white;
            padding: 20px;
            border-radius: 10px;
            box-shadow: 0 0 5px rgba(0, 0, 0, 0.1);
        }

        .upload-form input[type="file"] {
            margin: 10px 0;
        }

        .upload-form button {
            background-color: #000;
            color: white;
            border: none;
            padding: 10px 20px;
            cursor: pointer;
            border-radius: 5px;
            font-weight: bold;
        }

        .upload-form button:hover {
            background-color: #555;
        }

        footer {
            background-color: #F1E8DA;
            padding: 20px;
            text-align: center;
            margin-top: 20px;
        }

        footer .footer-content {
            display: flex;
            justify-content: space-between;
            align-items: center;
        }

        footer .footer-content span {
            font-weight: 200;
            font-style: italic;
            color: #828282;
            font-size: 12px;
        }

        footer .footer-content img {
            height: 40px;
            margin-left: 25px;
        }

        footer .footer-content .social-icons img {
            height: 30px;
            margin-left: 0px;
            margin-right: 0px;
        }

        .dropdown {
            position: relative;
            display: inline-block;
        }

        .dropdown-content {
            display: none;
            position: absolute;
            left: 50%;
            transform: translateX(-50%);
            background-color: #F1E8DA;
            min-width: 60px;
            box-shadow: 0 2px 8px rgba(0, 0, 0, 0.2);
            z-index: 1;
            margin-top: 10px;
        }

        .dropdown-content a {
            color: black;
            padding: 12px 16px;
            text-decoration: none;
            display: block;
        }

        .dropdown-content a:hover {
            background-color: #ddd;
        }

        .dropdown:hover .dropdown-content {
            display: block;
        }

        .modal {
            display: none;
            position: fixed;
            z-index: 1;
            left: 0;
            top: 0;
            width: 100%;
            height: 100%;
            overflow: auto;
            background-color: rgb(0,0,0);
            background-color: rgba(0,0,0,0.4);
        }

        .modal-content {
            background-color: #fefefe;
            margin: 15% auto;
            padding: 20px;
            border: 1px solid #888;
            width: 80%;
            text-align: center;
            border-radius: 10px;
        }

        .close {
            color: #aaa;
            float: right;
            font-size: 28px;
            font-weight: bold;
        }

        .close:hover,
        .close:focus {
            color: black;
            text-decoration: none;
            cursor: pointer;
        }

        button {
            padding: 10px 20px;
            margin: 10px;
            border: none;
            background-color: #000;
            color: #fff;
            cursor: pointer;
            border-radius: 5px;
            font-size: 16px;
        }

        button:hover {
            background-color: #555;
        }

        .upload-form button {
            background-color: #000;
            color: white;
            border: none;
            padding: 10px 20px;
            cursor: pointer;
            border-radius: 5px;
            font-weight: bold;
            font-size: 16px;
        }

        .upload-form button:hover {
            background-color: #555;
        }

        .upload-confirmation {
            display: none; /* Hidden by default */
            position: fixed;
            z-index: 1;
            left: 0;
            top: 0;
            width: 100%;
            height: 100%;
            overflow: auto;
            background-color: rgba(0, 0, 0, 0.5); /* Black background with opacity */
        }

        .upload-confirmation-content {
            background-color: #fefefe;
            margin: 15% auto;
            padding: 20px;
            border: 1px solid #888;
            width: 80%;
            text-align: center;
            border-radius: 10px;
        }

        .upload-confirmation .close {
            color: #aaa;
            float: right;
            font-size: 28px;
            font-weight: bold;
        }

        .upload-confirmation .close:hover,
        .upload-confirmation .close:focus {
            color: black;
            text-decoration: none;
            cursor: pointer;
        }

        /* CSS for the confirmation modal */
        .confirmation-modal {
            display: none; /* Hidden by default */
            position: fixed;
            z-index: 1;
            left: 0;
            top: 0;
            width: 100%;
            height: 100%;
            overflow: auto;
            background-color: rgba(0, 0, 0, 0.5); /* Black background with opacity */
        }

        .confirmation-modal-content {
            background-color: #fefefe;
            margin: 15% auto;
            padding: 20px;
            border: 1px solid #888;
            width: 80%;
            text-align: center;
            border-radius: 10px;
        }

        .confirmation-modal .close {
            color: #aaa;
            float: right;
            font-size: 28px;
            font-weight: bold;
        }

        .confirmation-modal .close:hover,
        .confirmation-modal .close:focus {
            color: black;
            text-decoration: none;
            cursor: pointer;
        }

        .confirmation-modal button {
            padding: 10px 20px;
            margin: 10px;
            border: none;
            background-color: #000;
            color: #fff;
            cursor: pointer;
            border-radius: 5px;
            font-size: 16px;
        }

        .confirmation-modal button:hover {
            background-color: #555;
        }
    </style>
</head>
<body>
    <header>
        <div class="logo">
            <a href="index.php"><img src="assets/images/Logo.png" alt="Siti Cookies"></a>
        </div>
        <nav>
            <ul>
                <li><a href="index.php">Home</a></li>
                <li><a href="Shop.php">Products</a></li>
                <li><a href="Checkout.php" style="color:#C80000">Cart</a></li>
                <?php if (isset($_SESSION['username'])): ?>
                    <li class="dropdown">
                        <a href="javascript:void(0)" class="login-signup"><?php echo htmlspecialchars($_SESSION['username']); ?></a>
                        <div class="dropdown-content">
                            <a href="Profile.php">Profile</a>
                            <a href="javascript:void(0)" onclick="confirmLogout()">Logout</a>
                        </div>
                    </li>
                <?php else: ?>
                    <li><a href="LoginSignup.php" class="login-signup">Login / SignUp</a></li>
                <?php endif; ?>
            </ul>
        </nav>
    </header>
    <main>
<div class="instructions">
    <h2>Online Banking Payment Instructions</h2>
    <p>Please follow the steps below to complete your payment:</p>
    <ol>
        <li>Log in to your online banking account through your bank's official website or mobile app.</li>
        <li>Navigate to the 'Transfer' or 'Payments' section. This may vary slightly depending on your bank.</li>
        <li>Initiate a new transfer and enter the payment details as follows:</li>
        <ul>
            <li><strong>Bank Name:</strong> Maybank</li>
            <li><strong>Account Number:</strong> 123456789</li>
            <li><strong>Account Holder Name:</strong> Siti Cookies Shop</li>
        </ul>
        <li>Double-check the entered details to ensure they are correct. Any mistakes may delay the processing of your order.</li>
        <li>Enter the total amount of your purchase. Make sure this matches the amount shown on your order confirmation.</li>
        <li>Review the details of your transfer and confirm the payment. Note down the transaction reference number for your records.</li>
        <li>Take a screenshot or save a copy of your transfer receipt. This will be needed for verification purposes.</li>
    </ol>
    <p>After completing the transfer, please upload your receipt using the form below. Make sure the receipt clearly shows the following details:</p>
    <ul>
        <li>The transaction reference number</li>
        <li>The date of the transaction</li>
        <li>The amount transferred</li>
        <li>The account holder's name (Siti Cookies Shop)</li>
    </ul>
    <p>Upload your receipt using the form below:</p>
        <!-- HTML for the confirmation modal -->
        <div id="confirmationModal" class="confirmation-modal">
            <div class="confirmation-modal-content">
                <span class="close" onclick="closeModal()">&times;</span>
                <h2>Are you sure you want to upload this receipt?</h2>
                <button onclick="submitForm()">Yes</button>
                <button onclick="closeModal()">No</button>
            </div>
        </div>
    </div>
        <div class="upload-form">
            <form id="uploadForm" action="OnlineBanking.php" method="post" enctype="multipart/form-data">
                <label for="receipt">Upload Receipt:</label>
                <input type="file" name="receipt" id="receipt" required>
                <button type="button" onclick="confirmUpload()">Upload</button>
            </form>
            <?php if ($uploadSuccess): ?>
                <script>
                    document.addEventListener('DOMContentLoaded', function() {
                        document.getElementById('uploadConfirmation').style.display = 'block';
                    });
                </script>
            <?php elseif ($uploadError): ?>
                <p style="color: red;"><?php echo htmlspecialchars($uploadError); ?></p>
            <?php endif; ?>
        </div>
        <div id="uploadConfirmation" class="upload-confirmation">
            <div class="upload-confirmation-content">
                <span class="close" onclick="closeUploadConfirmation()">&times;</span>
                <h2>Thank you for your upload!</h2>
                <p>We will check your receipt ASAP and your food will ship out within 3 working days.</p>
            </div>
        </div>
            <div id="logoutModal" class="modal">
                <div class="modal-content">
                    <span class="close" onclick="closeModal()">&times;</span>
                    <h2>Are you sure you want to logout?</h2>
                    <button onclick="logout()">Yes</button>
                    <button onclick="closeModal()">No</button>
                </div>
            </div>
    </main>
    <footer>
        <div class="footer-content">
            <img src="assets/images/Logo.png" alt="Siti Cookies">
            <span>Copyright @2024 Siti Cookies</span>
            <div class="social-icons">
                <a href="https://www.facebook.com/share/QxLx6VcdovGtKxer/?mibextid=LQQJ4d"><img src="assets/images/facebook.png" alt="Facebook"></a>
                <a href="https://www.instagram.com/sitizaleha9278?igsh=MXFiNWI2ZHFsOThyZw=="><img src="assets/images/instagram.png" alt="Instagram"></a>
                <br>
                <a href="FAQ.php"><span style="font-weight: 900; margin-left: 0px; font-size: 15px">FAQ</span></a>
            </div>
        </div>
    </footer>
    <script>
        // JavaScript to handle the confirmation modal
        function confirmUpload() {
            document.getElementById('confirmationModal').style.display = 'block';
        }

        function closeUploadModal() {
            document.getElementById('confirmationModal').style.display = 'none';
        }

        function submitForm() {
            document.getElementById('uploadForm').submit();
        }

        function closeUploadConfirmation() {
            document.getElementById('uploadConfirmation').style.display = 'none';
        }

        // JavaScript to handle the logout modal
        function confirmLogout() {
            document.getElementById("logoutModal").style.display = "block";
        }

        function closeLogoutModal() {
            document.getElementById("logoutModal").style.display = "none";
        }

        function logout() {
            window.location.href = "actions/logout.php"; // Redirect to logout
        }

        // Close the modal if the user clicks outside of it
        window.onclick = function(event) {
            let modal = document.getElementById("logoutModal");
            if (event.target == modal) {
                closeLogoutModal();
            }
        };
    </script>
</body>
</html>
