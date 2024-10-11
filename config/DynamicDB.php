<?php
include("S3Bucket.php");
require dirname(__DIR__) . '/vendor/autoload.php';


use Aws\DynamoDb\DynamoDbClient;
use Aws\DynamoDb\Exception\DynamoDbException;
use Aws\Exception\AwsException;

$dynamoDb = new DynamoDbClient([
    'region' => 'us-east-1', 
    'version' => 'latest'
]);


function getSubscriptionTitles($username) {
    global $dynamoDb;

    try {
        // Query DynamoDB to get subscriptions by username
        $result = $dynamoDb->query([
            'TableName' => 'subscribe',
            'KeyConditionExpression' => 'user_name = :user_name',
            'ExpressionAttributeValues' => [
                ':user_name' => ['S' => $username],
            ],
        ]);

        // Extract and return just the titles
        return array_map(function ($item) {
            return $item['title']['S'] ?? null;
        }, $result['Items']);
        
    } catch (AwsException $e) {
        error_log("Error retrieving subscriptions: " . $e->getMessage());
        return [];
    }
}

function getSongsByTitle(array $titles) {
    global $dynamoDb;

    $expressionAttributeValues = [];
    $filterExpression = [];

    foreach ($titles as $index => $title) {
        $key = ':title' . $index;
        $expressionAttributeValues[$key] = ['S' => $title];
        $filterExpression[] = "title = $key";
    }

    $params = [
        'TableName' => 'music',
        'FilterExpression' => implode(' OR ', $filterExpression),
        'ExpressionAttributeValues' => $expressionAttributeValues,
    ];

    try {
        $result = $dynamoDb->scan($params);
        return $result['Items'] ?? [];
    } catch (AwsException $e) {
        error_log("Error querying music: " . $e->getMessage());
        return [];
    }
}



function getSongsByTitle2(array $titles) {
    global $dynamoDb;

    
    $expressions = [];
    $expressionAttributeValues = [];
    $expressionAttributeNames = [];

    // Construct the filter expressions for each valid title
    foreach ($titles as $index => $title) {
        if (is_string($title) && !empty($title)) { // Check if title is a non-empty string
            $placeholder = ":title{$index}";
            $expressions[] = "#title{$index} = {$placeholder}"; // Build expression
            $expressionAttributeValues[$placeholder] = ['S' => $title]; // Set value
            $expressionAttributeNames["#title{$index}"] = 'title'; // Map attribute name
        } else {
            error_log("Invalid title provided: " . var_export($title, true));
        }
    }

    // If no valid titles are provided, return an empty array
    if (empty($expressions)) {
        return [];
    }

    // Join the expressions with ' OR '
    $filterExpression = implode(' OR ', $expressions);

    try {
        // Scan the 'music' table for matching titles
        $result = $dynamoDb->scan([
            'TableName' => 'music',
            'FilterExpression' => $filterExpression,
            'ExpressionAttributeValues' => $expressionAttributeValues,
            'ExpressionAttributeNames' => $expressionAttributeNames,
        ]);

        // Return the items found, or an empty array if none
        return $result['Items'] ?? [];
        
    } catch (AwsException $e) {
        error_log("Error querying music: " . $e->getMessage());
        return [];
    }
}




function removeFromSubscriptions($user_name, $title) {
    global $dynamoDb;

    try {
        $dynamoDb->deleteItem([
            'TableName' => 'subscribe',
            'Key' => [
                'user_name' => ['S' => $user_name],
                'title' => ['S' => $title],
            ],
        ]);
 
    } catch (AwsException $e) {
        error_log("Error removing subscription: " . $e->getMessage());
    }
}

// Function to query music based on user inputs
function queryMusic($title, $artist, $year) {
    global $dynamoDb;

    $expression = [];
    $expressionAttributeValues = [];
    $expressionAttributeNames = [];

    // Construct expressions based on what data is provided
    if (!empty($title)) {
        $expression[] = '#title = :title'; 
        $expressionAttributeValues[':title'] = ['S' => $title];
        $expressionAttributeNames['#title'] = 'title';
    }

    if (!empty($artist)) {
        $expression[] = '#artist = :artist';
        $expressionAttributeValues[':artist'] = ['S' => $artist];
        $expressionAttributeNames['#artist'] = 'artist';
    }

    # Year alias was mapped as its a reserved word
    if (!empty($year)) {
        $expression[] = '#yr = :year';
        $expressionAttributeValues[':year'] = ['S' => $year];
        $expressionAttributeNames['#yr'] = 'year';
    }

    // Join the expressions with 'AND'
    $filterExpression = implode(' AND ', $expression);

    try {
        $result = $dynamoDb->scan([
            'TableName' => 'music',
            'FilterExpression' => $filterExpression,
            'ExpressionAttributeValues' => $expressionAttributeValues,
            'ExpressionAttributeNames' => $expressionAttributeNames,
        ]);

        if (count($result['Items']) > 0) {
            return $result['Items'];
        } else {
            return [];
        }
    } catch (AwsException $e) {
        error_log("Error querying music: " . $e->getMessage());
        return [];
    }
}



// Function to subscribe to music and store in DynamoDB
function subscribeToMusic($user_name, $title) {
    global $dynamoDb;

    try {
        $dynamoDb->putItem([
            'TableName' => 'subscribe',
            'Item' => [
                'user_name' => ['S' => $user_name],
                'title' => ['S' => $title],
            ],
        ]);
        
    } catch (AwsException $e) {
        error_log("Error subscribing music: " . $e->getMessage());
    }
}

// Logout function
if (isset($_POST['logout'])) {
    session_destroy();
    header("Location: login.php");
    exit;
}

?>
