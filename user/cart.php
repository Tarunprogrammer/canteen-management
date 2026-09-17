<?php
session_start();
include "../config/db.php";

$cart = $_SESSION['cart'] ?? [];
$cart_count = array_sum($cart);

// Login Check for Checkout
$checkout_url = isset($_SESSION['user_id']) ? "checkout.php" : "login.php";
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Your Cart | Smart Canteen</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <script src="https://unpkg.com/lucide@latest"></script>
    <style>
        @import url('https://fonts.googleapis.com/css2?family=Outfit:wght@300;400;500;600;700;800&display=swap');
        body { font-family: 'Outfit', sans-serif; background-color: #f8f7ff; color: #1e0033; }
        .glass-header { background: rgba(30, 0, 51, 0.95); backdrop-filter: blur(10px); }
        .qty-bump { animation: bump 0.2s ease-out; }
        @keyframes bump { 0%, 100% { transform: scale(1); } 50% { transform: scale(1.1); } }
    </style>
</head>
<body class="antialiased">

    <nav class="sticky top-0 z-50 glass-header text-white px-6 py-4 md:px-12 lg:px-24 flex items-center justify-between border-b border-white/5">
        <div class="flex items-center space-x-4">
            <a href="../index.php" class="flex items-center space-x-3">
                <div class="bg-violet-600 p-2 rounded-2xl rotate-3"><i data-lucide="zap" class="w-6 h-6 fill-current"></i></div>
                <h1 class="text-2xl font-black tracking-tighter uppercase leading-none">Smart<span class="text-violet-400">Canteen</span></h1>
            </a>
        </div>
        <div class="flex items-center space-x-4">
            <span id="nav-item-count" class="text-xs font-black uppercase tracking-widest text-violet-300"><?= $cart_count ?> Items</span>
        </div>
    </nav>

    <main class="max-w-7xl mx-auto px-6 py-12">
        <div class="flex flex-col lg:flex-row gap-12">
            
            <div class="lg:w-2/3">
                <div class="flex items-center justify-between mb-8">
                    <h2 class="text-3xl font-black uppercase tracking-tight">Your <span class="text-violet-600">Basket</span></h2>
                    <a href="../index.php" class="text-sm font-bold text-violet-600 hover:underline flex items-center">
                        <i data-lucide="chevron-left" class="w-4 h-4 mr-1"></i> Add more items
                    </a>
                </div>

                <div id="cart-container" class="space-y-6">
                    <?php if(empty($cart)): ?>
                        <div id="empty-cart-msg" class="bg-white rounded-[2.5rem] p-16 text-center border border-dashed border-violet-200">
                            <i data-lucide="shopping-cart" class="w-10 h-10 text-violet-300 mx-auto mb-6"></i>
                            <h3 class="text-xl font-black mb-2 uppercase">Your cart is empty</h3>
                            <a href="../index.php" class="bg-violet-600 text-white px-8 py-3 rounded-2xl font-black uppercase text-sm inline-block">Start Ordering</a>
                        </div>
                    <?php else: 
                        $grand_total = 0;
                        foreach($cart as $item_id => $qty):
                            $query = "SELECT * FROM menu_items WHERE id = $item_id";
                            $res = $conn->query($query);
                            if($row = $res->fetch_assoc()):
                                $subtotal = $row['price'] * $qty;
                                $grand_total += $subtotal;
                        ?>
                            <div id="row-<?= $item_id ?>" class="cart-item bg-white rounded-[2rem] p-4 md:p-6 border border-violet-100 flex flex-col md:flex-row items-center gap-6 shadow-sm">
                                <div class="w-20 h-20 rounded-2xl overflow-hidden bg-gray-50 flex-shrink-0">
                                    <img src="../assets/images/<?= $row['image'] ?>" class="w-full h-full object-cover" onerror="this.src='https://images.unsplash.com/photo-1546069901-ba9599a7e63c?w=200'">
                                </div>
                                
                                <div class="flex-grow text-center md:text-left">
                                    <h4 class="text-lg font-black uppercase tracking-tight"><?= htmlspecialchars($row['name']) ?></h4>
                                    <p class="text-gray-400 text-xs font-medium uppercase tracking-widest">₹<?= $row['price'] ?> / unit</p>
                                </div>

                                <div class="flex items-center space-x-6">
                                    <!-- Qty Control -->
                                    <div class="flex items-center space-x-3 bg-violet-50 p-1.5 rounded-2xl border border-violet-100">
                                        <button onclick="updateCartItem(<?= $item_id ?>, -1, <?= $row['price'] ?>)" class="w-8 h-8 rounded-xl flex items-center justify-center bg-white text-violet-600 shadow-sm active:scale-90">
                                            <i data-lucide="minus" class="w-4 h-4"></i>
                                        </button>
                                        <span id="qty-<?= $item_id ?>" class="text-sm font-black text-violet-900 w-4 text-center"><?= $qty ?></span>
                                        <button onclick="updateCartItem(<?= $item_id ?>, 1, <?= $row['price'] ?>)" class="w-8 h-8 rounded-xl flex items-center justify-center bg-violet-600 text-white shadow-sm active:scale-90">
                                            <i data-lucide="plus" class="w-4 h-4"></i>
                                        </button>
                                    </div>

                                    <div class="flex flex-col items-end min-w-[80px]">
                                        <span class="text-[10px] font-black uppercase text-gray-400 mb-1">Subtotal</span>
                                        <span class="text-xl font-black text-violet-600 tracking-tighter">₹<span id="sub-<?= $item_id ?>"><?= $subtotal ?></span></span>
                                    </div>

                                    <!-- Trash Icon using remove_item.php -->
                                    <button onclick="deleteItem(<?= $item_id ?>)" class="text-red-300 hover:text-red-500 transition-colors p-2">
                                        <i data-lucide="trash-2" class="w-5 h-5"></i>
                                    </button>
                                </div>
                            </div>
                        <?php endif; endforeach; ?>
                    <?php endif; ?>
                </div>
            </div>

            <div id="summary-section" class="lg:w-1/3 <?= empty($cart) ? 'hidden' : '' ?>">
                <div class="sticky top-32 bg-[#1e0033] text-white rounded-[2.5rem] p-8 shadow-2xl relative overflow-hidden">
                    <h3 class="text-2xl font-black mb-8 uppercase tracking-tight">Order <span class="text-violet-400">Summary</span></h3>
                    <div class="space-y-4 mb-8">
                        <div class="flex justify-between text-sm font-bold text-violet-200/60 uppercase tracking-widest">
                            <span>Subtotal</span>
                            <span>₹<span id="summary-subtotal"><?= $grand_total ?? 0 ?></span></span>
                        </div>
                        <div class="h-px bg-white/10 my-4"></div>
                        <div class="flex justify-between items-end">
                            <span class="text-xs font-black uppercase tracking-[0.2em] text-violet-400">Total Amount</span>
                            <span class="text-4xl font-black tracking-tighter">₹<span id="summary-total"><?= $grand_total ?? 0 ?></span></span>
                        </div>
                    </div>
                    <a href="<?= $checkout_url ?>" class="w-full bg-violet-500 hover:bg-violet-400 text-white py-4 rounded-2xl font-black uppercase tracking-widest text-sm flex items-center justify-center shadow-xl transition-all active:scale-95">
                        Proceed to Pay <i data-lucide="arrow-right" class="w-5 h-5 ml-2"></i>
                    </a>
                </div>
            </div>
        </div>
    </main>

    <script>
        lucide.createIcons();

        async function updateCartItem(itemId, change, price) {
            const qtyEl = document.getElementById(`qty-${itemId}`);
            const subEl = document.getElementById(`sub-${itemId}`);
            const currentQty = parseInt(qtyEl.innerText);
            
            if (currentQty + change <= 0) {
                deleteItem(itemId);
                return;
            }

            const newQty = currentQty + change;
            qtyEl.innerText = newQty;
            subEl.innerText = newQty * price;
            qtyEl.classList.add('qty-bump');
            setTimeout(() => qtyEl.classList.remove('qty-bump'), 200);

            recalculateTotal();

            // Uses your existing remove_from_cart.php for decrement
            const endpoint = change > 0 ? '../api/add_to_cart.php' : '../api/remove_from_cart.php';
            const response = await fetch(endpoint, {
                method: 'POST',
                headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
                body: `item_id=${itemId}`
            });
            const data = await response.json();
            if (data.success) {
                document.getElementById('nav-item-count').innerText = `${data.cart_count} Items`;
            }
        }

        async function deleteItem(itemId) {
            const row = document.getElementById(`row-${itemId}`);
            row.style.opacity = '0.5';
            row.style.pointerEvents = 'none';

            // Now using remove_item.php as requested
            const response = await fetch('../api/remove_item.php', {
                method: 'POST',
                headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
                body: `item_id=${itemId}`
            });
            const data = await response.json();

            if (data.success) {
                row.remove();
                document.getElementById('nav-item-count').innerText = `${data.cart_count} Items`;
                recalculateTotal();
                if (data.cart_count === 0) location.reload();
            }
        }

        function recalculateTotal() {
            let grandTotal = 0;
            document.querySelectorAll('[id^="sub-"]').forEach(el => {
                grandTotal += parseInt(el.innerText);
            });
            const subtotalEl = document.getElementById('summary-subtotal');
            const totalEl = document.getElementById('summary-total');
            if(subtotalEl) subtotalEl.innerText = grandTotal;
            if(totalEl) totalEl.innerText = grandTotal;
        }
    </script>
</body>
</html>