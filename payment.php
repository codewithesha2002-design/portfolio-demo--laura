<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Razorpay Demo Payment</title>
    <link href="assets/vendor/bootstrap/css/bootstrap.min.css" rel="stylesheet">
</head>
<body class="p-4">

<div class="container">
    <h1 class="mb-4">Make a Payment</h1>

    <div class="mb-3">
        <label for="amount" class="form-label">Amount (INR)</label>
        <input type="number" id="amount" class="form-control" placeholder="100" step="0.01">
    </div>
    <!-- type="button" prevents the button from submitting a form and reloading the page -->
    <button id="payBtn" type="button" class="btn btn-primary">Pay Now</button>

    <div id="message" class="mt-3"></div>
</div>

<script src="https://checkout.razorpay.com/v1/checkout.js"></script>
<script>
    document.getElementById('payBtn').addEventListener('click', function () {
        const amount = parseFloat(document.getElementById('amount').value);
        if (isNaN(amount) || amount <= 0) {
            document.getElementById('message').innerText = 'Please enter a valid amount';
            return;
        }

        fetch('forms/order.php', {
            method: 'POST',
            headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
            body: 'amount=' + amount
        })
        .then(res => res.json())
        .then(order => {
            if (order.error) {
                document.getElementById('message').innerText = order.error;
                return;
            }

            const options = {
                key: 'YOUR_KEY_ID', // Enter the Key ID generated from the Dashboard
                amount: order.amount,
                currency: 'INR',
                name: 'Your Site Name',
                description: 'Purchase Description',
                order_id: order.id,
                handler: function (response){
                    // pass the response to server for verification
                    fetch('forms/verify.php', {
                        method: 'POST',
                        headers: { 'Content-Type': 'application/json' },
                        body: JSON.stringify(response)
                    })
                    .then(res => res.json())
                    .then(data => {
                        document.getElementById('message').innerText = JSON.stringify(data);
                    });
                },
                prefill: {
                    name: '',
                    email: '',
                    contact: ''
                },
                theme: {
                    color: '#3399cc'
                }
            };
            const rzp1 = new Razorpay(options);
            rzp1.open();
        })
        .catch(err => {
            document.getElementById('message').innerText = err;
        });
    });
</script>
</body>
</html>