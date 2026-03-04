# Razorpay Integration Guide

This folder contains a simple PHP implementation for Razorpay checkout that supports
UPI, Netbanking, Google Pay, Paytm, credit/debit cards and other methods supported by
Razorpay’s standard checkout.

## Prerequisites

* A PHP‑capable web server (Apache/Nginx with PHP 7.2+).
* MySQL (or MariaDB) database for storing orders.
* Composer installed globally so we can pull in the Razorpay SDK.
* Razorpay account for obtaining API keys (test and live keys).

## Setup steps

1. **Database**
   - Create a database (e.g. `portfolio_demo`).
   - Run `forms/payment_schema.sql` against it to create the `payments` table.
   - Update `forms/db.php` with your DB hostname, user, password and database name.

2. **Dependencies**
   - From the repository root run:
     ```bash
     composer install
     ```
   - This will create `vendor/` with the Razorpay SDK and autoloader. Our PHP scripts
     already include `require_once __DIR__ . '/../vendor/autoload.php';`.

3. **API keys**
   - Insert your Razorpay `KEY_ID` and `KEY_SECRET` into `forms/order.php` and
     `forms/verify.php` (replace the placeholder strings).
   - Use test keys while developing; switch to live keys before going to production.

4. **Front end**
   - The demo page `payment.php` contains a simple form where the visitor can enter an
     amount and click "Pay Now" (or you may label your button "Buy"). **Important:**
     ensure the button has `type="button"` or your click handler calls
     `event.preventDefault()`; otherwise it may submit the surrounding form or act as
     a link and reload the current page (which is why you were being returned to the
     home page). Do not wrap the control in an `<a href="#">` or a form with an
     action URL. Place the button wherever needed (product page, pricing table,
     modal, etc.), and have the handler perform the `fetch('forms/order.php', …)` call
     directly, as shown in the example.
   - The Razorpay checkout widget (loaded from `https://checkout.razorpay.com/v1/checkout.js`)
     automatically detects the customer’s environment and presents **all supported
     payment methods**:
     * **UPI apps** – Google Pay, Paytm, PhonePe, Razorpay UPI, and any other UPI ID
       the user has installed on their device.
     * **Net‑banking** options for the banks you’ve enabled in your Razorpay dashboard.
     * **Credit/Debit cards** (Visa/MasterCard/RuPay, etc.).
     * **Wallets, EMI, and more** depending on your configuration.
   - No additional coding is required to show Paytm or Google Pay; as long as UPI is
     enabled in your dashboard, the corresponding buttons appear and the user can pay
     using their bank‑linked account. You can filter or preselect methods using the
     `options.method` parameter if you wish to restrict choices (see Razorpay docs).
   - A link to `payment.php` has been added to the navbar of `index.html`.

5. **Workflow**
   - When the user clicks the "Pay Now" or "Buy" button, client‑side JavaScript POSTs the amount (and
     optionally product ID/description) to `forms/order.php`. This script creates a Razorpay order via the API
     and records a `created` row in the database.
   - The front end opens the checkout widget using the returned `order_id`. The
     widget presents all enabled payment methods (cards, UPI apps like Google Pay/
     Paytm, netbanking, etc.) based on Razorpay's configuration.
   - After a successful payment the JavaScript `handler` sends the payment details
     (`razorpay_order_id`, `razorpay_payment_id`, `razorpay_signature`) to
     `forms/verify.php`.
   - `verify.php` recalculates the HMAC signature using the shared secret. If the
     signature matches, it updates the corresponding row to `paid` and stores the
     method (e.g. `upi`, `netbanking`) returned by the Razorpay API. If verification
     fails the row is marked `failed`.

6. **Customization**
   - You can add fields such as `customer_email`, `notes` etc. to the order creation
     payload and persist them with the record.
   - By default the checkout shows all methods; if you need to restrict to a subset
     pass `method`, `prefill`, or `notes` in the `options` object in the JS.
   - Store more details (customer ID, products, etc.) in the `payments` table as
     needed.

7. **Security notes**
   - Never expose the secret key or perform any sensitive logic in client‑side code.
   - Always verify the signature on the server; do not trust the front‑end response.
   - Use HTTPS in production to prevent MITM attacks.

8. **Testing**
   - Use Razorpay's test cards/UPI IDs (e.g. `4242 4242 4242 4242` for card, `test@upi`
     for UPI) to simulate various flows.
   - Try clicking the button from a mobile device with Google Pay/Paytm installed to
     confirm the appropriate UPI option shows up. Net‑banking and card forms appear
     automatically as well.
This completes a minimal, complete gateway integration with storage and verification.
Feel free to adapt the file locations and UI to fit your real website.