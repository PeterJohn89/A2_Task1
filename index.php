<?php

session_start(); // Start the session
error_reporting(E_ALL); // Report all errors
ini_set('display_errors', 1); // Display errors on the screen


include("./config/DynamicDB.php");

$error_message = "";  // Initialize an empty error message

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $email = $_POST['email'];
    $password = $_POST['password'];

    // Fetch user details from DynamoDB
    try {
        $result = $dynamoDb->getItem([
            'TableName' => 'login',
            'Key' => [
                'email' => ['S' => $email],
            ],
        ]);

        // Check if the user exists and validate the password
        if (isset($result['Item']) && isset($result['Item']['password']['S']) && $result['Item']['password']['S'] === $password) {
            // Store user info in the session
            $_SESSION['user_name'] = $result['Item']['user_name']['S'];
            $_SESSION['email'] = $email;

            // Redirect to the main page after successful login
            header("Location: main");
            exit();
        } else {
            // Set error message for invalid credentials
            $error_message = "Email or password is invalid";
        }
    } catch (DynamoDbException $e) {
        // Set error message for a DynamoDB-related error
        $error_message = "Unable to fetch user details. Please try again later.";
    }
}
?>

<?php include './includes/header.php'; ?>

<div class="container mx-auto p-6 m-20 max-w-lg flex-grow">
    <div class="bg-white p-6 rounded-lg shadow-md">
        <h1 class="text-2xl font-bold mb-4">Login</h1>
        
        <?php if (isset($_GET['message'])): ?>
            <div class="mb-4 p-4 bg-green-100 text-green-700 border border-green-400 rounded">
                <p><?php echo htmlspecialchars($_GET['message']); ?></p>
            </div>
        <?php endif; ?>

        <?php if ($error_message): ?>
        <div class="mb-4 p-4 bg-red-100 text-red-700 border border-red-400 rounded">
            <?php echo $error_message; ?>
        </div>
        <?php endif; ?>

        <form action="login.php" method="POST">
            <div class="mb-4">
                <label for="email" class="block text-gray-700">Email</label>
                <input type="email" name="email" class="w-full p-2 border border-gray-300 rounded-lg" required>
            </div>
            <div class="mb-4">
                <label for="password" class="block text-gray-700">Password</label>
                <input type="password" name="password" class="w-full p-2 border border-gray-300 rounded-lg" required>
            </div>
            <button type="submit" class="bg-blue-500 text-white px-4 py-2 rounded hover:bg-blue-700">Login</button>
        </form>
        <div class="mt-4">
            <p class="text-gray-600">Don't have an account? <a href="register.php" class="text-blue-500 hover:text-blue-700">Register here</a></p>
        </div>
    </div>
</div>

<?php include './includes/footer.php'; ?>
