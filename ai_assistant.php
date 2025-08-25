<?php
// ai_assistant.php
header('Content-Type: application/json');
require_once 'config.php'; // must define $conn (PDO)

function h($s){ return htmlspecialchars((string)$s, ENT_QUOTES, 'UTF-8'); }
function out($arr){ echo json_encode($arr); exit; }

$raw = file_get_contents('php://input');
$in  = json_decode($raw, true) ?: [];
$action = $in['action'] ?? '';

try {
  switch($action){

    case 'search_products': {
      $q = trim($in['q'] ?? '');
      if($q===''){ out(['message'=>'Type something to search.']); }
      $stmt = $conn->prepare("SELECT id,name,price,image,quantity FROM products WHERE name LIKE ? OR category LIKE ? ORDER BY id DESC LIMIT 8");
      $like = '%'.$q.'%';
      $stmt->execute([$like,$like]);
      if($stmt->rowCount()===0){ out(['html'=>'No matching products. Try a different term.']); }
      $html = '<div><strong>Results:</strong><ul class="list-disc pl-5">';
      while($r = $stmt->fetch(PDO::FETCH_ASSOC)){
        $html .= '<li><a href="view_page.php?pid='.((int)$r['id']).'">'.h($r['name']).'</a> — Rs '.number_format((float)$r['price'],2).' (qty: '.(int)$r['quantity'].')</li>';
      }
      $html .= '</ul></div>';
      out(['html'=>$html]);
    }

    case 'order_status': {
      $oid = trim($in['order_id'] ?? '');
      if($oid===''){ out(['message'=>'Please provide an order ID (e.g., KP-2025-00123).']); }
      // Adjust to your schema: try ID or order_code
      $q = $conn->prepare("SELECT id, payment_status, total_price, placed_on, name FROM orders WHERE id=? OR order_code=? LIMIT 1");
      $q->execute([$oid, $oid]);
      if(!$row = $q->fetch(PDO::FETCH_ASSOC)){ out(['html'=>'Order not found. Double-check the ID.']); }
      $html = '<div><strong>Order #'.h($row['id']).'</strong><br>'.
              'Customer: '.h($row['name']).'<br>'.
              'Total: Rs '.number_format((float)$row['total_price'],2).'<br>'.
              'Placed on: '.h($row['placed_on']).'<br>'.
              'Status: <span class="px-2 py-0.5 rounded border" style="border-color:rgba(0,0,0,.12)">'.h($row['payment_status']).'</span></div>';
      out(['html'=>$html]);
    }

    case 'low_stock': {
      $stmt = $conn->query("SELECT id,name,quantity FROM products WHERE quantity <= 10 ORDER BY quantity ASC LIMIT 10");
      if($stmt->rowCount()===0){ out(['html'=>'All good — no low stock items (≤10) right now.']); }
      $html = '<div><strong>Low stock items:</strong><ul class="list-disc pl-5">';
      while($r = $stmt->fetch(PDO::FETCH_ASSOC)){
        $html .= '<li>'.h($r['name']).' — qty: <strong>'.(int)$r['quantity'].'</strong></li>';
      }
      $html .= '</ul></div>';
      out(['html'=>$html]);
    }

    case 'top_products': {
      $stmt = $conn->query("SELECT id,name,price FROM products ORDER BY id DESC LIMIT 6");
      $html = '<div><strong>Top picks:</strong><ul class="list-disc pl-5">';
      while($r = $stmt->fetch(PDO::FETCH_ASSOC)){
        $html.='<li><a href="view_page.php?pid='.(int)$r['id'].'">'.h($r['name']).'</a> — Rs '.number_format((float)$r['price'],2).'</li>';
      }
      $html .= '</ul></div>';
      out(['html'=>$html]);
    }

    case 'latest_deals': {
      $now = date('Y-m-d H:i:s');
      $sql = "SELECT p.id,p.name,p.price,pr.promo_price,pr.discount_percent
              FROM promotions pr JOIN products p ON p.id=pr.product_id
              WHERE pr.active=1 AND (pr.starts_at IS NULL OR pr.starts_at<=?) AND (pr.ends_at IS NULL OR pr.ends_at>=?)
              ORDER BY pr.created_at DESC LIMIT 6";
      $s = $conn->prepare($sql); $s->execute([$now,$now]);
      if($s->rowCount()===0){ out(['html'=>'No active deals right now.']); }
      $html = '<div><strong>Latest deals:</strong><ul class="list-disc pl-5">';
      while($r = $s->fetch(PDO::FETCH_ASSOC)){
        $price = (float)$r['price'];
        $pp    = $r['promo_price'] ? (float)$r['promo_price'] : ($r['discount_percent'] ? max(0,$price*(1-((float)$r['discount_percent']/100))) : $price);
        $html .= '<li><a href="view_page.php?pid='.(int)$r['id'].'">'.h($r['name']).'</a> — <del>Rs '.number_format($price,2).'</del> → <strong>Rs '.number_format($pp,2).'</strong></li>';
      }
      $html .= '</ul></div>';
      out(['html'=>$html]);
    }

    case 'reservation_parse': {
      $text = strtolower(trim($in['text'] ?? ''));
      preg_match('/(\d+)\s*(people|persons|pax|guests|for)/', $text, $m1);
      preg_match('/(\d{1,2})(?:\:(\d{2}))?\s*(am|pm)?/', $text, $m2);
      $size = isset($m1[1]) ? (int)$m1[1] : null;
      $time = isset($m2[0]) ? $m2[0] : null;

      $html = '<div><strong>Reservation draft</strong><br>';
      $html .= 'Party size: '.($size ? $size : 'not specified').'<br>';
      $html .= 'Time: '.($time ? h($time) : 'not specified').'<br>';
      $html .= 'Please <a href="reservation.php">complete your reservation</a> to confirm.</div>';
      out(['html'=>$html]);
    }

    case 'faq': {
      $html = '
      <div class="space-y-2">
        <div><strong>Do you ship island-wide?</strong><br>Yes, standard 2–4 business days.</div>
        <div><strong>Can I customize an order?</strong><br>Yes! Use the product page “Custom Order” or contact us.</div>
        <div><strong>How do I track my order?</strong><br>Type “Track order KP-2025-00123”.</div>
        <div><strong>What payment methods?</strong><br>Cash on delivery, card, and bank transfer.</div>
      </div>';
      out(['html'=>$html]);
    }

    case 'faq_query': {
      $q = strtolower(trim($in['q'] ?? ''));
      if(strpos($q,'ship')!==false)  out(['message'=>'Yes, we ship island-wide (2–4 business days).']);
      if(strpos($q,'custom')!==false)out(['message'=>'Yes, custom orders are welcome. Share details on the product page or via contact.']);
      if(strpos($q,'pay')!==false)   out(['message'=>'We accept Cash on Delivery, card, and bank transfer.']);
      out(['message'=>"I didn't find that in FAQs — try asking about products, orders, or reservations."]);
    }

    default: out(['message'=>'Tell me what you need: search products, order status, or reservations.']);
  }
} catch(Throwable $e){
  out(['message'=>'Assistant error. Please try again.']);
}
