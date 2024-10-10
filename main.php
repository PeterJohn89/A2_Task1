<?php
session_start();
include("./config/DynamicDB.php");

use Aws\S3\S3Client;

$s3Client = new S3Client([
    'region'  => 'us-east-1', 
    'version' => 'latest'
]);

// Check if user is logged in
if (!isset($_SESSION['user_name'])) {
    header('Location: login.php');
    exit();
}

// Assign session variables
$username = $_SESSION['user_name'];
$email = $_SESSION['email'];
$get_subscription_titles = []; 
$subscribed_music = []; 
$search_results = []; 



// Logout
if (isset($_POST['logout'])) {
    session_destroy();
    header('Location: login.php');
    exit();
}

// Remove Subscription
if (isset($_POST['remove'])) {
    $title = $_POST['title'];
    removeFromSubscriptions($username, $title);
}

// Subscribe to Music
if (isset($_POST['subscribe'])) {
    $title = $_POST['title'];
    subscribeToMusic($username, $title);
}
//Get subscribed music
$get_subscription_titles = getSubscriptionTitles($username);
$subscribed_music = getSongsByTitle($get_subscription_titles);

// Fetch search results based on user query
if (isset($_POST['query'])) {
    $title = $_POST['title'] ?? '';
    $artist = $_POST['artist'] ?? '';
    $year = $_POST['year'] ?? '';

    $search_results = queryMusic($title, $artist, $year); 
}
?>

<?php include './includes/header.php'; ?>
<div class="container mx-auto p-6 m-20 flex-grow">
    <!-- User Area -->
    <div class="flex justify-between items-center bg-gray-100 p-4 rounded-lg mb-6 shadow-sm">
        <h1 class="text-2xl font-bold text-gray-800">Welcome, <?= htmlspecialchars($username); ?></h1>
        <form method="POST">
            <button type="submit" name="logout" class="bg-red-500 text-white px-4 py-2 rounded hover:bg-red-600">Logout</button>
        </form>
    </div>

    <!-- Subscription Area -->
    <h2 class="text-xl font-bold text-gray-800 mt-6 mb-6">Your Subscriptions</h2>
    <div class="grid grid-cols-1 md:grid-cols-4 gap-6">
        <?php if (!empty($subscribed_music)): ?>
            <?php foreach ($subscribed_music as $music): ?>
                <div class="bg-white p-4 rounded-lg shadow-md">
                    <h3 class="text-lg font-bold text-gray-900"><?= htmlspecialchars($music['title']['S']); ?></h3>
                    <p class="text-gray-700">Artist: <?= htmlspecialchars($music['artist']['S']); ?></p>
                    <p class="text-gray-700">Year: <?= htmlspecialchars($music['year']['S']); ?></p>
                    <img src="<?= $music['img_url']['S'] ?>" alt="Artist Image" class="w-full h-auto rounded-lg mt-2">
                    <form method="POST">
                        <input type="hidden" name="title" value="<?= htmlspecialchars($music['title']['S']); ?>">
                        <button type="submit" name="remove" class="bg-red-500 text-white px-4 py-2 mt-2 rounded hover:bg-red-600">Remove</button>
                    </form>
                </div>
            <?php endforeach; ?>
        <?php else: ?>
            <!-- Callout for no subscribed music -->
            <div class="col-span-1 md:col-span-4 bg-yellow-100 p-4 rounded-lg shadow-md text-yellow-800 text-center">
                <p>No subscribed music found. Start by searching and subscribing to some music!</p>
            </div>
        <?php endif; ?>
    </div>
    <!-- Search Results Area -->
    <?php if (isset($_POST['query'])): ?>
        <?php if (!empty($search_results)): ?>
            <h3 class="text-lg font-bold mt-6 text-gray-800">Search Results</h3>
            <div class="grid grid-cols-1 md:grid-cols-4 gap-6">
                <?php foreach ($search_results as $music): ?>
                    <div class="bg-white p-4 rounded-lg shadow-md">
                        <img src="<?= $music['img_url']['S'] ?>" alt="Artist Image" class="w-full h-auto rounded-lg">
                        <h3 class="text-lg font-bold text-gray-900"><?= htmlspecialchars($music['title']['S']); ?></h3>
                        <p class="text-gray-700">Artist: <?= htmlspecialchars($music['artist']['S']); ?></p>
                        <p class="text-gray-700">Year: <?= htmlspecialchars($music['year']['S']); ?></p>
                        <form method="POST">
                            <input type="hidden" name="title" value="<?= htmlspecialchars($music['title']['S']); ?>">
                            <button type="submit" name="subscribe" class="bg-green-500 text-white px-4 py-2 mt-2 rounded hover:bg-green-600">Subscribe</button>
                        </form>
                    </div>
                <?php endforeach; ?>
            </div>
        <?php else: ?>
            <!-- Callout for no search results -->
            <div class="bg-red-100 p-4 mt-6 rounded-lg shadow-md text-red-800 text-center">
                <p>No result is retrieved. Please query again.</p>
            </div>
        <?php endif; ?>
    <?php endif; ?>


    <!-- Query Area -->
    <h2 class="text-xl font-bold mt-6 text-gray-800">Search Music</h2>
    <form method="POST" class="bg-gray-100 p-4 rounded-lg shadow-sm mt-4">
        <div class="flex items-center mb-4">
            <label for="title" class="mr-2 w-24 text-gray-700">Title:</label>
            <input type="text" name="title" class="border p-2 flex-1 rounded-lg" id="title">
        </div>
        <div class="flex items-center mb-4">
            <label for="artist" class="mr-2 w-24 text-gray-700">Artist:</label>
            <input type="text" name="artist" class="border p-2 flex-1 rounded-lg" id="artist">
        </div>
        <div class="flex items-center mb-4">
            <label for="year" class="mr-2 w-24 text-gray-700">Year:</label>
            <input type="text" name="year" class="border p-2 flex-1 rounded-lg" id="year">
        </div>
        <button type="submit" name="query" class="bg-blue-500 text-white px-4 py-2 rounded hover:bg-blue-600">Query</button>
    </form>
</div>


<?php include './includes/footer.php'; ?>

