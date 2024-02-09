<?php
session_start();
// print_r($_SESSION);
require_once '../database/database.php';

// Check if the user is already logged in
if (empty($_SESSION["tenant"])) {
    header("Location: ../data_page/renters_login.php");
    exit();
}

// Get the user ID (userid)
$userid = $_SESSION["tenant"]["userid"];

// Get the house ID from the URL
$houseId = isset($_GET['house_id']) ? $_GET['house_id'] : null;

// Fetch data from rental_options table based on house_id
$sqlHouseOptions = "SELECT * FROM rental_options WHERE house_id = ?";
$stmtHouseOptions = $conn->prepare($sqlHouseOptions);

if ($stmtHouseOptions === false) {
    die('Error in SQL query: ' . $conn->error);
}

$stmtHouseOptions->bind_param('s', $houseId);
$stmtHouseOptions->execute();
$resultHouseOptions = $stmtHouseOptions->get_result();

if ($resultHouseOptions === false) {
    die('Error in SQL result: ' . $stmtHouseOptions->error);
}

if ($resultHouseOptions->num_rows > 0) {
    $houseOptionsData = $resultHouseOptions->fetch_assoc();

    // Save houseOptionsData in the session
    $_SESSION['houseOptionsData'] = $houseOptionsData;
} else {
    // Handle the case where no rows are returned
    $houseOptionsData = array(); // Initialize as an empty array
}

// Fetch data from rental_options table to get owner_user_id
$sqlRentalOptions = "SELECT owner_user_id FROM rental_options WHERE house_id = ?";
$stmtRentalOptions = $conn->prepare($sqlRentalOptions);

if ($stmtRentalOptions === false) {
    die('Error in SQL query: ' . $conn->error);
}

$stmtRentalOptions->bind_param('s', $houseId);
$stmtRentalOptions->execute();
$resultRentalOptions = $stmtRentalOptions->get_result();

if ($resultRentalOptions === false) {
    die('Error in SQL result: ' . $stmtRentalOptions->error);
}

if ($resultRentalOptions->num_rows > 0) {
    $rentalOptionsData = $resultRentalOptions->fetch_assoc();
    $ownerUserId = $rentalOptionsData['owner_user_id'];

    // Debugging: Print the content of $_SESSION['rentalOptionsData']
        // echo '<pre>';
        // print_r($_SESSION['rentalOptionsData']);
        // echo '</pre>';


    // Save rentalOptionsData in the session
    $_SESSION['rentalOptionsData'] = $rentalOptionsData;
} else {
    // Handle the case where no rows are returned
    $ownerUserId = null;
}

?>




<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>ResiHive - Contact</title>
    <link rel="icon" type="image/x-icon" href="..\data_image\favicon.png">
    <link rel="stylesheet" type="text/css" href="..\data_style\styles-renters.css">
    <script src="https://kit.fontawesome.com/4d86b94a8a.js" crossorigin="anonymous"></script>

    <script src="https://code.jquery.com/jquery-3.6.4.min.js"></script>

</head>
    <body>

        <?php include 'navbar.php'; ?>

        <section class="main">
            <div class="wrapper" id="container">
                <header>
                    <div class="title">
                        <h1>Chat</h1>
                    </div>
                    <div class="content">

                        <?php
                            include_once "../database/database.php";

                            // Fetch data from rental_options, tbl_landowner_account, and house_rentals using JOIN statements
                            $sql = mysqli_query($conn, "SELECT ro.owner_user_id, hr.house_name, hr.house_image
                                                        FROM house_rentals hr 
                                                        JOIN rental_options ro ON ro.owner_user_id = hr.landowner_userid
                                                        WHERE ro.owner_user_id = $userid");

                            if ($sql && mysqli_num_rows($sql) > 0) {
                                $row = mysqli_fetch_assoc($sql);
                            } else {
                                // Handle the case where no rows are returned
                                $row = array(); // Initialize $row as an empty array
                            }

                        ?>
                        <img src="..\data_image\favicon.png" alt="logo">
                        <div class="details">   
                        <?php if (!empty($row)): ?>
                                <span><a><?php echo $row['house_name']?></a></span>
                                <p><?php echo $row['msg_status']; ?></p>
                                <div class="active_user">
                                    <div class="status-dot"><i class="fa-solid fa-circle"></i></div>
                                    <p style="color:white"> Active Now</p>
                                </div> 
                            <?php else: ?>
                                <p style="color:white">loobLandowner</p>
                            <?php endif; ?>
                        </div>

                     </div>
                    </header>
                    <section class="users">
                        <div class="search">
                            <span class="text">Select a user to start chat</span>
                            <input type="text" placeholder="Enter name to search...">
                            <button><i class="#"></i></button>
                        </div>
                        <div class="users-list">

                        </div>
                    </section>

                    <section class="chatBox">
                        <div class="content">
                            <div class="user-details">  
                                <?php
                                    include_once "../database/database.php";

                                    // Fetch data from rental_options, tbl_landowner_account, and house_rentals using JOIN statements
                                    $sql = mysqli_query($conn, "SELECT ro.owner_user_id, la.f_name, la.s_name, hr.house_id
                                        FROM rental_options ro
                                        JOIN tbl_landowner_account la ON ro.owner_user_id = la.userid 
                                        JOIN house_rentals hr ON ro.house_id = hr.house_id
                                        WHERE ro.owner_user_id = '{$_SESSION['tenant']['userid']}'");

                                    if ($sql && mysqli_num_rows($sql) > 0) {
                                        $row = mysqli_fetch_assoc($sql);
                                    } else {
                                        // Handle the case where no rows are returned
                                        $row = array(); // Initialize $row as an empty array
                                    }

                                ?>

                                <?php if (!empty($row) && array_key_exists('f_name', $row)): ?>
                                    <img src="..\data_image\favicon.png" alt="logo">
                                    <div class="details">
                                        <span><a style="color:black"><?php echo $row['f_name']. " " . $row['s_name'] ?></a></span>
                                        <div class="active_user">
                                            <div class="status-dot"><i class="fa-solid fa-circle"></i></div>
                                            <p style="color:black"> Active Now</p>
                                        </div>
                                    </div>
                                <?php else: ?>
                                    <p>Landowner</p>
                                <?php endif; ?>                  
                            </div>

                        </div>
                        
                        <div class="chat-cont"  id="loadchat">
                    
                        </div>

                        <div class="typing-area">
                            <input type="text" name="chatmsg" id="chatmsg" class="input-field" placeholder="Type a message here..." autocomplete="off">
                            <button type="button" id="btnSend" title="Send Message"><i class="fab fa-telegram-plane"></i></button>
                        </div>       
                    </section>

            </div>
        </section>

        <footer>
            <div class="watermark">
                <p>by &copy;ResiHive 2023</p>
            </div>
        </footer>

        <!-- <script src="/jscripts/users.js"></script> -->

        <!-- <script src="../jscripts/chat.js"></script> -->

        <!-- <script src="../jscripts/chat copy.js"></script> -->

        <script src="/jscripts/dropdownfeat.js"></script>
        
        <script src="/jscripts/chatBoxFeat.js"></script>
        
        <script>
    $(document).ready(function () {
        let land_id = '<?= $_SESSION['rentalOptionsData']["owner_user_id"] ?>';

        // Function to send a message
        function sendMessage() {
            let chatmsg = $('#chatmsg').val();

            $.post({
                url: "../ajax/INSERTCHAT_RENTER.php",
                data: { land_id: land_id, chatmsg: chatmsg }
            }).done(function (data) {
                if (data == "success") {
                    $('#chatmsg').val('');
                }
                console.log(data);
            });
        }

        // Click event for the send button
        $('#btnSend').click(function () {
            sendMessage();
        });

        // Keypress event for the Enter key in the input field
        $('#chatmsg').keypress(function (e) {
            if (e.which == 13) {
                e.preventDefault();
                sendMessage();
            }
        });

        // Periodic function to load chat messages
        function loadChat() {
            $.post({
                url: "../ajax/LOADCHAT_RENTER.php",
                data: { land_id: land_id }
            }).done(function (data) {
                // Reverse the order of chat messages before inserting
                $('#loadchat').html(data).scrollTop($('#loadchat')[0].scrollHeight);
                console.log(data);
            });
        }

        // Initial load of chat messages
        loadChat();

        // Periodic function to load chat messages
        setInterval(function () {
            loadChat();
        }, 1000);
    });
</script>


<?php

?>
    </body>
</html>