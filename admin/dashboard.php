<?php
session_start();
include "../config/db.php";

/* ======================
ADMIN LOGIN CHECK
====================== */
// Using the session logic from your provided dashboard.php
if(!isset($_SESSION['admin_id'])){
    header("Location: login.php");
    exit();
}
$admin_id = $_SESSION['admin_id'];

/* ======================
SAFE DATABASE HELPERS
====================== */
function safeCount($conn, $sql) {
    $res = $conn->query($sql);
    if ($res) {
        $row = $res->fetch_assoc();
        return $row['total'] ?? 0;
    }
    return 0;
}

function safeFetchOrders($conn, $sql) {
    $data = [];
    $res = $conn->query($sql);
    if ($res && $res->num_rows > 0) {
        while($row = $res->fetch_assoc()){
            $order_id = $row['id'];
            // Fetch items for this specific order
            $items_sql = "SELECT mi.name, mi.price, oi.quantity 
                          FROM order_items oi 
                          JOIN menu_items mi ON oi.item_id = mi.id 
                          WHERE oi.order_id = $order_id";
            $items_res = $conn->query($items_sql);
            $items = [];
            if ($items_res && $items_res->num_rows > 0) {
                while($item_row = $items_res->fetch_assoc()){
                    $items[] = $item_row;
                }
            }
            $row['items'] = $items;
            $data[] = $row;
        }
    }
    return $data;
}

/* ======================
AJAX LOGIC (Updates & Polling)
====================== */
if($_SERVER['REQUEST_METHOD']=="POST" && isset($_POST['order_id']) && isset($_POST['status'])){
    $order_id = intval($_POST['order_id']);
    $status = mysqli_real_escape_string($conn, $_POST['status']);
    $response = ['success' => false];
    
    $stmt = $conn->prepare("UPDATE orders SET status=?, updated_at=NOW() WHERE id=?");
    if ($stmt) {
        $stmt->bind_param("si", $status, $order_id);
        if($stmt->execute()) $response['success'] = true;
    }
    header("Content-Type: application/json"); echo json_encode($response); exit();
}

if($_SERVER['REQUEST_METHOD']=="GET" && isset($_GET['action']) && $_GET['action'] == 'fetch_updates'){
    $pending = safeFetchOrders($conn, "SELECT orders.id, orders.payment_method, orders.created_at, users.name FROM orders LEFT JOIN users ON orders.user_id = users.id WHERE LOWER(orders.status) = 'pending' ORDER BY orders.created_at DESC");
    $preparing = safeFetchOrders($conn, "SELECT orders.id, orders.payment_method, orders.created_at, users.name FROM orders LEFT JOIN users ON orders.user_id = users.id WHERE LOWER(orders.status) = 'accepted' ORDER BY orders.updated_at DESC");
    $stats = [
        'total_orders' => safeCount($conn, "SELECT COUNT(*) as total FROM orders"),
        'pending_count' => count($pending),
        'preparing_count' => count($preparing),
        'total_users' => safeCount($conn, "SELECT COUNT(*) as total FROM users")
    ];
    header("Content-Type: application/json"); echo json_encode(['pending' => $pending, 'preparing' => $preparing, 'stats' => $stats]); exit();
}

/* Initial Load Data */
$pending_orders = safeFetchOrders($conn, "SELECT orders.id, orders.payment_method, orders.created_at, users.name FROM orders LEFT JOIN users ON orders.user_id = users.id WHERE LOWER(orders.status) = 'pending' ORDER BY orders.created_at DESC");
$preparing_orders = safeFetchOrders($conn, "SELECT orders.id, orders.payment_method, orders.created_at, users.name FROM orders LEFT JOIN users ON orders.user_id = users.id WHERE LOWER(orders.status) = 'accepted' ORDER BY orders.updated_at DESC");
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Smart Canteen | Admin Control</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <script src="https://unpkg.com/lucide@latest"></script>
    <style>
        @import url('https://fonts.googleapis.com/css2?family=Outfit:wght@300;400;500;600;700;800&display=swap');
        body { font-family: 'Outfit', sans-serif; }
        .order-card { transition: all 0.4s cubic-bezier(0.4, 0, 0.2, 1); }
        .order-card.new-item { animation: slideIn 0.5s ease-out forwards; }
        @keyframes slideIn { from { opacity: 0; transform: translateY(20px); } to { opacity: 1; transform: translateY(0); } }
        .pulse-orange { animation: pulseOrange 2s infinite; }
        @keyframes pulseOrange { 0% { box-shadow: 0 0 0 0 rgba(249, 115, 22, 0.4); } 70% { box-shadow: 0 0 0 10px rgba(249, 115, 22, 0); } 100% { box-shadow: 0 0 0 0 rgba(249, 115, 22, 0); } }
    </style>
</head>
<body class="bg-gray-50 text-gray-900">

    <!-- Notification Toast -->
    <div id="notification-toast" class="fixed top-6 right-6 bg-slate-900 text-white px-6 py-4 rounded-[2rem] shadow-2xl transform translate-x-[120%] transition-transform duration-500 z-[100] flex items-center gap-4 border border-slate-700">
        <div class="w-10 h-10 bg-violet-600 rounded-full flex items-center justify-center">
            <i data-lucide="bell" class="w-5 h-5 text-white"></i>
        </div>
        <div>
            <p id="notification-text" class="font-black uppercase text-[10px] tracking-widest text-violet-400">Alert</p>
            <p class="font-bold text-sm">New Order Incoming!</p>
        </div>
    </div>

    <div class="flex h-screen overflow-hidden">
        
        <?php include "admin_sidebar.php"; ?>

        <main class="flex-1 flex flex-col overflow-hidden">
            <!-- Header -->
            <header class="flex items-center justify-between h-20 px-8 bg-white border-b border-gray-100">
                <div class="flex items-center gap-4">
                    <button onclick="toggleSidebar()" class="lg:hidden p-2 text-gray-400 hover:text-slate-900">
                        <i data-lucide="menu" class="w-6 h-6"></i>
                    </button>
                    <div>
                        <h2 class="text-xl font-black text-slate-900 tracking-tight uppercase">Dashboard</h2>
                        <div class="flex items-center gap-2 mt-0.5">
                            <span class="flex h-1.5 w-1.5 rounded-full bg-green-500"></span>
                            <span class="text-[9px] font-black uppercase text-gray-400 tracking-widest">Live Kitchen System</span>
                        </div>
                    </div>
                </div>
                <div class="flex items-center gap-4">
                    <button onclick="testSound()" class="hidden md:flex items-center gap-2 px-4 py-2 bg-gray-50 hover:bg-gray-100 rounded-full transition text-[10px] font-black uppercase tracking-widest text-gray-500">
                        <i data-lucide="volume-2" class="w-4 h-4"></i> Test Audio
                    </button>
                    <div class="h-10 w-10 rounded-2xl bg-violet-600 flex items-center justify-center text-white font-black text-xs">
                        AD
                    </div>
                </div>
            </header>

            <div class="flex-1 overflow-x-hidden overflow-y-auto p-8">
                <div class="max-w-7xl mx-auto space-y-10">
                    
                    <!-- Quick Actions Section -->
                    <section>
                        <h3 class="text-[10px] font-black text-gray-400 uppercase tracking-[0.3em] mb-6">Management Portal</h3>
                        <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                            <a href="add_item.php" class="group bg-white p-6 rounded-[2.5rem] border border-gray-100 shadow-sm hover:shadow-xl hover:border-violet-200 transition-all flex items-center gap-5">
                                <div class="w-14 h-14 bg-violet-50 text-violet-600 rounded-2xl flex items-center justify-center group-hover:bg-violet-600 group-hover:text-white transition-all">
                                    <i data-lucide="plus-circle" class="w-6 h-6"></i>
                                </div>
                                <div>
                                    <h4 class="font-black text-slate-800 uppercase text-xs tracking-widest">Add New Item</h4>
                                    <p class="text-[10px] text-gray-400 font-medium">Update the canteen menu</p>
                                </div>
                            </a>
                            <a href="manage_items.php" class="group bg-white p-6 rounded-[2.5rem] border border-gray-100 shadow-sm hover:shadow-xl hover:border-orange-200 transition-all flex items-center gap-5">
                                <div class="w-14 h-14 bg-orange-50 text-orange-600 rounded-2xl flex items-center justify-center group-hover:bg-orange-600 group-hover:text-white transition-all">
                                    <i data-lucide="layout-grid" class="w-6 h-6"></i>
                                </div>
                                <div>
                                    <h4 class="font-black text-slate-800 uppercase text-xs tracking-widest">Manage Menu</h4>
                                    <p class="text-[10px] text-gray-400 font-medium">Edit inventory & prices</p>
                                </div>
                            </a>
                            <a href="order_history.php" class="group bg-white p-6 rounded-[2.5rem] border border-gray-100 shadow-sm hover:shadow-xl hover:border-blue-200 transition-all flex items-center gap-5">
                                <div class="w-14 h-14 bg-blue-50 text-blue-600 rounded-2xl flex items-center justify-center group-hover:bg-blue-600 group-hover:text-white transition-all">
                                    <i data-lucide="clock" class="w-6 h-6"></i>
                                </div>
                                <div>
                                    <h4 class="font-black text-slate-800 uppercase text-xs tracking-widest">Order History</h4>
                                    <p class="text-[10px] text-gray-400 font-medium">View past performance</p>
                                </div>
                            </a>
                        </div>
                    </section>

                    <!-- Real-time Stats -->
                    <div class="grid grid-cols-2 lg:grid-cols-4 gap-6">
                        <div class="bg-slate-900 p-6 rounded-[2.5rem] text-white">
                            <p class="text-[9px] font-black text-slate-400 uppercase tracking-widest">Total Orders</p>
                            <h5 id="stat-total-orders" class="text-3xl font-black mt-1"><?= count($pending_orders) + count($preparing_orders) ?></h5>
                        </div>
                        <div class="bg-white p-6 rounded-[2.5rem] border border-gray-100 shadow-sm">
                            <p class="text-[9px] font-black text-orange-500 uppercase tracking-widest">Incoming</p>
                            <h5 id="stat-pending-orders" class="text-3xl font-black mt-1"><?= count($pending_orders) ?></h5>
                        </div>
                        <div class="bg-white p-6 rounded-[2.5rem] border border-gray-100 shadow-sm">
                            <p class="text-[9px] font-black text-blue-500 uppercase tracking-widest">Cooking</p>
                            <h5 id="stat-preparing-orders" class="text-3xl font-black mt-1"><?= count($preparing_orders) ?></h5>
                        </div>
                         <div class="bg-white p-6 rounded-[2.5rem] border border-gray-100 shadow-sm">
                            <p class="text-[9px] font-black text-green-500 uppercase tracking-widest">Success</p>
                            <h5 id="stat-total-users" class="text-3xl font-black mt-1">99%</h5>
                        </div>
                    </div>

                    <!-- Order Workflow -->
                    <div class="grid grid-cols-1 lg:grid-cols-2 gap-10">
                        
                        <!-- NEW ORDERS COLUMN -->
                        <div class="space-y-6">
                            <div class="flex items-center justify-between px-2">
                                <h3 class="text-xs font-black text-slate-800 uppercase tracking-[0.2em]">New Orders</h3>
                                <span class="px-3 py-1 bg-orange-100 text-orange-600 text-[9px] font-black rounded-full uppercase tracking-widest pulse-orange">Action Required</span>
                            </div>
                            
                            <div id="pending-container" class="space-y-4">
                                <?php if(empty($pending_orders)): ?>
                                    <div id="no-pending-msg" class="bg-white/50 border-2 border-dashed border-gray-100 rounded-[2.5rem] p-12 text-center">
                                        <i data-lucide="coffee" class="w-10 h-10 text-gray-200 mx-auto mb-4"></i>
                                        <p class="text-[10px] font-black text-gray-300 uppercase tracking-widest">No pending orders</p>
                                    </div>
                                <?php else: ?>
                                    <?php foreach($pending_orders as $order): renderOrderCard($order, 'pending'); endforeach; ?>
                                <?php endif; ?>
                            </div>
                        </div>

                        <!-- PREPARING COLUMN -->
                        <div class="space-y-6">
                            <div class="flex items-center justify-between px-2">
                                <h3 class="text-xs font-black text-slate-800 uppercase tracking-[0.2em]">In Preparation</h3>
                                <span class="px-3 py-1 bg-blue-100 text-blue-600 text-[9px] font-black rounded-full uppercase tracking-widest">Kitchen Active</span>
                            </div>

                            <div id="preparing-container" class="space-y-4">
                                <?php if(empty($preparing_orders)): ?>
                                    <div id="no-preparing-msg" class="bg-white/50 border-2 border-dashed border-gray-100 rounded-[2.5rem] p-12 text-center">
                                        <i data-lucide="flame" class="w-10 h-10 text-gray-200 mx-auto mb-4"></i>
                                        <p class="text-[10px] font-black text-gray-300 uppercase tracking-widest">Kitchen is currently idle</p>
                                    </div>
                                <?php else: ?>
                                    <?php foreach($preparing_orders as $order): renderOrderCard($order, 'preparing'); endforeach; ?>
                                <?php endif; ?>
                            </div>
                        </div>

                    </div>
                </div>
            </div>
        </main>
    </div>

    <?php
    /**
     * Helper to render order cards consistently
     */
    function renderOrderCard($order, $type) {
        $bgColor = $type == 'pending' ? 'bg-white' : 'bg-blue-50/50 border-blue-100';
        $btnColor = $type == 'pending' ? 'bg-violet-600 hover:bg-violet-700' : 'bg-blue-600 hover:bg-blue-700';
        $btnText = $type == 'pending' ? 'Send to Kitchen' : 'Mark as Ready';
        $nextStatus = $type == 'pending' ? 'accepted' : 'completed';
        ?>
        <div id="item-<?= $order['id'] ?>" class="order-card <?= $bgColor ?> p-6 rounded-[2rem] border shadow-sm relative overflow-hidden group">
            <div class="flex justify-between items-start mb-6">
                <div>
                    <h4 class="text-xl font-black text-slate-900 tracking-tighter">#<?= $order['id'] ?></h4>
                    <p class="text-[10px] font-black text-gray-400 uppercase tracking-widest"><?= htmlspecialchars($order['name'] ?? 'Guest') ?></p>
                </div>
                <div class="text-right">
                    <p class="text-[9px] font-black uppercase text-slate-400 tracking-widest mb-1"><?= date('h:i A', strtotime($order['created_at'])) ?></p>
                    <span class="text-[8px] font-black bg-slate-900 text-white px-2 py-0.5 rounded uppercase tracking-tighter italic"><?= $order['payment_method'] ?></span>
                </div>
            </div>

            <div class="bg-gray-50/80 rounded-2xl p-4 mb-6 space-y-2">
                <?php $total = 0; foreach($order['items'] as $item): $sub = $item['price'] * $item['quantity']; $total+=$sub; ?>
                    <div class="flex justify-between items-center text-xs">
                        <span class="font-bold text-slate-700"><?= $item['quantity'] ?>x <?= $item['name'] ?></span>
                        <span class="text-gray-400">₹<?= number_format($sub, 2) ?></span>
                    </div>
                <?php endforeach; ?>
                <div class="pt-3 border-t border-gray-200 flex justify-between items-center">
                    <span class="text-[10px] font-black uppercase tracking-widest text-slate-900">Total Bill</span>
                    <span class="text-sm font-black text-slate-900">₹<?= number_format($total, 2) ?></span>
                </div>
            </div>

            <div class="flex gap-2">
                <button onclick="updateOrder(<?= $order['id'] ?>, '<?= $nextStatus ?>')" class="flex-1 <?= $btnColor ?> text-white text-[10px] font-black uppercase tracking-widest py-4 rounded-2xl transition shadow-lg shadow-violet-200">
                    <?= $btnText ?>
                </button>
                <?php if($type == 'pending'): ?>
                    <button onclick="updateOrder(<?= $order['id'] ?>, 'rejected')" class="bg-red-50 text-red-500 hover:bg-red-100 px-4 py-4 rounded-2xl transition">
                        <i data-lucide="trash-2" class="w-4 h-4"></i>
                    </button>
                <?php endif; ?>
            </div>
        </div>
        <?php
    }
    ?>

    <script>
        lucide.createIcons();
        const notificationSound = new Audio('https://assets.mixkit.co/active_storage/sfx/2869/2869-preview.mp3');
        let knownOrders = new Set([<?php 
            $all_ids = array_merge(array_column($pending_orders, 'id'), array_column($preparing_orders, 'id'));
            echo implode(',', $all_ids);
        ?>]);

        function testSound() {
            notificationSound.play();
            showNotification("Audio Test Successful");
        }

        function showNotification(text) {
            const toast = document.getElementById('notification-toast');
            toast.querySelector('#notification-text').innerText = text;
            toast.classList.remove('translate-x-[120%]');
            setTimeout(() => toast.classList.add('translate-x-[120%]'), 4000);
        }

        async function updateOrder(id, status){
            const card = document.getElementById(`item-${id}`);
            card.style.opacity = "0.5";
            card.style.pointerEvents = "none";

            try {
                const res = await fetch("dashboard.php", {
                    method: "POST",
                    headers: { "Content-Type": "application/x-www-form-urlencoded" },
                    body: `order_id=${id}&status=${status}`
                });
                const data = await res.json();
                if(data.success){
                    card.style.transform = "scale(0.9) translateY(-20px)";
                    card.style.opacity = "0";
                    setTimeout(() => { card.remove(); fetchUpdates(); }, 400);
                }
            } catch (e) {
                alert("Network error. Please try again.");
                card.style.opacity = "1";
                card.style.pointerEvents = "auto";
            }
        }

        async function fetchUpdates() {
            try {
                const res = await fetch(`dashboard.php?action=fetch_updates&_=${Date.now()}`);
                const data = await res.json();
                
                // Update Stats
                document.getElementById('stat-total-orders').innerText = data.stats.total_orders;
                document.getElementById('stat-pending-orders').innerText = data.stats.pending_count;
                document.getElementById('stat-preparing-orders').innerText = data.stats.preparing_count;

                // Handle Empty States
                toggleEmptyState('pending', data.pending.length);
                toggleEmptyState('preparing', data.preparing.length);

                // Reconcile Pending Orders
                data.pending.forEach(order => {
                    if(!knownOrders.has(parseInt(order.id))) {
                        addNewOrderCard(order, 'pending');
                        notificationSound.play().catch(e => console.log("Audio block"));
                        showNotification("New Incoming Order!");
                        knownOrders.add(parseInt(order.id));
                    }
                });

                // Reconcile Preparing Orders (if moved from pending via other session)
                data.preparing.forEach(order => {
                    if(!document.getElementById(`item-${order.id}`)) {
                        addNewOrderCard(order, 'preparing');
                        knownOrders.add(parseInt(order.id));
                    }
                });

            } catch (e) {}
        }

        function toggleEmptyState(containerId, length) {
            const msg = document.getElementById(`no-${containerId}-msg`);
            if(length === 0) {
                if(!msg) {
                    const html = `<div id="no-${containerId}-msg" class="bg-white/50 border-2 border-dashed border-gray-100 rounded-[2.5rem] p-12 text-center">
                        <i data-lucide="${containerId === 'pending' ? 'coffee' : 'flame'}" class="w-10 h-10 text-gray-200 mx-auto mb-4"></i>
                        <p class="text-[10px] font-black text-gray-300 uppercase tracking-widest">No orders here</p>
                    </div>`;
                    document.getElementById(`${containerId}-container`).innerHTML = html;
                    lucide.createIcons();
                }
            } else if (msg) {
                msg.remove();
            }
        }

        function addNewOrderCard(order, type) {
            const container = document.getElementById(`${type}-container`);
            const div = document.createElement('div');
            div.id = `item-${order.id}`;
            div.className = `order-card new-item ${type === 'pending' ? 'bg-white' : 'bg-blue-50/50 border-blue-100'} p-6 rounded-[2rem] border shadow-sm relative overflow-hidden group`;
            
            let itemsHtml = '';
            let total = 0;
            order.items.forEach(item => {
                const sub = item.price * item.quantity;
                total += sub;
                itemsHtml += `<div class="flex justify-between items-center text-xs">
                    <span class="font-bold text-slate-700">${item.quantity}x ${item.name}</span>
                    <span class="text-gray-400">₹${sub.toFixed(2)}</span>
                </div>`;
            });

            const nextStatus = type === 'pending' ? 'accepted' : 'completed';
            const btnText = type === 'pending' ? 'Send to Kitchen' : 'Mark as Ready';
            const btnClass = type === 'pending' ? 'bg-violet-600 hover:bg-violet-700' : 'bg-blue-600 hover:bg-blue-700';

            div.innerHTML = `
                <div class="flex justify-between items-start mb-6">
                    <div>
                        <h4 class="text-xl font-black text-slate-900 tracking-tighter">#${order.id}</h4>
                        <p class="text-[10px] font-black text-gray-400 uppercase tracking-widest">${order.name || 'Guest'}</p>
                    </div>
                    <div class="text-right">
                        <p class="text-[9px] font-black uppercase text-slate-400 tracking-widest mb-1">JUST NOW</p>
                        <span class="text-[8px] font-black bg-slate-900 text-white px-2 py-0.5 rounded uppercase tracking-tighter italic">${order.payment_method}</span>
                    </div>
                </div>
                <div class="bg-gray-50/80 rounded-2xl p-4 mb-6 space-y-2">
                    ${itemsHtml}
                    <div class="pt-3 border-t border-gray-200 flex justify-between items-center">
                        <span class="text-[10px] font-black uppercase tracking-widest text-slate-900">Total Bill</span>
                        <span class="text-sm font-black text-slate-900">₹${total.toFixed(2)}</span>
                    </div>
                </div>
                <div class="flex gap-2">
                    <button onclick="updateOrder(${order.id}, '${nextStatus}')" class="flex-1 ${btnClass} text-white text-[10px] font-black uppercase tracking-widest py-4 rounded-2xl transition shadow-lg shadow-violet-200">
                        ${btnText}
                    </button>
                    ${type === 'pending' ? `<button onclick="updateOrder(${order.id}, 'rejected')" class="bg-red-50 text-red-500 hover:bg-red-100 px-4 py-4 rounded-2xl transition"><i data-lucide="trash-2" class="w-4 h-4"></i></button>` : ''}
                </div>
            `;
            container.prepend(div);
            lucide.createIcons();
        }

        setInterval(fetchUpdates, 4000);
    </script>
</body>
</html>