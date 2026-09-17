<?php
session_start();
include "../config/db.php";

// 1. Redirect if cart is empty or user not logged in
if (!isset($_SESSION['cart']) || empty($_SESSION['cart'])) {
    header("Location: ../index.php");
    exit();
}

if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

$cart = $_SESSION['cart'];
$grand_total = 0;
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Checkout | Smart Canteen</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <script src="https://unpkg.com/lucide@latest"></script>
    <style>
        @import url('https://fonts.googleapis.com/css2?family=Outfit:wght@300;400;500;600;700;800&display=swap');
        body { font-family: 'Outfit', sans-serif; background-color: #f8f7ff; color: #1e0033; }
        .glass-header { background: rgba(30, 0, 51, 0.95); backdrop-filter: blur(10px); }
        .payment-card { transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1); cursor: pointer; border: 2px solid transparent; }
        .payment-radio:checked + .payment-card { border-color: #8b5cf6; background-color: #f5f3ff; transform: translateY(-4px); box-shadow: 0 10px 25px -5px rgba(139, 92, 246, 0.2); }
        .payment-radio:checked + .payment-card .check-icon { display: flex; }
        
        /* Modal Animation */
        .modal-overlay { background: rgba(15, 0, 26, 0.85); backdrop-filter: blur(12px); }
        @keyframes modalEnter {
            from { opacity: 0; transform: scale(0.9) translateY(20px); }
            to { opacity: 1; transform: scale(1) translateY(0); }
        }
        .animate-modal { animation: modalEnter 0.4s cubic-bezier(0.16, 1, 0.3, 1) forwards; }
    </style>
</head>
<body class="antialiased">

    <!-- Pre-Confirmation Modal -->
    <div id="confirm-modal" class="fixed inset-0 z-[110] hidden flex items-center justify-center p-6 modal-overlay">
        <div class="bg-white rounded-[3rem] p-10 max-w-sm w-full text-center shadow-2xl animate-modal border border-white/20">
            <div class="w-20 h-20 bg-violet-100 rounded-3xl flex items-center justify-center mx-auto mb-6">
                <i data-lucide="shopping-basket" class="w-10 h-10 text-violet-600"></i>
            </div>
            <h3 class="text-2xl font-black uppercase tracking-tight mb-2">Confirm <span class="text-violet-600">Order?</span></h3>
            <p class="text-gray-400 text-sm font-medium leading-relaxed mb-8">
                Are you sure you want to place this order? It will be sent to the canteen immediately.
            </p>
            <div class="flex flex-col gap-3">
                <button id="final-confirm-btn" class="w-full bg-violet-600 text-white py-4 rounded-2xl font-black uppercase tracking-widest text-xs shadow-lg shadow-violet-200 hover:bg-[#1e0033] transition-all">
                    Yes, Place Order
                </button>
                <button onclick="closeModal('confirm-modal')" class="text-xs font-black uppercase tracking-widest text-gray-400 hover:text-red-500 transition-colors py-2">
                    Cancel
                </button>
            </div>
        </div>
    </div>

    <!-- Header -->
    <nav class="sticky top-0 z-50 glass-header text-white px-6 py-4 md:px-12 lg:px-24 flex items-center justify-between border-b border-white/5">
        <div class="flex items-center space-x-4">
            <a href="cart.php" class="flex items-center space-x-3">
                <div class="bg-violet-600 p-2 rounded-2xl rotate-3"><i data-lucide="zap" class="w-6 h-6 text-white fill-current"></i></div>
                <h1 class="text-2xl font-black tracking-tighter uppercase leading-none">Smart<span class="text-violet-400">Canteen</span></h1>
            </a>
        </div>
    </nav>

    <main class="max-w-7xl mx-auto px-6 py-12">
        <form id="checkout-form" action="../api/place_order.php" method="POST">
            <div class="flex flex-col lg:flex-row gap-12">
                <div class="lg:w-2/3">
                    <h2 class="text-3xl font-black uppercase tracking-tight mb-8">Secure <span class="text-violet-600">Payment</span></h2>
                    
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mb-10">
                        <label class="relative block">
                            <input type="radio" name="payment_method" value="card" class="payment-radio hidden" checked>
                            <div class="payment-card bg-white p-6 rounded-[2rem] border-violet-100 shadow-sm flex items-start space-x-4">
                                <div class="bg-violet-100 p-3 rounded-xl text-violet-600"><i data-lucide="credit-card" class="w-6 h-6"></i></div>
                                <div class="flex-1">
                                    <h4 class="font-black uppercase text-sm tracking-widest mb-1">Card Payment</h4>
                                    <p class="text-gray-400 text-[10px] font-medium leading-relaxed uppercase">Visa, Mastercard, RuPay</p>
                                </div>
                                <div class="check-icon hidden bg-violet-600 text-white p-1 rounded-full"><i data-lucide="check" class="w-3 h-3"></i></div>
                            </div>
                        </label>
                        <label class="relative block">
                            <input type="radio" name="payment_method" value="upi" class="payment-radio hidden">
                            <div class="payment-card bg-white p-6 rounded-[2rem] border-violet-100 shadow-sm flex items-start space-x-4">
                                <div class="bg-blue-100 p-3 rounded-xl text-blue-600"><i data-lucide="qr-code" class="w-6 h-6"></i></div>
                                <div class="flex-1">
                                    <h4 class="font-black uppercase text-sm tracking-widest mb-1">UPI / QR Code</h4>
                                    <p class="text-gray-400 text-[10px] font-medium leading-relaxed uppercase">GPay, PhonePe, Paytm</p>
                                </div>
                                <div class="check-icon hidden bg-violet-600 text-white p-1 rounded-full"><i data-lucide="check" class="w-3 h-3"></i></div>
                            </div>
                        </label>
                    </div>
                </div>

                <div class="lg:w-1/3">
                    <div class="sticky top-32 bg-[#1e0033] text-white rounded-[2.5rem] p-8 shadow-2xl overflow-hidden relative">
                        <h3 class="text-2xl font-black mb-8 uppercase tracking-tight">Final <span class="text-violet-400">Total</span></h3>
                        <div class="space-y-4 mb-8">
                            <?php 
                            foreach($cart as $item_id => $qty):
                                $q = "SELECT name, price FROM menu_items WHERE id = $item_id";
                                $res = $conn->query($q);
                                if($row = $res->fetch_assoc()):
                                    $subtotal = $row['price'] * $qty;
                                    $grand_total += $subtotal;
                            ?>
                                <div class="flex justify-between items-center text-xs">
                                    <span class="text-violet-300/60 font-bold uppercase tracking-widest"><?= htmlspecialchars($row['name']) ?> x<?= $qty ?></span>
                                    <span class="font-black">₹<?= $subtotal ?></span>
                                </div>
                            <?php endif; endforeach; ?>
                            <div class="h-px bg-white/10 my-6"></div>
                            <div class="flex justify-between items-end">
                                <span class="text-xs font-black uppercase tracking-[0.2em] text-violet-400">Grand Total</span>
                                <span class="text-4xl font-black tracking-tighter">₹<?= $grand_total ?></span>
                            </div>
                        </div>

                        <button type="submit" id="confirm-btn" class="w-full bg-violet-500 hover:bg-violet-400 text-white py-5 rounded-2xl font-black uppercase tracking-widest text-sm flex items-center justify-center shadow-xl transition-all active:scale-95 group">
                            Confirm Order <i data-lucide="check-circle" class="w-5 h-5 ml-3 group-hover:rotate-12 transition-transform"></i>
                        </button>
                    </div>
                </div>
            </div>
        </form>
    </main>

    <script>
        lucide.createIcons();
        
        const form = document.getElementById('checkout-form');
        const confirmModal = document.getElementById('confirm-modal');
        const finalConfirmBtn = document.getElementById('final-confirm-btn');

        // Step 1: User clicks "Confirm Order" button on form
        form.addEventListener('submit', (e) => {
            e.preventDefault();
            confirmModal.classList.remove('hidden');
        });

        // Step 2: User clicks "Yes, Place Order" -> directly submits form
        finalConfirmBtn.addEventListener('click', () => {
            finalConfirmBtn.disabled = true;
            finalConfirmBtn.innerText = "Processing...";
            form.submit(); // Final submission to place_order.php
        });

        function closeModal(id) {
            document.getElementById(id).classList.add('hidden');
        }
    </script>
</body>
</html>