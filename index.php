<?php
error_reporting(1);
include "comments.php";

?>
<html>

<head>
    <link href='https://fonts.googleapis.com/css?family=Raleway:400,700' rel='stylesheet' type='text/css'>
    <link href='https://fonts.googleapis.com/css?family=Open+Sans:400,600,800,400italic,600italic' rel='stylesheet' type='text/css'>
    <link rel="stylesheet" type="text/css" href="style.css">
</head>



<body>
<div class="wrapper">
<div class="expand-header" id="expand-header"><a>Login or Register</a> to track personal stats</div>
    <div id="header" class="header">
        <?php include "login/index.php"; ?>
        <style type="text/css">
    <?php if (isset($_SESSION['user_name'])){?>
.header{
    display:block;
}
.expand-header{
    display: none;
}
<?php }; ?>
</style>
    <div id="register-container" class="register-container"><?php include "login/register.php"; ?></div>

        <div class="clearfix"></div>
    </div>
    <div class="container">
        <div class="stats">
            <?php if (isset($_SESSION['user_name'])){?>
            <div id="playerStats">Your Stats:<br><span id="player-t"></span> / <span id="player-f"></span><br><span id="player-p"></span></div>
            <?php }; ?>
            <div id="globalStats">Global:<br><span id="global-t"></span> / <span id="global-f"></span><br><span id="global-p"></span></div>
        </div>
        <div id="question"></div>
        <div>
            <ul id="answers">


            </ul>
        </div>
        <div class="btn" id="next-question">Next Question</div>
        <div id="info"></div>
    </div>
    <div class="push"></div>
</div>

<footer class="footer"><a href="http://Hollings.io">Hollings.io</a> - <a href="https://github.com/Hollings">github/Hollings</a>
</footer>
<script>
    // callAjax("comments.php",function(data){
    //    alert(data);
    //    })
    function callAjax(url, callback) {
        var xmlhttp;
        // compatible with IE7+, Firefox, Chrome, Opera, Safari
        xmlhttp = new XMLHttpRequest();
        xmlhttp.onreadystatechange = function () {
            if(xmlhttp.readyState == 4 && xmlhttp.status == 200) {
                callback(xmlhttp.responseText);
            }
        }
        xmlhttp.open("GET", url, true);
        xmlhttp.send();
    }


    function newQuestion() {
        document.getElementById("question").style.maxWidth = "100px";
        document.getElementById("info").style.display = "none";
        document.getElementById("next-question").style.display = "none";
        document.getElementById("answers").innerHTML = "";
        document.getElementById("question").innerHTML = "<div style='text-align:center;'><img src='dashinfinity.gif'></div>";

        var data;
        callAjax("comments.php?x=1", function (resp) {
            data = eval('(' + resp + ')');

            var subreddits;
            callAjax("comments.php?x=2", function (resp) {
                subreddits = eval('(' + resp + ')');
                document.getElementById("question").style.maxWidth = "600px";
                window.setTimeout(function () {
                    document.getElementById("question").innerHTML = data['comment'];
                }, 250);



                //document.getElementById("info").innerHTML = data['comment'];

                var subInserted = false;
                var answerIndex = Math.floor((Math.random() * 4) + 1);
                var selectedAnswer = "";
                var solution = data['sub'];
                var comment_id = data['id'];


                for(var i = subreddits.length - 1; i >= 0; i--) {
                    document.getElementById("answers").innerHTML = document.getElementById("answers").innerHTML + "<li class='btn select'>/r/" + subreddits[i] + "</li>";
                    if(i == answerIndex) {
                        document.getElementById("answers").innerHTML = document.getElementById("answers").innerHTML + "<li class='btn select' id='answer'>/r/" + data['sub'] + "</li>";
                        subInserted = true;
                    }
                }
                if(subInserted !== true) {
                    document.getElementById("answers").innerHTML = document.getElementById("answers").innerHTML + "<li class='btn select' id='answer'>/r/" + data['sub'] + "</li>";
                    var subInserted = true;
                }

                var answerListElems = document.getElementsByClassName("select")
                for(var i = 0; i < answerListElems.length; ++i) {
                    var item = answerListElems[i];
                    item.onclick = function () {
                        this.style.color = "red";
                        selectedAnswer = this.innerHTML;
                        document.getElementById("answer").style.color = "white";
                        document.getElementById("answer").style.backgroundColor = "green";
                        document.getElementById("next-question").style.display = "block";
                    };
                }

                document.getElementById("next-question").onclick = function () {
                    submitAnswer(selectedAnswer, solution, comment_id);
                    newQuestion();
                };
                //end get subreddits
            });
            //endcallajaxdata
        });
        //end newquestion   
    }

    function submitAnswer(selectedAnswer, solution, id) {
        selectedAnswer = selectedAnswer.replace("\/r\/", "");
        callAjax("comments.php?x=3&a=" + selectedAnswer + "&s=" + solution + "&i=" + id, function (resp) {
            console.log(resp);
        });
    }

    function getGlobalStats() {
        var t;
        var f;

        callAjax("comments.php?x=4", function (resp) {
            data = eval('(' + resp + ')');
            t = parseInt(data['T']);
            f = parseInt(data['F']);
            document.getElementById("global-t").innerHTML = parseInt(t);
            document.getElementById("global-f").innerHTML = parseInt(f);
            document.getElementById("global-p").innerHTML = ((t / (t + f)) * 100).toString().substr(0, 4) + "%";
        });

    }

    function getPlayerScore() {
        var t;
        var f;
        callAjax("comments.php?x=5", function (resp) {
            data = eval('(' + resp + ')');
            t = parseInt(data['T']);
            f = parseInt(data['F']);
            document.getElementById("player-t").innerHTML = parseInt(t);
            document.getElementById("player-f").innerHTML = parseInt(f);
            document.getElementById("player-p").innerHTML = ((t / (t + f)) * 100).toString().substr(0, 4) + "%";
        });
    }

    document.getElementById("expand-header").onclick = function () {
            var header = document.getElementById("header");
            header.style.display = "block";
            this.style.display = "none";
        };

        if(document.getElementById("register-new-account") != null){
 document.getElementById("register-new-account").onclick = function () {
            document.getElementById("register-container").style.display = "block";
            document.getElementById("loginform").style.display = "none";
            document.getElementById("register-new-account").style.display = "none";
        };
  }
       

    window.setInterval(getGlobalStats, 1000);
    window.setInterval(getPlayerScore, 1000);

    getGlobalStats();
    getPlayerScore();
    newQuestion();
</script>



</body>

</html>