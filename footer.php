<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Cool Modern Footer - Light Theme</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <style>
        .footer {
            background: linear-gradient(135deg, #e8f5e8 0%, #f0f8f0 50%, #e8f5e8 100%);
            color: #2c3e50;
            padding: 2rem 0 1rem;
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            position: relative;
            overflow: hidden;
        }

        .footer::before {
            content: '';
            position: absolute;
            top: 0;
            left: -50%;
            width: 200%;
            height: 2px;
            background: linear-gradient(90deg, transparent, #4CAF50, #66BB6A, #4CAF50, transparent);
            animation: shimmer 3s infinite linear;
        }

        @keyframes shimmer {
            0% { transform: translateX(-100%); }
            100% { transform: translateX(100%); }
        }

        .box-container {
            max-width: 1200px;
            margin: 0 auto;
            display: grid;
            grid-template-columns: 1fr 1fr 1.5fr 1fr;
            gap: 2rem;
            padding: 0 2rem;
            align-items: start;
            position: relative;
            z-index: 2;
        }

        .box {
            min-height: 200px;
            padding: 1.5rem;
            background: rgba(255, 255, 255, 0.6);
            border-radius: 15px;
            backdrop-filter: blur(10px);
            border: 1px solid rgba(76, 175, 80, 0.2);
            transition: all 0.4s ease;
            position: relative;
            overflow: hidden;
            box-shadow: 0 4px 15px rgba(0, 0, 0, 0.1);
        }

        .box::before {
            content: '';
            position: absolute;
            top: 0;
            left: -100%;
            width: 100%;
            height: 100%;
            background: linear-gradient(90deg, transparent, rgba(76, 175, 80, 0.1), transparent);
            transition: left 0.6s ease;
        }

        .box:hover::before {
            left: 100%;
        }

        .box:hover {
            transform: translateY(-5px);
            background: rgba(255, 255, 255, 0.8);
            border-color: rgba(76, 175, 80, 0.4);
            box-shadow: 0 10px 30px rgba(76, 175, 80, 0.2);
        }

        .box h3 {
            color: #2e7d32;
            font-size: 1.2rem;
            margin-bottom: 1.5rem;
            text-transform: lowercase;
            font-weight: 600;
            position: relative;
            z-index: 3;
            text-shadow: 0 0 10px rgba(46, 125, 50, 0.3);
        }

        .box h3::after {
            content: '';
            position: absolute;
            bottom: -5px;
            left: 0;
            width: 30px;
            height: 2px;
            background: linear-gradient(90deg, #4CAF50, #66BB6A);
            border-radius: 2px;
        }

        .box a, .box p {
            display: flex;
            align-items: center;
            color: #495057;
            text-decoration: none;
            margin-bottom: 1rem;
            font-size: 1rem;
            line-height: 1.5;
            transition: all 0.3s ease;
            position: relative;
            z-index: 3;
            padding: 0.8rem 1rem;
            background: rgba(255, 255, 255, 0.3);
            border-radius: 8px;
            border: 1px solid rgba(76, 175, 80, 0.1);
            opacity: 0.9;
            min-height: 45px;
        }

        .box a:hover {
            color: #2e7d32;
            transform: translateX(8px) scale(1.02);
            text-shadow: 0 0 8px rgba(46, 125, 50, 0.4);
            opacity: 1;
            background: rgba(255, 255, 255, 0.8);
            border-color: rgba(76, 175, 80, 0.3);
            box-shadow: 0 4px 15px rgba(76, 175, 80, 0.15);
        }

        .box i {
            margin-right: 1rem;
            color: #4CAF50;
            width: 20px;
            font-size: 1.1rem;
            transition: all 0.3s ease;
        }

        .box a:hover i {
            transform: scale(1.3);
            filter: drop-shadow(0 0 8px rgba(76, 175, 80, 0.8));
        }

        .map-container {
            margin-top: 1rem;
            border-radius: 12px;
            overflow: hidden;
            box-shadow: 0 8px 25px rgba(0,0,0,0.15);
            border: 2px solid rgba(76, 175, 80, 0.3);
            transition: all 0.3s ease;
            position: relative;
        }

        .map-container::before {
            content: '';
            position: absolute;
            top: -2px;
            left: -2px;
            right: -2px;
            bottom: -2px;
            background: linear-gradient(45deg, #4CAF50, #66BB6A, #4CAF50);
            border-radius: 12px;
            z-index: -1;
            opacity: 0;
            transition: opacity 0.3s ease;
        }

        .map-container:hover::before {
            opacity: 1;
        }

        .map-container:hover {
            transform: scale(1.02);
            box-shadow: 0 12px 35px rgba(76, 175, 80, 0.3);
        }

        .map-container iframe {
            width: 100%;
            height: 140px;
            border: none;
            transition: filter 0.3s ease;
        }

        .map-container:hover iframe {
            filter: brightness(1.1) contrast(1.1);
        }

        .credit {
            text-align: center;
            margin-top: 2rem;
            padding-top: 1.5rem;
            border-top: 1px solid rgba(0, 0, 0, 0.1);
            color: #6c757d;
            font-size: 0.85rem;
            position: relative;
        }

        .credit::before {
            content: '';
            position: absolute;
            top: 0;
            left: 50%;
            transform: translateX(-50%);
            width: 100px;
            height: 1px;
            background: linear-gradient(90deg, transparent, #4CAF50, transparent);
        }

        .credit span {
            color: #2e7d32;
            font-weight: bold;
            text-shadow: 0 0 10px rgba(46, 125, 50, 0.3);
        }

        /* Floating particles effect */
        .footer::after {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background-image: 
                radial-gradient(2px 2px at 20px 30px, rgba(76, 175, 80, 0.2), transparent),
                radial-gradient(2px 2px at 40px 70px, rgba(102, 187, 106, 0.15), transparent),
                radial-gradient(1px 1px at 90px 40px, rgba(76, 175, 80, 0.25), transparent),
                radial-gradient(1px 1px at 130px 80px, rgba(102, 187, 106, 0.2), transparent);
            background-repeat: repeat;
            background-size: 200px 150px;
            animation: float 20s infinite linear;
            pointer-events: none;
            z-index: 1;
        }

        @keyframes float {
            0% { transform: translateY(0) translateX(0); }
            33% { transform: translateY(-10px) translateX(10px); }
            66% { transform: translateY(5px) translateX(-5px); }
            100% { transform: translateY(0) translateX(0); }
        }

        @media (max-width: 968px) {
            .box-container {
                grid-template-columns: 1fr 1fr;
                gap: 2rem;
            }
        }

        @media (max-width: 768px) {
            .box-container {
                grid-template-columns: 1fr;
                gap: 2rem;
                padding: 0 1rem;
            }
            
            .map-container iframe {
                height: 160px;
            }

            .box {
                min-height: auto;
                padding: 1.2rem;
            }

            .box a, .box p {
                padding: 0.7rem 0.8rem;
                font-size: 0.95rem;
                min-height: 42px;
            }

            .footer {
                padding: 2rem 0 1rem;
            }
        }
    </style>
</head>
<body>

<footer class="footer">
   <section class="box-container">

      <div class="box">
         <h3>quick links</h3>
         <a href="home.php"> <i class="fas fa-angle-right"></i> home</a>
         <a href="shop.php"> <i class="fas fa-angle-right"></i> shop</a>
         <a href="about.php"> <i class="fas fa-angle-right"></i> about</a>
         <a href="contact.php"> <i class="fas fa-angle-right"></i> contact</a>
      </div>

      <div class="box">
         <h3>extra links</h3>
         <a href="cart.php"> <i class="fas fa-angle-right"></i> cart</a>
         <a href="wishlist.php"> <i class="fas fa-angle-right"></i> wishlist</a>
         <a href="login.php"> <i class="fas fa-angle-right"></i> login</a>
         <a href="register.php"> <i class="fas fa-angle-right"></i> register</a>
      </div>

      <div class="box">
         <h3>contact info</h3>
         <p> <i class="fas fa-phone"></i> +9470-456-7890 </p>
         <p> <i class="fas fa-envelope"></i> kandupinnawala@gmail.com </p>
         
         <!-- Google Map -->
         <div class="map-container">
            <iframe 
               src="https://www.google.com/maps/embed?pb=!1m18!1m12!1m3!1d3958.123456789!2d80.123456!3d7.123456!2m3!1f0!2f0!3f0!3m2!1i1024!2i768!4f13.1!3m3!1m2!1s0x0%3A0x0!2zN8KwMDcnMDAuMCJOIDgwwrAwNycwMC4wIkU!5e0!3m2!1sen!2slk!4v1234567890123!5m2!1sen!2slk"
               allowfullscreen="" 
               loading="lazy" 
               referrerpolicy="no-referrer-when-downgrade">
            </iframe>
         </div>
      </div>

      <div class="box">
         <h3>follow us</h3>
         <a href="#"> <i class="fab fa-facebook-f"></i> facebook </a>
         <a href="#"> <i class="fab fa-tiktok"></i> ticktok </a>
         <a href="#"> <i class="fab fa-instagram"></i> instagram </a>
         <a href="#"> <i class="fab fa-linkedin"></i> linkedin </a>
      </div>

   </section>

   <p class="credit"> &copy; copyright @ 2025 by <span>Kandu Pinnawala</span> | all rights reserved! </p>

</footer>

</body>
</html>