<!-- ===== Footer CSS (place AFTER your main CSS) ===== -->
<style>
  :root{
    --footer-bg:#141216;
    --footer-accent:#8b5cf6;
    --footer-accent-2:#f43f5e;
    --footer-text:#eaeaea;
    --footer-muted:#bdbdbd;
    --footer-card:rgba(255,255,255,.06);
    --footer-border:rgba(255,255,255,.12);
  }

  .footer{
    position:relative;
    color:var(--footer-text);
    background:
      radial-gradient(1200px 600px at 10% -10%, rgba(139,92,246,.12), transparent 40%),
      radial-gradient(1000px 500px at 90% -20%, rgba(244,63,94,.12), transparent 45%),
      var(--footer-bg);
    padding:2.5rem 0 1.5rem;
    overflow:hidden;
  }
  .footer::before{
    content:"";
    position:absolute; inset:0 0 auto 0; height:2px;
    background:linear-gradient(90deg,transparent,var(--footer-accent),var(--footer-accent-2),transparent);
    opacity:.7;
  }

  /* FORCE a grid and kill conflicting flex rules */
  .footer .box-container{
    width:min(1200px,92%);
    margin-inline:auto;
    display:grid !important;
    grid-template-columns:repeat(12,minmax(0,1fr)) !important;
    gap:1rem !important;
    align-items:stretch;
    box-sizing:border-box;
  }

  .footer .box{
    /* neutralize old flex-based sizing */
    flex:initial !important;
    width:auto !important;

    grid-column:span 3 !important; /* 4 columns on desktop */
    background:var(--footer-card);
    border:1px solid var(--footer-border);
    border-radius:18px;
    padding:1.25rem;
    backdrop-filter:blur(6px);
    box-sizing:border-box;
  }
  .footer .box.map-box{
    grid-column:span 3 !important; /* same size as others on desktop */
  }

  /* Tablet: 2 columns */
  @media (max-width:1024px){
    .footer .box{ grid-column:span 6 !important; }
  }
  /* Mobile: 1 column */
  @media (max-width:640px){
    .footer .box{ grid-column:span 12 !important; }
  }

  .footer h3{
    margin:0 0 .75rem 0; font-size:1.1rem; letter-spacing:.3px; font-weight:700; color:#fff;
  }
  .footer a{
    display:block; color:var(--footer-text); text-decoration:none; opacity:.9; padding:.25rem 0;
    transition:transform .2s ease, opacity .2s ease, color .2s ease;
  }
  .footer a i{ margin-right:.45rem; }
  .footer a:hover{ color:#fff; opacity:1; transform:translateX(2px); }
  .footer p{ margin:.35rem 0; color:var(--footer-muted); }

  .footer .socials{ display:flex; gap:.5rem; margin-top:.5rem; }
  .footer .socials a{
    width:38px; height:38px; display:grid; place-items:center;
    background:var(--footer-card); border:1px solid var(--footer-border);
    border-radius:12px; transition:transform .2s, box-shadow .2s;
  }
  .footer .socials a:hover{ transform:translateY(-2px); box-shadow:0 6px 14px rgba(0,0,0,.25); }

  .map-wrap{ position:relative; border-radius:14px; overflow:hidden; border:1px solid var(--footer-border); }
  .map-wrap iframe{ width:100%; height:260px; border:0; display:block; filter:saturate(1.05) contrast(1.05); }

  .footer .credit{
    width:min(1200px,92%); margin:1.25rem auto 0; text-align:center;
    color:var(--footer-muted); font-size:.95rem;
  }
  .footer .credit span{
    background:linear-gradient(90deg,var(--footer-accent),var(--footer-accent-2));
    -webkit-background-clip:text; background-clip:text; color:transparent; font-weight:700;
  }
</style>

<!-- ===== Footer HTML ===== -->
<footer class="footer">

  <section class="box-container">

    <div class="box">
      <h3>quick links</h3>
      <a href="home.php"><i class="fas fa-angle-right"></i> home</a>
      <a href="shop.php"><i class="fas fa-angle-right"></i> shop</a>
      <a href="about.php"><i class="fas fa-angle-right"></i> about</a>
      <a href="contact.php"><i class="fas fa-angle-right"></i> contact</a>
    </div>

    <div class="box">
      <h3>extra links</h3>
      <a href="cart.php"><i class="fas fa-angle-right"></i> cart</a>
      <a href="wishlist.php"><i class="fas fa-angle-right"></i> wishlist</a>
      <a href="login.php"><i class="fas fa-angle-right"></i> login</a>
      <a href="register.php"><i class="fas fa-angle-right"></i> register</a>
    </div>

    <div class="box">
      <h3>contact info</h3>
      <p><i class="fas fa-phone"></i> +123-456-7890</p>
      <p><i class="fas fa-phone"></i> +111-222-3333</p>
      <p><i class="fas fa-envelope"></i> shaikhanas@gmail.com</p>
      <p><i class="fas fa-map-marker-alt"></i> mumbai, india - 400104</p>
      <div class="socials">
        <a href="#"><i class="fab fa-facebook-f" aria-hidden="true"></i></a>
        <a href="#"><i class="fab fa-twitter" aria-hidden="true"></i></a>
        <a href="#"><i class="fab fa-instagram" aria-hidden="true"></i></a>
        <a href="#"><i class="fab fa-linkedin" aria-hidden="true"></i></a>
      </div>
    </div>

    <div class="box map-box">
      <h3>find us on the map</h3>
      <div class="map-wrap">
        <!-- Your exact coordinates from the link you sent -->
        <iframe
          loading="lazy"
          allowfullscreen
          referrerpolicy="no-referrer-when-downgrade"
          src="https://www.google.com/maps?q=7.300432,80.3856581&z=17&output=embed">
        </iframe>
      </div>
      <p style="margin-top:.5rem;color:var(--footer-muted);">
        KANDU Pinnawala – Pinnawala, Sri Lanka
      </p>
    </div>

  </section>

  <p class="credit">© <span id="year"></span> by <span>Kandu Pinnawala</span> · all rights reserved.</p>
</footer>

<script>
  document.getElementById('year').textContent = new Date().getFullYear();
</script>
