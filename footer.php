<?php
// footer.php — scoped, portable footer for all pages
// If Font Awesome is already loaded in your header, you can remove the link below.
?>
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css"/>

<style>
  /* ===== Scoped variables on the wrapper to avoid global collisions ===== */
  .kp-footer{
    --footer-bg: linear-gradient(135deg,#1B0F0A 0%,#3E2723 50%,#5D4037 100%);
    --accent-1:#c08457;  /* warm bronze */
    --accent-2:#e0b97a;  /* light gold  */
    --txt:#f5f5f5;
    --muted:#cfc9c3;
    --card:rgba(255,255,255,.06);
    --bd:rgba(255,255,255,.12);
    position:relative;
    background:var(--footer-bg);
    color:var(--txt);
    padding:2rem 0 1rem;
    overflow:hidden;
  }
  .kp-footer::before{
    content:"";
    position:absolute; inset:0 0 auto 0; height:2px;
    background:linear-gradient(90deg,transparent,var(--accent-1),var(--accent-2),transparent);
    opacity:.75;
  }

  .kp-footer .box-container{
    width:min(1200px,92%);
    margin-inline:auto;
    display:grid;
    grid-template-columns:repeat(12,minmax(0,1fr));
    gap:.9rem;
  }
  .kp-footer .box{
    grid-column:span 3;
    background:var(--card);
    border:1px solid var(--bd);
    border-radius:16px;
    padding:1rem;
    backdrop-filter:blur(6px);
  }
  @media (max-width:1024px){ .kp-footer .box{ grid-column:span 6; } }
  @media (max-width:640px){  .kp-footer .box{ grid-column:span 12; } }

  /* Titles */
  .kp-footer h3{
    margin:0 0 .6rem;
    font-size:1.28rem;
    font-weight:800;
    color:#fff;
    letter-spacing:.2px;
    display:flex; align-items:center; gap:.5rem;
  }
  .kp-footer h3::before{
    content:""; width:4px; height:1.1em; border-radius:2px;
    background:linear-gradient(180deg,var(--accent-1),var(--accent-2));
    display:inline-block;
  }

  /* Links + text */
  .kp-footer a{
    display:block; color:var(--txt); text-decoration:none;
    font-size:1rem; line-height:1.55;
    padding:.25rem 0; opacity:.95;
    transition:transform .22s ease, color .22s ease, opacity .22s ease;
  }
  .kp-footer a i{ margin-right:.45rem; }
  .kp-footer a:hover{ color:var(--accent-2); transform:translateX(4px); opacity:1; }

  .kp-footer p{
    margin:.28rem 0; color:var(--muted); font-size:1rem; line-height:1.55;
  }

  /* Socials */
  .kp-footer .socials{ display:flex; gap:.5rem; margin-top:.4rem; }
  .kp-footer .socials a{
    width:36px; height:36px; display:grid; place-items:center;
    background:var(--card); border:1px solid var(--bd);
    border-radius:10px; font-size:1.05rem;
    transition:transform .22s, background .22s, box-shadow .22s, color .22s;
  }
  .kp-footer .socials a:hover{
    background:linear-gradient(135deg,var(--accent-1),var(--accent-2));
    color:#1B0F0A; transform:translateY(-2px);
    box-shadow:0 6px 14px rgba(0,0,0,.28);
  }

  /* Map */
  .kp-footer .map-wrap{
    position:relative; border-radius:12px; overflow:hidden;
    border:1px solid var(--bd); box-shadow:0 6px 16px rgba(0,0,0,.25);
  }
  .kp-footer .map-wrap iframe{
    width:100%; height:220px;
    border:0; display:block;
    filter:contrast(1.08) saturate(1.05);
  }

  /* Copyright */
  .kp-footer .credit{
    width:min(1200px,92%); margin:1rem auto 0;
    text-align:center; color:var(--muted);
    font-size:1rem; font-weight:600;
  }
  .kp-footer .credit span{
    background:linear-gradient(90deg,var(--accent-1),var(--accent-2));
    -webkit-background-clip:text; background-clip:text; color:transparent;
    font-weight:800;
  }
</style>

<footer class="kp-footer">
  <section class="box-container">

    <div class="box">
      <h3>Quick Links</h3>
      <a href="home.php"><i class="fas fa-angle-right"></i>Home</a>
      <a href="shop.php"><i class="fas fa-angle-right"></i>Shop</a>
      <a href="about.php"><i class="fas fa-angle-right"></i>About</a>
      <a href="contact.php"><i class="fas fa-angle-right"></i>Contact</a>
    </div>

    <div class="box">
      <h3>Extra Links</h3>
      <a href="cart.php"><i class="fas fa-angle-right"></i>Cart</a>
      <a href="wishlist.php"><i class="fas fa-angle-right"></i>Wishlist</a>
      <a href="login.php"><i class="fas fa-angle-right"></i>Login</a>
      <a href="register.php"><i class="fas fa-angle-right"></i>Register</a>
    </div>

    <div class="box">
      <h3>Contact Info</h3>
      <p><i class="fas fa-phone"></i> +94-77 450 8787</p>
      <p><i class="fas fa-phone"></i> 077 450 8787</p>
      <p><i class="fas fa-envelope"></i> kandupinnawala@gmail.com</p>
      <p><i class="fas fa-map-marker-alt"></i> Elephant Bathing Road, Rambukkana 7100</p>
      <div class="socials">
        <a href="#" aria-label="Facebook"><i class="fab fa-facebook-f"></i></a>
        <a href="#" aria-label="Twitter"><i class="fab fa-twitter"></i></a>
        <a href="#" aria-label="Instagram"><i class="fab fa-instagram"></i></a>
        <a href="#" aria-label="LinkedIn"><i class="fab fa-linkedin"></i></a>
      </div>
    </div>

    <div class="box">
      <h3>Find Us on the Map</h3>
      <div class="map-wrap">
        <iframe
          loading="lazy"
          allowfullscreen
          referrerpolicy="no-referrer-when-downgrade"
          src="https://www.google.com/maps?q=7.300432,80.3856581&z=17&output=embed">
        </iframe>
      </div>
      <p style="margin-top:.4rem;">KANDU Pinnawala – Pinnawala, Sri Lanka</p>
    </div>

  </section>

  <p class="credit">
    © <span id="kp-year"></span> by <span>Kandu Pinnawala</span> · All rights reserved.
  </p>
</footer>

<script>
  // Year auto-fill (scoped)
  (function(){
    var y=document.getElementById('kp-year');
    if(y){ y.textContent=new Date().getFullYear(); }
  })();
</script>
