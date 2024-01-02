<html>
<head>


    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css"
          integrity="sha384-T3c6CoIi6uLrA9TneNEoa7RxnatzjcDSCmG1MXxSR1GAsXEV/Dwwykc2MPK8M2HN" crossorigin="anonymous">

    <link href="https://maxcdn.bootstrapcdn.com/font-awesome/4.3.0/css/font-awesome.min.css" rel="stylesheet">
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.min.js" integrity="sha384-BBtl+eGJRgqQAUMxJ7pMwbEyER4l1g+O15P+16Ep7Q9Q+zqX6gSbd85u4mG4QzX+" crossorigin="anonymous"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js" integrity="sha384-C6RzsynM9kWDrMNeT87bh95OGNyZPhcTNXj1NW7RuBCsyN/o0jlpcV8Qyq46cDfL" crossorigin="anonymous"></script>
    <link rel='stylesheet' href='styles.css'>
</head>
<body>

<?php
session_start();
include("header.php");
?>




<div class = "container" style="margin-top: 2%">
    <div class="post-content" style="background-color: #faaf40;">
        <div class="post-container">
            <?php
            echo  displayEventRequests();?>
        </div>
    </div>
</div>


<div class="container">
    <br>
    <div class="post-content"  style="background-color: white; padding: 20px">
        <h3 > Create Event</h3>
        <form action="index.php" method="POST">
            <label for="postTitle" >Event title</label>
            <textarea class="form-control" name="title" id="title" > </textarea>

            <label for="postBody" >Event body</label>
            <textarea class="form-control" name="body" id="body"  ></textarea>

            <label for="postLocation" >Event Location</label>
            <textarea class="form-control" name="location" id="location"  ></textarea>

            <label for="postLocation" >Event Date</label>
            <input type="date" class="form-control" name="date" id="date"  ></input>

            <br>
            <button style="width: 90px" class="btn btn-warning" name="create_post" type="submit" >Create</button>
        </form>

    </div>
</div>


<div class="container">
    <?php
    // Read the JSON file
    $jsonFile = '../data/posts.json';
    $jsonData = file_get_contents($jsonFile);

    // Decode JSON data
    $events = json_decode($jsonData, true);

    if (empty($events)) {
        echo '<h2 style="margin-left: 5%"> No Events Available </h2>';}
    else {
        echo '<h2 style="margin-left: 5%"> Events Available </h2>';
        echo getPosts();
    }
    ?>
</div>


