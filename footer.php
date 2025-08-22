<?php
// footer.php — portable footer with dual themes (dark + off-white)
// Add class "theme-light" on <footer> to switch to off-white theme.
?>
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css"/>

<style>
  /* ===== Base wrapper + default (dark wood) variables ===== */
  .kp-footer{
    --footer-bg: linear-gradient(135deg,#1B0F0A 0%,#3E2723 50%,#5D4037 100%);
    --accent-1:#c08457;   /* warm bronze */
    --accent-2:#e0b97a;   /* light gold  */
    --txt:#f5f5f5;
    --muted:#cfc9c3;
    --card:rgba(255,255,255,.06);
    --bd:rgba(255,255,255,.12);
    --chip-bg: var(--card);
    --chip-txt:#f5f5f5;
    --shadow: 0 6px 16px rgba(0,0,0,.25);
    position:relative;
    background:var(--footer-bg);
    color:var(--txt);
    padding:2rem 0 1rem;
    overflow:hidden;
  }

  /* ===== OFF-WHITE THEME ===== */
  .kp-footer.theme-light{
    --footer-bg: linear-gradient(135deg,#F9F9F6 0%, #F2EFEA 50%, #EAE5DD 100%);
    --accent-1:#C89F6D;  /* warm tan */
    --accent-2:#7B5E42;  /* cocoa   */
    --txt:#3E2723;
    --muted:#6a584d;
    --card:rgba(255,255,255,.78);
    --bd:rgba(140,120,100,.22);
    --chip-bg:#ffffff;
    --chip-txt:#3E2723;
    --shadow: 0 8px 20px rgba(120,100,80,.12);
  }

  .kp-footer::before{
    content:"";
    position:absolute; inset:0 0 auto 0; height:2px;
    background:linear-gradient(90deg,transparent,var(--accent-1),var(--accent-2),transparent);
    opacity:.8;
  }

  .kp-footer .box-container{
    width:min(1200px,92%);
    margin-inline:auto;
    display:grid;
    grid-template-columns:repeat(12,minmax(0,1fr));
    gap:1rem;
  }
  .kp-footer .box{
    grid-column:span 3;
    background:var(--card);
    border:1px solid var(--bd);
    border-radius:16px;
    padding:1.2rem 1rem;
    backdrop-filter:blur(6px);
    box-shadow: var(--shadow);
  }
  @media (max-width:1024px){ .kp-footer .box{ grid-column:span 6; } }
  @media (max-width:640px){  .kp-footer .box{ grid-column:span 12; } }

  /* Titles */
  .kp-footer h3{
    margin:0 0 .7rem;
    font-size:1.28rem;
    font-weight:800;
    color:var(--txt);
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
  .kp-footer .socials{
    display:flex; gap:.6rem; margin-top:.8rem; flex-wrap:wrap;
  }
  .kp-footer .socials a{
    height:38px; padding:0 .9rem;
    display:inline-flex; align-items:center; gap:.45rem;
    background:var(--chip-bg); color:var(--chip-txt);
    border:1px solid var(--bd);
    border-radius:999px; font-size:0.95rem; font-weight:600;
    white-space:nowrap;
    box-shadow: var(--shadow);
    transition:transform .22s, background .22s, box-shadow .22s, color .22s, border-color .22s;
  }
  .kp-footer .socials a i{ font-size:1rem; }
  .kp-footer .socials a:hover{
    background:linear-gradient(135deg,var(--accent-1),var(--accent-2));
    color:#fff; transform:translateY(-2px);
    border-color:transparent; box-shadow:0 8px 18px rgba(0,0,0,.18);
  }

  /* Map */
  .kp-footer .map-wrap{
    position:relative; border-radius:12px; overflow:hidden;
    border:1px solid var(--bd); box-shadow: var(--shadow);
  }
  .kp-footer .map-wrap iframe{
    width:100%; height:220px;
    border:0; display:block;
    filter:contrast(1.04) saturate(1.02);
  }

  /* Copyright */
  .kp-footer .credit{
    width:min(1200px,92%); margin:1.2rem auto 0;
    text-align:center; color:var(--muted);
    font-size:1rem; font-weight:600;
  }
  .kp-footer .credit span{
    background:linear-gradient(90deg,var(--accent-1),var(--accent-2));
    -webkit-background-clip:text; background-clip:text; color:transparent;
    font-weight:800;
  }
</style>

<footer class="kp-footer theme-light"><!-- remove 'theme-light' to use dark version -->
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
        <a href="#" aria-label="Facebook"><i class="fab fa-facebook-f"></i><span>Facebook</span></a>
        <a href="#" aria-label="Instagram"><i class="fab fa-instagram"></i><span>Instagram</span></a>
        <a href="#" aria-label="Twitter"><i class="fab fa-twitter"></i><span>Twitter/X</span></a>
        <a href="#" aria-label="LinkedIn"><i class="fab fa-linkedin"></i><span>LinkedIn</span></a>
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
