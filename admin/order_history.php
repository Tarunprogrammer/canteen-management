<?php
session_start();
include "../config/db.php";

if(!isset($_SESSION['admin_id'])){
    header("Location: login.php");
    exit();
}

$history = $conn->query("SELECT o.id, o.updated_at, u.name as user_name, SUM(mi.price * oi.quantity) as real_total_price, GROUP_CONCAT(CONCAT(mi.name, ' x', oi.quantity) SEPARATOR ', ') as items_list
                        FROM orders o 
                        JOIN users u ON o.user_id = u.id 
                        JOIN order_items oi ON o.id = oi.order_id
                        JOIN menu_items mi ON oi.item_id = mi.id
                        WHERE o.status = 'completed' GROUP BY o.id ORDER BY o.updated_at DESC");
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Order History | Smart Canteen</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <script src="https://unpkg.com/lucide@latest"></script>
    <style>
        @import url('https://fonts.googleapis.com/css2?family=Outfit:wght@300;400;500;600;700;800&display=swap');
        body { font-family: 'Outfit', sans-serif; }
    </style>
</head>
<body class="bg-gray-50 text-gray-900">

    <div class="flex h-screen overflow-hidden">
        
        <?php include "admin_sidebar.php"; ?>

        <main class="flex-1 flex flex-col overflow-hidden">
            <header class="flex items-center justify-between h-16 px-6 bg-white border-b border-gray-200">
                <div class="flex items-center">
                    <button onclick="toggleSidebar()" class="p-2 mr-4 text-gray-600 rounded-md lg:hidden hover:bg-gray-100">
                        <i data-lucide="menu" class="w-6 h-6"></i>
                    </button>
                    <h2 class="text-xl font-bold text-gray-800 tracking-tight uppercase">Order History</h2>
                </div>
            </header>

            <div class="flex-1 overflow-x-hidden overflow-y-auto bg-gray-50 p-6">
                <div class="max-w-5xl mx-auto space-y-4">
                    <h3 class="text-[10px] font-black text-gray-400 uppercase tracking-widest mb-6">Past Prepared Orders</h3>

                    <?php if ($history && $history->num_rows > 0): ?>
                        <div class="grid grid-cols-1 gap-4">
                            <?php while($order = $history->fetch_assoc()): ?>
                                <div class="bg-white p-5 rounded-2xl border border-gray-100 shadow-sm flex flex-col md:flex-row md:items-center justify-between gap-4">
                                    <div class="flex items-center space-x-6">
                                        <div class="text-2xl font-black text-slate-200">#<?= $order['id'] ?></div>
                                        <div>
                                            <p class="font-bold text-slate-800"><?= htmlspecialchars($order['user_name']) ?></p>
                                            <p class="text-[9px] text-gray-400 uppercase tracking-widest font-black"><?= date('h:i A, d M', strtotime($order['updated_at'])) ?></p>
                                        </div>
                                    </div>
                                    <div class="flex-1 md:mx-10 bg-gray-50/50 px-4 py-3 rounded-xl text-xs text-gray-600 font-medium">
                                        <?= htmlspecialchars($order['items_list']) ?>
                                    </div>
                                    <div class="text-right">
                                        <p class="text-lg font-black text-slate-900">₹<?= number_format($order['real_total_price'], 2) ?></p>
                                        <span class="text-[9px] font-black uppercase text-green-500 bg-green-50 px-2 py-1 rounded-md tracking-widest">Completed</span>
                                    </div>
                                </div>
                            <?php endwhile; ?>
                        </div>
                    <?php else: ?>
                        <div class="text-center py-24 bg-white rounded-[3rem] border-2 border-dashed border-gray-100">
                            <i data-lucide="archive" class="w-16 h-16 text-gray-100 mx-auto mb-4"></i>
                            <p class="text-gray-300 text-xs font-black uppercase tracking-widest">No history found.</p>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
        </main>
    </div>

    <script>lucide.createIcons();</script>
</body>
</html>