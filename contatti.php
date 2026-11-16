<?php
// Modulo contatti di drinedelaide.com: invia il messaggio alla casella della compagnia.
$destinatario = 'drinedelaide@gmail.com';
$mittente     = 'sito@drinedelaide.com'; // indirizzo del dominio: molti hosting rifiutano mittenti esterni

if ($_SERVER['REQUEST_METHOD'] !== 'POST') { header('Location: /'); exit; }

// Campo trappola: i bot lo compilano, le persone no
if (!empty($_POST['bot-field'])) { header('Location: /grazie.html'); exit; }

function pulisci($v, $max) {
  $v = trim((string)($v ?? ''));
  $v = str_replace(["\r", "\0"], '', $v);
  return mb_substr($v, 0, $max);
}

$nome      = pulisci($_POST['nome'] ?? '', 80);
$cognome   = pulisci($_POST['cognome'] ?? '', 80);
$email     = pulisci($_POST['email'] ?? '', 160);
$oggetto   = pulisci($_POST['oggetto'] ?? '', 150);
$messaggio = pulisci($_POST['messaggio'] ?? '', 5000);

if (!filter_var($email, FILTER_VALIDATE_EMAIL) || $messaggio === '' || empty($_POST['privacy'])) {
  header('Location: /?errore=1#contatti'); exit;
}

// Niente a capo negli header: blocca l'iniezione di destinatari extra
$nomeCompleto = trim(str_replace("\n", ' ', "$nome $cognome"));
$oggettoMail  = 'Sito web: ' . ($oggetto !== '' ? str_replace("\n", ' ', $oggetto) : 'nuovo messaggio');

$corpo  = "Nuovo messaggio dal modulo contatti di drinedelaide.com\n\n";
$corpo .= "Nome: " . ($nomeCompleto !== '' ? $nomeCompleto : '(non indicato)') . "\n";
$corpo .= "Email: $email\n";
$corpo .= "Oggetto: " . ($oggetto !== '' ? $oggetto : '(non indicato)') . "\n\n";
$corpo .= $messaggio . "\n";

$headers  = "From: Sito Drin e Delaide <$mittente>\r\n";
$headers .= "Reply-To: $email\r\n";
$headers .= "MIME-Version: 1.0\r\n";
$headers .= "Content-Type: text/plain; charset=UTF-8\r\n";

$ok = mail($destinatario, '=?UTF-8?B?' . base64_encode($oggettoMail) . '?=', $corpo, $headers, '-f' . $mittente);

header('Location: ' . ($ok ? '/grazie.html' : '/?errore=1#contatti'));
exit;
