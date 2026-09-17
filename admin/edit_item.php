<?php
session_start();
include "../config/db.php";

/** * 1. Security Check 
 * Ensures only admins can access the edit portal.
 */
if(!isset($_SESSION['admin_id'])){
    header("Location: login.php");
    exit();
}

$message = "";
$status = "";

/**
 * 2. Fetch Current Item Data
 */
if (!isset($_GET['id'])) {
    header("Location: manage_items.php");
    exit();
}

$item_id = intval($_GET['id']);
$stmt = $conn->prepare("SELECT * FROM menu_items WHERE id = ?");
$stmt->bind_param("i", $item_id);
$stmt->execute();
$item = $stmt->get_result()->fetch_assoc();

if (!$item) {
    die("Item not found in database.");
}

/**
 * 3. Update Logic
 */
if (isset($_POST['update'])) {
    $name = mysqli_real_escape_string($conn, $_POST['name']);
    $price = floatval($_POST['price']);
    $stock = intval($_POST['stock']);
    $desc = mysqli_real_escape_string($conn, $_POST['description']);
    $category = mysqli_real_escape_string($conn, $_POST['category']);
    
    $image_name = $item['image']; // Keep existing image name by default

    // Handle New Image Upload
    if (!empty($_FILES['image']['name'])) {
        $image_file = $_FILES['image'];
        $ext = pathinfo($image_file['name'], PATHINFO_EXTENSION);
        $image_name = "item_" . time() . "_" . bin2hex(random_bytes(4)) . "." . $ext;
        $target_dir = "../assets/images/";

        if (move_uploaded_file($image_file['tmp_name'], $target_dir . $image_name)) {
            // Update or Insert into item_images table if tracking multiple
            $check_img = $conn->query("SELECT id FROM item_images WHERE item_id = $item_id");
            if ($check_img->num_rows > 0) {
                $img_stmt = $conn->prepare("UPDATE item_images SET image = ? WHERE item_id = ?");
                $img_stmt->bind_param("si", $image_name, $item_id);
                $img_stmt->execute();
            } else {
                $img_stmt = $conn->prepare("INSERT INTO item_images (item_id, image) VALUES (?, ?)");
                $img_stmt->bind_param("is", $item_id, $image_name);
                $img_stmt->execute();
            }
        }
    }

    // Update main menu_items table
    $update_stmt = $conn->prepare("UPDATE menu_items SET name=?, price=?, stock=?, description=?, category=?, image=? WHERE id=?");
    $update_stmt->bind_param("sdisssi", $name, $price, $stock, $desc, $category, $image_name, $item_id);

    if ($update_stmt->execute()) {
        $message = "Menu item updated successfully!";
        $status = "success";
        
        // Refresh local variables for immediate display
        $item['name'] = $name;
        $item['price'] = $price;
        $item['stock'] = $stock;
        $item['description'] = $desc;
        $item['category'] = $category;
        $item['image'] = $image_name;
    } else {
        $message = "Database Error: " . $conn->error;
        $status = "error";
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Edit Item | Smart Canteen</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <script src="https://unpkg.com/lucide@latest"></script>
    <style>
        @import url('https://fonts.googleapis.com/css2?family=Outfit:wght@300;400;500;600;700;800&display=swap');
        body { font-family: 'Outfit', sans-serif; }
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
                        <h2 class="text-xl font-black text-slate-900 tracking-tight uppercase">Update Menu</h2>
                        <p class="text-[10px] text-slate-400 font-bold uppercase tracking-widest">Editing: <?= htmlspecialchars($item['name']) ?></p>
                    </div>
                </div>
            </header>

            <div class="flex-1 overflow-x-hidden overflow-y-auto bg-gray-50/50 p-6 lg:p-10">
                <div class="max-w-4xl mx-auto">
                    
                    <!-- Alert Notification -->
                    <?php if ($message): ?>
                        <div class="mb-8 p-5 rounded-[2rem] flex items-center border-2 <?php echo $status === 'success' ? 'bg-green-50 text-green-700 border-green-100' : 'bg-red-50 text-red-700 border-red-100'; ?> transition-all animate-in fade-in zoom-in duration-300">
                            <i data-lucide="<?php echo $status === 'success' ? 'check-circle' : 'alert-circle'; ?>" class="w-6 h-6 mr-4"></i>
                            <p class="font-black uppercase text-xs tracking-widest"><?php echo $message; ?></p>
                        </div>
                    <?php endif; ?>

                    <form method="POST" enctype="multipart/form-data" class="grid grid-cols-1 lg:grid-cols-3 gap-8">
                        
                        <!-- Main Details Column -->
                        <div class="lg:col-span-2 space-y-6">
                            <div class="bg-white p-8 rounded-[2.5rem] shadow-sm border border-gray-100">
                                <h3 class="text-xs font-black text-violet-600 uppercase tracking-[0.2em] mb-6 px-1">Core Identity</h3>
                                
                                <div class="space-y-5">
                                    <div>
                                        <label class="block text-[10px] font-black text-gray-400 uppercase tracking-widest mb-2 px-1">Item Name</label>
                                        <input type="text" name="name" required value="<?= htmlspecialchars($item['name']) ?>"
                                               class="w-full bg-gray-50 px-6 py-4 rounded-2xl border-2 border-transparent focus:border-violet-200 focus:bg-white outline-none transition-all font-bold text-gray-800">
                                    </div>

                                    <div>
                                        <label class="block text-[10px] font-black text-gray-400 uppercase tracking-widest mb-2 px-1">Category</label>
                                        <input type="text" name="category" required value="<?= htmlspecialchars($item['category'] ?: 'Snacks') ?>"
                                               class="w-full bg-gray-50 px-6 py-4 rounded-2xl border-2 border-transparent focus:border-violet-200 focus:bg-white outline-none transition-all font-bold text-gray-800">
                                    </div>

                                    <div>
                                        <label class="block text-[10px] font-black text-gray-400 uppercase tracking-widest mb-2 px-1">Description</label>
                                        <textarea name="description" rows="4" 
                                                  class="w-full bg-gray-50 px-6 py-4 rounded-2xl border-2 border-transparent focus:border-violet-200 focus:bg-white outline-none transition-all font-medium text-gray-600 resize-none"><?= htmlspecialchars($item['description']) ?></textarea>
                                    </div>
                                </div>
                            </div>

                            <div class="bg-white p-8 rounded-[2.5rem] shadow-sm border border-gray-100">
                                <h3 class="text-xs font-black text-orange-500 uppercase tracking-[0.2em] mb-6 px-1">Inventory Management</h3>
                                <div class="grid grid-cols-2 gap-4">
                                    <div>
                                        <label class="block text-[10px] font-black text-gray-400 uppercase tracking-widest mb-2 px-1">Price (₹)</label>
                                        <input type="number" step="0.01" name="price" required value="<?= $item['price'] ?>"
                                               class="w-full bg-gray-50 px-6 py-4 rounded-2xl border-2 border-transparent focus:border-violet-200 focus:bg-white outline-none transition-all font-black text-lg">
                                    </div>
                                    <div>
                                        <label class="block text-[10px] font-black text-gray-400 uppercase tracking-widest mb-2 px-1">Stock Level</label>
                                        <input type="number" name="stock" required value="<?= $item['stock'] ?>"
                                               class="w-full bg-gray-50 px-6 py-4 rounded-2xl border-2 border-transparent focus:border-violet-200 focus:bg-white outline-none transition-all font-black text-lg">
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Sidebar Actions Column -->
                        <div class="space-y-6">
                            <div class="bg-white p-8 rounded-[2.5rem] shadow-sm border border-gray-100 text-center">
                                <h3 class="text-xs font-black text-blue-500 uppercase tracking-[0.2em] mb-6">Product Media</h3>
                                
                                <div class="mb-6 aspect-square rounded-[2rem] overflow-hidden border-2 border-gray-100 shadow-inner group relative">
                                    <img src="../assets/images/<?= htmlspecialchars($item['image']) ?>" 
                                         class="w-full h-full object-cover transition-transform duration-500 group-hover:scale-110" 
                                         id="preview-img"
                                         onerror="this.src='https://placehold.co/400x400?text=No+Image'">
                                    <div class="absolute inset-0 bg-black/20 opacity-0 group-hover:opacity-100 transition-opacity pointer-events-none flex items-center justify-center">
                                        <i data-lucide="camera" class="text-white w-8 h-8"></i>
                                    </div>
                                </div>

                                <div class="relative group">
                                    <div class="py-4 rounded-2xl border-2 border-dashed border-gray-100 flex flex-col items-center justify-center text-center hover:border-violet-200 hover:bg-violet-50/30 transition-all cursor-pointer">
                                        <p class="text-[10px] font-black text-gray-400 uppercase tracking-widest">
                                            Replace <span class="text-violet-600">Image</span>
                                        </p>
                                    </div>
                                    <input type="file" name="image" class="absolute inset-0 opacity-0 cursor-pointer" onchange="previewFile(this)">
                                </div>
                                <p class="mt-4 text-[9px] text-gray-300 font-bold uppercase tracking-widest leading-relaxed">Leave empty to keep current image</p>
                            </div>

                            <button type="submit" name="update" class="w-full bg-slate-900 hover:bg-violet-600 text-white py-6 rounded-[2.5rem] font-black uppercase text-xs tracking-[0.3em] transition-all shadow-xl hover:shadow-violet-200 flex items-center justify-center">
                                <i data-lucide="save" class="w-4 h-4 mr-2"></i>
                                Commit Changes
                            </button>
                            
                            <a href="manage_items.php" class="block text-center text-[10px] font-black text-gray-400 uppercase tracking-widest hover:text-red-500 transition-colors">
                                Discard & Exit
                            </a>
                        </div>
                    </form>
                </div>
            </div>
        </main>
    </div>

    <script>
        lucide.createIcons();
        
        // Live image preview
        function previewFile(input) {
            if (input.files && input.files[0]) {
                var reader = new FileReader();
                reader.onload = function(e) {
                    document.getElementById('preview-img').src = e.target.result;
                }
                reader.readAsDataURL(input.files[0]);
            }
        }
    </script>
</body>
</html>