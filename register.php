<?php
session_start();
include("./config/DynamicDB.php");

// Check if user is already logged in (optional)
if (isset($_SESSION['user_name'])) {
    header('Location: dashboard.php');
    exit();
}

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $email = $_POST['email'];
    $user_name = $_POST['user_name'];
    $password = $_POST['password'];

    // Check if email already exists in the login table
    $response = $dynamoDb->getItem([
        'TableName' => 'login',
        'Key' => [
            'email' => ['S' => $email]
        ]
    ]);

    if (isset($response['Item'])) {
        // Email exists, show error message
        $error_message = "The email already exists.";
    } else {
        // Email is unique, store new user information in the login table
        $dynamoDb->putItem([
            'TableName' => 'login',
            'Item' => [
                'email' => ['S' => $email],
                'user_name' => ['S' => $user_name],
                'password' => ['S' => $password],
            ],
        ]);

        // Redirect to login page after successful registration
        header("Location: login?message=You have successful registration. Please log in.");
        exit();
    }
}
?>

<?php include './includes/header.php'; ?>
<div class="container mx-auto p-6 m-20 max-w-lg flex-grow">

  <div class="bg-white p-6 rounded-lg shadow-md">
  <h1 class="text-2xl font-bold mb-4">Register</h1>
    <?php if (isset($error_message)) : ?>
      <div class="mb-4 p-4 bg-red-100 text-red-700 border border-red-400 rounded">
        <?php echo $error_message; ?>
      </div>
    <?php endif; ?>
    

    <form action="register.php" method="POST" >
      <div class="mb-4">
        <label for="email" class="block text-gray-700">Email</label>
        <input type="email" name="email" class="w-full p-2 border border-gray-300 rounded-lg" required>
      </div>
      <div class="mb-4">
        <label for="user_name" class="block text-gray-700">Username</label>
        <input type="text" name="user_name" class="w-full p-2 border border-gray-300 rounded-lg" required>
      </div>
      <div class="mb-4">
        <label for="password" class="block text-gray-700">Password</label>
        <input type="password" name="password" class="w-full p-2 border border-gray-300 rounded-lg" required>
      </div>
      <button type="submit" class="bg-blue-500 text-white px-4 py-2 rounded hover:bg-blue-700">Register</button>
    </form>
    <div class="mt-4">
        <p class="text-gray-600">Already has an account? <a href="login.php" class="text-blue-500 hover:text-blue-700">login here</a></p>
    </div>
  </div>
</div>
<?php include './includes/footer.php'; ?>
