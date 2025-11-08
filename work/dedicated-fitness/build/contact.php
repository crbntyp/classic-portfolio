


<!DOCTYPE html>

<head>
    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <title>Dedicated Fitness</title>
    <meta name="description" content="">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <link rel="stylesheet" href="css/main.css">
    <script src="https://use.typekit.net/mjp5uoz.js"></script>
    <script>
        try {
            Typekit.load({
                async: true
            });
        } catch (e) {}
    </script>
</head>

<body>



    <section class="container strip strip-white none">
        <header class="main clearfix">
            <div class="base">
                 <div class="logo hide-mobile hide-tablet">
                    <a href="home.htm"><img src="img/logo.png" alt=""></a>
                </div>
                <div class="logo show-mobile show-tablet">
                    <a href="home.htm"><img src="img/logo-mobile.png" alt=""></a>
                </div>
                <nav class="util clearfix">
                    <ul class="clearfix pull-right">
                        <li class="selected"><a href="contact.php">Contact</a></li>
                        <li><a href="faqs.htm">FAQs</a></li> 
                    </ul>
                </nav>
                <nav class="main clearfix">
                    <ul class="clearfix pull-right">
                        <li><a href="results.htm">Results</a></li>
                        <li><a href="team.htm">Team</a></li>
                        <li><a href="programs.htm">Program</a></li>
                        <li><a href="gallery.htm">Gallery</a></li>
                        <li><a href="videos.htm">Videos</a></li>
                        <li><a href="john.htm">John</a></li>
                    </ul>
                </nav>
                <div class="clearfix mobile-nav-container">
                    <nav class="bun pull-right">
                        <div class="ingredient salad"></div>
                        <div class="ingredient cheese"></div>
                        <div class="ingredient meat"></div>
                </div>
                </nav>
                <div class="mobile-nav-interface valign color-white">
                    <div class="valign-center text-center">
                        <img src="img/logo-banner.png" alt="">
                        <div class="main">
                            <a href="results.htm">Results</a> <span>/</span> <a href="team.htm">Team</a> <span>/</span> <a
                                href="programs.htm">Program</a> <span>/</span> <a href="gallery.htm">Gallery</a> <span>/</span>                            <a href="videos.htm">Videos</a> <span>/</span> <a href="john.htm">John</a>
                        </div>
                        <div class="util">
                            <a href="contact.php">Contact</a> <span>/</span> 
                            <a href="faqs.htm">FAQs</a>
                        </div>
                    </div>
                </div>
            </div>
        </header>
        <section class="strip strip-white">
            <div class="base">


                <h2 class="text-center mb-4">Contact</h2>

                <div class="banner-lower text-center mb-4 hide-mobile">
                    <img src="img/banner-map.png" class="img-responsive" alt="">
                </div>

                <div class="clearfix row wide-9 pull-center form-container">
                    <div class="col-6 mb-2">
                        <div class="form">

                            <?php 
                            if(isset($_POST['submit'])){
                                $to = "jonathan@carbontype.co"; // this is your Email address
                                $from = $_POST['email']; // this is the sender's Email address
                                $first_name = $_POST['first_name'];
                                $last_name = $_POST['last_name'];
                                $subject = "Contact submission from website";
                                $message = $first_name . " " . $last_name . " wrote the following:" . "\n\n" . $_POST['message'];
                                
                                $headers = "From:" . $from;
                                mail($to,$subject,$message,$headers);
                                echo "<h3 class='color-secondary'>Contact form sent</h3>";
                                echo "<div class=''>";
                                echo "Thank you " . $first_name . ", our dedicated team will contact you shortly.";
                                echo "</div>";
                                // You can also use header('Location: thank_you.php'); to redirect to another page.
                                // You cannot use header and echo together. It's one or the other.
                                }
                                else  {
                            ?>

                            <div class="text-sml mb-2 text-uppercase"><span class="color-accent text-bold">*</span> required fields</div>
                            <form action="contact.php" method="post">
                                <label for="">First Name<span class="color-accent">*</span></label>
                                <input type="text" name="first_name" required>
                                <label for="">Surname</label> 
                                <input type="text" name="last_name">
                                <label for="">Your Email<span class="color-accent">*</span></label>
                                <input type="email" name="email" required>
                                <label for="">Your Enquiry<span class="color-accent">*</span></label>
                                <textarea rows="6" name="message" required></textarea>
                                <input type="submit" class="btn btn-primary btn-block" name="submit" value="Get in touch">
                            </form>

                            <?php
                                }
                            ?>
                        </div>
                    </div>
                    <div class="col-5 pull-right mb-2 pt-5">
                        <div class="address">
                            Townsend Business Park, <br>
                            28 Townsend Street, <br>
                            Unit 24, <br>
                            Belfast, BT13 2ES <br>
                            <br>
                            <strong>
                            e: <a href="mailto:info@dedicatedfitness.com">info@dedicatedfitness.com</a><br>
                            T: 028 9043 5778
                            </strong>
                        </div>
                    </div>
                </div>
                
            </div>
        </section>
        <section class="strip strip-lgrey">
            <div class="base">
                <div class="quote text-center">
                    <h2>What our customers say</h2>
                    <div class="wide-9 pull-center">
                        <p>I’ve been training with dedicated fitness for over 12 weeks and the results were insane, they kept
                            me motivated and I got the results I wanted!</p>
                        <author location="Crumlin">Eugene McLaughlin</author>
                    </div>
                </div>
            </div>
        </section>
    </section>
    <footer>
        <section class="strip strip-black">
            <div class="base">
                <div class="wide-9 pull-center text-center">
                    <h3 class="mb-1">Dedicated Fitness</h3>
                    <p class="text-sml text-uppercase mb-1">Townsend Business Park, 28 Townsend Street, Unit 24, Belfast, BT13 2ES</p>
                    <h3 class="text-sml">TEL: 028 9043 5778 - email: <a href="mailto:info@dedicatedfitness.com">info@dedicatedfitness.com</a></h3>
                </div>
            </div>
        </section>
    </footer>
</body>
<script src="js/apps.js"></script>
<script src="js/execute.js"></script>

</html>