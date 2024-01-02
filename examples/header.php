
<?php
ini_set("display_errors", "1");
ini_set("display_startup_errors", "1");
error_reporting(E_ALL);
?>

<html lang="">
<body style="background-color: #eedf8f; box-shadow: inset 0 1px 1px rgba(0,0,0,.075);">
<header style="background-color: #ddbf21; height: 130px; ">
    <div class="container">

      <div class="d-flex flex-wrap align-items-center justify-content-center justify-content-lg-start">

        <a href="/" class="d-flex align-items-center mb-2 mb-lg-0 text-white text-decoration-none">

          <svg class="bi me-2" width="40" height="32" role="img" aria-label="Bootstrap"><use xlink:href="#bootstrap"></use></svg>
            <div style="padding-top: 25px; font-size: 45px "> METRO EVENT </div>
        </a>


<?php
include("api.php");
global $userData;

if ($userData) {
    echo '<h2 style="margin-left: 600px; margin-top: 2%"> Hello ' . $userData["username"] . '!</h2>';
    echo '<form method="POST" action="index.php" style=" margin-left: 70%; margin-top: 0%  ">';

    if ($userData['type'] == 'user') {
        echo '<button type="submit" class="btn btn-primary"  name="request_Organizer"> Request Organizer </button>     ';
    }

    echo '<button type="submit" class="btn btn-danger"  name="log_Out"> Logout</button>';
    echo '</form>';
}

else{
    echo  '<div class="text-end" style="margin-left: 60%; margin-top: 2%">
            <!-- Button trigger modal -->
            <button type="button" style="color: #ddbf21" class="btn btn-dark" data-bs-toggle="modal" data-bs-target="#staticBackdrop">
                Log In
            </button>

            <!-- Modal -->
            <div class="modal fade" id="staticBackdrop" data-bs-backdrop="static" data-bs-keyboard="false" tabindex="-1" aria-labelledby="staticBackdropLabel" aria-hidden="true">
                <div class="modal-dialog">
               
                    <div class="modal-content">
                        <div class="modal-header" style="color: black">
                         LOG IN
                            <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                        </div>
                        <form method="POST" id ="username">
                            <div class="modal-body" style="color:black; text-align:left">

                                <label for="uname2">Username:</label><br>
                                <input type="text" id="uname2" name="uname2" style="width: 90%" > <br>
                                <br>


                                <div class="modal-footer" >
                                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                                    <button type="submit" class="btn btn-primary" name="login_submit" >Log-In</button>
                                </div>
                            </div>
                        </form>
                    </div>
                </div>
            </div>

            <!--signUp-->
            <button type="button" style="color: #ddbf21" class="btn btn-dark" data-bs-toggle="modal" data-bs-target="#staticBackdrop2">
                Sign Up
            </button>

            <!-- Modal -->
            <div class="modal fade" id="staticBackdrop2" data-bs-backdrop="static" data-bs-keyboard="false" tabindex="-1" aria-labelledby="staticBackdropLabel" aria-hidden="true">
                <div class="modal-dialog">
                    <div class="modal-content">
                        
                        <div class="modal-header" style="color: black">
                         SIGN UP
                            <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                        </div>


                        <div class="modal-body" style="color:black; text-align:left">

                            <form method="POST" action="index.php">

                                <label for="name">Name:</label><br>
                                <input type="text" id="name" name="name" style="width: 90%" ><br>


                                <label for="uname">Username:</label><br>
                                <input type="text" id="uname" name="uname" style="width: 90%" ><br>

                                <label for="email">Email:</label><br>
                                <input type="text" id="email" name="email" style="width: 90%"  ><br>

                                <label for="company">Company:</label><br>
                                <input type="text" id="company" name ="company" style="width: 90%" ><br>

                                <br>


                                <div class="modal-footer">
                                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                                    <button type="submit" class="btn btn-primary" name="signup_submit">Sign-Up</button>
                                </div>
                            </form>
                        </div>

                    </div>
                </div>
            </div>

        </div>';
}
?>

    </div>
    </div>


</header>


</body>
</html>