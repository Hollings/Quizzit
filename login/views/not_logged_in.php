<?php
// show potential errors / feedback (from login object)
if (isset($login)) {
    if ($login->errors) {
        foreach ($login->errors as $error) {
            echo $error;
        }
    }
    if ($login->messages) {
        foreach ($login->messages as $message) {
            //echo $message;
        }
    }
}
?>

<!-- login form box -->
<form method="post" action="index.php" id="loginform" name="loginform">

    <input id="login_input_username" placeholder="username" class="login_input" type="text" name="user_name" required />

    <input id="login_input_password" placeholder="password" class="login_input" type="password" name="user_password" autocomplete="off" required />

    <input type="submit"  name="login" value="Log in" />

</form>

<a href="#" id="register-new-account">Register new account</a>
