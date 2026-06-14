<?php
if ($_SERVER["REQUEST_METHOD"] !== "POST") {
    http_response_code(405);
    exit("Method not allowed");
}

// Validare hCaptcha
$hcaptcha_secret = "ES_fdac7130b6c34eafa3fbb3400fcf3bc6";
$hcaptcha_response = $_POST["h-captcha-response"] ?? "";

$verify = file_get_contents("https://hcaptcha.com/siteverify?secret={$hcaptcha_secret}&response={$hcaptcha_response}");
$verify_data = json_decode($verify, true);

if (!$verify_data["success"]) {
    http_response_code(400);
    echo json_encode(["status" => "error", "message" => "Verificarea captcha a esuat. Te rugam sa incerci din nou."]);
    exit;
}

// Sanitizare date
function clean($data) {
    return htmlspecialchars(strip_tags(trim($data)));
}

$nume     = clean($_POST["Nume"] ?? "");
$telefon  = clean($_POST["Telefon"] ?? "");
$email    = clean($_POST["Email"] ?? "");
$adresa   = clean($_POST["Adresa imobil"] ?? "");
$nrap     = clean($_POST["Numar apartamente"] ?? "");
$subiect  = clean($_POST["Subiect"] ?? "");
$mesaj    = clean($_POST["Mesaj"] ?? "");

// Validare campuri obligatorii
if (empty($nume) || empty($telefon) || empty($email) || empty($adresa) || empty($subiect) || empty($mesaj)) {
    http_response_code(400);
    echo json_encode(["status" => "error", "message" => "Te rugam sa completezi toate campurile obligatorii."]);
    exit;
}

// Validare email
if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
    http_response_code(400);
    echo json_encode(["status" => "error", "message" => "Adresa de email nu este valida."]);
    exit;
}

// Trimitere email
$to      = "info@blocexpertsrl.ro";
$subject = "Solicitare noua: " . $subiect;
$headers = "From: noreply@blocexpertsrl.ro\r\n";
$headers .= "Reply-To: " . $email . "\r\n";
$headers .= "Content-Type: text/html; charset=UTF-8\r\n";
$headers .= "MIME-Version: 1.0\r\n";

$body = "
<!DOCTYPE html>
<html>
<body style='font-family: Arial, sans-serif; max-width: 600px; margin: 0 auto; padding: 20px;'>
  <div style='background: #1E2A5E; padding: 24px; border-radius: 8px 8px 0 0;'>
    <h2 style='color: #fff; margin: 0; font-size: 20px;'>Solicitare noua — Bloc Expert</h2>
  </div>
  <div style='background: #f5f6f8; padding: 24px; border-radius: 0 0 8px 8px;'>
    <table style='width: 100%; border-collapse: collapse;'>
      <tr><td style='padding: 8px 0; font-weight: bold; color: #3D4460; width: 40%;'>Nume:</td><td style='padding: 8px 0; color: #1A1A2E;'>{$nume}</td></tr>
      <tr><td style='padding: 8px 0; font-weight: bold; color: #3D4460;'>Telefon:</td><td style='padding: 8px 0; color: #1A1A2E;'>{$telefon}</td></tr>
      <tr><td style='padding: 8px 0; font-weight: bold; color: #3D4460;'>Email:</td><td style='padding: 8px 0; color: #1A1A2E;'>{$email}</td></tr>
      <tr><td style='padding: 8px 0; font-weight: bold; color: #3D4460;'>Adresa imobil:</td><td style='padding: 8px 0; color: #1A1A2E;'>{$adresa}</td></tr>
      <tr><td style='padding: 8px 0; font-weight: bold; color: #3D4460;'>Nr. apartamente:</td><td style='padding: 8px 0; color: #1A1A2E;'>{$nrap}</td></tr>
      <tr><td style='padding: 8px 0; font-weight: bold; color: #3D4460;'>Subiect:</td><td style='padding: 8px 0; color: #1A1A2E;'>{$subiect}</td></tr>
    </table>
    <div style='margin-top: 16px; padding: 16px; background: #fff; border-radius: 6px; border-left: 4px solid #2E3B7B;'>
      <p style='font-weight: bold; color: #3D4460; margin: 0 0 8px;'>Mesaj:</p>
      <p style='color: #1A1A2E; margin: 0; line-height: 1.6;'>{$mesaj}</p>
    </div>
    <p style='margin-top: 20px; font-size: 12px; color: #6B7280;'>Mesaj trimis de pe blocexpertsrl.ro</p>
  </div>
</body>
</html>
";

$sent = mail($to, $subject, $body, $headers);

if ($sent) {
    echo json_encode(["status" => "success", "message" => "Mesajul a fost trimis cu succes! Te vom contacta in cel mai scurt timp."]);
} else {
    http_response_code(500);
    echo json_encode(["status" => "error", "message" => "A aparut o eroare la trimiterea mesajului. Te rugam sa ne contactezi direct la info@blocexpertsrl.ro."]);
}
?>
