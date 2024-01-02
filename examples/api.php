
<?php

ini_set("display_errors", "1");
ini_set("display_startup_errors", "1");
error_reporting(E_ALL);

?>

<?php
$userData = NULL;
$usersJSON = '../data/users.json';

// posts JSON
$postsJSON = '../data/posts.json';

// comments JSON
$commentsJSON = '../data/comments.json';

$requestsJSON = '../data/organizerRequests.json';
$requestsEventJSON = '../data/eventRequests.json';
//$notificationJSON = "../data/notifications.json";




if (isset($_SESSION["user"])){
    $userData = json_decode($_SESSION["user"],true);
}

function getUsersData()
{
    global $usersJSON;
    if (!file_exists($usersJSON)) {
        echo 1;
        return [];
    }

    $data = file_get_contents($usersJSON);
    return json_decode($data, true);
}

function getNotifsdata()
{
    global $notificationJSON;
    if (!file_exists($notificationJSON)) {
        echo 1;
        return [];
    }

    $data = file_get_contents($notificationJSON);
    return json_decode($data, true);
}

function getRequestsData()
{
    global $requestsJSON;
    if (!file_exists($requestsJSON)) {
        echo 1;
        return [];
    }

    $data2 = file_get_contents($requestsJSON);
    return json_decode($data2, true);
}

function getRequestsDataEvent()
{
    global $requestsEventJSON;
    if (!file_exists($requestsEventJSON)) {
        echo 1;
        return [];
    }

    $data2 = file_get_contents($requestsEventJSON);
    return json_decode($data2, true);
}

function counterId($jsonData)
{
    $initial = 0;
    foreach ($jsonData as $obj)
    {
        if ($initial < $obj["id"])
        {
            $initial = $obj["id"];
        }
    }
    return $initial + 1;
}

function counterIdReq($jsonData)
{
    $initial = 0;
    foreach ($jsonData as $obj)
    {
        if ($initial < $obj["user_id"])
        {
            $initial = $obj["user_id"];
        }
    }
    return $initial + 1;
}

function counterIdReqEvent($jsonData)
{
    $initial = 0;
    foreach ($jsonData as $obj)
    {
        if ($initial < $obj["request_id"])
        {
            $initial = $obj["request_id"];
        }
    }
    return $initial + 1;
}


function addComment($newComment){
    global $commentsJSON;
    $jsonData = getCommentsData();

    $jsonData[] = $newComment;
    file_put_contents($commentsJSON, json_encode($jsonData, JSON_PRETTY_PRINT));
}


function logIn($username)
{
    $jsonData = getUsersData();
    foreach ($jsonData as $user) {

        if ($username == "admin")
        {
            echo '<script>alert("Interacting as admin..");</script>';
            return $user;
        }

        else if ($user["username"] == $username && $user["username"] != "admin" )
        {
            return $user;
        }

    }
}

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    if (isset($_POST["signup_submit"])) {
        $currentUser = null;
        $username = ($_POST["uname"]);
        $jsonData = getUsersData();
        foreach ($jsonData as $user) {

            if ($user["username"] == 'admin' && $username == 'admin') {
                echo '<script>alert("Cannot sign-up as admin. Admin already exists");</script>';
                $currentUser = 1;

            }

            if ($user["username"] == $username && $user["username"] != "admin") {
                $currentUser = 1;
                echo '<script>alert("Username already taken! Please Sign-Up again.");</script>';
            }

        }

        if (!$currentUser) {
            $userType = 'user';

            // If the username is 'admin', set user type as 'admin'
            if ($username == 'admin') {
                $userType = 'admin';
            }
            $userData = [
                'id' => counterId(getUsersData()),
                'name' => ($_POST["name"]),
                'username' => ($_POST["uname"]),
                'email' => ($_POST["email"]),
                'company' => ($_POST["company"]),
                'type' => $userType

            ];
            echo '<script>alert("Sign-Up Success");</script>';

            global $usersJSON;
            $jsonData[] = $userData;
            file_put_contents($usersJSON, json_encode($jsonData, JSON_PRETTY_PRINT));

            $_SESSION["user"] = json_encode($userData, true);

            if ($userType == 'admin') {
                header("Location: admin.php");
            }
        }

    } elseif (isset($_POST["login_submit"])) {
        $userData = logIn(($_POST["uname2"]));

        if (!$userData) {
            echo '<script>alert("Username not found! Please Sign-Up.");</script>';
        }
        if ($userData) {
            $_SESSION["user"] = json_encode($userData, true);


            if ($userData['type'] == 'admin') {
                header("Location: admin.php");
            } elseif ($userData['type'] == 'organizer') {
                header("Location: organizer.php");
                echo '<script>alert("Your request to be an organizer has been accepted!");</script>';

            }
        }
    } elseif (isset($_POST["create_post"])) {
        $posts = getPostsData();
        $postIdUser = counterId($posts);
//        var_dump($_SESSION);

        $userData = json_decode($_SESSION["user"], true);

        if (!$userData && $userData["username"] != "admin") {
            echo '<script>alert("Invalid! Please Log-In as Admin First");</script>';
        }

        if ($userData["type"] === "organizer") {
            {
                $posts[] = array(
                    'uid' => $userData["id"],
                    'id' => $postIdUser,
                    'title' => $_POST['title'],
                    'body' => $_POST['body'],
                    'location' => $_POST['location'],
                    'date' => $_POST['date'],
                    'participants' => []
                );
                $posts[count($posts) - 1]['participants'] = [];

                file_put_contents($postsJSON, json_encode($posts, JSON_PRETTY_PRINT));
                sendNotificationToAll("A new post has been created: " . $_POST['title']);

            }
        }

        header("Location: organizer.php");

    } elseif (isset($_POST["create_comment"])) {
        $comment = htmlspecialchars($_POST["comment"]);
        $userData = json_decode($_SESSION["user"], true);
        addComment([
            "postId" => intval($_POST["post-id"]),
            "id" => counterId(getCommentsData()),
            "name" => $userData["name"],
            "email" => $userData["email"],
            "body" => $comment
        ]);
        header("Location: index.php");
    } elseif (isset($_POST["delete_comment"])) {
        global $commentsJSON;
        $jsonData = getCommentsData();
        foreach ($jsonData as $index => $comment) {
            if ($comment["id"] === intval($_POST["comment-id"])) {
                unset($jsonData[$index]);
                break;
            }
        }
        file_put_contents($commentsJSON, json_encode($jsonData, JSON_PRETTY_PRINT));
        header("Location: index.php");
    }  elseif (isset($_POST["delete_post"])) {
    $deletedPostId = intval($_POST["post-id"]);
    $deleteReason = $_POST["delete_reason"];
    $deletedPostTitle = '';

    $jsonData = getPostsData();

    foreach ($jsonData as $index => $post) {
        if ($post["id"] === $deletedPostId) {
            $deletedPostTitle = $post['title'];
            $deletedParticipantIds = isset($post['participants']) ? $post['participants'] : [];

            // Remove the post
            unset($jsonData[$index]);
            break;
        }
    }

    file_put_contents($postsJSON, json_encode($jsonData, JSON_PRETTY_PRINT));

    foreach ($deletedParticipantIds as $participantId) {
        sendNotification($participantId, "The event you joined, " . $deletedPostTitle . ", has been deleted. Reason: " . $deleteReason);
    }

    $jsonData = getCommentsData();
    foreach ($jsonData as $index => $comment) {
        if ($comment["id"] == intval($_POST["comment-id"])) {
            unset($jsonData[$index]);
            break;
        }
    }
    file_put_contents($commentsJSON, json_encode($jsonData, JSON_PRETTY_PRINT));

    header("Location: organizer.php");
}

    elseif (isset($_POST["log_Out"])) {
        unset($_SESSION["user"]);
        session_destroy();
        header("Location: index.php");
    } elseif (isset($_POST["request_Organizer"])) {

        $userData = json_decode($_SESSION["user"], true);

        $requestsData = [];

        if (file_exists($requestsJSON)) {
            $requestsData = json_decode(file_get_contents($requestsJSON), true);
        }

        $requestsData[] = [
            'user_id' => $userData['id'],
            'request_id' => counterIdReq(getRequestsData()),
            'username' => $userData['username'],
            'name' => $userData['name'],
            'email' => $userData['email'],
            'request_time' => date("Y-M-d H:i:s"),
            'status' => 'pending'
        ];

        file_put_contents($requestsJSON, json_encode($requestsData, JSON_PRETTY_PRINT));

        echo '<script>alert("Request as an organizer sent!");</script>';
    }

    elseif (isset($_POST["join_event"]) && isset($_SESSION["user"])) {

        $userData = json_decode($_SESSION["user"], true);
        $userId = $userData["id"];

        $eventId = $_POST["post-id"];
        $usersData = getUsersData();

        $eventRequests = file_exists($requestsEventJSON) ? json_decode(file_get_contents($requestsEventJSON), true) : [];

        $existingRequest = array_filter($eventRequests, function ($request) use ($userId, $eventId) {
            return $request['user_id'] == $userId && $request['event_id'] == $eventId;
        });

        if (!empty($existingRequest)) {
            echo '<script>alert("You have already sent a request for this event.");</script>';
        } else {
            $posts = getPostsData();
            $event = array_values(array_filter($posts, function ($post) use ($eventId) {
                return $post['id'] == $eventId;
            }))[0];

            if ($event) {
                $newRequest = [
                    'user_id' => $userId,
                    'request_id' => counterIdReqEvent(getRequestsDataEvent()),
                    'username' => $userData['username'],
                    'name' => $userData['name'],
                    'event_id' => $eventId,
                    'event_title' => $event['title'],
                    'request_time' => (new DateTime())->format('M-d-Y H:i:s'),
                    'status' => 'pending'
                ];

                $eventRequests[] = $newRequest;

                file_put_contents($requestsEventJSON, json_encode($eventRequests, JSON_PRETTY_PRINT));

                echo '<script>alert("Request to join event ' . $event['title'] . ' sent successfully!");</script>';


            }
        }

    }



}


function sendNotificationToAll($message){
    $notificationJSON = '../data/notifications.json';
    $usersJSON = '../data/users.json';

    $notifications = json_decode(file_get_contents($notificationJSON), true);
    $registeredUsers = json_decode(file_get_contents($usersJSON), true);

    foreach ($registeredUsers as $user) {
        $userID = $user['id'];

        $newNotif = [
            'id' => counterId(getNotifsdata()),
            'userId' => $userID,
            'message' => $message
        ];

        $notifications[] = $newNotif;
    }

    file_put_contents($notificationJSON, json_encode($notifications, JSON_PRETTY_PRINT));
}

function getPendingOrganizerRequests() {
    global $requestsJSON;
    if (!file_exists($requestsJSON)) {
        return [];
    }

    $requestsData = json_decode(file_get_contents($requestsJSON), true);
    $pendingRequests = array_filter($requestsData, function ($request)
    {
        return $request['status'] == 'pending';
    });

    return $pendingRequests;
}


function displayOrganizerRequests() {
    $pendingRequests = getPendingOrganizerRequests();

    if (empty($pendingRequests)) {
        return '<br> <h2> No pending organizer requests.</h2>';
    }

    $userData = json_decode($_SESSION["user"], true);

    $requestsHTML = '<h2>Pending Organizer Requests</h2>  <br> ';
    $requestsHTML .= '<ul style="list-style-type:none;">';

    foreach ($pendingRequests as $request) {
//        var_dump($request);
        $requestsHTML .= '<div class = "post-content" style="padding: 15px">';
            $requestsHTML .= '<li>';
            $requestsHTML .= '<strong>Username:</strong> ' . $request['username'] . '<br>';
            $requestsHTML .= '<strong>Name:</strong> ' . $request['name'] . '<br>';
            $requestsHTML .= '<strong>Email:</strong> ' . $request['email'] . '<br>';
            $requestsHTML .= '<strong>Request Time:</strong> ' . $request['request_time'] . '<br>';
            $requestsHTML .= '<strong>Status:</strong> ' . $request['status'] . '<br> <br>';

            $requestsHTML .= '<form action="../examples/handle_requests.php" method="post">';
            $requestsHTML .= '<input type="hidden" name="request_id" value="' . $request['request_id'] . '">';

            $requestsHTML .= '<button type="submit" class ="btn btn-success" name="action" value="accepted">Accept</button>     ';

            $requestsHTML .= '<button type="submit"  class ="btn btn-danger" name="action" value="rejected">Reject</button>';
            $requestsHTML .= '</form>';

            $requestsHTML .= '</li>';
        $requestsHTML .= '</div>';

    }

    $requestsHTML .= '</ul>';
    return $requestsHTML;
}

function getPendingEventRequests() {
    global $requestsEventJSON;
    if (!file_exists($requestsEventJSON)) {
        return [];
    }

    $requestsData2 = json_decode(file_get_contents($requestsEventJSON), true);
    $pendingRequests2 = array_filter($requestsData2, function ($request) {
        return $request['status'] == 'pending';
    });

    return $pendingRequests2;
}

function displayEventRequests() {
    $pendingEventRequests = getPendingEventRequests();

    if (empty($pendingEventRequests)) {
        return '<br> <br> <h2> No pending join event requests.</h2>';
    }

    $userData = json_decode($_SESSION["user"], true);

    $requestsHTML = '<h2>Pending Event Requests 📩 </h2> <br>  ';
    $requestsHTML .= '<div class="post-content"> <ul style="list-style-type: none">';

    foreach ($pendingEventRequests as $request) {
        $requestsHTML .= '<li style="font-size:20px">';

        $requestsHTML .= '<br><strong>Name:</strong> ' . $request['name'] . '<br>';
        $requestsHTML .= '<strong>Wants to Join Event:</strong> ' . $request['event_title'] . '<br>';
        $requestsHTML .= '<strong>Event ID:</strong> ' . $request['event_id'] . '<br>';
        $requestsHTML .= '<strong>Request Time:</strong> ' . $request['request_time'] . '<br>';
        $requestsHTML .= '<strong>Status:</strong> ' . $request['status'] . '<br>';

        $requestsHTML .= '<form action="../examples/handle_event_request.php" method="post">';
        $requestsHTML .= '<input type="hidden" name="user_id" value="' . $request['user_id'] . '">';
        $requestsHTML .= '<input type="hidden" name="event_id" value="' . $request['event_id'] . '">';
        $requestsHTML .= '<input type="hidden" name="request_id" value="' . $request['request_id'] . '">';


        $requestsHTML .= '<br> <button type="submit" class="btn btn-success" name="action" value="accepted">Accept</button>      ';

        $requestsHTML .= '<button type="submit" class = "btn btn-danger" name="action" value="rejected">Reject</button>';
        $requestsHTML .= '</form>';

        $requestsHTML .= '</li>';
    }

    $requestsHTML .= '</ul> </div>';
    return $requestsHTML;
}


// function get posts from json
function getPostsData() {
    global $postsJSON;
    if (!file_exists($postsJSON)) {
        echo 1;
        return [];
    }

    $data = file_get_contents($postsJSON);
    return json_decode($data, true);
}

// function get comments from json
function getCommentsData() {
    global $commentsJSON;
    if (!file_exists($commentsJSON)) {
        echo 1;
        return [];
    }

    $data = file_get_contents($commentsJSON);
    return json_decode($data, true);
}

function displayJoinedEvents()
{
    $userData = json_decode($_SESSION["user"], true);
    $userId = $userData["id"];

    $usersData = getUsersData();
    $postsData = getPostsData();

    $joinedEvents = [];
    foreach ($usersData as $user) {
        if ($user['id'] == $userId && isset($user['joined_events'])) {
            $userJoinedEvents = $user['joined_events'];
            foreach ($userJoinedEvents as $eventId) {
                foreach ($postsData as $post) {

                    if ($post['id'] == $eventId) {
                        $joinedEvents[] = $post;
                        break;
                    }
                }
            }
            break;
        }
    }
    if (empty($joinedEvents)) {
        $html = '<h2>No events joined yet.</h2>';
    } else {
        $html = '<h2>Events Joined by ' . $userData['name'].  " 🎮 " . '</h2><br><br>';

        foreach ($joinedEvents as $event) {
            $html .= '<div class="post-content" style="padding: 10px">';
            $html .= '<h3>' . $event['title'] . '</h3>';
            $html .= '<p>' . $event['body'] . '</p>';
            $html .= '<p>' . $event['location'] . '</p>';
            $html .= '<p>' . $event['date'] . '</p>';
            $html .= '</div>';
            $html .= '<div class="line-divider"></div>';
        }
    }


    return $html;
        }




function sendNotification($userID, $message) {
    $notificationJSON = '../data/notifications.json';
    $usersJSON = '../data/users.json';

    $notifications = json_decode(file_get_contents($notificationJSON), true);
    $registeredUsers = json_decode(file_get_contents($usersJSON), true);

    function counterId22($data) {
        return count($data) + 1;
    }

    $newNotifID = counterId22($notifications);

    $newNotif = [
        'id' => $newNotifID,
        'userId' => $userID,
        'message' => $message
    ];

    $notifications[] = $newNotif;

    file_put_contents($notificationJSON, json_encode($notifications, JSON_PRETTY_PRINT));
}

function displayNotificationsForCurrentUser() {
    $notificationJSON = '../data/notifications.json';
    $usersJSON = '../data/users.json';


        $userData = json_decode($_SESSION["user"], true);


    if (!isset($userData)) {
        return 'No user logged in.';
    }

    $notifications = json_decode(file_get_contents($notificationJSON), true);
    $usersData = json_decode(file_get_contents($usersJSON), true);

    $notificationHTML = '';

    foreach ($usersData as $user) {
        if ($user['id'] === $userData['id']) {
            $notificationHTML .= '<h2>' . $user['name'] . '\'s Notifications 🔔</h2>';

            $userNotifications = array_filter($notifications, function ($notification) use ($user) {
                return $notification['userId'] === $user['id'];
            });

            if (!empty($userNotifications)) {
                $notificationHTML .= '<ul>';

                foreach ($userNotifications as $notification) {
                    $notificationHTML .= '<li>';
                    $notificationHTML .= '<strong>Message:</strong> ' . '<h5>'.$notification['message'].'</h5>' . '<br>';
                    $notificationHTML .= '</li>';
                }

                $notificationHTML .= '</ul>';
            } else {
                $notificationHTML .= '<h5> <i> No notifications for ' . $user['name'] . '. </i> </h5>';
            }

            $notificationHTML .= '<hr>';
        }
    }

    return $notificationHTML;
}


function getPosts()
{

    global $userData;
    global $postsJSON;

    $users = getUsersData();
    $posts = getPostsData();
    $comments = getCommentsData();
    $postsarr = array();

    foreach ($posts as $post) {
        foreach ($users as $user) {
            if ($user['id'] == $post['uid']) {
                $post['uid'] = $user;
                break;
            }
        }
        $post['comments'] = array();
        foreach ($comments as $comment) {
            if ($post['id'] == $comment['postId']) {
                $post['comments'][] = $comment;
            }
        }
        $postsarr[] = $post;
    }


    $str = '<div class = "postLists" style="display: flex; flex-wrap: wrap; gap: 10px;">';


    foreach ($postsarr as $parr) {

        $eventId = $parr['id'];
        $postsData = [];
        if (file_exists($postsJSON)) {
            $postsData = json_decode(file_get_contents($postsJSON), true);
        }

        $participantCount = 0;
        foreach ($postsData as $post) {
            if ($post['id'] == $eventId && isset($post['participants'])) {
                $participantCount = count($post['participants']);
                break;
            }
        }

        $currentUserJoined = false;
        if ($userData && isset($userData['id']) && isset($parr['participants'])) {
            $currentUserJoined = in_array($userData['id'], $parr['participants']);
        }



        $str .= '<!-- start of post -->
   
    <div class="row" style="width: 50%; ">
        <div class="col-md-12" >
            <div class="post-content" >
              <div class="post-container" style="height: 95%; margin-top: 5px">
                <img src="https://ui-avatars.com/api/?rounded=true&name=' . $parr['uid']['name'] . '" alt="user" class="profile-photo-md pull-left">
                <div class="post-detail">
                  <div class="user-info">
                  <div class="reaction">
                     <a class="btn text-gray"><i class="fa fa-users"></i> ' . $participantCount . '</a>
                     <!-- Replace the static number 13 with a span that holds the count -->
<a class="btn text-green" id="thumbs-up-btn"><i class="fa fa-thumbs-up"></i> <span id="thumbs-up-count"></span></a>

<a class="btn text-red" id="thumbs-down-btn"><i class="fa fa-thumbs-down"></i> <span id="thumbs-down-count"></span></a>
                  </div>
                  
                  
                    <h4><a class="profile-link" style="color: #faaf40; text-decoration: none">' . $parr['uid']['name'] . '</a></h4>'
            . '
         </div>
                  <div class="line-divider"></div>
                  <div class="post-text">
                   <h3>' . $parr['title'] . '</h3>
                     <p><i>' . $parr['body'] . '</i></p>
                     📍: <p>' . $parr['location'] . '</p>
                     Date: <p>' . $parr['date'] . '</p>
                  </div>
                  <div class="line-divider"></div>';


        foreach ($parr['comments'] as $comm)
            $str .= '<div class="post-comment">
                    <img src="https://ui-avatars.com/api/?rounded=true&name=' . $comm['name'] . '" alt="" class="profile-photo-sm">
                    <p>' . $comm['body'] . '</p>
                    
                     ' .
                (($userData && $userData["email"] == $comm["email"]) ?
                    '<form class="ms-auto" action="index.php" method="post">
                            <input type="hidden" name="comment-id" value="' . $comm["id"] . '">
                            <button type="submit" class="btn btn-danger" name="delete_comment">Delete Review</button>
                        </form>'
                    : "")
                . '
               
        </div>';

        if ($userData && $userData["username"] != "admin"  && $userData["type"] != "organizer") {
            $userID = $userData['id'];

            if (!(in_array($userID, $parr['participants']))) {
                $str .= '<form action="index.php" method="post">
                        <div class="mb-3">
                            <input type="hidden" name="post-id" value="' . $parr["id"] . '">
                        </div>
                        <button type="submit" class="btn btn-warning" name="join_event">Join Event</button>
                        
                    </form>';
            }
        }

        if($userData) {
            $userID = $userData['id'];
            if (in_array($userID, $parr['participants'])) {
                $str .= '<form action="index.php" method="POST" >
                <input type="hidden" name="post-id" value="' . $parr["id"] . '">
                <div class="mb-3">
                    <label for="comment" class="form-label">Write a review: </label>
                    <textarea class="form-control" id="commment" name="comment" rows="1"></textarea>
                    <input type="hidden" name="post-id" value="' . $parr["id"] . '">
                </div>
                <button type="submit" class="btn btn-warning" name="create_comment">Write Review</button>
            </form>';
            }


            if ($userData && $userData["id"] === $parr["uid"]["id"]) {
                $str .= '<form action="index.php" method="POST" >
                <input type="hidden" name="post-id" value="' . $parr["id"] . '">
               
                <button type="submit" class="btn btn-danger" name="delete_post" style="width: 120px">Delete Event</button>
                <br>
                  <label for="delete_reason">Reason for deleting:</label><br>
                     <textarea id="delete_reason" name="delete_reason" rows="4" cols="50"></textarea><br>

            </form>';
            }
        }


        $str.='</div>
              </div>
        </div>
        </div>
    </div>';



    }
    $str .= "</div>";

    return $str;
}
?>







