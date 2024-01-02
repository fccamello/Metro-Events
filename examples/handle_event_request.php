<?php
include("api.php");

session_start();

$requestsJSON = '../data/eventRequests.json';
$usersJSON = '../data/users.json';
$postsJSON = '../data/posts.json';

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

                    // Load existing users data
                    $usersData = [];
                    if (file_exists($usersJSON)) {
                        $usersData = json_decode(file_get_contents($usersJSON), true);
                    }

                    foreach ($usersData as $userKey => $user) {
                        if ($user['id'] == $userId) {
                            $eventId = $request['event_id'];

                            // Ensure 'joined_events' array exists for the user
                            if (!isset($user['joined_events'])) {
                                $user['joined_events'] = [];
                            }

                            // Add the event to the user's joined events
                            $user['joined_events'][] = $eventId;
                            $usersData[$userKey] = $user; // Update the user data in the array

                            break; // Stop looping once the user is found and updated
                        }
                    }

                    // Save the updated users data back to users.json
                    file_put_contents($usersJSON, json_encode($usersData, JSON_PRETTY_PRINT));

                    // Update the posts.json with participants count
                    $postsData = [];
                    if (file_exists($postsJSON)) {
                        $postsData = json_decode(file_get_contents($postsJSON), true);
                    }

                    foreach ($postsData as &$post) {
                        if ($post['id'] == $eventId) {
                            // Ensure 'participants' field exists for the event
                            if (!isset($post['participants'])) {
                                $post['participants'] = [];
                            }

                            // Add the user to the event's participants list
                            $post['participants'][] = $userId;
                            $post['participants'] = array_unique($post['participants']); // Remove duplicates
                            $loggedInUserId = isset($_SESSION['user']['id']) ? $_SESSION['user']['id'] : null;
                            $eventName = $post['title'];
                            sendNotification($userId,  "Your application to join the event '$eventName' was accepted!");


                            break; // Stop looping once the event is found and updated
                        }
                    }

                    file_put_contents($postsJSON, json_encode($postsData, JSON_PRETTY_PRINT));

                } elseif ($action === 'rejected') {
                    $requestsData[$key]['status'] = 'rejected';
                    $userId = $request['user_id'];

                    // Load existing users data
                    $usersData = [];
                    if (file_exists($usersJSON)) {
                        $usersData = json_decode(file_get_contents($usersJSON), true);
                    }

                    foreach ($usersData as $userKey => $user) {
                        if ($user['id'] == $userId) {
                            // Load existing posts data
                            $postsData = [];
                            if (file_exists($postsJSON)) {
                                $postsData = json_decode(file_get_contents($postsJSON), true);
                            }

                            foreach ($postsData as $post) {
                                if ($post['id'] == $request['event_id']) {
                                    $eventName = $post['title'];
                                    sendNotification($userId, "Your application for the event '$eventName' was rejected.");
                                    break; // Stop looping once the user is found and notification is sent
                                }
                            }
                            break; // Stop looping once the user is found
                        }
                    }
                }
                $found = true;
                break;
            }
        }

        if ($found) {
            // Save updated requests data
            file_put_contents($requestsJSON, json_encode($requestsData, JSON_PRETTY_PRINT));

            // Redirect to the organizer page or any other appropriate page after processing
            header("Location: organizer.php");
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
