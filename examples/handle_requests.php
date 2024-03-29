<?php
include("api.php");

session_start();

$requestsJSON = '../data/organizerRequests.json';
$usersJSON = '../data/users.json';
$notificationJSON = '../data/notifications.json';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['request_id']) && isset($_POST['action'])) {
        $requestId = $_POST['request_id'];
        $action = $_POST['action'];

        // Load existing requests data
        $requestsData = [];
        if (file_exists($requestsJSON)) {
            $requestsData = json_decode(file_get_contents($requestsJSON), true);
        }

        $found = false;
        foreach ($requestsData as $key => $request) {
            if ($request['request_id'] == $requestId) {
                if ($action === 'accepted') {
                    $requestsData[$key]['status'] = 'accepted';
                    $userId = $request['user_id'];

                    $usersData = [];
                    if (file_exists($usersJSON)) {
                        $usersData = json_decode(file_get_contents($usersJSON), true);
                    }

                    foreach ($usersData as $userKey => $user) {
                        if ($user['id'] == $userId) {
                            $usersData[$userKey]['type'] = 'organizer';
                            sendNotification($userId,  "Your application to be an Organizer was accepted!");
                            break;
                        }
                    }

                    file_put_contents($usersJSON, json_encode($usersData, JSON_PRETTY_PRINT));

                } elseif ($action === 'rejected') {
                    $requestsData[$key]['status'] = 'rejected';
                    $userId = $request['user_id'];

                    $usersData = [];
                    if (file_exists($usersJSON)) {
                        $usersData = json_decode(file_get_contents($usersJSON), true);
                    }

                    foreach ($usersData as $userKey => $user) {
                        if ($user['id'] == $userId) {
                            sendNotification($userId,  "Your application as Organizer was rejected.");
                            break;
                        }
                    }
                }

                $found = true;
                break;
            }
        }

        if ($found) {
            file_put_contents($requestsJSON, json_encode($requestsData, JSON_PRETTY_PRINT));

            header("Location: admin.php");
            exit;
        } else {
            echo "Request not found!";
        }
    } else {
        echo "Invalid request!";
    }
} else {
    echo "Method not allowed!";
}

?>
