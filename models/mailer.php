<?php
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

require_once __DIR__ . '/../vendor/autoload.php';

function sendWelcomeEmail(string $toEmail, string $userName): bool
{
    $mail = new PHPMailer(true);

    try {
        $mail->isSMTP();
        $mail->Host       = 'smtp.gmail.com';
        $mail->SMTPAuth   = true;
        $mail->Username   = 'quench.store.sg@gmail.com';
        $mail->Password   = 'zqjg bfwb fynp kuft';
        $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
        $mail->Port       = 587;

        $mail->setFrom('quench.store.sg@gmail.com', 'QUENCH');
        $mail->addAddress($toEmail, $userName);
        $mail->isHTML(true);
        $mail->Subject = 'Welcome to QUENCH - Here\'s Your 10% Off!';
$mail->Body = '
<!DOCTYPE html>
<html>
<head><meta charset="UTF-8"></head>
<body style="margin:0;padding:0;background:#f5f5f5;font-family:Arial,sans-serif;">
  <table width="100%" cellpadding="0" cellspacing="0" style="background:#f5f5f5;padding:40px 0;">
    <tr><td align="center">
      <table width="500" cellpadding="0" cellspacing="0" style="background:#ffffff;border-radius:16px;overflow:hidden;box-shadow:0 4px 24px rgba(0,0,0,0.08);">

        <!-- Header -->
        <tr>
          <td style="background:#111;padding:32px 40px;text-align:center;">
            <h1 style="margin:0;font-size:28px;letter-spacing:6px;color:#fff;font-family:Arial,sans-serif;">QUENCH</h1>
          </td>
        </tr>

        <!-- Body -->
        <tr>
          <td style="padding:40px;">
            <h2 style="margin:0 0 8px;font-size:22px;color:#111;">Welcome, ' . htmlspecialchars($userName) . '!</h2>
            <p style="font-size:15px;color:#888;margin:0 0 28px;line-height:1.6;">Thanks for joining QUENCH. As promised, here is your exclusive discount code for your first order:</p>

            <!-- Code box -->
            <table width="100%" cellpadding="0" cellspacing="0">
              <tr><td align="center" style="padding:24px 0;">
                <div style="display:inline-block;background:#fff5f0;border:2px dashed #ff6b35;border-radius:12px;padding:18px 40px;">
                  <span style="font-size:32px;font-weight:800;color:#ff6b35;letter-spacing:4px;">QUENCH10</span>
                </div>
              </td></tr>
            </table>

            <p style="font-size:15px;color:#555;line-height:1.6;margin:24px 0 0;text-align:center;">Use it at checkout to get <strong style="color:#111;">10% off</strong> your first purchase.</p>

            <!-- CTA button -->
            <table width="100%" cellpadding="0" cellspacing="0">
              <tr><td align="center" style="padding:32px 0 8px;">
                <a href="http://35.212.142.222/pages/products.php" style="display:inline-block;background:#111;color:#fff;text-decoration:none;padding:14px 36px;border-radius:50px;font-size:14px;font-weight:700;letter-spacing:1px;">SHOP NOW</a>
              </td></tr>
            </table>
          </td>
        </tr>

        <!-- Footer -->
        <tr>
          <td style="background:#fafafa;padding:24px 40px;border-top:1px solid #eee;text-align:center;">
            <p style="margin:0;font-size:12px;color:#bbb;line-height:1.6;">You received this email because you signed up at QUENCH.<br>If this wasn\'t you, please ignore this email.</p>
          </td>
        </tr>

      </table>
    </td></tr>
  </table>
</body>
</html>';
        $mail->send();
        return true;
    } catch (Exception $e) {
        error_log('Welcome email failed: ' . $mail->ErrorInfo);
        return false;
    }
}

function sendNewsletterConfirmation(string $toEmail): bool
{
    $mail = new PHPMailer(true);

    try {
        $mail->isSMTP();
        $mail->Host       = 'smtp.gmail.com';
        $mail->SMTPAuth   = true;
        $mail->Username   = 'quench.store.sg@gmail.com';
        $mail->Password   = 'zqjg bfwb fynp kuft';
        $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
        $mail->Port       = 587;

        $mail->setFrom('quench.store.sg@gmail.com', 'QUENCH');
        $mail->addAddress($toEmail);
        $mail->isHTML(true);
        $mail->Subject = 'You\'re In! Welcome to the QUENCH Newsletter';
        $mail->Body = '
<!DOCTYPE html>
<html>
<head><meta charset="UTF-8"></head>
<body style="margin:0;padding:0;background:#f5f5f5;font-family:Arial,sans-serif;">
  <table width="100%" cellpadding="0" cellspacing="0" style="background:#f5f5f5;padding:40px 0;">
    <tr><td align="center">
      <table width="500" cellpadding="0" cellspacing="0" style="background:#ffffff;border-radius:16px;overflow:hidden;box-shadow:0 4px 24px rgba(0,0,0,0.08);">

        <!-- Header -->
        <tr>
          <td style="background:#111;padding:32px 40px;text-align:center;">
            <h1 style="margin:0;font-size:28px;letter-spacing:6px;color:#fff;font-family:Arial,sans-serif;">QUENCH</h1>
          </td>
        </tr>

        <!-- Body -->
        <tr>
          <td style="padding:40px;">
            <h2 style="margin:0 0 8px;font-size:22px;color:#111;">You\'re subscribed!</h2>
            <p style="font-size:15px;color:#555;margin:0 0 24px;line-height:1.6;">Thanks for signing up for the QUENCH newsletter. You\'ll be the first to hear about new drops, exclusive deals, and limited-time offers.</p>

            <p style="font-size:15px;color:#555;line-height:1.6;margin:0 0 0;text-align:center;">In the meantime, check out what\'s new:</p>

            <!-- CTA button -->
            <table width="100%" cellpadding="0" cellspacing="0">
              <tr><td align="center" style="padding:32px 0 8px;">
                <a href="http://35.212.142.222/pages/products.php" style="display:inline-block;background:#111;color:#fff;text-decoration:none;padding:14px 36px;border-radius:50px;font-size:14px;font-weight:700;letter-spacing:1px;">BROWSE DRINKS</a>
              </td></tr>
            </table>
          </td>
        </tr>

        <!-- Footer -->
        <tr>
          <td style="background:#fafafa;padding:24px 40px;border-top:1px solid #eee;text-align:center;">
            <p style="margin:0;font-size:12px;color:#bbb;line-height:1.6;">You received this email because you subscribed to the QUENCH newsletter.<br>If this wasn\'t you, please ignore this email.</p>
          </td>
        </tr>

      </table>
    </td></tr>
  </table>
</body>
</html>';
        $mail->send();
        return true;
    } catch (Exception $e) {
        error_log('Newsletter confirmation email failed: ' . $mail->ErrorInfo);
        return false;
    }
}

function sendNewsletterWithCode(string $toEmail): bool
{
    $mail = new PHPMailer(true);

    try {
        $mail->isSMTP();
        $mail->Host       = 'smtp.gmail.com';
        $mail->SMTPAuth   = true;
        $mail->Username   = 'quench.store.sg@gmail.com';
        $mail->Password   = 'zqjg bfwb fynp kuft';
        $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
        $mail->Port       = 587;

        $mail->setFrom('quench.store.sg@gmail.com', 'QUENCH');
        $mail->addAddress($toEmail);
        $mail->isHTML(true);
        $mail->Subject = 'Your 10% Discount Code is Here!';
        $mail->Body = '
<!DOCTYPE html>
<html>
<head><meta charset="UTF-8"></head>
<body style="margin:0;padding:0;background:#f5f5f5;font-family:Arial,sans-serif;">
  <table width="100%" cellpadding="0" cellspacing="0" style="background:#f5f5f5;padding:40px 0;">
    <tr><td align="center">
      <table width="500" cellpadding="0" cellspacing="0" style="background:#ffffff;border-radius:16px;overflow:hidden;box-shadow:0 4px 24px rgba(0,0,0,0.08);">

        <!-- Header -->
        <tr>
          <td style="background:#111;padding:32px 40px;text-align:center;">
            <h1 style="margin:0;font-size:28px;letter-spacing:6px;color:#fff;font-family:Arial,sans-serif;">QUENCH</h1>
          </td>
        </tr>

        <!-- Body -->
        <tr>
          <td style="padding:40px;">
            <h2 style="margin:0 0 8px;font-size:22px;color:#111;">Thanks for subscribing!</h2>
            <p style="font-size:15px;color:#555;margin:0 0 28px;line-height:1.6;">As promised, here is your exclusive discount code. Use it at checkout to save on your first order:</p>

            <!-- Code box -->
            <table width="100%" cellpadding="0" cellspacing="0">
              <tr><td align="center" style="padding:24px 0;">
                <div style="display:inline-block;background:#fff5f0;border:2px dashed #ff6b35;border-radius:12px;padding:18px 40px;">
                  <span style="font-size:32px;font-weight:800;color:#ff6b35;letter-spacing:4px;">QUENCH10</span>
                </div>
              </td></tr>
            </table>

            <p style="font-size:15px;color:#555;line-height:1.6;margin:24px 0 0;text-align:center;">Use it at checkout to get <strong style="color:#111;">10% off</strong> your first purchase. Create an account to start shopping!</p>

            <!-- CTA button -->
            <table width="100%" cellpadding="0" cellspacing="0">
              <tr><td align="center" style="padding:32px 0 8px;">
                <a href="http://35.212.142.222/auth/register.php" style="display:inline-block;background:#111;color:#fff;text-decoration:none;padding:14px 36px;border-radius:50px;font-size:14px;font-weight:700;letter-spacing:1px;">CREATE ACCOUNT</a>
              </td></tr>
            </table>
          </td>
        </tr>

        <!-- Footer -->
        <tr>
          <td style="background:#fafafa;padding:24px 40px;border-top:1px solid #eee;text-align:center;">
            <p style="margin:0;font-size:12px;color:#bbb;line-height:1.6;">You received this email because you subscribed at QUENCH.<br>If this wasn\'t you, please ignore this email.</p>
          </td>
        </tr>

      </table>
    </td></tr>
  </table>
</body>
</html>';
        $mail->send();
        return true;
    } catch (Exception $e) {
        error_log('Newsletter discount email failed: ' . $mail->ErrorInfo);
        return false;
    }
}