<?php
/**
 * add_item.php
 * Located in the admin folder.
 */
session_start();

if(!isset($_SESSION['admin_id'])){
    header("Location: login.php");
    exit();
}
// Database configuration path (assuming config is still outside admin folder)
include "../config/db.php";

$message = ""; $status = "";

if (isset($_POST['add'])) {
    $name = mysqli_real_escape_string($conn, $_POST['name']);
    $price = floatval($_POST['price']);
    $stock = intval($_POST['stock']);
    $desc = mysqli_real_escape_string($conn, $_POST['description']);

    if (isset($_FILES['image']) && $_FILES['image']['error'] === 0) {
        $ext = strtolower(pathinfo($_FILES['image']['name'], PATHINFO_EXTENSION));
        if (in_array($ext, ['jpg', 'jpeg', 'png', 'webp'])) {
            $image_name = "item_" . time() . "_" . bin2hex(random_bytes(4)) . "." . $ext;
            $target_dir = "../assets/images/";
            if (!is_dir($target_dir)) mkdir($target_dir, 0777, true);

            if (move_uploaded_file($_FILES['image']['tmp_name'], $target_dir . $image_name)) {
                $conn->begin_transaction();
                try {
                    $stmt1 = $conn->prepare("INSERT INTO menu_items (name, price, stock, description, available, image) VALUES (?, ?, ?, ?, 1, ?)");
                    $stmt1->bind_param("sdiss", $name, $price, $stock, $desc, $image_name);
                    $stmt1->execute();
                    $conn->commit();
                    $message = "New item added successfully!"; $status = "success";
                } catch (Exception $e) {
                    $conn->rollback(); @unlink($target_dir . $image_name);
                    $message = "Database error: " . $e->getMessage(); $status = "error";
                }
            } else { $message = "Upload failed."; $status = "error"; }
        } else { $message = "Invalid format."; $status = "error"; }
    } else { $message = "Image required."; $status = "error"; }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Add Menu Item | Smart Canteen</title>
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
                    <h2 class="text-xl font-bold text-gray-800 tracking-tight uppercase">New Menu Item</h2>
                </div>
            </header>

            <div class="flex-1 overflow-x-hidden overflow-y-auto bg-gray-50 p-6">
                <div class="max-w-4xl mx-auto">
                    
                    <?php if ($message): ?>
                        <div class="mb-8 p-5 rounded-[2rem] flex items-center border-2 <?php echo $status === 'success' ? 'bg-green-50 text-green-700 border-green-100' : 'bg-red-50 text-red-700 border-red-100'; ?>">
                            <i data-lucide="<?php echo $status === 'success' ? 'check-circle' : 'alert-circle'; ?>" class="w-6 h-6 mr-4"></i>
                            <p class="font-black uppercase text-xs tracking-widest"><?php echo $message; ?></p>
                        </div>
                    <?php endif; ?>

                    <form method="POST" enctype="multipart/form-data" class="grid grid-cols-1 lg:grid-cols-3 gap-8">
                        <div class="lg:col-span-2 space-y-6">
                            <div class="bg-white p-8 rounded-[2.5rem] shadow-sm border border-gray-100">
                                <h3 class="text-xs font-black text-violet-600 uppercase tracking-[0.2em] mb-6">Item Identity</h3>
                                <div class="space-y-5">
                                    <div>
                                        <label class="block text-[10px] font-black text-gray-400 uppercase tracking-widest mb-2 px-1">Food Name</label>
                                        <input type="text" name="name" required placeholder="e.g. Grilled Chicken Salad" class="w-full bg-gray-50 px-6 py-4 rounded-2xl border-2 border-transparent focus:border-violet-200 outline-none transition-all font-bold">
                                    </div>
                                    <div>
                                        <label class="block text-[10px] font-black text-gray-400 uppercase tracking-widest mb-2 px-1">Description</label>
                                        <textarea name="description" rows="4" class="w-full bg-gray-50 px-6 py-4 rounded-2xl border-2 border-transparent focus:border-violet-200 outline-none transition-all font-medium"></textarea>
                                    </div>
                                </div>
                            </div>
                            <div class="bg-white p-8 rounded-[2.5rem] shadow-sm border border-gray-100">
                                <h3 class="text-xs font-black text-orange-500 uppercase tracking-[0.2em] mb-6">Pricing & Inventory</h3>
                                <div class="grid grid-cols-2 gap-4">
                                    <div>
                                        <label class="block text-[10px] font-black text-gray-400 uppercase tracking-widest mb-2 px-1">Price (₹)</label>
                                        <input type="number" step="0.01" name="price" required class="w-full bg-gray-50 px-6 py-4 rounded-2xl outline-none font-black text-lg">
                                    </div>
                                    <div>
                                        <label class="block text-[10px] font-black text-gray-400 uppercase tracking-widest mb-2 px-1">Stock</label>
                                        <input type="number" name="stock" required class="w-full bg-gray-50 px-6 py-4 rounded-2xl outline-none font-black text-lg">
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="space-y-6">
                            <div class="bg-white p-8 rounded-[2.5rem] shadow-sm border border-gray-100 text-center">
                                <h3 class="text-xs font-black text-blue-500 uppercase tracking-[0.2em] mb-6">Visuals</h3>
                                <div class="relative group">
                                    <div id="image-preview" class="aspect-square rounded-[2rem] border-4 border-dashed border-gray-100 flex flex-col items-center justify-center p-6 text-center hover:border-violet-200 transition-colors bg-gray-50/50 overflow-hidden">
                                        <i data-lucide="image-plus" class="w-12 h-12 text-gray-200 mb-4"></i>
                                        <p class="text-[10px] font-black text-gray-400 uppercase tracking-widest">Upload <span class="text-violet-600">Image</span></p>
                                    </div>
                                    <input type="file" name="image" id="image-input" required accept="image/*" class="absolute inset-0 opacity-0 cursor-pointer">
                                </div>
                            </div>
                            <button type="submit" name="add" class="w-full bg-slate-900 hover:bg-violet-600 text-white py-6 rounded-[2.5rem] font-black uppercase text-xs tracking-[0.3em] transition-all shadow-xl">Publish Item</button>
                        </div>
                    </form>
                </div>
            </div>
        </main>
    </div>

    <script>
        lucide.createIcons();
        document.getElementById('image-input').addEventListener('change', function(e) {
            const preview = document.getElementById('image-preview');
            const file = e.target.files[0];
            if (file) {
                const reader = new FileReader();
                reader.onload = (event) => {
                    preview.innerHTML = `<img src="${event.target.result}" class="w-full h-full object-cover">`;
                    preview.classList.remove('border-dashed');
                }
                reader.readAsDataURL(file);
            }
        });
    </script>
</body>
</html>