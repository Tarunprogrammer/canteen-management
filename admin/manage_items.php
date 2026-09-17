<?php
session_start();
include "../config/db.php";

/** * 1. Security Check 
 */
if(!isset($_SESSION['admin_id'])){
    header("Location: login.php");
    exit();
}

/**
 * 2. Handle Status Toggles via AJAX
 */
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action'])) {
    $item_id = intval($_POST['item_id']);
    
    if ($_POST['action'] === 'toggle_status') {
        $stmt = $conn->prepare("UPDATE menu_items SET available = 1 - available WHERE id = ?");
        $stmt->bind_param("i", $item_id);
        $stmt->execute();
        exit(); 
    }
}

/**
 * 3. Fetch All Items
 */
$query = "SELECT * FROM menu_items ORDER BY id DESC";
$result = $conn->query($query);

if (!$result) {
    die("Database Query Failed: " . $conn->error);
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Manage Menu | Smart Canteen</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <script src="https://unpkg.com/lucide@latest"></script>
    <style>
        @import url('https://fonts.googleapis.com/css2?family=Outfit:wght@300;400;500;600;700;800&display=swap');
        body { font-family: 'Outfit', sans-serif; }
        .table-container { scrollbar-width: none; }
        .table-container::-webkit-scrollbar { display: none; }
    </style>
</head>
<body class="bg-gray-50 text-gray-900">

    <div class="flex h-screen overflow-hidden">
        
        <!-- Integrated Sidebar -->
        <?php include "admin_sidebar.php"; ?>

        <!-- Main Content -->
        <main class="flex-1 flex flex-col overflow-hidden">
            
            <header class="flex items-center justify-between h-20 px-8 bg-white border-b border-gray-100">
                <div class="flex items-center">
                    <button onclick="toggleSidebar()" class="p-2 mr-4 text-gray-600 rounded-xl lg:hidden hover:bg-gray-100">
                        <i data-lucide="menu" class="w-6 h-6"></i>
                    </button>
                    <div>
                        <h2 class="text-xl font-black text-slate-900 tracking-tight uppercase">Inventory Control</h2>
                        <p class="text-[10px] text-slate-400 font-bold uppercase tracking-widest">Update item details and stock levels</p>
                    </div>
                </div>
                <a href="add_item.php" class="bg-violet-600 text-white px-6 py-3 rounded-2xl text-[10px] font-black uppercase tracking-widest hover:bg-violet-700 transition-all shadow-lg shadow-violet-200 flex items-center">
                    <i data-lucide="plus" class="w-4 h-4 mr-2"></i> New Product
                </a>
            </header>

            <div class="flex-1 overflow-x-hidden overflow-y-auto bg-gray-50/50 p-6">
                
                <div class="bg-white rounded-[2.5rem] shadow-sm border border-gray-100 overflow-hidden">
                    <div class="table-container overflow-x-auto">
                        <table class="w-full text-left border-collapse">
                            <thead>
                                <tr class="bg-gray-50/50 border-b border-gray-100">
                                    <th class="px-8 py-6 text-[10px] font-black text-gray-400 uppercase tracking-widest">Product Info</th>
                                    <th class="px-4 py-6 text-[10px] font-black text-gray-400 uppercase tracking-widest">Category</th>
                                    <th class="px-4 py-6 text-[10px] font-black text-gray-400 uppercase tracking-widest">Price</th>
                                    <th class="px-4 py-6 text-[10px] font-black text-gray-400 uppercase tracking-widest">Stock</th>
                                    <th class="px-4 py-6 text-[10px] font-black text-gray-400 uppercase tracking-widest">Visibility</th>
                                    <th class="px-8 py-6 text-[10px] font-black text-gray-400 uppercase tracking-widest text-right">Actions</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-gray-50">
                                <?php while($item = $result->fetch_assoc()): ?>
                                <tr class="hover:bg-violet-50/30 transition-colors group">
                                    <td class="px-8 py-6">
                                        <div class="flex items-center space-x-4">
                                            <div class="w-14 h-14 rounded-2xl bg-gray-100 overflow-hidden flex-shrink-0 border-2 border-white shadow-sm">
                                                <img src="../assets/images/<?= htmlspecialchars($item['image']) ?>" 
                                                     class="w-full h-full object-cover" 
                                                     alt="<?= htmlspecialchars($item['name']) ?>"
                                                     onerror="this.src='https://placehold.co/100x100?text=Food'">
                                            </div>
                                            <div>
                                                <h4 class="text-sm font-black text-gray-800 uppercase tracking-tight"><?= htmlspecialchars($item['name']) ?></h4>
                                                <p class="text-[9px] text-gray-400 font-medium line-clamp-1 max-w-[200px] uppercase tracking-wider"><?= htmlspecialchars($item['description'] ?: 'No description provided') ?></p>
                                            </div>
                                        </div>
                                    </td>
                                    <td class="px-4 py-6">
                                        <span class="text-[10px] font-black uppercase tracking-widest text-violet-500 bg-violet-50 px-3 py-1.5 rounded-xl">
                                            <?= htmlspecialchars($item['category'] ?: 'General') ?>
                                        </span>
                                    </td>
                                    <td class="px-4 py-6 font-black text-gray-700 text-sm">₹<?= number_format($item['price'], 2) ?></td>
                                    <td class="px-4 py-6">
                                        <span class="px-3 py-1.5 rounded-xl text-[10px] font-black uppercase <?= $item['stock'] < 10 ? 'bg-red-50 text-red-500' : 'bg-green-50 text-green-600' ?>">
                                            <?= $item['stock'] ?> Units
                                        </span>
                                    </td>
                                    <td class="px-4 py-6">
                                        <button onclick="toggleStatus(<?= $item['id'] ?>, this)" class="flex items-center space-x-2 px-3 py-2 rounded-xl border transition-all <?= $item['available'] ? 'bg-white border-green-100 text-green-600' : 'bg-gray-50 border-gray-200 text-gray-400' ?>">
                                            <div class="w-2 h-2 rounded-full <?= $item['available'] ? 'bg-green-500 animate-pulse' : 'bg-gray-300' ?>"></div>
                                            <span class="text-[9px] font-black uppercase tracking-widest"><?= $item['available'] ? 'Live' : 'Hidden' ?></span>
                                        </button>
                                    </td>
                                    <td class="px-8 py-6 text-right">
                                        <div class="flex items-center justify-end space-x-2">
                                            <a href="edit_item.php?id=<?= $item['id'] ?>" class="p-3 bg-gray-50 text-gray-400 hover:bg-violet-600 hover:text-white rounded-xl transition-all shadow-sm">
                                                <i data-lucide="edit-3" class="w-4 h-4"></i>
                                            </a>
                                            <button onclick="deleteItem(<?= $item['id'] ?>)" class="p-3 bg-gray-50 text-gray-400 hover:bg-red-500 hover:text-white rounded-xl transition-all shadow-sm">
                                                <i data-lucide="trash-2" class="w-4 h-4"></i>
                                            </button>
                                        </div>
                                    </td>
                                </tr>
                                <?php endwhile; ?>
                            </tbody>
                        </table>
                    </div>
                </div>

            </div>
        </main>
    </div>

    <script>
        lucide.createIcons();

        async function toggleStatus(id, btn) {
            const fd = new FormData();
            fd.append('action', 'toggle_status');
            fd.append('item_id', id);
            
            try {
                await fetch('manage_items.php', { method: 'POST', body: fd });
                
                const isLive = btn.innerText.trim() === 'LIVE';
                const statusDot = btn.querySelector('div');
                const statusText = btn.querySelector('span');

                if (isLive) {
                    btn.className = 'flex items-center space-x-2 px-3 py-2 rounded-xl border transition-all bg-gray-50 border-gray-200 text-gray-400';
                    statusDot.className = 'w-2 h-2 rounded-full bg-gray-300';
                    statusText.innerText = 'HIDDEN';
                } else {
                    btn.className = 'flex items-center space-x-2 px-3 py-2 rounded-xl border transition-all bg-white border-green-100 text-green-600';
                    statusDot.className = 'w-2 h-2 rounded-full bg-green-500 animate-pulse';
                    statusText.innerText = 'LIVE';
                }
            } catch (e) { console.error("Toggle failed", e); }
        }

        function deleteItem(id) {
            if (confirm("Move this item to trash? This will remove it from the menu permanently.")) {
                window.location.href = `delete_item.php?id=${id}`;
            }
        }
    </script>
</body>
</html>